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

    return enviar_correo_texto($email, $asunto, $mensaje);
}

function enviar_correo_texto(string $email, string $asunto, string $mensaje): bool
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
        return enviar_por_smtp_gmail($email, $asunto, $mensaje, $remitente, $nombreRemitente, $usuarioSmtp, $passwordSmtp, $archivoCertificados, $verificarCertificado);
    }

    $cabeceras = [
        'From: "' . addcslashes($nombreRemitente, '"\\') . '" <' . $remitente . '>',
        'Reply-To: ' . $remitente,
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $enviado = mail(
        $email,
        $asunto,
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
    bool $verificarCertificado = true
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
        $cabeceras = [
            'From: ' . $nombreRemitente . ' <' . $remitente . '>',
            'To: <' . $destinatario . '>',
            'Subject: ' . $asunto,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        $contenido = implode("\r\n", $cabeceras) . "\r\n\r\n" . str_replace("\n.", "\n..", $mensaje);
        $correcto = $enviar($contenido . "\r\n.");
    }

    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (!$correcto) {
        logger('Gmail rechazó el envío del código de verificación. Respuesta SMTP: ' . ($ultimaRespuesta ?: $ultimoCodigo ?: 'sin respuesta'));
    }

    return $correcto;
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
