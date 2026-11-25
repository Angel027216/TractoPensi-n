<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de inicio de sesión | Tracto Pensión</title>
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=autofill-20261125">
    <link rel="stylesheet" href="public/assets/css/login.css?v=strong-password-20261125">
  </head>
  <body class="app-auth-page">
    <div class="login-layout">
      <main class="login-access">
        <section class="login-card" aria-labelledby="verification-title">
          <div class="login-brand">
            <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión San Leonardo">
          </div>

          <header class="login-intro">
            <h1 id="verification-title">Verifica tu <span>inicio de sesión</span></h1>
            <p>Enviamos un código de 6 dígitos a tu correo electrónico</p>
          </header>

          <?php if (!empty($errorVerificacion)): ?>
            <div class="alert alert-danger login-alert" role="alert"><?= e($errorVerificacion) ?></div>
          <?php endif; ?>

          <form class="login-form" data-loading-overlay method="post" action="index.php?menu=verificar">
            <?= csrf_input() ?>
            <div class="login-field">
              <label for="codigo"><i class="mdi mdi-shield-key-outline" aria-hidden="true"></i> Código de verificación</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-numeric-6-circle-outline login-input-icon" aria-hidden="true"></i>
                <input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="codigo" id="codigo" placeholder="Ingresa el código de 6 dígitos" autocomplete="one-time-code" autofocus required>
              </div>
            </div>

            <button type="submit" class="login-submit">
              <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
              <span>Verificar</span>
            </button>

            <div class="login-divider" aria-hidden="true">
              <span></span><i class="mdi mdi-truck"></i><span></span>
            </div>

            <p class="login-register"><a href="index.php?menu=logout">Cancelar y cerrar sesión</a></p>
          </form>
        </section>
      </main>

      <aside class="login-visual login-visual-no-truck">
        <div class="login-benefits">
          <div class="login-benefit">
            <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
            <span>Acceso seguro</span>
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
