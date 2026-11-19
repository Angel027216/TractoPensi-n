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
        string $operadorEmpresa,
        string $marca,
        string $modelo,
        string $color,
        ?string $fecha,
        ?string $hora,
        string $estado
    ): int {
        $sql = "INSERT INTO camiones
                    (id_usuario, id_linea, operador_empresa, placa, marca, modelo, color, anio, fecha_entrada, hora_entrada, estado)
                VALUES (?, NULL, ?, ?, ?, ?, ?, NULL, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute([
            $id_usuario,
            $operadorEmpresa,
            strtoupper($placa),
            $marca,
            $modelo,
            $color,
            $fecha,
            $hora,
            $estado,
        ])) {
            return 0;
        }

        return (int) $this->db->lastInsertId();
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

    /**
     * Obtiene el apartado activo del camión
     */
    public function obtenerApartadoActivo($id_camion) {
        $sql = "SELECT a.*, e.numero_espacio, e.terreno FROM apartados a 
                JOIN espacios e ON a.id_espacio = e.id_espacio
                WHERE a.id_camion = ? AND a.estado = 'activo' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_camion]);
        return $stmt->fetch();
    }
}
?>
