<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css">
    <link rel="stylesheet" href="public/assets/css/login.css">
    <link rel="shortcut icon" href="public/assets/images/favicon.png">
  </head>
  <body class="app-auth-page">
    <div class="login-layout">
      <main class="login-access">
        <section class="login-card" aria-labelledby="login-title">
          <div class="login-brand">
            <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión San Leonardo">
          </div>

          <header class="login-intro">
            <h1 id="login-title">¡Bienvenido de <span>nuevo!</span></h1>
            <p>Accede al sistema para continuar</p>
          </header>

          <?php if (!empty($_SESSION['mensajeLogin'])): ?>
            <div class="alert alert-success login-alert" role="status"><?= e($_SESSION['mensajeLogin']) ?></div>
            <?php unset($_SESSION['mensajeLogin']); ?>
          <?php endif; ?>
          <?php if (!empty($errorLogin)): ?>
            <div class="alert alert-danger login-alert" role="alert"><?= e($errorLogin) ?></div>
          <?php endif; ?>

          <form class="login-form" method="post" action="index.php?menu=login">
            <?= csrf_input() ?>
            <div class="login-field">
              <label for="identificador"><i class="mdi mdi-account" aria-hidden="true"></i> Usuario</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-account-outline login-input-icon" aria-hidden="true"></i>
                <input type="text" name="identificador" id="identificador" placeholder="Ingresa tu usuario" autocomplete="username" required>
              </div>
            </div>

            <div class="login-field">
              <label for="login-password"><i class="mdi mdi-lock" aria-hidden="true"></i> Contraseña</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-lock-outline login-input-icon" aria-hidden="true"></i>
                <input type="password" name="password" id="login-password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
                <button class="login-password-toggle" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                  <i class="mdi mdi-eye-off-outline" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="login-options">
              <label class="login-remember" for="login-remember">
                <input type="checkbox" id="login-remember">
                <span>Mantener sesión iniciada</span>
              </label>
              <a href="#" class="login-forgot">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="login-submit">
              <i class="mdi mdi-login-variant" aria-hidden="true"></i>
              <span>Ingresar</span>
            </button>

            <div class="login-divider" aria-hidden="true">
              <span></span><i class="mdi mdi-truck"></i><span></span>
            </div>

            <p class="login-register">¿No tienes cuenta? <a href="index.php?menu=registro">Crear cuenta</a></p>
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

    <script>
      (function () {
        var password = document.getElementById('login-password');
        var toggle = document.querySelector('.login-password-toggle');
        var icon = toggle.querySelector('i');

        toggle.addEventListener('click', function () {
          var isVisible = password.type === 'text';
          password.type = isVisible ? 'password' : 'text';
          toggle.setAttribute('aria-pressed', String(!isVisible));
          toggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
          icon.className = isVisible ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
        });
      }());
    </script>
  </body>
</html>
