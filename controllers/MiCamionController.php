<?php
require_once 'models/MiCamionModel.php';

class MiCamionController {
    private $micamionmodel;
    
    public function __construct($conexion) {
        $this->micamionmodel = new MiCamionModel($conexion);
    }

    /**
     * Muestra la lista de camiones del usuario
     */
    public function index(): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            header("Location: ?menu=login");
            exit;
        }

        $camiones = $this->micamionmodel->obtenerPorUsuario($id_usuario);
        $seccionSolicitada = $_GET['seccion'] ?? 'visualizar';
        $seccionCamiones = is_string($seccionSolicitada)
            && in_array($seccionSolicitada, ['registrar', 'visualizar'], true)
            ? $seccionSolicitada
            : 'visualizar';
        $mensajeCamion = $_SESSION['mensaje_camion'] ?? '';
        unset($_SESSION['mensaje_camion']);
        $camionRegistradoQuery = $_GET['camion_registrado'] ?? null;
        $idCamionRegistrado = is_string($camionRegistradoQuery)
            ? filter_var($camionRegistradoQuery, FILTER_VALIDATE_INT)
            : false;

        if ($idCamionRegistrado !== false && $idCamionRegistrado !== null) {
            foreach ($camiones as $camionRegistrado) {
                if ((int) $camionRegistrado['id_camion'] === $idCamionRegistrado) {
                    $mensajeCamion = 'El camión se guardó y ya aparece en tu lista.';
                    break;
                }
            }
        }

        include "views/MiCamionView.php";
    }

    /**
     * Muestra el formulario para agregar un camión
     */
    public function crear(): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            header("Location: ?menu=login");
            exit;
        }

        $accion = 'crear';
        include "views/MiCamionView.php";
    }

    /**
     * Guarda un nuevo camión
     */
    public function guardar(): void {
        header('Content-Type: application/json; charset=utf-8');
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            $this->responderJson(['exito' => false, 'mensaje' => 'No autorizado'], 401);
        }

        $leerCampo = static function (string $campo): string {
            $valor = $_POST[$campo] ?? '';

            return is_string($valor) ? trim($valor) : '';
        };

        $placa = strtoupper($leerCampo('placa'));
        $placaCaja = strtoupper($leerCampo('placa_caja'));
        $operadorEmpresa = $leerCampo('operador_empresa');
        $marca = $leerCampo('marca');
        $modelo = $leerCampo('modelo');
        $color = $leerCampo('color');
        $fechaEntrada = $leerCampo('fecha');
        $horaEntrada = $leerCampo('hora');
        $estado = $leerCampo('estado') ?: 'en_pension';

        if (
            !preg_match('/^\d{2}-[A-Z]{2}-\d[A-Z]$/', $placa)
            || !preg_match('/^(?:\d{3}-[A-Z]{2}-\d|\d{2}-[A-Z]{2}-\d[A-Z])$/', $placaCaja)
            || $operadorEmpresa === ''
            || mb_strlen($operadorEmpresa, 'UTF-8') > 150
            || !in_array($marca, MiCamionModel::MARCAS_COMUNES, true)
            || $modelo === ''
            || mb_strlen($modelo, 'UTF-8') > 100
            || !in_array($color, MiCamionModel::COLORES_COMUNES, true)
        ) {
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'Verifica operador o empresa, placas, marca, modelo y color. El tracto debe usar 00-AA-0A y la caja 000-AA-0 o 00-AA-0A.',
            ], 422);
        }

        if ($fechaEntrada !== '') {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEntrada)) {
                $this->responderJson([
                    'exito' => false,
                    'mensaje' => 'La fecha de entrada no es válida.',
                ], 422);
            }

            [$anioFecha, $mesFecha, $diaFecha] = array_map('intval', explode('-', $fechaEntrada));
            if (!checkdate($mesFecha, $diaFecha, $anioFecha)) {
                $this->responderJson([
                    'exito' => false,
                    'mensaje' => 'La fecha de entrada no es válida.',
                ], 422);
            }
        } else {
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'Indica la fecha de entrada del camión.',
            ], 422);
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaEntrada)) {
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'La hora de entrada no es válida.',
            ], 422);
        }
        $hora = $horaEntrada;

        if (!in_array($estado, ['en_pension', 'fuera_de_pension'], true)) {
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'Selecciona un estado válido.',
            ], 422);
        }

        try {
            $idCamion = $this->micamionmodel->guardar(
                (int) $id_usuario,
                $placa,
                $placaCaja,
                $operadorEmpresa,
                $marca,
                $modelo,
                $color,
                $fechaEntrada,
                $hora,
                $estado
            );
        } catch (PDOException $e) {
            logger('Error al registrar un camión.', [
                'usuario_id' => (int) $id_usuario,
                'error' => $e->getMessage(),
            ]);
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'No fue posible guardar el camión. Revisa los datos e inténtalo nuevamente.',
            ], 500);
        }

        if ($idCamion <= 0) {
            $this->responderJson([
                'exito' => false,
                'mensaje' => 'No fue posible guardar el camión.',
            ], 500);
        }

        logger('Registro de camión.', [
            'usuario_id' => (int) $id_usuario,
            'id_camion' => $idCamion,
            'placa' => $placa,
        ]);

        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        if (strpos($accept, 'application/json') === false) {
            $_SESSION['mensaje_camion'] = 'El camión se guardó y ya aparece en tu lista.';
            header(
                'Location: index.php?menu=micamion&seccion=visualizar&camion_registrado='
                . $idCamion
                . '#camion-' . $idCamion,
                true,
                303
            );
            exit;
        }

        $this->responderJson([
            'exito' => true,
            'mensaje' => 'Camión registrado correctamente.',
            'id_camion' => $idCamion,
        ]);
    }

    private function responderJson(array $respuesta, int $codigoHttp = 200): void
    {
        http_response_code($codigoHttp);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Muestra el formulario para editar un camión
     */
    public function editar(): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            header("Location: ?menu=login");
            exit;
        }

        $id_camion = $_GET['id'] ?? null;
        if (!$id_camion) {
            header("Location: ?menu=micamion");
            exit;
        }

        $camion = $this->micamionmodel->obtenerPorId($id_camion);
        if (!$camion || $camion['id_usuario'] != $id_usuario) {
            header("Location: ?menu=micamion");
            exit;
        }

        $accion = 'editar';
        include "views/MiCamionView.php";
    }

    /**
     * Actualiza un camión
     */
    public function actualizar(): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
            exit;
        }

        $id_camion = $_POST['id_camion'] ?? null;
        $placa = trim((string) ($_POST['placa'] ?? ''));
        $marca = $_POST['marca'] ?? '';
        $modelo = $_POST['modelo'] ?? '';
        $fecha = $_POST['fecha'] ?? null;
        $hora = $_POST['hora'] ?? null;
        $estado = $_POST['estado'] ?? 'en_pension';
        $color = $_POST['color'] ?? '';
        $anio = $_POST['anio'] ?? '';

        if (!$id_camion || $placa === '' || empty($modelo)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Faltan campos requeridos']);
            exit;
        }

        $camion = $this->micamionmodel->obtenerPorId($id_camion);
        if (!$camion || $camion['id_usuario'] != $id_usuario) {
            echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
            exit;
        }

        if ($this->micamionmodel->actualizar($id_camion, $placa, $modelo, $fecha, $hora, $estado, $marca, $color, $anio)) {
            logger($_SESSION['usuario_id'], "Actualización de camión", "camiones", $id_camion);
            echo json_encode(['exito' => true, 'mensaje' => 'Camión actualizado correctamente']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al actualizar el camión']);
        }
    }

    /**
     * Elimina un camión
     */
    public function eliminar(): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;
        if (!$id_usuario) {
            echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
            exit;
        }

        $id_camion = $_POST['id_camion'] ?? null;
        if (!$id_camion) {
            echo json_encode(['exito' => false, 'mensaje' => 'ID de camión no especificado']);
            exit;
        }

        $camion = $this->micamionmodel->obtenerPorId($id_camion);
        if (!$camion || $camion['id_usuario'] != $id_usuario) {
            echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
            exit;
        }

        if ($this->micamionmodel->eliminar($id_camion)) {
            logger($_SESSION['usuario_id'], "Eliminacion de camión", "camiones", $id_camion);
            echo json_encode(['exito' => true, 'mensaje' => 'Camión eliminado correctamente']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar el camión']);
        }
    }
}
?>
