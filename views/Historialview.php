<?php 
require_once 'views/includes/SideBarView.php'; 
require_once 'views/includes/NavBarView.php';

// Datos de ejemplo si el controlador no provee `$historial`
if(!isset($historial) || !is_array($historial)){
  $historial = [
    ['id'=>1,'fecha'=>'2026-05-15 10:00','usuario'=>'Juan','accion'=>'Ingreso','detalles'=>'Entró al sistema'],
    ['id'=>2,'fecha'=>'2026-05-14 09:30','usuario'=>'María','accion'=>'Pago','detalles'=>'Pago de espacio #12'],
    ['id'=>3,'fecha'=>'2026-05-13 14:20','usuario'=>'Pedro','accion'=>'Salida','detalles'=>'Salió camión #7'],
  ];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tracto Pensión</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="public/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="public/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=autofill-20261125">
    <link rel="stylesheet" href="public/assets/css/historial.css?v=20261121">
    <!-- End layout styles -->
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121" />
  </head>
  <body class="app-history">
    <div class="container-scroller">
      <!-- partial:partials/_navbar.html -->
      
      <?=NavBarView(); ?>

      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_settings-panel.html -->
        <div id="settings-trigger"><i class="mdi mdi-settings"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close mdi mdi-close"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-default-theme">
            <div class="img-ss rounded-circle bg-dark border mr-3"></div>Default
          </div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme">
            <div class="img-ss rounded-circle bg-light border mr-3"></div>Light
          </div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles primary"></div>
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default light"></div>
          </div>
        </div>
        <!-- partial -->
        <!-- partial:partials/_sidebar.html -->
        
        <?=SideBarView(); ?>

        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <section class="history-hero">
              <div class="history-hero-copy">
                <span class="history-kicker"><i class="mdi mdi-history"></i> CONTROL DEL SISTEMA</span>
                <h1>Historial de Actividad</h1>
                <p>Visualiza el registro de actividades del sistema.</p>
              </div>
              <img src="public/assets/images/Dashboar1.png" alt="" class="history-hero-truck" aria-hidden="true" loading="eager" decoding="async">
            </section>

            <section class="history-card">
              <header class="history-card-header">
                <div>
                  <span class="history-card-icon"><i class="mdi mdi-format-list-bulleted"></i></span>
                  <div>
                    <h2>Registros de Actividad</h2>
                    <p>Consulta los movimientos recientes de la pensión.</p>
                  </div>
                </div>
                <span class="history-status"><i class="mdi mdi-circle"></i> Sistema en línea</span>
              </header>

              <div class="table-responsive history-table-wrap">
                <table class="table history-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Fecha</th>
                      <th>Usuario</th>
                      <th>Acción</th>
                      <th>Detalles</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(isset($historial) && is_array($historial) && count($historial) > 0): ?>
                      <?php foreach($historial as $i => $row): ?>
                        <tr>
                          <td><?= htmlspecialchars($row['id'] ?? $i+1) ?></td>
                          <td><?= htmlspecialchars($row['fecha'] ?? '') ?></td>
                          <td><?= htmlspecialchars($row['usuario'] ?? '') ?></td>
                          <td><span class="history-action"><?= htmlspecialchars($row['accion'] ?? '') ?></span></td>
                          <td><?= htmlspecialchars($row['detalles'] ?? '') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="5" class="history-empty">No hay registros en el historial.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <footer class="history-card-footer">
                <span><i class="mdi mdi-information-outline"></i> Registro de actividad del sistema</span>
                <span><?= count($historial) ?> registro<?= count($historial) === 1 ? '' : 's' ?></span>
              </footer>
            </section>

          </div>
          <!-- content-wrapper ends -->
          <!-- partial:partials/_footer.html -->
          <footer class="footer">
            <div class="footer-inner-wraper">
              <div class="d-flex justify-content-start">
                <span class="text-muted text-left d-block">Copyright © 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.</span>
              </div>
            </div>
          </footer>
          <!-- partial -->
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="public/assets/vendors/chart.js/Chart.min.js"></script>
    <script src="public/assets/vendors/jquery-circle-progress/js/circle-progress.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="public/assets/js/off-canvas.js"></script>
    <script src="public/assets/js/hoverable-collapse.js"></script>
    <script src="public/assets/js/misc.js"></script>
    <script src="public/assets/js/settings.js"></script>
    <script src="public/assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <script src="public/assets/js/dashboard.js"></script>
    <!-- End custom js for this page -->
  </body>
</html>

          