<?php
class PanelModel
{
    public function __construct(private PDO $db)
    {
    }

    public function getStats(): array
    {
        $camiones = $this->tableExists('camiones')
            ? (int) $this->db->query('SELECT COUNT(*) FROM camiones')->fetchColumn()
            : 0;

        $espaciosDisponibles = $this->tableExists('espacios')
            ? (int) $this->db->query(
                "SELECT COUNT(*) FROM espacios WHERE estado IN ('libre', 'disponible')"
            )->fetchColumn()
            : 0;

        $pagosPensionados = 0;
        if ($this->tableExists('pagos')) {
            if ($this->tableExists('reservas')) {
                $pagosPensionados = (int) $this->db->query(
                    "SELECT COUNT(DISTINCT p.id_pago)
                     FROM pagos p
                     INNER JOIN reservas r
                         ON r.id_reserva = p.id_reserva
                        AND r.espacios_id_espacio = p.espacios_id_espacio
                        AND r.terrenos_id_terreno = p.terrenos_id_terreno
                     WHERE p.estado = 'pendiente'"
                )->fetchColumn();
            } else {
                $pagosPensionados = (int) $this->db->query(
                    "SELECT COUNT(*) FROM pagos WHERE estado = 'pendiente'"
                )->fetchColumn();
            }
        }

        $boletosPendientes = $this->tableExists('boletos_diarios')
            ? (int) $this->db->query(
                "SELECT COUNT(*) FROM boletos_diarios WHERE estado = 'ACTIVO'"
            )->fetchColumn()
            : 0;

        return [
            [
                'title' => 'Camiones registrados',
                'value' => $camiones,
                'icon' => 'mdi-truck',
                'color' => 'primary',
                'subtitle' => 'Total en el sistema',
            ],
            [
                'title' => 'Espacios disponibles',
                'value' => $espaciosDisponibles,
                'icon' => 'mdi-map-marker-radius',
                'color' => 'success',
                'subtitle' => 'Estado actual del patio',
            ],
            [
                'title' => 'Pagos de pensionados',
                'value' => $pagosPensionados,
                'icon' => 'mdi-cash-multiple',
                'color' => 'orange',
                'subtitle' => 'Pagos pendientes',
            ],
            [
                'title' => 'Pagos de boletos',
                'value' => $boletosPendientes,
                'icon' => 'mdi-ticket-confirmation',
                'color' => 'orange',
                'subtitle' => 'Boletos activos por cobrar',
            ],
            [
                'title' => 'Camiones adentro',
                'value' => $boletosPendientes,
                'icon' => 'mdi-truck-check',
                'color' => 'info',
                'subtitle' => 'Boletos activos sin salida',
            ],
        ];
    }

    public function getActivity(): array
    {
        if (!$this->tableExists('boletos_diarios')) {
            return $this->emptyActivity();
        }

        $fechaInicio = (new DateTimeImmutable('today'))->modify('-6 days')->format('Y-m-d 00:00:00');
        $fechaFin = (new DateTimeImmutable('tomorrow'))->format('Y-m-d 00:00:00');
        $stmt = $this->db->prepare(
            "SELECT fecha, SUM(movimientos) AS total
             FROM (
                 SELECT DATE(fecha_hora_entrada) AS fecha, COUNT(*) AS movimientos
                 FROM boletos_diarios
                 WHERE fecha_hora_entrada >= ? AND fecha_hora_entrada < ?
                 GROUP BY DATE(fecha_hora_entrada)
                 UNION ALL
                 SELECT DATE(fecha_hora_salida) AS fecha, COUNT(*) AS movimientos
                 FROM boletos_diarios
                 WHERE fecha_hora_salida IS NOT NULL
                   AND fecha_hora_salida >= ? AND fecha_hora_salida < ?
                 GROUP BY DATE(fecha_hora_salida)
             ) movimientos_diarios
             GROUP BY fecha
             ORDER BY fecha"
        );
        $stmt->execute([$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);

        $movimientosPorFecha = [];
        foreach ($stmt->fetchAll() as $movimiento) {
            $movimientosPorFecha[(string) $movimiento['fecha']] = (int) $movimiento['total'];
        }

        $labels = [];
        $values = [];
        $fecha = new DateTimeImmutable('today');
        for ($i = 6; $i >= 0; $i--) {
            $dia = $fecha->modify("-{$i} days");
            $clave = $dia->format('Y-m-d');
            $labels[] = $dia->format('d/m');
            $values[] = $movimientosPorFecha[$clave] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getOccupancy(): array
    {
        if (!$this->tableExists('espacios')) {
            return ['occupied' => 0, 'available' => 0];
        }

        $stmt = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN estado IN ('ocupado', 'reservado') THEN 1 ELSE 0 END), 0) AS occupied,
                COALESCE(SUM(CASE WHEN estado IN ('libre', 'disponible') THEN 1 ELSE 0 END), 0) AS available
             FROM espacios"
        );
        $ocupacion = $stmt->fetch() ?: [];

        return [
            'occupied' => (int) ($ocupacion['occupied'] ?? 0),
            'available' => (int) ($ocupacion['available'] ?? 0),
        ];
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = ?'
        );
        $stmt->execute([$table]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function emptyActivity(): array
    {
        $labels = [];
        $fecha = new DateTimeImmutable('today');

        for ($i = 6; $i >= 0; $i--) {
            $labels[] = $fecha->modify("-{$i} days")->format('d/m');
        }

        return ['labels' => $labels, 'values' => array_fill(0, 7, 0)];
    }
}
