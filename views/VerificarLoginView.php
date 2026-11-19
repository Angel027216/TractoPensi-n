<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verificación de inicio de sesión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css">
  </head>
  <body class="app-auth-page">
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
          <div class="row flex-grow">
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
              <div class="auth-form-transparent text-left p-3">
                <div class="brand-logo">
                  <img src="public/assets/images/logotractopension.png" alt="Tracto Pensión">
                </div>
                <h4>Verifica tu inicio de sesión</h4>
                <h6 class="font-weight-light">Enviamos un código de 6 dígitos a tu correo electrónico.</h6>
                <?php if (!empty($errorVerificacion)): ?>
                  <div class="alert alert-danger"><?= e($errorVerificacion) ?></div>
                <?php endif; ?>
                <form class="pt-3" method="post" action="index.php?menu=verificar">
                  <?= csrf_input() ?>
                  <div class="form-group">
                    <label for="codigo">Código de verificación</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="codigo" id="codigo" class="form-control form-control-lg" autocomplete="one-time-code" required>
                  </div>
                  <div class="my-3">
                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">Verificar</button>
                  </div>
                </form>
                <div class="text-center mt-4 font-weight-light">
                  <a href="index.php?menu=logout" class="text-primary">Cancelar y cerrar sesión</a>
                </div>
              </div>
            </div>
            <div class="col-lg-6 login-half-bg d-flex flex-row"></div>
          </div>
        </div>
      </div>
    </div>
    <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
  </body>
</html>
