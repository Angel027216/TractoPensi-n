<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva contraseña | Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=autofill-20261125">
    <link rel="stylesheet" href="public/assets/css/login.css?v=strong-password-20261125">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
  </head>
  <body class="app-auth-page">
    <div class="login-layout">
      <main class="login-access">
        <section class="login-card" aria-labelledby="reset-title">
          <div class="login-brand">
            <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión San Leonardo">
          </div>

          <header class="login-intro">
            <h1 id="reset-title">Crea una contraseña <span>nueva</span></h1>
            <p>Usa al menos 9 caracteres, idealmente 12 o más, para proteger tu cuenta</p>
          </header>

          <?php if (!empty($errorRestablecimiento)): ?>
            <div class="alert alert-danger login-alert" role="alert"><?= e($errorRestablecimiento) ?></div>
          <?php endif; ?>

          <?php if ($tokenRestablecimientoValido): ?>
            <form class="login-form" data-loading-overlay method="post" action="index.php?menu=restablecer">
              <?= csrf_input() ?>
              <input type="hidden" name="token" value="<?= e($tokenRestablecimiento) ?>">
              <div class="login-field">
                <label for="nueva-password"><i class="mdi mdi-lock" aria-hidden="true"></i> Nueva contraseña</label>
                <div class="login-input-wrap">
                  <i class="mdi mdi-lock-outline login-input-icon" aria-hidden="true"></i>
                  <input type="password" name="password" id="nueva-password" placeholder="Ingresa tu nueva contraseña" minlength="9" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{9,128}" title="Usa de 9 a 128 caracteres, con mayúsculas, minúsculas, números y un símbolo." autocomplete="new-password" required>
                </div>
                <div class="register-password-hint">
                  <p>Combina mayúsculas, minúsculas, números y símbolos. Evita secuencias, palabras obvias y datos personales, y usa una contraseña distinta para cada cuenta.</p>
                  <p class="register-password-example">Ejemplo de formato (no lo uses literalmente): <strong>Cafe-Luna-93!Libro</strong></p>
                </div>
              </div>

              <div class="login-field">
                <label for="confirmar-password"><i class="mdi mdi-lock-check-outline" aria-hidden="true"></i> Confirmar contraseña</label>
                <div class="login-input-wrap">
                  <i class="mdi mdi-lock-outline login-input-icon" aria-hidden="true"></i>
                  <input type="password" name="confirmar_password" id="confirmar-password" placeholder="Repite tu nueva contraseña" minlength="9" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{9,128}" title="Usa de 9 a 128 caracteres, con mayúsculas, minúsculas, números y un símbolo." autocomplete="new-password" required>
                </div>
              </div>

              <button type="submit" class="login-submit">
                <i class="mdi mdi-lock-reset" aria-hidden="true"></i>
                <span>Guardar contraseña</span>
              </button>
            </form>
          <?php else: ?>
            <div class="login-alert alert alert-warning" role="status">
              Este enlace no es válido o ya venció. Solicita uno nuevo para continuar.
            </div>
            <p class="login-register"><a href="index.php?menu=recuperar">Solicitar otro enlace</a></p>
          <?php endif; ?>
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
    <script src="public/assets/js/form-loading.js?v=loader-20261121"></script>
  </body>
</html>
