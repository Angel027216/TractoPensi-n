<?php
class PensionadosModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listar(string $busqueda = '', int $idLinea = 0): array
    {
        $sql = "SELECT
                    c.id_camion,
                    c.placa,
                    c.modelo,
                    c.estado AS estado_camion,
                    u.nombre AS pensionado,
                    l.nom_linea,
                    p.estado_cuenta,
                    p.proximo_pago
                FROM camiones c
                LEFT JOIN usuarios u ON u.id = c.id_usuario
                LEFT JOIN linea l ON l.id_linea = c.id_linea
                LEFT JOIN pensionados p
                    ON p.id_camion = c.id_camion
                    AND p.id_usuario = c.id_usuario
                WHERE (? = '' OR LOWER(COALESCE(l.nom_linea, 'Sin línea')) LIKE LOWER(?))
                  AND (? = 0 OR c.id_linea = ?)
                ORDER BY COALESCE(l.nom_linea, 'Sin línea'), c.id_camion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$busqueda, '%' . $busqueda . '%', $idLinea, $idLinea]);

            return $stmt->fetchAll();
    }

    public function listarLineas(): array
    {
        $stmt = $this->db->query(
            'SELECT id_linea, nom_linea
             FROM linea
             ORDER BY nom_linea'
        );

        return $stmt->fetchAll();
    }

    public function buscarLineaExacta(string $nombre): ?array
    {
        if ($nombre === '') {
            return null;
        }

        $nombreNormalizado = $this->normalizarNombreLinea($nombre);
        if ($nombreNormalizado === '') {
            return null;
        }

        foreach ($this->listarLineas() as $linea) {
            if (
                $this->normalizarNombreLinea((string) $linea['nom_linea'])
                === $nombreNormalizado
            ) {
                return $linea;
            }
        }

        return null;
    }

    private function normalizarNombreLinea(string $nombre): string
    {
        $nombre = trim($nombre);
        $nombre = preg_replace('/\s+/u', ' ', $nombre) ?? $nombre;
        $nombre = strtr(
            mb_strtolower($nombre, 'UTF-8'),
            [
                'á' => 'a',
                'é' => 'e',
                'í' => 'i',
                'ó' => 'o',
                'ú' => 'u',
                'ü' => 'u',
                'ñ' => 'n',
            ]
        );

        return str_replace(' ', '', trim($nombre));
    }

    public function listarCamionesParaAsignar(int $idLinea = 0): array
    {
        $sql = 'SELECT c.id_camion, c.placa, c.modelo, c.id_linea,
                       l.nom_linea, u.nombre AS propietario
                FROM camiones c
                LEFT JOIN linea l ON l.id_linea = c.id_linea
                LEFT JOIN usuarios u ON u.id = c.id_usuario';
        $parametros = [];

        if ($idLinea > 0) {
            $sql .= ' WHERE c.id_linea IS NULL OR c.id_linea <> ?';
            $parametros[] = $idLinea;
        }

        $sql .= ' ORDER BY c.placa';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function asignarLinea(int $idCamion, int $idLinea): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id_linea FROM linea WHERE id_linea = ?'
        );
        $stmt->execute([$idLinea]);
        if (!$stmt->fetchColumn()) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE camiones SET id_linea = ? WHERE id_camion = ?'
        );
        if (!$stmt->execute([$idLinea, $idCamion])) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id_usuario FROM camiones WHERE id_camion = ?'
        );
        $stmt->execute([$idCamion]);
        $idUsuario = $stmt->fetchColumn();
        if ($idUsuario === false) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT id_pensionado FROM pensionados
             WHERE id_usuario = ? AND id_camion = ?
             LIMIT 1'
        );
        $stmt->execute([(int) $idUsuario, $idCamion]);
        if (!$stmt->fetchColumn()) {
            $stmt = $this->db->prepare(
                'INSERT INTO pensionados (id_usuario, id_camion, estado_cuenta)
                 VALUES (?, ?, ?)'
            );
            return $stmt->execute([(int) $idUsuario, $idCamion, 'al_corriente']);
        }

        return true;
    }

    public function quitarLinea(int $idCamion): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE camiones SET id_linea = NULL WHERE id_camion = ?'
        );

        return $stmt->execute([$idCamion]);
    }
}
