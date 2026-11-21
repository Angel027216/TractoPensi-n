<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña | Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css">
    <link rel="stylesheet" href="public/assets/css/login.css">
    <link rel="shortcut icon" href="public/assets/images/favicon.png">
  </head>
  <body class="app-auth-page">
    <div class="login-layout">
      <main class="login-access">
        <section class="login-card" aria-labelledby="recover-title">
          <div class="login-brand">
            <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión San Leonardo">
          </div>

          <header class="login-intro">
            <h1 id="recover-title">Recupera tu <span>contraseña</span></h1>
            <p>Te enviaremos un enlace para crear una nueva</p>
          </header>

          <?php if (!empty($errorRecuperacion)): ?>
            <div class="alert alert-danger login-alert" role="alert"><?= e($errorRecuperacion) ?></div>
          <?php endif; ?>
          <?php if (!empty($mensajeRecuperacion)): ?>
            <div class="alert alert-success login-alert" role="status"><?= e($mensajeRecuperacion) ?></div>
          <?php endif; ?>

          <form class="login-form" method="post" action="index.php?menu=recuperar">
            <?= csrf_input() ?>
            <div class="login-field">
              <label for="correo-recuperacion"><i class="mdi mdi-email-outline" aria-hidden="true"></i> Correo electrónico</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-email-outline login-input-icon" aria-hidden="true"></i>
                <input type="email" name="email" id="correo-recuperacion" placeholder="Ingresa el correo de tu cuenta" autocomplete="email" required>
              </div>
            </div>

            <button type="submit" class="login-submit">
              <i class="mdi mdi-email-send-outline" aria-hidden="true"></i>
              <span>Enviar enlace</span>
            </button>

            <div class="login-divider" aria-hidden="true">
              <span></span><i class="mdi mdi-truck"></i><span></span>
            </div>

            <p class="login-register"><a href="index.php?menu=login">Volver a iniciar sesión</a></p>
          </form>
        </section>
      </main>

      <aside class="login-visual">
        <div class="login-benefits">
          <div class="login-benefit">
            <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
            <span>Seguridad</span>
          </div>
          <div class="login-benefit">
            <i class="mdi mdi-settings" aria-hidden="true"></i>
            <span>Control Operativo</span>
          </div>
          <div class="login-benefit">
            <i class="mdi mdi-chart-bar" aria-hidden="true"></i>
            <span>Administración Eficiente</span>
          </div>
        </div>

        <p class="login-visual-footer"><span></span>Tracto Pensión San Leonardo&nbsp; · &nbsp;Siempre contigo en el camino<span></span></p>
      </aside>
    </div>
  </body>
</html>
