<?php
class Usuario
{
    private PDO $db;

    public function __construct(PDO $conexion)
    {
        $this->db = $conexion;
    }

    public function listar(): array
    {
        $sql = "SELECT * FROM usuarios ORDER BY id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function guardar(string $nombre, string $email): bool
    {
        $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([trim($nombre), trim($email)]);
    }

    public function registrar(string $nombre, string $email, string $password, string $tipoCuenta): int
    {
        if (!in_array($tipoCuenta, ['propietario', 'trabajador'], true)) {
            throw new InvalidArgumentException('El tipo de cuenta no es válido.');
        }

        $this->asegurarColumnaPassword();
        $this->asegurarColumnaTipoCuenta();

        $sql = "INSERT INTO usuarios (nombre, email, password, tipo_cuenta) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            trim($nombre),
            trim($email),
            password_hash($password, PASSWORD_DEFAULT),
            $tipoCuenta,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function asegurarColumnaTipoCuenta(): void
    {
        $columna = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'tipo_cuenta'")->fetch();

        if (!$columna) {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN tipo_cuenta VARCHAR(30) NULL AFTER password");
        }
    }

    private function asegurarColumnaPassword(): void
    {
        $columnas = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'password'")->fetch();

        if (!$columnas) {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN password VARCHAR(255) NULL AFTER email");
        }
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function obtenerPorEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([trim($email)]);
        return $stmt->fetch();
    }

    public function obtenerPorNombre(string $nombre): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE nombre = ?");
        $stmt->execute([trim($nombre)]);
        return $stmt->fetch();
    }

    public function asegurarTablaRestablecimiento(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS password_resets (
                user_id INT NOT NULL PRIMARY KEY,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_password_resets_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function guardarTokenRestablecimiento(int $id, string $tokenHash, string $venceEn): bool
    {
        $stmt = $this->db->prepare(
            "SELECT created_at FROM password_resets
             WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
        );
        $stmt->execute([$id]);
        if ($stmt->fetch()) {
            return false;
        }

        $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$id]);
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
        );

        return $stmt->execute([$id, $tokenHash, $venceEn]);
    }

    public function obtenerRestablecimientoPorHash(string $tokenHash): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT user_id, expires_at FROM password_resets
             WHERE token_hash = ? AND expires_at > NOW()"
        );
        $stmt->execute([$tokenHash]);

        return $stmt->fetch();
    }

    public function eliminarRestablecimiento(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        return $stmt->execute([$id]);
    }

    public function actualizarPasswordConToken(string $tokenHash, string $passwordHash): bool
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT user_id FROM password_resets
                 WHERE token_hash = ? AND expires_at > NOW()
                 FOR UPDATE"
            );
            $stmt->execute([$tokenHash]);
            $userId = $stmt->fetchColumn();

            if ($userId === false) {
                $this->db->commit();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->execute([$passwordHash, (int) $userId]);
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('No fue posible actualizar la contraseña del usuario.');
            }

            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([(int) $userId]);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function actualizarFoto(int $id, string $foto): bool
    {
        $columna = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'foto'")->fetch();
        if (!$columna) {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN foto VARCHAR(255) NULL AFTER email");
        }

        $stmt = $this->db->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        return $stmt->execute([$foto, $id]);
    }

    public function actualizarTelefono(int $id, string $telefono): bool
    {
        $columna = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'telefono'")->fetch();
        if (!$columna) {
            $this->db->exec("ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) NULL");
        }

        $columnaId = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'id'")->fetch()
            ? 'id'
            : 'id_usuario';

        $stmt = $this->db->prepare("UPDATE usuarios SET telefono = ? WHERE {$columnaId} = ?");
        $resultado = $stmt->execute([trim($telefono), $id]);

        return $resultado && $stmt->rowCount() >= 0;
    }

    public function actualizar(int $id, string $nombre, string $email): bool
    {
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([trim($nombre), trim($email), $id]);
    }
}
