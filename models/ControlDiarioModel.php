<?php
class ControlDiarioModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function tieneAcceso(int $usuarioId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM control_diario_usuarios_roles ur
             INNER JOIN control_diario_roles r ON r.id_rol = ur.rol_id
             WHERE ur.usuario_id = ? AND r.codigo IN ('SUPERPATRON', 'TRABAJADOR')"
        );
        $stmt->execute([$usuarioId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function obtenerTarifasActivas(): array
    {
        $stmt = $this->db->query(
            'SELECT id_tarifa, tipo_unidad, categoria, descripcion, monto
             FROM tarifas_diarias
             WHERE activa = 1
             ORDER BY tipo_unidad, categoria'
        );

        return $stmt->fetchAll();
    }

    public function obtenerResumenFondo(): ?array
    {
        $stmt = $this->db->query(
            'SELECT f.saldo_inicial,
                    COALESCE(SUM(e.monto), 0) AS egresos_acumulados,
                    f.saldo_inicial - COALESCE(SUM(e.monto), 0) AS saldo_actual
             FROM fondo_cambio f
             LEFT JOIN fondo_egresos e ON e.id_fondo = f.id_fondo
             WHERE f.id_fondo = 1
             GROUP BY f.saldo_inicial'
        );
        $fondo = $stmt->fetch();

        return $fondo ?: null;
    }

    public function listarEgresosFondoDelDia(string $fecha): array
    {
        [$inicio, $fin] = $this->obtenerRangoDia($fecha);
        $stmt = $this->db->prepare(
            'SELECT e.id_egreso, e.concepto, e.monto, e.fecha_hora, u.nombre AS usuario
             FROM fondo_egresos e
             INNER JOIN usuarios u ON u.id = e.usuario_id
             WHERE e.fecha_hora >= ? AND e.fecha_hora < ?
             ORDER BY e.fecha_hora DESC, e.id_egreso DESC'
        );
        $stmt->execute([$inicio, $fin]);

        return $stmt->fetchAll();
    }

    public function guardarSaldoInicial(string $monto, int $usuarioId): void
    {
        $centavosNuevoSaldo = $this->montoEnCentavos($monto);
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('SELECT * FROM fondo_cambio WHERE id_fondo = 1 FOR UPDATE');
            $stmt->execute();
            $anterior = $stmt->fetch() ?: null;

            if ($anterior !== null) {
                $stmt = $this->db->prepare(
                    'SELECT COALESCE(SUM(monto), 0) FROM fondo_egresos WHERE id_fondo = 1'
                );
                $stmt->execute();
                $egresosAcumulados = $this->montoEnCentavos((string) $stmt->fetchColumn());

                if ($centavosNuevoSaldo < $egresosAcumulados) {
                    throw new DomainException(
                        'El saldo inicial no puede ser menor que los egresos ya registrados.'
                    );
                }

                $stmt = $this->db->prepare(
                    'UPDATE fondo_cambio SET saldo_inicial = ? WHERE id_fondo = 1'
                );
                $stmt->execute([$monto]);
                $accion = 'MODIFICAR_SALDO_INICIAL';
            } else {
                $stmt = $this->db->prepare(
                    'INSERT INTO fondo_cambio (id_fondo, saldo_inicial, registrado_por)
                     VALUES (1, ?, ?)'
                );
                $stmt->execute([$monto, $usuarioId]);
                $accion = 'CONFIGURAR_SALDO_INICIAL';
            }

            $nuevo = $this->obtenerFilaFondo();
            if (!$nuevo) {
                throw new RuntimeException('No fue posible recuperar la configuración del fondo.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'fondo_cambio',
                '1',
                $accion,
                $anterior,
                $nuevo
            );

            $this->db->commit();
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function registrarEgresoFondo(
        string $concepto,
        string $monto,
        string $fechaHora,
        int $usuarioId
    ): int {
        $centavosEgreso = $this->montoEnCentavos($monto);
        if ($centavosEgreso <= 0) {
            throw new InvalidArgumentException('El monto del egreso debe ser mayor que cero.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('SELECT saldo_inicial FROM fondo_cambio WHERE id_fondo = 1 FOR UPDATE');
            $stmt->execute();
            $saldoInicial = $stmt->fetchColumn();

            if ($saldoInicial === false) {
                throw new DomainException('Configura primero el saldo inicial del fondo.');
            }

            $stmt = $this->db->prepare(
                'SELECT COALESCE(SUM(monto), 0) FROM fondo_egresos WHERE id_fondo = 1'
            );
            $stmt->execute();
            $egresosAcumulados = $this->montoEnCentavos((string) $stmt->fetchColumn());
            $saldoDisponible = $this->montoEnCentavos((string) $saldoInicial) - $egresosAcumulados;

            if ($centavosEgreso > $saldoDisponible) {
                throw new DomainException(
                    'El egreso supera el saldo disponible de $' . $this->formatearCentavos($saldoDisponible) . '.'
                );
            }

            $stmt = $this->db->prepare(
                'INSERT INTO fondo_egresos (id_fondo, concepto, monto, fecha_hora, usuario_id)
                 VALUES (1, ?, ?, ?, ?)'
            );
            $stmt->execute([$concepto, $monto, $fechaHora, $usuarioId]);
            $idEgreso = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare('SELECT * FROM fondo_egresos WHERE id_egreso = ?');
            $stmt->execute([$idEgreso]);
            $nuevo = $stmt->fetch();
            if (!$nuevo) {
                throw new RuntimeException('No fue posible recuperar el egreso recién registrado.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'fondo_egreso',
                (string) $idEgreso,
                'REGISTRAR_EGRESO',
                null,
                $nuevo
            );

            $this->db->commit();
            return $idEgreso;
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function obtenerTurnoAbierto(): ?array
    {
        $stmt = $this->db->query(
            "SELECT t.id_turno, t.fecha_hora_inicio, t.usuario_inicio, u.nombre AS usuario_inicio_nombre
             FROM turnos t
             INNER JOIN usuarios u ON u.id = t.usuario_inicio
             WHERE t.estado = 'ABIERTO'
             ORDER BY t.id_turno DESC
             LIMIT 1"
        );
        $turno = $stmt->fetch();
        if (!$turno) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT m.nombre, tm.cantidad_recibida, tm.cantidad_entregada, tm.nota
             FROM turno_materiales tm
             INNER JOIN materiales m ON m.id_material = tm.id_material
             WHERE tm.id_turno = ?
             ORDER BY m.nombre'
        );
        $stmt->execute([(int) $turno['id_turno']]);
        $turno['materiales'] = [];
        foreach ($stmt->fetchAll() as $material) {
            $clave = $this->claveMaterial((string) $material['nombre']);
            if ($clave !== null) {
                $turno['materiales'][$clave] = $material;
            }
        }

        return $turno;
    }

    public function iniciarTurno(array $cantidadesRecibidas, int $usuarioId): int
    {
        $this->db->beginTransaction();

        try {
            $materiales = $this->obtenerMaterialesConfigurados(true);
            foreach (['lamparas', 'radios'] as $clave) {
                if (!isset($materiales[$clave])) {
                    throw new DomainException('No están configurados los materiales lámparas y radios.');
                }
            }

            $stmt = $this->db->query(
                "SELECT id_turno FROM turnos WHERE estado = 'ABIERTO' ORDER BY id_turno DESC LIMIT 1 FOR UPDATE"
            );
            if ($stmt->fetchColumn() !== false) {
                throw new DomainException('Ya hay un turno abierto. Ciérralo antes de iniciar otro.');
            }

            $stmt = $this->db->prepare('INSERT INTO turnos (usuario_inicio) VALUES (?)');
            $stmt->execute([$usuarioId]);
            $idTurno = (int) $this->db->lastInsertId();

            foreach (['lamparas', 'radios'] as $clave) {
                $cantidad = $cantidadesRecibidas[$clave] ?? null;
                if (!is_int($cantidad) || $cantidad < 0 || $cantidad > 4294967295) {
                    throw new InvalidArgumentException('Las cantidades recibidas deben ser números enteros no negativos.');
                }

                $idMaterial = (int) $materiales[$clave]['id_material'];
                $stmt = $this->db->prepare(
                    'INSERT INTO turno_materiales (id_turno, id_material, cantidad_recibida)
                     VALUES (?, ?, ?)'
                );
                $stmt->execute([$idTurno, $idMaterial, $cantidad]);

                $stmt = $this->db->prepare(
                    'SELECT * FROM turno_materiales WHERE id_turno = ? AND id_material = ?'
                );
                $stmt->execute([$idTurno, $idMaterial]);
                $nuevoMaterial = $stmt->fetch();
                if (!$nuevoMaterial) {
                    throw new RuntimeException('No fue posible recuperar el inventario del turno.');
                }

                $this->registrarAuditoria(
                    $usuarioId,
                    'turno_material',
                    $idTurno . ':' . $idMaterial,
                    'REGISTRAR_MATERIAL_INICIO',
                    null,
                    $nuevoMaterial
                );
            }

            $stmt = $this->db->prepare('SELECT * FROM turnos WHERE id_turno = ?');
            $stmt->execute([$idTurno]);
            $nuevoTurno = $stmt->fetch();
            if (!$nuevoTurno) {
                throw new RuntimeException('No fue posible recuperar el turno recién iniciado.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'turno',
                (string) $idTurno,
                'INICIAR_TURNO',
                null,
                $nuevoTurno
            );

            $this->db->commit();
            return $idTurno;
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function cerrarTurno(array $cantidadesEntregadas, int $usuarioId): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->query(
                "SELECT * FROM turnos WHERE estado = 'ABIERTO' ORDER BY id_turno DESC LIMIT 1 FOR UPDATE"
            );
            $turno = $stmt->fetch();
            if (!$turno) {
                throw new DomainException('No hay un turno abierto para cerrar.');
            }

            $idTurno = (int) $turno['id_turno'];
            $stmt = $this->db->prepare(
                'SELECT tm.*, m.nombre
                 FROM turno_materiales tm
                 INNER JOIN materiales m ON m.id_material = tm.id_material
                 WHERE tm.id_turno = ?
                 ORDER BY tm.id_material
                 FOR UPDATE'
            );
            $stmt->execute([$idTurno]);
            $materiales = [];
            foreach ($stmt->fetchAll() as $material) {
                $clave = $this->claveMaterial((string) $material['nombre']);
                if ($clave !== null) {
                    $materiales[$clave] = $material;
                }
            }

            foreach (['lamparas', 'radios'] as $clave) {
                if (!isset($materiales[$clave])) {
                    throw new RuntimeException('El turno no tiene registrado todo el material requerido.');
                }

                $cantidadEntregada = $cantidadesEntregadas[$clave] ?? null;
                if (!is_int($cantidadEntregada) || $cantidadEntregada < 0 || $cantidadEntregada > 4294967295) {
                    throw new InvalidArgumentException('Las cantidades entregadas deben ser números enteros no negativos.');
                }

                $notaValor = $cantidadesEntregadas['nota_' . $clave] ?? null;
                $nota = is_string($notaValor) ? trim($notaValor) : '';
                if (mb_strlen($nota, 'UTF-8') > 255) {
                    throw new InvalidArgumentException('Cada nota puede tener hasta 255 caracteres.');
                }
                if ($cantidadEntregada !== (int) $materiales[$clave]['cantidad_recibida'] && $nota === '') {
                    throw new DomainException(
                        'Agrega una nota para explicar la diferencia en ' . $materiales[$clave]['nombre'] . '.'
                    );
                }

                $idMaterial = (int) $materiales[$clave]['id_material'];
                $stmt = $this->db->prepare(
                    'UPDATE turno_materiales
                     SET cantidad_entregada = ?, nota = ?
                     WHERE id_turno = ? AND id_material = ?'
                );
                $stmt->execute([
                    $cantidadEntregada,
                    $nota === '' ? null : $nota,
                    $idTurno,
                    $idMaterial,
                ]);

                $stmt = $this->db->prepare(
                    'SELECT * FROM turno_materiales WHERE id_turno = ? AND id_material = ?'
                );
                $stmt->execute([$idTurno, $idMaterial]);
                $nuevoMaterial = $stmt->fetch();
                if (!$nuevoMaterial) {
                    throw new RuntimeException('No fue posible recuperar el material actualizado.');
                }

                $this->registrarAuditoria(
                    $usuarioId,
                    'turno_material',
                    $idTurno . ':' . $idMaterial,
                    'REGISTRAR_MATERIAL_CIERRE',
                    $materiales[$clave],
                    $nuevoMaterial
                );
            }

            $stmt = $this->db->prepare(
                "UPDATE turnos
                 SET fecha_hora_fin = CURRENT_TIMESTAMP, usuario_cierre = ?, estado = 'CERRADO'
                 WHERE id_turno = ? AND estado = 'ABIERTO'"
            );
            $stmt->execute([$usuarioId, $idTurno]);
            if ($stmt->rowCount() !== 1) {
                throw new DomainException('El turno ya fue cerrado por otro usuario. Actualiza la página.');
            }

            $stmt = $this->db->prepare('SELECT * FROM turnos WHERE id_turno = ?');
            $stmt->execute([$idTurno]);
            $nuevoTurno = $stmt->fetch();
            if (!$nuevoTurno) {
                throw new RuntimeException('No fue posible recuperar el turno cerrado.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'turno',
                (string) $idTurno,
                'CERRAR_TURNO',
                $turno,
                $nuevoTurno
            );

            $this->db->commit();
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function registrarEntrada(array $boleto, int $usuarioId): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT id_tarifa, descripcion, monto
                 FROM tarifas_diarias
                 WHERE tipo_unidad = ? AND categoria = ? AND activa = 1
                 FOR SHARE"
            );
            $stmt->execute([$boleto['tipo_unidad'], $boleto['categoria_tarifa']]);
            $tarifa = $stmt->fetch();

            if (!$tarifa) {
                throw new DomainException('No hay una tarifa activa para el tipo de unidad seleccionado.');
            }

            $stmt = $this->db->prepare(
                'SELECT COUNT(*)
                 FROM camiones c
                 INNER JOIN pensionados p ON p.id_camion = c.id_camion
                 WHERE UPPER(TRIM(c.placa)) = ?'
            );
            $stmt->execute([$boleto['placa_tracto']]);

            if ((int) $stmt->fetchColumn() > 0) {
                throw new DomainException('Esta placa pertenece a un camión pensionado; no se puede registrar como boleto.');
            }

            $placaCaja1 = $boleto['placa_caja_1'] !== '' ? $boleto['placa_caja_1'] : null;
            $placaCaja2 = $boleto['placa_caja_2'] !== '' ? $boleto['placa_caja_2'] : null;

            if (!empty($boleto['folio'])) {
                $stmt = $this->db->prepare(
                    'INSERT INTO boletos_diarios (
                    folio,
                    operador_empresa,
                    marca_trailer,
                    placa_tracto,
                    placa_caja_1,
                    placa_caja_2,
                    tipo_unidad,
                    categoria_tarifa,
                    id_tarifa,
                    descripcion_tarifa_aplicada,
                    monto_tarifa_aplicada
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $parametros = [
                    (int) $boleto['folio'],
                    $boleto['operador_empresa'],
                    $boleto['marca_trailer'],
                    $boleto['placa_tracto'],
                    $placaCaja1,
                    $placaCaja2,
                    $boleto['tipo_unidad'],
                    $boleto['categoria_tarifa'],
                    $tarifa['id_tarifa'],
                    $tarifa['descripcion'],
                    $tarifa['monto'],
                ];
            } else {
                $stmt = $this->db->prepare(
                    'INSERT INTO boletos_diarios (
                    operador_empresa,
                    marca_trailer,
                    placa_tracto,
                    placa_caja_1,
                    placa_caja_2,
                    tipo_unidad,
                    categoria_tarifa,
                    id_tarifa,
                    descripcion_tarifa_aplicada,
                    monto_tarifa_aplicada
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $parametros = [
                    $boleto['operador_empresa'],
                    $boleto['marca_trailer'],
                    $boleto['placa_tracto'],
                    $placaCaja1,
                    $placaCaja2,
                    $boleto['tipo_unidad'],
                    $boleto['categoria_tarifa'],
                    $tarifa['id_tarifa'],
                    $tarifa['descripcion'],
                    $tarifa['monto'],
                ];
            }
            $stmt->execute($parametros);

            $folio = !empty($boleto['folio'])
                ? (int) $boleto['folio']
                : (int) $this->db->lastInsertId();
            $registro = $this->obtenerBoleto($folio);
            if (!$registro) {
                throw new RuntimeException('No fue posible recuperar el boleto recién registrado.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'boleto',
                (string) $folio,
                'REGISTRAR_ENTRADA',
                null,
                $registro
            );

            $this->db->commit();
            return $folio;
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function registrarSalida(int $folio, int $usuarioId, string $fechaHoraSalida): void
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM boletos_diarios WHERE folio = ? FOR UPDATE'
            );
            $stmt->execute([$folio]);
            $anterior = $stmt->fetch();

            if (!$anterior) {
                throw new DomainException('No se encontró el boleto solicitado.');
            }
            if ($anterior['estado'] !== 'ACTIVO') {
                throw new DomainException('Este boleto ya fue cobrado y no tiene una salida pendiente.');
            }

            $entrada = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $anterior['fecha_hora_entrada']);
            $salida = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fechaHoraSalida);
            if (!$entrada || !$salida) {
                throw new DomainException('La fecha de salida no es válida.');
            }
            if ($salida < $entrada) {
                throw new DomainException('La salida no puede ser anterior a la entrada del boleto.');
            }

            $segundos = $salida->getTimestamp() - $entrada->getTimestamp();
            $periodos = max(1, (int) ceil($segundos / 86400));
            $tarifaBaseCentavos = $this->montoEnCentavos((string) number_format((float) $anterior['monto_tarifa_aplicada'], 2, '.', ''));
            $montoCobradoCentavos = $tarifaBaseCentavos * $periodos;
            $montoCobrado = $this->formatearCentavos($montoCobradoCentavos);

            $stmt = $this->db->prepare(
                "UPDATE boletos_diarios
                 SET estado = 'COBRADO',
                     fecha_hora_salida = ?,
                     monto_tarifa_aplicada = ?,
                     descripcion_tarifa_aplicada = CONCAT(descripcion_tarifa_aplicada, ' (', ?, ' período(s) de 24 h)')
                 WHERE folio = ? AND estado = 'ACTIVO'"
            );
            $stmt->execute([$fechaHoraSalida, $montoCobrado, (string) $periodos, $folio]);

            if ($stmt->rowCount() !== 1) {
                throw new DomainException('No se pudo registrar la salida; actualiza la página e inténtalo nuevamente.');
            }

            $nuevo = $this->obtenerBoleto($folio);
            if (!$nuevo) {
                throw new RuntimeException('No fue posible recuperar el boleto actualizado.');
            }

            $this->registrarAuditoria(
                $usuarioId,
                'boleto',
                (string) $folio,
                'REGISTRAR_SALIDA_Y_COBRO',
                $anterior,
                $nuevo
            );

            $this->db->commit();
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    public function listarActivos(string $busqueda = ''): array
    {
        $sql = "SELECT * FROM boletos_diarios WHERE estado = 'ACTIVO'";
        $parametros = [];

        if ($busqueda !== '') {
            $sql .= ' AND (placa_tracto LIKE ? OR placa_caja_1 LIKE ? OR placa_caja_2 LIKE ?';
            $comodin = '%' . $busqueda . '%';
            $parametros = [$comodin, $comodin, $comodin];

            if (ctype_digit($busqueda)) {
                $sql .= ' OR folio = ?';
                $parametros[] = (int) $busqueda;
            }

            $sql .= ')';
        }

        $sql .= ' ORDER BY fecha_hora_entrada DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function listarDelDia(string $fecha, string $busqueda = ''): array
    {
        $inicio = new DateTimeImmutable($fecha . ' 00:00:00');
        $fin = $inicio->modify('+1 day');
        $sql = 'SELECT * FROM boletos_diarios
                WHERE fecha_hora_entrada >= ? AND fecha_hora_entrada < ?';
        $parametros = [
            $inicio->format('Y-m-d H:i:s'),
            $fin->format('Y-m-d H:i:s'),
        ];

        if ($busqueda !== '') {
            $sql .= ' AND (placa_tracto LIKE ? OR placa_caja_1 LIKE ? OR placa_caja_2 LIKE ?';
            $comodin = '%' . $busqueda . '%';
            $parametros[] = $comodin;
            $parametros[] = $comodin;
            $parametros[] = $comodin;

            if (ctype_digit($busqueda)) {
                $sql .= ' OR folio = ?';
                $parametros[] = (int) $busqueda;
            }

            $sql .= ')';
        }

        $sql .= ' ORDER BY fecha_hora_entrada DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function obtenerReporteDiario(string $fecha): array
    {
        $inicioDia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        $erroresFecha = DateTimeImmutable::getLastErrors();
        if (
            !$inicioDia
            || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))
            || $inicioDia->format('Y-m-d') !== $fecha
        ) {
            throw new InvalidArgumentException('La fecha del reporte no es válida.');
        }

        $finDia = $inicioDia->modify('+1 day');
        $inicio = $inicioDia->format('Y-m-d H:i:s');
        $fin = $finDia->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare(
            'SELECT * FROM boletos_diarios
             WHERE fecha_hora_entrada >= ? AND fecha_hora_entrada < ?
             ORDER BY fecha_hora_entrada'
        );
        $stmt->execute([$inicio, $fin]);
        $entradas = $stmt->fetchAll();

        $stmt = $this->db->prepare(
            "SELECT * FROM boletos_diarios
             WHERE estado = 'COBRADO'
               AND fecha_hora_salida >= ? AND fecha_hora_salida < ?
             ORDER BY fecha_hora_salida"
        );
        $stmt->execute([$inicio, $fin]);
        $salidas = $stmt->fetchAll();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*), COALESCE(SUM(monto_tarifa_aplicada), 0)
             FROM boletos_diarios
             WHERE estado = 'COBRADO'
               AND fecha_hora_salida >= ? AND fecha_hora_salida < ?"
        );
        $stmt->execute([$inicio, $fin]);
        [$boletosCobrados, $totalCobrado] = $stmt->fetch(PDO::FETCH_NUM);

        $stmt = $this->db->prepare(
            "SELECT descripcion_tarifa_aplicada, tipo_unidad, categoria_tarifa,
                    COUNT(*) AS cantidad, SUM(monto_tarifa_aplicada) AS total
             FROM boletos_diarios
             WHERE estado = 'COBRADO'
               AND fecha_hora_salida >= ? AND fecha_hora_salida < ?
             GROUP BY descripcion_tarifa_aplicada, tipo_unidad, categoria_tarifa
             ORDER BY descripcion_tarifa_aplicada"
        );
        $stmt->execute([$inicio, $fin]);
        $desgloseTarifas = $stmt->fetchAll();

        $boletosActivos = (int) $this->db->query(
            "SELECT COUNT(*) FROM boletos_diarios WHERE estado = 'ACTIVO'"
        )->fetchColumn();

        $fondo = $this->obtenerResumenFondo();
        $egresosFondo = $this->listarEgresosFondoDelDia($fecha);

        $stmt = $this->db->prepare(
            'SELECT t.id_turno, t.fecha_hora_inicio, t.fecha_hora_fin, t.estado,
                    ui.nombre AS usuario_inicio, uc.nombre AS usuario_cierre,
                    m.nombre AS material, tm.cantidad_recibida, tm.cantidad_entregada,
                    tm.nota,
                    CASE
                        WHEN tm.cantidad_entregada IS NOT NULL
                         AND tm.cantidad_entregada <> tm.cantidad_recibida THEN 1
                        ELSE 0
                    END AS alerta
             FROM turnos t
             INNER JOIN usuarios ui ON ui.id = t.usuario_inicio
             LEFT JOIN usuarios uc ON uc.id = t.usuario_cierre
             LEFT JOIN turno_materiales tm ON tm.id_turno = t.id_turno
             LEFT JOIN materiales m ON m.id_material = tm.id_material
             WHERE t.fecha_hora_inicio < ?
               AND (t.fecha_hora_fin IS NULL OR t.fecha_hora_fin >= ?)
             ORDER BY t.fecha_hora_inicio, t.id_turno, m.nombre'
        );
        $stmt->execute([$fin, $inicio]);
        $materialesTurno = $stmt->fetchAll();

        return [
            'boletos_activos' => $boletosActivos,
            'boletos_cobrados' => (int) $boletosCobrados,
            'total_cobrado' => (float) $totalCobrado,
            'entradas' => $entradas,
            'salidas' => $salidas,
            'desglose_tarifas' => $desgloseTarifas,
            'fondo' => $fondo,
            'egresos_fondo' => $egresosFondo,
            'materiales_turno' => $materialesTurno,
        ];
    }

    public function obtenerBoleto(int $folio): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM boletos_diarios WHERE folio = ?');
        $stmt->execute([$folio]);
        $boleto = $stmt->fetch();

        return $boleto ?: null;
    }

    public function obtenerSiguienteFolio(): int
    {
        $maximo = (int) $this->db->query(
            'SELECT COALESCE(MAX(folio), 0) FROM boletos_diarios'
        )->fetchColumn();

        return $maximo >= 9999 ? 9999 : $maximo + 1;
    }

    private function obtenerRangoDia(string $fecha): array
    {
        $inicioDia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        $erroresFecha = DateTimeImmutable::getLastErrors();
        if (
            !$inicioDia
            || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))
            || $inicioDia->format('Y-m-d') !== $fecha
        ) {
            throw new InvalidArgumentException('La fecha indicada no es válida.');
        }

        return [
            $inicioDia->format('Y-m-d H:i:s'),
            $inicioDia->modify('+1 day')->format('Y-m-d H:i:s'),
        ];
    }

    private function obtenerFilaFondo(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM fondo_cambio WHERE id_fondo = 1');
        $fondo = $stmt->fetch();

        return $fondo ?: null;
    }

    private function obtenerMaterialesConfigurados(bool $bloquear): array
    {
        $sql = 'SELECT id_material, nombre FROM materiales WHERE activo = 1 ORDER BY id_material';
        if ($bloquear) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->db->query($sql);
        $materiales = [];
        foreach ($stmt->fetchAll() as $material) {
            $clave = $this->claveMaterial((string) $material['nombre']);
            if ($clave !== null) {
                $materiales[$clave] = $material;
            }
        }

        return $materiales;
    }

    private function claveMaterial(string $nombre): ?string
    {
        $nombre = strtr(
            mb_strtoupper(trim($nombre), 'UTF-8'),
            ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']
        );

        if ($nombre === 'LAMPARAS') {
            return 'lamparas';
        }
        if ($nombre === 'RADIOS') {
            return 'radios';
        }

        return null;
    }

    private function montoEnCentavos(string $monto): int
    {
        if (!preg_match('/^\d{1,8}\.\d{2}$/', $monto)) {
            throw new InvalidArgumentException('El monto debe tener formato decimal válido.');
        }

        [$pesos, $centavos] = explode('.', $monto, 2);
        return ((int) $pesos * 100) + (int) $centavos;
    }

    private function formatearCentavos(int $monto): string
    {
        $signo = $monto < 0 ? '-' : '';
        $monto = abs($monto);

        return $signo . intdiv($monto, 100) . '.' . str_pad((string) ($monto % 100), 2, '0', STR_PAD_LEFT);
    }

    private function registrarAuditoria(
        int $usuarioId,
        string $entidad,
        string $registroId,
        string $accion,
        ?array $anterior,
        ?array $nuevo
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO control_diario_auditoria (
                usuario_id, entidad, registro_id, accion,
                valores_anteriores, valores_nuevos
            ) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $usuarioId,
            $entidad,
            $registroId,
            $accion,
            $anterior === null ? null : $this->codificarJson($anterior),
            $nuevo === null ? null : $this->codificarJson($nuevo),
        ]);
    }

    private function codificarJson(array $datos): string
    {
        $json = json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($json)) {
            throw new RuntimeException('No fue posible preparar el registro de auditoría.');
        }

        return $json;
    }
}
