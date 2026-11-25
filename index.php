<?php
// Cargar configuración global primero
require_once 'functions.php';
require_once 'config/database.php';
require_once 'models/UsuarioModel.php';
require_once 'models/PanelModel.php';
require_once 'controllers/PanelController.php';

$menu=$_GET['menu']??'login';

// Cerrar completamente la sesión actual.
if ($menu === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('index.php?menu=login');
}

// Iniciar sesión con el usuario nuevo, reemplazando cualquier sesión anterior.
if ($menu === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $identificador = trim((string) ($_POST['identificador'] ?? ''));
    $passwordLogin = (string) ($_POST['password'] ?? '');
    $usuarioModel = new Usuario((new Database())->getConnection());
    $usuario = $usuarioModel->obtenerPorEmail($identificador);

    if (!$usuario && method_exists($usuarioModel, 'obtenerPorNombre')) {
        $usuario = $usuarioModel->obtenerPorNombre($identificador);
    }

    if ($usuario && !empty($usuario['password']) && password_verify($passwordLogin, $usuario['password'])) {
        $_SESSION = [];
        session_regenerate_id(true);

        if (preparar_verificacion_login($usuario)) {
            redirect('index.php?menu=verificar');
        }

        $errorLogin = 'No fue posible enviar el código de verificación. Inténtalo nuevamente.';
    } else {
        $errorLogin = 'Usuario o contraseña incorrectos.';
    }
}

if ($menu === 'verificar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $verificacion = $_SESSION['login_2fa'] ?? null;
    $codigo = trim((string) ($_POST['codigo'] ?? ''));

    if (!is_array($verificacion)) {
        redirect('index.php?menu=login');
    }

    $codigoValido = preg_match('/^\d{6}$/', $codigo)
        && time() <= (int) ($verificacion['vence_en'] ?? 0)
        && password_verify($codigo, (string) ($verificacion['codigo_hash'] ?? ''));

    if ($codigoValido) {
        $usuario = $verificacion['usuario'];
        unset($_SESSION['login_2fa']);
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario'] = $usuario;
        redirect('index.php?menu=panel');
    }

    $_SESSION['login_2fa']['intentos'] = (int) ($verificacion['intentos'] ?? 0) + 1;
    if ($_SESSION['login_2fa']['intentos'] >= 5 || time() > (int) ($verificacion['vence_en'] ?? 0)) {
        unset($_SESSION['login_2fa']);
        $errorVerificacion = 'El código venció o superaste el número de intentos. Inicia sesión nuevamente.';
    } else {
        $errorVerificacion = 'El código ingresado no es válido.';
    }
}

if ($menu === 'registro' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $tipoCuenta = trim((string) ($_POST['tipo_cuenta'] ?? ''));
    $errores = [];
    $usuarioRepetido = false;

    if ($nombre === '') {
        $errores[] = 'El nombre de usuario es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (!password_cumple_requisitos($password)) {
        $errores[] = 'La contraseña debe tener entre 9 y 128 caracteres e incluir mayúsculas, minúsculas, números y al menos un símbolo.';
    }
    if (!in_array($tipoCuenta, ['propietario', 'trabajador'], true)) {
        $errores[] = 'Selecciona un tipo de perfil válido.';
    }
    if (empty($_POST['terminos'])) {
        $errores[] = 'Debes aceptar los términos y condiciones.';
    }

    if (!$errores) {
        try {
            $usuarioModel = new Usuario((new Database())->getConnection());

            if ($usuarioModel->obtenerPorNombre($nombre)) {
                $usuarioRepetido = true;
                $errores[] = 'Usuario repetido, elige otro nombre de usuario.';
            } elseif ($usuarioModel->obtenerPorEmail($email)) {
                $errores[] = 'Ya existe una cuenta con ese correo electrónico.';
            } else {
                $usuarioModel->registrar($nombre, $email, $password, $tipoCuenta);
                $_SESSION['mensajeLogin'] = 'Cuenta creada correctamente. Inicia sesión para verificar tu correo.';
                redirect('index.php?menu=login');
            }
        } catch (Throwable $e) {
            logger('Error durante el registro: ' . $e->getMessage());
            $errores[] = 'No fue posible crear la cuenta. Inténtalo nuevamente.';
        }
    }
}

if ($menu === 'recuperar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $emailRecuperacion = trim((string) ($_POST['email'] ?? ''));
    $mensajeRecuperacion = 'Si existe una cuenta con ese correo, recibirás un enlace para restablecer la contraseña. Revisa también la carpeta de correo no deseado.';

    try {
        $urlBase = url_base_aplicacion();
        $usuarioModel = new Usuario((new Database())->getConnection());
        $usuarioModel->asegurarTablaRestablecimiento();

        if (filter_var($emailRecuperacion, FILTER_VALIDATE_EMAIL)) {
            $usuario = $usuarioModel->obtenerPorEmail($emailRecuperacion);
            if ($usuario && $usuarioModel->guardarTokenRestablecimiento(
                (int) $usuario['id'],
                hash('sha256', $tokenRecuperacion = bin2hex(random_bytes(32))),
                date('Y-m-d H:i:s', time() + 3600)
            )) {
                $enlace = $urlBase . '/index.php?menu=restablecer&token=' . rawurlencode($tokenRecuperacion);
                $asunto = 'Restablece tu contraseña - Tracto Pensión San Leonardo';
                $mensaje = "Recibimos una solicitud para restablecer la contraseña de tu cuenta.\n\n"
                    . "Abre este enlace para crear una nueva contraseña:\n{$enlace}\n\n"
                    . "El enlace vence en una hora y solo puede utilizarse una vez. "
                    . "Si no solicitaste el cambio, ignora este correo.";

                if (!enviar_correo_texto((string) $usuario['email'], $asunto, $mensaje)) {
                    $usuarioModel->eliminarRestablecimiento((int) $usuario['id']);
                    logger('No se pudo enviar un correo de recuperación de contraseña.');
                }
            }
        }
    } catch (RuntimeException $e) {
        logger('Configuración inválida para enlaces de recuperación: ' . $e->getMessage());
        $errorRecuperacion = 'No se pudo iniciar la recuperación. Inténtalo más tarde o contacta al administrador.';
    } catch (PDOException $e) {
        logger('Error de base de datos durante la recuperación de contraseña: ' . $e->getMessage());
        $errorRecuperacion = 'No se pudo procesar la solicitud. Inténtalo más tarde.';
    }
}

if ($menu === 'restablecer') {
    header('Referrer-Policy: no-referrer');
    $tokenRestablecimiento = $_POST['token'] ?? $_GET['token'] ?? '';
    $tokenRestablecimiento = is_string($tokenRestablecimiento) ? $tokenRestablecimiento : '';
    $tokenValidoFormato = preg_match('/^[a-f0-9]{64}$/i', $tokenRestablecimiento) === 1;
    $tokenRestablecimientoValido = false;
    $errorRestablecimiento = '';
    $usuarioModel = new Usuario((new Database())->getConnection());
    $usuarioModel->asegurarTablaRestablecimiento();
    $tokenHash = $tokenValidoFormato ? hash('sha256', $tokenRestablecimiento) : '';
    $tokenRestablecimientoValido = $tokenValidoFormato
        && (bool) $usuarioModel->obtenerRestablecimientoPorHash($tokenHash);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();
        $passwordNueva = (string) ($_POST['password'] ?? '');
        $passwordConfirmacion = (string) ($_POST['confirmar_password'] ?? '');

        if (!$tokenRestablecimientoValido) {
            $errorRestablecimiento = 'El enlace no es válido o ya venció. Solicita uno nuevo.';
        } elseif (!password_cumple_requisitos($passwordNueva)) {
            $errorRestablecimiento = 'La contraseña debe tener entre 9 y 128 caracteres e incluir mayúsculas, minúsculas, números y al menos un símbolo.';
        } elseif ($passwordNueva !== $passwordConfirmacion) {
            $errorRestablecimiento = 'Las contraseñas no coinciden.';
        } elseif ($usuarioModel->actualizarPasswordConToken($tokenHash, password_hash($passwordNueva, PASSWORD_DEFAULT))) {
            $_SESSION['mensajeLogin'] = 'Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.';
            redirect('index.php?menu=login');
        } else {
            $tokenRestablecimientoValido = false;
            $errorRestablecimiento = 'El enlace ya no es válido. Solicita uno nuevo.';
        }
    }
}

$menusProtegidos = ['panel', 'micamion', 'apartarespacio', 'pagos', 'respaldo', 'historial', 'accesos', 'pensionados', 'linea', 'control_diario', 'perfil', 'password'];
if (in_array($menu, $menusProtegidos, true) && !isset($_SESSION['usuario_id'])) {
    redirect('index.php?menu=login');
}

if ($menu === 'perfil' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('index.php?menu=login');
    }

    verify_csrf_token();

    $errores = [];
    $usuarioModel = new Usuario((new Database())->getConnection());

    if (array_key_exists('telefono', $_POST)) {
        $telefono = trim((string) $_POST['telefono']);
        if ($telefono === '') {
            $errores[] = 'El número de teléfono no puede quedar vacío.';
        } elseif (!preg_match('/^\+?[0-9 ()-]{7,20}$/', $telefono)) {
            $errores[] = 'Ingresa un número de teléfono válido.';
        } elseif (!$usuarioModel->actualizarTelefono((int) $_SESSION['usuario_id'], $telefono)) {
            $errores[] = 'No fue posible guardar el número de teléfono.';
        } else {
            $_SESSION['usuario']['telefono'] = $telefono;
            $_SESSION['mensaje_perfil'] = 'Número de teléfono guardado correctamente.';
        }
    }

    $archivo = $_FILES['foto'] ?? null;
    if (is_array($archivo) && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errores[] = 'No fue posible cargar la imagen.';
        } elseif (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
            $errores[] = 'La imagen no debe superar los 5 MB.';
        } else {
            $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
            if (!isset($tiposPermitidos[$tipo])) {
                $errores[] = 'El archivo debe ser una imagen JPG, PNG, GIF o WEBP.';
            }
        }

        if (!$errores) {
            $directorio = APP_ROOT . '/public/assets/uploads';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $nombreArchivo = 'perfil_' . (int) $_SESSION['usuario_id'] . '_' . bin2hex(random_bytes(8)) . '.' . $tiposPermitidos[$tipo];
            $rutaArchivo = $directorio . '/' . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                $foto = 'public/assets/uploads/' . $nombreArchivo;
                $usuarioModel = new Usuario((new Database())->getConnection());
                if ($usuarioModel->actualizarFoto((int) $_SESSION['usuario_id'], $foto)) {
                    $_SESSION['usuario']['foto'] = $foto;
                    $_SESSION['mensaje_perfil'] = 'Foto de perfil actualizada correctamente.';
                } else {
                    $errores[] = 'No fue posible actualizar tu perfil.';
                }
            } else {
                $errores[] = 'No fue posible guardar la imagen.';
            }
        }

        if ($errores) {
            $_SESSION['error_perfil'] = implode(' ', $errores);
        }
    }

    redirect('index.php?menu=perfil');
}

if ($menu=='login'){
    include 'views/LoginView.php';
}
else if ($menu=='verificar'){
    if (!isset($_SESSION['login_2fa'])) {
        redirect('index.php?menu=login');
    }
    include 'views/VerificarLoginView.php';
}
else if ($menu=='registro'){
    include 'views/RegistroLoginView.php';
}
else if ($menu === 'recuperar') {
    include 'views/RecuperarPasswordView.php';
}
else if ($menu === 'restablecer') {
    include 'views/RestablecerPasswordView.php';
}
else if ($menu=='panel'){
    $panelModel = new PanelModel((new Database())->getConnection());
    $panelController = new PanelController($panelModel);
    $panelData = $panelController->getDashboardData();
    include 'views/PanelView.php';
}
else if ($menu=='micamion'){
    require_once 'models/MiCamionModel.php';
    require_once 'controllers/MiCamionController.php';

    $miCamionController = new MiCamionController((new Database())->getConnection());
    $accionCamion = (string) ($_POST['accion'] ?? '');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();
        if ($accionCamion === 'guardar') {
            $miCamionController->guardar();
        } elseif ($accionCamion === 'actualizar') {
            $miCamionController->actualizar();
        } elseif ($accionCamion === 'eliminar') {
            $miCamionController->eliminar();
        }
    }
    $miCamionController->index();

}
else if ($menu=='apartarespacio'){
    redirect('index.php?menu=panel');
}
else if ($menu=='pagos'){
    require_once 'models/PagosModel.php';
    require_once 'controllers/PagosController.php';

    $conexion = (new Database())->getConnection();
    if (!tabla_disponible($conexion, 'pagos')) {
        $pagos = [];
        $resumen = [];
        include 'views/PagosView.php';
    } else {
        $pagosController = new PagosController($conexion);
        $accionPago = (string) ($_POST['accion'] ?? '');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            verify_csrf_token();
            if ($accionPago === 'guardar') {
                $pagosController->guardar();
            } elseif ($accionPago === 'marcar_pagado') {
                $pagosController->marcarPagado();
            } elseif ($accionPago === 'rechazar') {
                $pagosController->rechazar();
            }
        }
        $pagosController->index();
    }
}

else if ($menu=='respaldo'){
    require_once 'config/database.php';
    require_once 'controllers/BackupController.php';

    include 'views/BackupView.php';
}

else if ($menu=='historial'){
    require_once 'config/database.php';
    require_once 'models/Historial.php';
    require_once 'controllers/HistorialController.php';

    $historialModel = new HistorialModel();
    $historialController = new HistorialController($historialModel);
    $historialController->run();

    $historial = $historialController->getHistorial();
    $message = $historialController->getMessage();
    $type = $historialController->getType();
    $registroSeleccionado = $historialController->getRegistroSeleccionado();
    $ultimoId = $historialController->getUltimoId();

    include 'views/Historialview.php';
}

else if ($menu=='accesos'){
    require_once 'models/AccesosModel.php';
    require_once 'controllers/AccesosController.php';

    $conexion = (new Database())->getConnection();
    if (!tabla_disponible($conexion, 'accesos')) {
        $accesos = [];
        $estadisticas = [];
        $accesos_hoy = [];
        include 'views/Accesosview.php';
    } else {
        $accesosController = new AccesosController($conexion);
        $accionAcceso = (string) ($_POST['accion'] ?? '');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            verify_csrf_token();
            if ($accionAcceso === 'registrar') {
                $accesosController->registrar();
            }
        }
        $accesosController->index();
    }
}

else if ($menu=='pensionados'){
    require_once 'models/PensionadosModel.php';
    $pensionadosModel = new PensionadosModel((new Database())->getConnection());
    $busquedaPensionados = trim((string) ($_GET['buscar'] ?? ''));
    $lineaPensionadosId = (int) ($_GET['id_linea'] ?? 0);
    $mensajePensionados = $_SESSION['mensaje_pensionados'] ?? null;
    $errorBusquedaPensionados = null;
    unset($_SESSION['mensaje_pensionados']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();
        $accionPensionados = (string) ($_POST['accion'] ?? '');
        $idCamionPensionados = (int) ($_POST['id_camion'] ?? 0);
        $idLineaPost = (int) ($_POST['id_linea'] ?? 0);

        if ($accionPensionados === 'asignar_linea' && $idCamionPensionados > 0 && $idLineaPost > 0) {
            $_SESSION['mensaje_pensionados'] = $pensionadosModel->asignarLinea($idCamionPensionados, $idLineaPost)
                ? 'El camión fue asignado a la línea correctamente.'
                : 'No fue posible asignar el camión a la línea.';
            redirect('index.php?menu=pensionados&id_linea=' . $idLineaPost);
        }

        if ($accionPensionados === 'quitar_linea' && $idCamionPensionados > 0) {
            $_SESSION['mensaje_pensionados'] = $pensionadosModel->quitarLinea($idCamionPensionados)
                ? 'El camión fue retirado de la línea.'
                : 'No fue posible retirar el camión de la línea.';
            redirect('index.php?menu=pensionados&id_linea=' . $lineaPensionadosId);
        }
    }

    $lineasPensionados = $pensionadosModel->listarLineas();
    $lineaPensionadosSeleccionada = null;
    if ($busquedaPensionados !== '') {
        $lineaPensionadosSeleccionada = $pensionadosModel->buscarLineaExacta($busquedaPensionados);
        if ($lineaPensionadosSeleccionada) {
            $lineaPensionadosId = (int) $lineaPensionadosSeleccionada['id_linea'];
        } else {
            $lineaPensionadosId = 0;
            $errorBusquedaPensionados = 'La línea no está registrada. Escribe exactamente el nombre agregado en la interfaz de Línea.';
        }
    }

    foreach ($lineasPensionados as $lineaPensionados) {
        if (!$busquedaPensionados && (int) $lineaPensionados['id_linea'] === $lineaPensionadosId) {
            $lineaPensionadosSeleccionada = $lineaPensionados;
            break;
        }
    }
    if (!$lineaPensionadosSeleccionada) {
        $lineaPensionadosId = 0;
    }

    $pensionados = $lineaPensionadosSeleccionada
        ? $pensionadosModel->listar('', $lineaPensionadosId)
        : ($busquedaPensionados === '' ? $pensionadosModel->listar('', 0) : []);
    $camionesDisponiblesPensionados = $pensionadosModel->listarCamionesParaAsignar($lineaPensionadosId);
    include 'views/Pensionados.php';
}

else if ($menu=='linea'){
    require_once 'models/LineaModel.php';
    require_once 'controllers/LineaController.php';

    $lineaModel = new LineaModel((new Database())->getConnection());
    $lineaController = new LineaController($lineaModel);
    $mensajeLinea = null;
    $mensajeBusquedaLinea = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();
        $mensajeLinea = $lineaController->procesar(
            (string) ($_POST['accion'] ?? ''),
            $_POST
        );
    }

    $busquedaLinea = trim((string) ($_GET['buscar'] ?? ''));
    $lineas = $lineaModel->listar($busquedaLinea);
    if ($busquedaLinea !== '' && !$lineas) {
        $mensajeBusquedaLinea = [
            'tipo' => 'danger',
            'mensaje' => 'La linea esta mal escrita o no esta registrada. Escribe el nombre completo.',
        ];
    }
    $lineaSeleccionadaId = (int) ($_GET['id_linea'] ?? $_POST['id_linea'] ?? 0);
    $lineaSeleccionada = $lineaSeleccionadaId > 0
        ? $lineaModel->obtener($lineaSeleccionadaId)
        : null;
    $camionesPensionados = $lineaSeleccionada
        ? $lineaModel->listarCamionesPensionados($lineaSeleccionadaId)
        : [];

    include 'views/Linea.php';
}

else if ($menu === 'control_diario') {
    require_once 'models/ControlDiarioModel.php';
    require_once 'controllers/ControlDiarioController.php';

    $conexion = (new Database())->getConnection();
    $controlDiarioModel = new ControlDiarioModel($conexion);
    $controlDiarioController = new ControlDiarioController($controlDiarioModel);
    $usuarioControlDiario = (int) ($_SESSION['usuario_id'] ?? 0);

    if (!$controlDiarioModel->tieneAcceso($usuarioControlDiario)) {
        abort(403, 'No tienes permiso para acceder a Control diario.');
    }
    $siguienteFolioControlDiario = str_pad(
        (string) $controlDiarioModel->obtenerSiguienteFolio(),
        4,
        '0',
        STR_PAD_LEFT
    );

    $vistaControlDiario = $_GET['vista'] ?? 'entrada';
    if (!is_string($vistaControlDiario) || !in_array($vistaControlDiario, ['entrada', 'salida', 'listado', 'reporte', 'fondo', 'material', 'imprimir'], true)) {
        $vistaControlDiario = 'entrada';
    }

    $mensajeControlDiario = $_SESSION['control_diario_mensaje'] ?? null;
    $folioCreadoControlDiario = (int) ($_SESSION['control_diario_folio_creado'] ?? 0);
    $folioSalidaControlDiario = (int) ($_SESSION['control_diario_folio_salida'] ?? 0);
    unset(
        $_SESSION['control_diario_mensaje'],
        $_SESSION['control_diario_folio_creado'],
        $_SESSION['control_diario_folio_salida']
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();

        try {
            $accionControlDiario = $_POST['accion'] ?? '';
            if ($accionControlDiario === 'registrar_entrada') {
                $resultadoControlDiario = $controlDiarioController->registrarEntrada($_POST, $usuarioControlDiario);
            } elseif ($accionControlDiario === 'registrar_salida') {
                $resultadoControlDiario = $controlDiarioController->registrarSalida($_POST, $usuarioControlDiario);
            } elseif ($accionControlDiario === 'guardar_saldo_inicial') {
                $resultadoControlDiario = $controlDiarioController->guardarSaldoInicial($_POST, $usuarioControlDiario);
            } elseif ($accionControlDiario === 'registrar_egreso_fondo') {
                $resultadoControlDiario = $controlDiarioController->registrarEgresoFondo($_POST, $usuarioControlDiario);
            } elseif ($accionControlDiario === 'iniciar_turno') {
                $resultadoControlDiario = $controlDiarioController->iniciarTurno($_POST, $usuarioControlDiario);
            } elseif ($accionControlDiario === 'cerrar_turno') {
                $resultadoControlDiario = $controlDiarioController->cerrarTurno($_POST, $usuarioControlDiario);
            } else {
                $resultadoControlDiario = [
                    'tipo' => 'danger',
                    'mensaje' => 'La operación solicitada no es válida.',
                ];
            }

            if ($resultadoControlDiario['tipo'] === 'success') {
                $_SESSION['control_diario_mensaje'] = $resultadoControlDiario['mensaje'];
                if ($accionControlDiario === 'registrar_entrada') {
                    $_SESSION['control_diario_folio_creado'] = (int) $resultadoControlDiario['folio'];
                    redirect('index.php?menu=control_diario&vista=entrada');
                }

                if ($accionControlDiario === 'registrar_salida') {
                    $_SESSION['control_diario_folio_salida'] = (int) $resultadoControlDiario['folio'];
                    redirect('index.php?menu=control_diario&vista=salida');
                }

                if (in_array($accionControlDiario, ['iniciar_turno', 'cerrar_turno'], true)) {
                    redirect('index.php?menu=control_diario&vista=material');
                }

                redirect('index.php?menu=control_diario&vista=fondo');
            }

            $mensajeControlDiario = $resultadoControlDiario;
        } catch (PDOException $e) {
            logger('Error de base de datos en Control diario: ' . $e->getMessage());
            $mensajeControlDiario = [
                'tipo' => 'danger',
                'mensaje' => 'No se pudo completar la operación. Verifica los datos e inténtalo nuevamente.',
            ];
        } catch (RuntimeException $e) {
            logger('Error de operación en Control diario: ' . $e->getMessage());
            $mensajeControlDiario = [
                'tipo' => 'danger',
                'mensaje' => 'No se pudo completar la operación. Inténtalo nuevamente.',
            ];
        }
    }

    $folioCreadoControlDiario = (int) ($folioCreadoControlDiario ?: ($_SESSION['control_diario_folio_creado'] ?? 0));
    $folioSalidaControlDiario = (int) ($folioSalidaControlDiario ?: ($_SESSION['control_diario_folio_salida'] ?? 0));
    unset($_SESSION['control_diario_folio_creado'], $_SESSION['control_diario_folio_salida']);

    $fechaControlDiario = $_GET['fecha'] ?? date('Y-m-d');
    $fechaControlDiario = is_string($fechaControlDiario) ? $fechaControlDiario : '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaControlDiario)) {
        $mensajeControlDiario = [
            'tipo' => 'danger',
            'mensaje' => 'La fecha indicada no es válida; se muestran los datos de hoy.',
        ];
        $fechaControlDiario = date('Y-m-d');
    } else {
        [$anioControlDiario, $mesControlDiario, $diaControlDiario] = array_map(
            'intval',
            explode('-', $fechaControlDiario)
        );
        if (!checkdate($mesControlDiario, $diaControlDiario, $anioControlDiario)) {
            $mensajeControlDiario = [
                'tipo' => 'danger',
                'mensaje' => 'La fecha indicada no existe; se muestran los datos de hoy.',
            ];
            $fechaControlDiario = date('Y-m-d');
        }
    }

    if ($vistaControlDiario === 'imprimir') {
        $folioImpresion = filter_var(
            $_GET['folio'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if ($folioImpresion === false) {
            abort(404);
        }

        $boletoImpresion = $controlDiarioModel->obtenerBoleto((int) $folioImpresion);
        if (!$boletoImpresion) {
            abort(404);
        }

        include 'views/ControlDiarioBoletoPrintView.php';
    } elseif ($vistaControlDiario === 'reporte') {
        try {
            $reporteControlDiario = $controlDiarioModel->obtenerReporteDiario($fechaControlDiario);
        } catch (PDOException $e) {
            logger('Error cargando reporte de Control diario: ' . $e->getMessage());
            $mensajeControlDiario = [
                'tipo' => 'danger',
                'mensaje' => 'No fue posible cargar el reporte diario. Inténtalo nuevamente.',
            ];
            $reporteControlDiario = [];
        }

        include 'views/ControlDiarioReporteView.php';
    } else {
        $busquedaControlDiario = $_GET['buscar'] ?? '';
        $busquedaControlDiario = is_string($busquedaControlDiario) ? trim($busquedaControlDiario) : '';
        $busquedaSalidaControlDiario = $busquedaControlDiario;

        if (mb_strlen($busquedaControlDiario, 'UTF-8') > 60) {
            $mensajeControlDiario = [
                'tipo' => 'danger',
                'mensaje' => 'La búsqueda no puede superar los 60 caracteres.',
            ];
            $busquedaControlDiario = mb_substr($busquedaControlDiario, 0, 60, 'UTF-8');
            $busquedaSalidaControlDiario = $busquedaControlDiario;
        }

        $tarifasControlDiario = $vistaControlDiario === 'entrada'
            ? $controlDiarioModel->obtenerTarifasActivas()
            : [];
        $activosControlDiario = $vistaControlDiario === 'salida'
            ? $controlDiarioModel->listarActivos($busquedaControlDiario)
            : [];
        $boletosControlDiario = $vistaControlDiario === 'listado'
            ? $controlDiarioModel->listarDelDia($fechaControlDiario, $busquedaControlDiario)
            : [];
        $fondoControlDiario = $controlDiarioModel->obtenerResumenFondo();
        $egresosControlDiario = $vistaControlDiario === 'fondo'
            ? $controlDiarioModel->listarEgresosFondoDelDia($fechaControlDiario)
            : [];
        $turnoAbiertoControlDiario = $vistaControlDiario === 'material'
            ? $controlDiarioModel->obtenerTurnoAbierto()
            : null;

        include 'views/ControlDiarioView.php';
    }
}

else if ($menu == 'perfil') {
    if (!isset($_SESSION['usuario_id'])) {
        redirect('index.php?menu=login');
    }

    $usuarioModel = new Usuario((new Database())->getConnection());
    $usuarioActual = $usuarioModel->obtenerPorId((int) $_SESSION['usuario_id']);

    if (!$usuarioActual) {
        $_SESSION = [];
        session_destroy();
        redirect('index.php?menu=login');
    }

    $_SESSION['usuario'] = $usuarioActual;

    include 'views/PerfilView.php';

}


else if ($menu == 'password') {

    include 'views/CambiarContraseñaView.php';

}


else {
    include 'views/Error404View.php';
}


//este es el index 
?>