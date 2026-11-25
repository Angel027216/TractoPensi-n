<?php 
require_once 'views/includes/SideBarView.php';
require_once 'views/includes/NavBarView.php';

if (!isset($accesos) || !is_array($accesos)) {
  $accesos = [];
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Historial de Accesos - Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=autofill-20261125">
    <link rel="stylesheet" href="public/assets/css/dashboard.css?v=accesos-20261122">
    <link rel="stylesheet" href="public/assets/css/accesos.css?v=accesos-20261122">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121" />
  </head>
  <body class="app-dashboard app-accesos">
    <div class="container-scroller">
      <?=NavBarView(); ?>
      <div class="container-fluid page-body-wrapper">
        <?=SideBarView(); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <section class="dashboard-hero accesos-hero">
              <div class="dashboard-hero-copy">
                <span class="dashboard-kicker"><i class="mdi mdi-door-open"></i> CONTROL DE ACCESOS</span>
                <h1>Historial de accesos</h1>
                <p>Consulta las entradas y salidas registradas de los camiones.</p>
              </div>
              <img src="public/assets/images/Dashboar1.png" alt="" class="dashboard-truck" aria-hidden="true" loading="eager" decoding="async">
            </section>

            <section class="accesos-card">
              <header class="accesos-card-header">
                <div>
                  <span class="accesos-card-icon"><i class="mdi mdi-format-list-bulleted"></i></span>
                  <div>
                    <h2>Estado de accesos</h2>
                    <p>Registro actual de entradas y salidas</p>
                  </div>
                </div>
              </header>
              <div class="accesos-table-wrap">
                <div class="table-responsive">
                  <table class="table accesos-table mb-0">
                            <thead>
                              <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Camión</th>
                                <th>Entrada / Salida</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php if (empty($accesos)): ?>
                                <tr>
                                  <td colspan="4" class="text-muted py-4">Aún no hay accesos registrados.</td>
                                </tr>
                              <?php else: ?>
                                <?php foreach ($accesos as $row): ?>
                                  <?php
                                    $fechaAcceso = (string) ($row['fecha'] ?? '');
                                    $horaAcceso = substr((string) ($row['hora'] ?? ''), 0, 5);
                                    $tipoAcceso = strtolower((string) ($row['tipo'] ?? $row['estado'] ?? 'entrada'));
                                    $placaAcceso = (string) ($row['placa'] ?? '—');
                                    if ($fechaAcceso !== '') {
                                      $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fechaAcceso);
                                      if ($fechaObjeto) {
                                        $fechaAcceso = $fechaObjeto->format('d/m/Y');
                                      }
                                    }
                                  ?>
                                  <tr>
                                    <td><?= htmlspecialchars($fechaAcceso ?: '—') ?></td>
                                    <td><?= htmlspecialchars($horaAcceso ?: '—') ?></td>
                                    <td><strong><?= htmlspecialchars($placaAcceso) ?></strong></td>
                                    <td>
                                      <?php if ($tipoAcceso === 'entrada'): ?>
                                        <span class="badge badge-success">Entrada</span>
                                      <?php else: ?>
                                        <span class="badge badge-danger">Salida</span>
                                      <?php endif; ?>
                                    </td>
                                  </tr>
                                <?php endforeach; ?>
                              <?php endif; ?>
                            </tbody>
                          </table>
                        </div>
                      </div>
            </section>

          </div>
          <!-- content-wrapper ends -->
          <footer class="footer">
            <div class="footer-inner-wraper">
              <div class="d-flex justify-content-start">
                <span class="text-muted text-left d-block">Copyright © 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.</span>
              </div>
            </div>
          </footer>
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>

    <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="public/assets/js/off-canvas.js"></script>
    <script src="public/assets/js/hoverable-collapse.js"></script>
    <script src="public/assets/js/misc.js"></script>
  </body>
</html>