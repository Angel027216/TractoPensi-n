<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css">
    <link rel="stylesheet" href="public/assets/css/login.css?v=loader-20261121">
    <link rel="shortcut icon" href="public/assets/images/favicon.png">
  </head>
  <body class="app-auth-page">
    <div class="login-layout">
      <main class="login-access">
        <section class="login-card register-card" aria-labelledby="register-title">
          <div class="login-brand">
            <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión San Leonardo">
          </div>

          <header class="login-intro">
            <h1 id="register-title">¿Eres nuevo?</h1>
            <p>¡Regístrate hoy! Solo toma unos pasos</p>
          </header>

          <?php if (!empty($errores)): ?>
            <div class="alert alert-danger login-alert" role="alert">
              <?= e(implode(' ', $errores)) ?>
            </div>
          <?php endif; ?>

          <form class="login-form" data-loading-overlay method="POST" action="index.php?menu=registro">
            <?= csrf_input() ?>
            <div class="login-field">
              <label for="nombre"><i class="mdi mdi-account" aria-hidden="true"></i> Nombre de usuario</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-account-outline login-input-icon" aria-hidden="true"></i>
                <input type="text" id="nombre" name="nombre" placeholder="Elige un nombre de usuario" autocomplete="username" required>
              </div>
            </div>

            <div class="login-field">
              <label for="email"><i class="mdi mdi-email-outline" aria-hidden="true"></i> Correo electrónico</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-email-outline login-input-icon" aria-hidden="true"></i>
                <input type="email" id="email" name="email" placeholder="Ingresa tu correo electrónico" autocomplete="email" required>
              </div>
            </div>

            <div class="login-field">
              <label for="register-password"><i class="mdi mdi-lock" aria-hidden="true"></i> Contraseña</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-lock-outline login-input-icon" aria-hidden="true"></i>
                <input type="password" id="register-password" name="password" placeholder="Crea una contraseña" minlength="6" autocomplete="new-password" required>
              </div>
            </div>

            <div class="register-terms">
              <label class="login-remember" for="terminos">
                <input type="checkbox" id="terminos" name="terminos" required>
                <span>Acepto todos los términos y condiciones</span>
              </label>
            </div>

            <button type="submit" class="login-submit">
              <i class="mdi mdi-account-plus" aria-hidden="true"></i>
              <span>Crear cuenta</span>
            </button>

            <div class="login-divider" aria-hidden="true">
              <span></span><i class="mdi mdi-truck"></i><span></span>
            </div>

            <p class="login-register">¿Ya tienes una cuenta? <a href="index.php?menu=login">Iniciar sesión</a></p>
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

    <?php if (!empty($usuarioRepetido)): ?>
      <div class="modal fade" id="usuarioRepetidoModal" tabindex="-1" role="dialog" aria-labelledby="usuarioRepetidoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title" id="usuarioRepetidoLabel">Usuario repetido</h5>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body text-center">
              Elige otro nombre de usuario.
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
    <?php if (!empty($usuarioRepetido)): ?>
      <script>
        $(function() {
          $('#usuarioRepetidoModal').modal('show');
        });
      </script>
    <?php endif; ?>
    <script src="public/assets/js/form-loading.js?v=loader-20261121"></script>
  </body>
</html>
