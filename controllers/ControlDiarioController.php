<?php
class ControlDiarioController
{
    private ControlDiarioModel $modelo;

    public function __construct(ControlDiarioModel $modelo)
    {
        $this->modelo = $modelo;
    }

    public function registrarEntrada(array $datos, int $usuarioId): array
    {
        $texto = static function (string $campo) use ($datos): string {
            $valor = $datos[$campo] ?? '';
            return is_string($valor) ? trim($valor) : '';
        };

        $boleto = [
            'folio' => $texto('folio'),
            'operador_empresa' => $texto('operador_empresa'),
            'marca_trailer' => $texto('marca_trailer'),
            'placa_tracto' => strtoupper($texto('placa_tracto')),
            'placa_caja_1' => strtoupper($texto('placa_caja_1')),
            'placa_caja_2' => strtoupper($texto('placa_caja_2')),
            'tipo_unidad' => $texto('tipo_unidad'),
            'categoria_tarifa' => $texto('categoria_tarifa'),
        ];

        $errores = [];
        $longitudes = [
            'operador_empresa' => 150,
            'marca_trailer' => 80,
            'placa_tracto' => 20,
            'placa_caja_1' => 20,
            'placa_caja_2' => 20,
        ];

        $folioInicio = null;
        if ($boleto['folio'] !== '') {
            if (!preg_match('/^\d{4}$/', $boleto['folio'])) {
                $errores[] = 'El número de folio debe tener exactamente 4 dígitos.';
            } else {
                $folioInicio = (int) $boleto['folio'];
            }
        }
        $boleto['folio'] = $folioInicio;

        $marcasPermitidas = [
            'Freightliner',
            'Kenworth',
            'International',
            'Peterbilt',
            'Volvo',
            'Mack',
            'Western Star',
            'Scania',
            'Hino',
            'Otra',
        ];
        if (!in_array($boleto['marca_trailer'], $marcasPermitidas, true)) {
            $errores[] = 'Selecciona una marca válida.';
        }

        if ($boleto['operador_empresa'] === '' || $boleto['marca_trailer'] === '' || $boleto['placa_tracto'] === '') {
            $errores[] = 'Completa todos los campos obligatorios.';
        }

        $esTrailerSencillo = $boleto['tipo_unidad'] === 'TRAILER_SENCILLO';
        $esFull = $boleto['tipo_unidad'] === 'TRAILER_FULL_DOBLE';
        $esUnidadSimple = in_array($boleto['tipo_unidad'], ['CAMIONETA', 'TORTON'], true);
        $formatoCaja = '/^(?:\d{3}-[A-Z]{2}-\d|\d{2}-[A-Z]{2}-\d[A-Z])$/';

        if ($boleto['placa_tracto'] !== '' && !preg_match('/^\d{2}-[A-Z]{2}-\d[A-Z]$/', $boleto['placa_tracto'])) {
            $errores[] = 'La placa principal debe usar el formato 00-AA-0A.';
        }

        if ($esTrailerSencillo && $boleto['placa_caja_1'] === '') {
            $errores[] = 'La placa de caja es obligatoria para tráiler sencillo.';
        }
        if ($esTrailerSencillo || $esFull) {
            if ($boleto['placa_caja_1'] !== '' && !preg_match($formatoCaja, $boleto['placa_caja_1'])) {
                $errores[] = 'La placa de caja debe usar 000-AA-0 o 00-AA-0A.';
            }
        }
        if ($esFull && ($boleto['placa_caja_1'] === '' || $boleto['placa_caja_2'] === '')) {
            $errores[] = 'Las placas de las dos cajas son obligatorias para tráiler full.';
        }
        if ($esFull && $boleto['placa_caja_2'] !== '' && !preg_match($formatoCaja, $boleto['placa_caja_2'])) {
            $errores[] = 'La segunda placa de caja debe usar 000-AA-0 o 00-AA-0A.';
        }
        if ($esUnidadSimple) {
            $boleto['placa_caja_1'] = '';
            $boleto['placa_caja_2'] = '';
        }

        foreach ($longitudes as $campo => $maximo) {
            if (mb_strlen($boleto[$campo], 'UTF-8') > $maximo) {
                $errores[] = 'Uno de los campos supera la longitud permitida.';
                break;
            }
        }

        $categoriasPermitidas = [
            'CAMIONETA' => ['CASA', 'ESPECIAL'],
            'TRAILER_SENCILLO' => ['CASA', 'ESPECIAL'],
            'TORTON' => ['CASA', 'ESPECIAL'],
            'TRAILER_FULL_DOBLE' => ['CASA', 'ESPECIAL'],
        ];
        if (
            !isset($categoriasPermitidas[$boleto['tipo_unidad']])
            || !in_array($boleto['categoria_tarifa'], $categoriasPermitidas[$boleto['tipo_unidad']], true)
        ) {
            $errores[] = 'Selecciona un tipo de unidad y una tarifa válidos.';
        }

        if (!$esFull && $boleto['placa_caja_2'] !== '') {
            $errores[] = 'La placa de caja 2 solo aplica para full o doble remolque.';
        }

        if ($errores) {
            return ['tipo' => 'danger', 'mensaje' => implode(' ', array_unique($errores))];
        }

        try {
            $folio = $this->modelo->registrarEntrada($boleto, $usuarioId);
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                return ['tipo' => 'danger', 'mensaje' => 'Ese número de folio ya existe. Usa otro folio o déjalo vacío para continuar consecutivamente.'];
            }
            throw $e;
        }

        return [
            'tipo' => 'success',
            'mensaje' => 'Entrada registrada. El boleto quedó activo.',
            'folio' => $folio,
        ];
    }

    public function registrarSalida(array $datos, int $usuarioId): array
    {
        $valorFolio = $datos['folio'] ?? null;
        $folio = is_string($valorFolio) || is_int($valorFolio)
            ? filter_var($valorFolio, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($folio === false) {
            return ['tipo' => 'danger', 'mensaje' => 'Selecciona un folio válido para registrar la salida.'];
        }

        $fechaSalida = is_string($datos['fecha_salida'] ?? null) ? trim($datos['fecha_salida']) : '';
        $horaSalida = is_string($datos['hora_salida'] ?? null) ? trim($datos['hora_salida']) : '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaSalida)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaSalida)) {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa una fecha y hora de salida válidas.'];
        }

        $fechaHoraSalida = $fechaSalida . ' ' . $horaSalida . ':00';
        $salida = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fechaHoraSalida);
        $erroresFecha = DateTimeImmutable::getLastErrors();
        if (!$salida || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))) {
            return ['tipo' => 'danger', 'mensaje' => 'La fecha de salida no es válida.'];
        }

        try {
            $this->modelo->registrarSalida((int) $folio, $usuarioId, $fechaHoraSalida);
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        }

        return [
            'tipo' => 'success',
            'mensaje' => 'Salida y cobro registrados correctamente.',
            'folio' => (int) $folio,
        ];
    }

    public function guardarSaldoInicial(array $datos, int $usuarioId): array
    {
        $monto = $this->normalizarMonto($datos['saldo_inicial'] ?? null);
        if ($monto === null) {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa un saldo inicial válido, de hasta dos decimales.'];
        }

        try {
            $this->modelo->guardarSaldoInicial($monto, $usuarioId);
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        }

        return ['tipo' => 'success', 'mensaje' => 'El saldo inicial del fondo se guardó correctamente.'];
    }

    public function registrarEgresoFondo(array $datos, int $usuarioId): array
    {
        $conceptoValor = $datos['concepto'] ?? '';
        $concepto = is_string($conceptoValor) ? trim($conceptoValor) : '';
        if ($concepto === '') {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa el concepto del egreso.'];
        }
        if (mb_strlen($concepto, 'UTF-8') > 150) {
            return ['tipo' => 'danger', 'mensaje' => 'El concepto no puede superar los 150 caracteres.'];
        }

        $monto = $this->normalizarMonto($datos['monto'] ?? null);
        if ($monto === null || $monto === '0.00') {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa un monto mayor que cero, con hasta dos decimales.'];
        }

        $fechaValor = $datos['fecha_hora'] ?? '';
        $fechaLocal = is_string($fechaValor) ? trim($fechaValor) : '';
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $fechaLocal);
        $erroresFecha = DateTimeImmutable::getLastErrors();
        if (
            !$fecha
            || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))
            || $fecha->format('Y-m-d\TH:i') !== $fechaLocal
        ) {
            return ['tipo' => 'danger', 'mensaje' => 'Selecciona una fecha y hora válidas para el egreso.'];
        }

        try {
            $idEgreso = $this->modelo->registrarEgresoFondo(
                $concepto,
                $monto,
                $fecha->format('Y-m-d H:i:s'),
                $usuarioId
            );
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        }

        return [
            'tipo' => 'success',
            'mensaje' => 'Egreso registrado correctamente.',
            'id_egreso' => $idEgreso,
        ];
    }

    public function iniciarTurno(array $datos, int $usuarioId): array
    {
        $lamparas = $this->normalizarCantidad($datos['lamparas_recibidas'] ?? null);
        $radios = $this->normalizarCantidad($datos['radios_recibidos'] ?? null);
        if ($lamparas === null || $radios === null) {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa cantidades enteras no negativas para lámparas y radios.'];
        }

        try {
            $idTurno = $this->modelo->iniciarTurno([
                'lamparas' => $lamparas,
                'radios' => $radios,
            ], $usuarioId);
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        }

        return [
            'tipo' => 'success',
            'mensaje' => 'Turno iniciado y material recibido registrado.',
            'id_turno' => $idTurno,
        ];
    }

    public function cerrarTurno(array $datos, int $usuarioId): array
    {
        $lamparas = $this->normalizarCantidad($datos['lamparas_entregadas'] ?? null);
        $radios = $this->normalizarCantidad($datos['radios_entregados'] ?? null);
        if ($lamparas === null || $radios === null) {
            return ['tipo' => 'danger', 'mensaje' => 'Ingresa cantidades enteras no negativas para lámparas y radios.'];
        }

        $notaLamparasValor = $datos['nota_lamparas'] ?? '';
        $notaRadiosValor = $datos['nota_radios'] ?? '';
        $notaLamparas = is_string($notaLamparasValor) ? trim($notaLamparasValor) : '';
        $notaRadios = is_string($notaRadiosValor) ? trim($notaRadiosValor) : '';
        if (
            mb_strlen($notaLamparas, 'UTF-8') > 255
            || mb_strlen($notaRadios, 'UTF-8') > 255
        ) {
            return ['tipo' => 'danger', 'mensaje' => 'Cada nota puede tener hasta 255 caracteres.'];
        }

        try {
            $this->modelo->cerrarTurno([
                'lamparas' => $lamparas,
                'radios' => $radios,
                'nota_lamparas' => $notaLamparas,
                'nota_radios' => $notaRadios,
            ], $usuarioId);
        } catch (DomainException $e) {
            return ['tipo' => 'warning', 'mensaje' => $e->getMessage()];
        }

        return ['tipo' => 'success', 'mensaje' => 'Turno cerrado y material entregado registrado.'];
    }

    private function normalizarMonto($valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }

        $valor = trim($valor);
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $valor)) {
            return null;
        }

        [$pesos, $centavos] = array_pad(explode('.', $valor, 2), 2, '');
        $pesos = ltrim($pesos, '0');
        $pesos = $pesos === '' ? '0' : $pesos;
        if (strlen($pesos) > 8) {
            return null;
        }

        return $pesos . '.' . str_pad($centavos, 2, '0');
    }

    private function normalizarCantidad($valor): ?int
    {
        if (!is_string($valor) && !is_int($valor)) {
            return null;
        }

        $valor = trim((string) $valor);
        if ($valor === '' || !ctype_digit($valor)) {
            return null;
        }

        $cantidad = ltrim($valor, '0');
        $cantidad = $cantidad === '' ? '0' : $cantidad;
        if (
            strlen($cantidad) > 10
            || (strlen($cantidad) === 10 && strcmp($cantidad, '4294967295') > 0)
        ) {
            return null;
        }

        return (int) $cantidad;
    }
}
