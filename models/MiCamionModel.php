<?php
class MiCamionModel {
    public const MARCAS_COMUNES = [
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

    public const COLORES_COMUNES = [
        'Blanco',
        'Rojo',
        'Azul',
        'Negro',
        'Gris',
        'Plata',
        'Amarillo',
        'Naranja',
        'Verde',
        'Café',
        'Otro',
    ];

    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /**
     * Obtiene todos los camiones del usuario
     */
    public function obtenerPorUsuario($id_usuario) {
        $sql = "SELECT c.*, l.nom_linea
                FROM camiones c
                LEFT JOIN linea l ON l.id_linea = c.id_linea
                WHERE c.id_usuario = ?
                ORDER BY c.id_camion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un camión específico
     */
    public function obtenerPorId($id_camion) {
        $sql = "SELECT * FROM camiones WHERE id_camion = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_camion]);
        return $stmt->fetch();
    }

    /**
     * Registra un nuevo camión
     */
    public function guardar(
        int $id_usuario,
        string $placa,
        string $placaCaja,
        string $operadorEmpresa,
        string $marca,
        string $modelo,
        string $color,
        ?string $fecha,
        ?string $hora,
        string $estado
    ): int {
        $columnaCaja = $this->db->query("SHOW COLUMNS FROM camiones LIKE 'placa_caja'")->fetch();
        if (!$columnaCaja) {
            $this->db->exec("ALTER TABLE camiones ADD COLUMN placa_caja VARCHAR(20) NULL AFTER placa");
        }

        $sql = "INSERT INTO camiones
                    (id_usuario, id_linea, operador_empresa, placa, placa_caja, marca, modelo, color, anio, fecha_entrada, hora_entrada, estado)
                VALUES (?, NULL, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute([
            $id_usuario,
            $operadorEmpresa,
            strtoupper($placa),
            strtoupper($placaCaja),
            $marca,
            $modelo,
            $color,
            $fecha,
            $hora,
            $estado,
        ])) {
            return 0;
        }

        $idCamion = (int) $this->db->lastInsertId();
        $this->registrarEntradaInicial($id_usuario, $idCamion, $fecha, $hora);

        return $idCamion;
    }

    private function registrarEntradaInicial(int $idUsuario, int $idCamion, ?string $fecha, ?string $hora): void
    {
        $tablaAccesos = $this->db->query("SHOW TABLES LIKE 'accesos'")->fetch();
        if (!$tablaAccesos) {
            $this->db->exec(
                "CREATE TABLE accesos (
                    id_acceso INT AUTO_INCREMENT PRIMARY KEY,
                    id_usuario INT NOT NULL,
                    id_camion INT NOT NULL,
                    tipo VARCHAR(10) NOT NULL,
                    fecha DATE NOT NULL,
                    hora TIME NOT NULL,
                    observaciones VARCHAR(255) NULL,
                    KEY idx_accesos_usuario (id_usuario),
                    KEY idx_accesos_camion (id_camion),
                    CONSTRAINT fk_accesos_camion_micamion
                        FOREIGN KEY (id_camion) REFERENCES camiones(id_camion)
                        ON UPDATE RESTRICT ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }

        $stmt = $this->db->prepare(
            "INSERT INTO accesos (id_usuario, id_camion, tipo, fecha, hora, observaciones)
             VALUES (?, ?, 'entrada', ?, ?, 'Entrada registrada al guardar el camión')"
        );
        $stmt->execute([$idUsuario, $idCamion, $fecha, $hora]);
    }

    /**
     * Actualiza datos del camión
     */
    public function actualizar($id_camion, $placa, $modelo, $fecha, $hora, $estado, $marca = '', $color = '', $anio = '') {
        $sql = "UPDATE camiones
                SET placa = ?, marca = ?, modelo = ?, color = ?, anio = ?,
                    fecha_entrada = ?, hora_entrada = ?, estado = ?
                WHERE id_camion = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            strtoupper($placa),
            $marca !== '' ? $marca : null,
            $modelo,
            $color !== '' ? $color : null,
            $anio !== '' ? $anio : null,
            $fecha,
            $hora,
            $estado,
            $id_camion,
        ]);
    }

    /**
     * Cambia estado del camión
     */
    public function cambiarEstado($id_camion, $estado) {
        $sql = "UPDATE camiones SET estado = ? WHERE id_camion = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $id_camion]);
    }

    /**
     * Elimina un camión
     */
    public function eliminar($id_camion) {
        $sql = "DELETE FROM camiones WHERE id_camion = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id_camion]);
    }

}
?>
