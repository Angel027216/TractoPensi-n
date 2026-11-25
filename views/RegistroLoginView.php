<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=autofill-20261125">
    <link rel="stylesheet" href="public/assets/css/login.css?v=strong-password-20261125">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
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
                <input type="password" id="register-password" name="password" placeholder="Crea una contraseña segura" minlength="9" maxlength="128" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s]).{9,128}" title="Usa de 9 a 128 caracteres, con mayúsculas, minúsculas, números y un símbolo." autocomplete="new-password" required>
              </div>
              <div class="register-password-hint">
                <p>Para proteger tu cuenta, crea una contraseña:</p>
                <ul>
                  <li>De al menos 9 caracteres; lo ideal son 12 o más.</li>
                  <li>Con mayúsculas, minúsculas, números y símbolos.</li>
                  <li>Única para esta cuenta; evita reutilizarla.</li>
                  <li>Sin secuencias (como «1234» o «abcd»), palabras obvias ni datos personales.</li>
                </ul>
                <p class="register-password-example">Puedes unir palabras sin relación con símbolos. Ejemplo de formato (no lo uses literalmente): <strong>Cafe-Luna-93!Libro</strong></p>
              </div>
            </div>

            <div class="login-field">
              <label for="tipo-cuenta"><i class="mdi mdi-account-cog-outline" aria-hidden="true"></i> Tipo de perfil</label>
              <div class="login-input-wrap">
                <i class="mdi mdi-account-group-outline login-input-icon" aria-hidden="true"></i>
                <select class="login-role-select" id="tipo-cuenta" name="tipo_cuenta" required>
                  <option value="" disabled <?= empty($tipoCuenta) ? 'selected' : '' ?>>Selecciona tu tipo de perfil</option>
                  <option value="propietario" <?= ($tipoCuenta ?? '') === 'propietario' ? 'selected' : '' ?>>Propietario / Administrador</option>
                  <option value="trabajador" <?= ($tipoCuenta ?? '') === 'trabajador' ? 'selected' : '' ?>>Personal operativo (trabajador)</option>
                </select>
                <i class="mdi mdi-chevron-down login-role-arrow" aria-hidden="true"></i>
              </div>
              <small class="register-role-note">El perfil identifica tu relación con la cuenta; los permisos de acceso se asignan por separado.</small>
            </div>

            <div class="register-terms">
              <label class="login-remember" for="terminos">
                <input type="checkbox" id="terminos" name="terminos" required>
                <span>Acepto todos los</span>
              </label>
              <a class="register-terms-link" href="#terminosModal" data-toggle="modal" aria-haspopup="dialog">términos y condiciones</a>
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

    <div class="modal fade terms-modal" id="terminosModal" tabindex="-1" role="dialog" aria-labelledby="terminosModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="terminosModalLabel">Términos y condiciones</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h4>Tracto Pensión San Leonardo</h4>
            <p class="terms-updated"><strong>Última actualización: noviembre de 2026</strong></p>
            <p>Bienvenido a Tracto Pensión San Leonardo. Al registrarte y utilizar nuestra plataforma web, aceptas los siguientes términos y condiciones. Te recomendamos leerlos cuidadosamente antes de crear tu cuenta.</p>

            <h6>1. Registro de usuario</h6>
            <ul>
              <li>El usuario deberá proporcionar información real, completa y actualizada al momento de crear su cuenta.</li>
              <li>Cada usuario será responsable de mantener la confidencialidad de su contraseña.</li>
              <li>La cuenta es personal e intransferible.</li>
              <li>El usuario deberá notificar cualquier uso no autorizado de su cuenta.</li>
            </ul>

            <h6>2. Registro de vehículos</h6>
            <ul>
              <li>El usuario deberá registrar correctamente los datos de su camión, incluyendo número de placas, marca, modelo y características.</li>
              <li>La información del vehículo deberá mantenerse actualizada.</li>
              <li>El usuario será responsable de que los datos registrados correspondan al vehículo que ingresa a la pensión.</li>
            </ul>

            <h6>3. Asignación y uso de espacios</h6>
            <ul>
              <li>La asignación de espacios dependerá de la disponibilidad dentro de las instalaciones.</li>
              <li>Los vehículos deberán permanecer en el espacio que les sea asignado.</li>
              <li>Los tráileres de doble remolque deberán ubicarse en el Terreno 2, debido a las necesidades de maniobra.</li>
              <li>El Terreno 1 estará destinado principalmente a vehículos pensionados.</li>
              <li>No se permitirá cambiar de espacio sin autorización del personal encargado.</li>
            </ul>

            <h6>4. Pagos y tarifas</h6>
            <ul>
              <li>El servicio de pensión se cobrará por periodos de 24 horas o de manera proporcional, de acuerdo con las tarifas establecidas.</li>
              <li>El usuario podrá consultar el estado de sus pagos desde su cuenta.</li>
              <li>Es responsabilidad del usuario verificar que sus pagos se encuentren registrados correctamente.</li>
              <li>Cualquier aclaración relacionada con cobros deberá realizarse directamente con la administración.</li>
            </ul>

            <h6>5. Horarios de salida</h6>
            <ul>
              <li>Una vez cumplidas las primeras 24 horas desde el ingreso del vehículo, el usuario contará con una hora adicional para realizar su salida.</li>
              <li>Después de este periodo, se aplicará el cobro correspondiente de acuerdo con las tarifas vigentes.</li>
              <li>El usuario deberá notificar su salida al personal encargado para mantener actualizado el registro del vehículo.</li>
            </ul>

            <h6>6. Seguridad y comportamiento</h6>
            <ul>
              <li>Los usuarios deberán respetar las indicaciones del personal de Tracto Pensión San Leonardo.</li>
              <li>Se deberán respetar los límites de velocidad y las áreas de circulación dentro de las instalaciones.</li>
              <li>Queda prohibido realizar actividades que pongan en riesgo a otros usuarios, vehículos o personal.</li>
              <li>Cualquier incidente, daño o situación irregular deberá reportarse inmediatamente a la administración.</li>
            </ul>

            <h6>7. Uso de la plataforma</h6>
            <ul>
              <li>La plataforma está destinada a facilitar el registro, administración y consulta de información relacionada con los vehículos y los servicios de la pensión.</li>
              <li>El usuario podrá consultar los datos de su camión, espacio asignado, pagos e historial cuando estas funciones estén disponibles.</li>
              <li>El usuario no deberá intentar modificar, alterar o acceder a información de otras cuentas.</li>
              <li>No se permitirá utilizar la plataforma para actividades ilegales o ajenas a su finalidad.</li>
            </ul>

            <h6>8. Responsabilidades del usuario</h6>
            <ul>
              <li>Proporcionar información verdadera durante el registro.</li>
              <li>Cumplir con las reglas establecidas por la administración.</li>
              <li>Realizar los pagos correspondientes al servicio.</li>
              <li>Mantener un comportamiento respetuoso dentro de las instalaciones.</li>
              <li>Informar sobre cualquier problema relacionado con su vehículo, espacio o cuenta.</li>
            </ul>

            <h6>9. Suspensión o cancelación de cuentas</h6>
            <p>La administración podrá restringir o suspender temporalmente el acceso a una cuenta cuando se detecte información falsa, uso indebido de la plataforma o incumplimiento de las condiciones establecidas.</p>
            <p>La cancelación de una cuenta no elimina las obligaciones de pago que se hayan generado previamente.</p>

            <h6>10. Protección de datos personales</h6>
            <p>Los datos proporcionados durante el registro serán utilizados para la administración de cuentas, identificación de usuarios, registro de vehículos, control de espacios y seguimiento de pagos.</p>
            <p>La información personal deberá tratarse conforme al Aviso de Privacidad de Tracto Pensión San Leonardo.</p>

            <h6>11. Modificaciones de los términos</h6>
            <p>Tracto Pensión San Leonardo podrá actualizar estos términos y condiciones cuando sea necesario para mejorar el funcionamiento de sus servicios. Los cambios deberán comunicarse a los usuarios mediante la plataforma o los medios de contacto disponibles.</p>

            <h6>12. Aceptación de los términos</h6>
            <p>Al marcar la casilla «Acepto todos los términos y condiciones» y seleccionar el botón «Crear cuenta», el usuario manifiesta que ha leído, comprendido y aceptado las condiciones establecidas para utilizar la plataforma.</p>

            <p><strong>Tracto Pensión San Leonardo</strong></p>
            <p>Gracias por confiar en nosotros. Nuestro compromiso es ofrecer un servicio organizado, seguro y eficiente.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
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
