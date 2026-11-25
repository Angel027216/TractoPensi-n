<?php
//Sirve para obligar a PHP a que respete rigurosamente los tipos de datos 
// definidas estrictamente en las funciones.
declare(strict_types=1);
define('APP_ROOT', __DIR__);
define('APP_LOG_FILE', __DIR__ . '/logs/app.log');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Función global para escapar HTML y prevenir XSS.
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function logger(string $mensaje, mixed ...$contexto): void
{
    $logDirectory = dirname(APP_LOG_FILE);
    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0775, true);
    }

    // Formateamos el mensaje con fecha, hora y un salto de línea (PHP_EOL)
    $fecha = date('Y-m-d H:i:s');
    if ($contexto) {
        $mensaje .= ' ' . implode(' ', array_map(
            static fn (mixed $valor): string => is_scalar($valor) ? (string) $valor : json_encode($valor),
            $contexto
        ));
    }

    $contenido = "[$fecha] " . $mensaje . PHP_EOL;

    // El parámetro '3' indica que escribiremos en un archivo específico
    error_log($contenido, 3, APP_LOG_FILE);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function password_cumple_requisitos(string $password): bool
{
    $longitudValida = preg_match('/\A.{9,128}\z/us', $password) === 1;
    $complejidadValida = preg_match('/[A-Z]/', $password) === 1
        && preg_match('/[a-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1
        && preg_match('/[^A-Za-z0-9\s]/', $password) === 1;

    return $longitudValida && $complejidadValida;
}

function verify_csrf_token(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        logger('Intento de solicitud con CSRF inválido.');
        abort(403, 'Solicitud no autorizada.');
    }
}

function enviar_codigo_verificacion(string $email, string $codigo): bool
{
    $asunto = 'Código de verificación - Tracto Pensión San Leonardo';
    $mensaje = "Tu código de verificación es: {$codigo}\n\n"
        . "Este código vence en 10 minutos. Si no solicitaste iniciar sesión, ignora este correo.";
    $codigoHtml = htmlspecialchars($codigo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $mensajeHtml = '<!doctype html>'
        . '<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>Código de verificación</title></head>'
        . '<body style="margin:0;padding:24px;background-color:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#19304b;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">'
        . '<tr><td align="center" style="padding:12px;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="width:100%;max-width:600px;border-collapse:separate;border-spacing:0;background-color:#ffffff;border:1px solid #dce6f0;border-radius:16px;overflow:hidden;">'
        . '<tr><td align="center" style="padding:28px 24px;background-color:#071a34;color:#ffffff;">'
        . '<div style="font-size:13px;font-weight:bold;letter-spacing:2px;color:#72caff;text-transform:uppercase;">Tracto Pensión</div>'
        . '<div style="margin-top:7px;font-size:23px;font-weight:bold;line-height:1.3;">San Leonardo</div>'
        . '</td></tr>'
        . '<tr><td style="padding:32px 32px 16px;">'
        . '<h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;color:#102b4c;">Verifica tu inicio de sesión</h1>'
        . '<p style="margin:0;font-size:15px;line-height:1.6;color:#53677e;">Usa este código para continuar de forma segura:</p>'
        . '</td></tr>'
        . '<tr><td align="center" style="padding:12px 32px 24px;">'
        . '<div style="display:inline-block;padding:16px 28px;border:1px solid #b9ddf7;border-radius:12px;background-color:#edf7ff;color:#075ca8;font-size:36px;font-weight:bold;letter-spacing:10px;line-height:1.2;">'
        . $codigoHtml
        . '</div></td></tr>'
        . '<tr><td style="padding:0 32px 28px;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background-color:#fff7e6;border-left:4px solid #f0ad32;">'
        . '<tr><td style="padding:14px 16px;color:#704d0a;font-size:15px;line-height:1.5;">'
        . '<strong style="font-size:16px;">Importante: el código vence en 10 minutos.</strong><br>'
        . 'Solicita uno nuevo si el plazo termina antes de que lo ingreses.'
        . '</td></tr></table>'
        . '<p style="margin:20px 0 0;font-size:13px;line-height:1.6;color:#687b90;">Si no solicitaste iniciar sesión, ignora este correo. No compartas este código con nadie.</p>'
        . '</td></tr>'
        . '<tr><td align="center" style="padding:18px 24px;border-top:1px solid #e6edf4;color:#8493a3;font-size:12px;">'
        . 'Mensaje automático de Tracto Pensión San Leonardo.'
        . '</td></tr></table></td></tr></table></body></html>';

    return enviar_correo_texto($email, $asunto, $mensaje, $mensajeHtml);
}

function enviar_correo_texto(string $email, string $asunto, string $mensaje, ?string $mensajeHtml = null): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logger('No se pudo enviar un correo del sistema: correo inválido.');
        return false;
    }

    $envPath = APP_ROOT . '/env.local.php';
    if (!file_exists($envPath)) {
        $envPath = APP_ROOT . '/env.php';
    }

    $env = require $envPath;
    $remitente = $env['MAIL_FROM'] ?? 'no-reply@tractopension.local';
    $nombreRemitente = $env['MAIL_FROM_NAME'] ?? 'Tracto Pension San Leonardo';
    $usuarioSmtp = (string) ($env['MAIL_USERNAME'] ?? $env['MAIL_SMTP_USER'] ?? $remitente);
    $passwordSmtp = (string) ($env['MAIL_SMTP_PASSWORD'] ?? $env['MAIL_PASSWORD'] ?? '');
    if ($passwordSmtp !== '') {
        $archivoCertificados = (string) ($env['MAIL_CA_FILE'] ?? '');
        $verificarCertificado = (bool) ($env['MAIL_VERIFY_SSL'] ?? true);
        return enviar_por_smtp_gmail($email, $asunto, $mensaje, $remitente, $nombreRemitente, $usuarioSmtp, $passwordSmtp, $archivoCertificados, $verificarCertificado, $mensajeHtml);
    }

    $cabeceras = [
        'From: =?UTF-8?B?' . base64_encode($nombreRemitente) . '?= <' . $remitente . '>',
        'Reply-To: ' . $remitente,
    ];

    if ($mensajeHtml !== null) {
        [$tipoContenido, $cuerpo] = crear_mensaje_multipart($mensaje, $mensajeHtml);
        $cabeceras[] = $tipoContenido;
        $mensaje = $cuerpo;
    } else {
        $cabeceras[] = 'Content-Type: text/plain; charset=UTF-8';
    }

    $enviado = mail(
        $email,
        '=?UTF-8?B?' . base64_encode($asunto) . '?=',
        $mensaje,
        implode("\r\n", $cabeceras),
        '-f' . $remitente
    );

    if (!$enviado) {
        $ultimoError = error_get_last();
        logger('Error de mail(): ' . (string) ($ultimoError['message'] ?? 'sin detalle'));
    }

    return $enviado;
}

function url_base_aplicacion(): string
{
    $envPath = APP_ROOT . '/env.local.php';
    if (!file_exists($envPath)) {
        $envPath = APP_ROOT . '/env.php';
    }

    $env = require $envPath;
    $urlConfigurada = trim((string) ($env['APP_URL'] ?? ''));
    if ($urlConfigurada !== '') {
        $partes = parse_url($urlConfigurada);
        if (
            !is_array($partes)
            || !in_array($partes['scheme'] ?? '', ['http', 'https'], true)
            || empty($partes['host'])
            || isset($partes['query'])
            || isset($partes['fragment'])
        ) {
            throw new RuntimeException('APP_URL debe ser una URL HTTP o HTTPS válida.');
        }

        return rtrim($urlConfigurada, '/');
    }

    $host = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
    if (!in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
        throw new RuntimeException('Configura APP_URL para generar enlaces de recuperación seguros.');
    }

    $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    $esquema = $https ? 'https' : 'http';
    $puerto = (int) ($_SERVER['SERVER_PORT'] ?? ($https ? 443 : 80));
    $puertoPredeterminado = $https ? 443 : 80;
    $puertoUrl = $puerto === $puertoPredeterminado ? '' : ':' . $puerto;
    $ruta = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    $ruta = $ruta === '/' || $ruta === '.' ? '' : '/' . trim($ruta, '/');

    return $esquema . '://' . $host . $puertoUrl . $ruta;
}

function enviar_por_smtp_gmail(
    string $destinatario,
    string $asunto,
    string $mensaje,
    string $remitente,
    string $nombreRemitente,
    string $usuario,
    string $password,
    string $archivoCertificados = '',
    bool $verificarCertificado = true,
    ?string $mensajeHtml = null
): bool {
    $errorNumero = 0;
    $errorMensaje = '';
    $contexto = stream_context_create([
        'ssl' => [
            'verify_peer' => $verificarCertificado,
            'verify_peer_name' => $verificarCertificado,
            'allow_self_signed' => !$verificarCertificado,
            'cafile' => $archivoCertificados,
        ],
    ]);
    $socket = stream_socket_client(
        'tcp://smtp.gmail.com:587',
        $errorNumero,
        $errorMensaje,
        30,
        STREAM_CLIENT_CONNECT,
        $contexto
    );

    if (!$socket) {
        logger('No se pudo conectar al SMTP de Gmail: ' . $errorMensaje);
        return false;
    }

    stream_set_timeout($socket, 30);
    $ultimoCodigo = '';
    $ultimaRespuesta = '';
    $leer = static function () use ($socket, &$ultimoCodigo, &$ultimaRespuesta): bool {
        do {
            $respuesta = fgets($socket, 515);
            if ($respuesta === false) {
                return false;
            }
        } while (isset($respuesta[3]) && $respuesta[3] === '-');

        $ultimoCodigo = substr($respuesta, 0, 3);
        $ultimaRespuesta = trim($respuesta);
        return in_array($ultimoCodigo, ['220', '221', '235', '250', '334', '354'], true);
    };
    $enviar = static function (string $comando) use ($socket, $leer): bool {
        fwrite($socket, $comando . "\r\n");
        return $leer();
    };

    $correcto = $leer()
        && $enviar('EHLO localhost')
        && $enviar('STARTTLS');

    if ($correcto) {
        $correcto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === true;
    }
    if ($correcto) {
        $correcto = $enviar('EHLO localhost')
            && $enviar('AUTH LOGIN')
            && $enviar(base64_encode($usuario))
            && $enviar(base64_encode($password))
            && $enviar('MAIL FROM:<' . $remitente . '>')
            && $enviar('RCPT TO:<' . $destinatario . '>')
            && $enviar('DATA');
    }
    if ($correcto) {
        $asuntoCodificado = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
        if ($mensajeHtml !== null) {
            [$tipoContenido, $cuerpo] = crear_mensaje_multipart($mensaje, $mensajeHtml);
        } else {
            $tipoContenido = 'Content-Type: text/plain; charset=UTF-8';
            $cuerpo = $mensaje;
        }

        $cabeceras = [
            'From: =?UTF-8?B?' . base64_encode($nombreRemitente) . '?= <' . $remitente . '>',
            'To: <' . $destinatario . '>',
            'Subject: ' . $asuntoCodificado,
            $tipoContenido,
        ];
        $contenido = implode("\r\n", $cabeceras) . "\r\n\r\n" . str_replace("\n.", "\n..", $cuerpo);
        $correcto = $enviar($contenido . "\r\n.");
    }

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (!$correcto) {
        logger('Gmail rechazó el envío del código de verificación. Respuesta SMTP: ' . ($ultimaRespuesta ?: $ultimoCodigo ?: 'sin respuesta'));
    }

    return $correcto;
}

function crear_mensaje_multipart(string $mensaje, string $mensajeHtml): array
{
    $limite = '=_TractoPension_' . bin2hex(random_bytes(16));
    $cuerpo = '--' . $limite . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($mensaje) . "\r\n"
        . '--' . $limite . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($mensajeHtml) . "\r\n"
        . '--' . $limite . "--\r\n";

    return ['Content-Type: multipart/alternative; boundary="' . $limite . '"', $cuerpo];
}

function preparar_verificacion_login(array $usuario): bool
{
    $codigo = (string) random_int(100000, 999999);
    $_SESSION['login_2fa'] = [
        'usuario' => $usuario,
        'codigo_hash' => password_hash($codigo, PASSWORD_DEFAULT),
        'vence_en' => time() + 600,
        'intentos' => 0,
    ];

    if (enviar_codigo_verificacion((string) $usuario['email'], $codigo)) {
        return true;
    }

    unset($_SESSION['login_2fa']);
    logger('No fue posible enviar el código 2FA a ' . (string) $usuario['email']);
    return false;
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function abort(int $statusCode, string $message = ''): void
{
    http_response_code($statusCode);

    switch ($statusCode) {
        case 403:
            echo $message !== '' ? e($message) : 'Acceso denegado.';
            break;
        case 404:
            require APP_ROOT . '/views/errors/404.php';
            break;
        default:
            require APP_ROOT . '/views/errors/500.php';
            break;
    }

    exit;
}

function tabla_disponible(PDO $db, string $tabla): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $stmt->execute([$tabla]);

    return (int) $stmt->fetchColumn() > 0;
}
