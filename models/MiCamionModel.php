<?php
class MiCamionModel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    /**
     * Obtiene todos los camiones del usuario
     */
    public function obtenerPorUsuario($id_usuario) {
        $sql = "SELECT * FROM camiones WHERE id_usuario = ? ORDER BY id_camion DESC";
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
    public function guardar($id_usuario, $id_linea, $placa, $marca, $modelo, $color, $anio, $fecha = null, $hora = null, $estado = 'en_pension'): int {
        $sql = "INSERT INTO camiones (id_usuario, id_linea, placa, modelo, fecha_entrada, hora_entrada, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute([$id_usuario, $id_linea, strtoupper($placa), $modelo, $fecha, $hora, $estado])) {
            return 0;
        }

        return (int) $this->db->lastInsertId();
    }

    public function obtenerLineas(): array
    {
        return $this->db->query('SELECT id_linea, nom_linea FROM linea ORDER BY nom_linea')->fetchAll();
    }

    /**
     * Actualiza datos del camión
     */
    public function actualizar($id_camion, $placa, $modelo, $fecha, $hora, $estado, $marca = '', $color = '', $anio = '') {
        $sql = "UPDATE camiones SET placa = ?, modelo = ?, fecha_entrada = ?, hora_entrada = ?, estado = ? WHERE id_camion = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([strtoupper($placa), $modelo, $fecha, $hora, $estado, $id_camion]);
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
