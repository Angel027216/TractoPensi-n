<?php
require_once 'views/includes/SideBarView.php';
require_once 'views/includes/NavBarView.php';

$ocupados = (int) ($panelData['occupancy']['occupied'] ?? 0);
$disponibles = (int) ($panelData['occupancy']['available'] ?? 0);
$totalEspacios = $ocupados + $disponibles;
$meses = [
  1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
  5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
  9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Dashboard | Tracto Pensión</title>
  <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="public/assets/vendors/flag-icon-css/css/flag-icon.min.css">
  <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=nav-20261121">
  <link rel="stylesheet" href="public/assets/css/dashboard.css?v=nav-20261121">
  <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
</head>
<body class="app-dashboard">
  <div class="container-scroller">
    <?= NavBarView(); ?>
    <div class="container-fluid page-body-wrapper">
      <?= SideBarView(); ?>
      <main class="main-panel">
        <div class="content-wrapper">
          <section class="dashboard-hero">
            <div class="dashboard-hero-copy">
              <span class="dashboard-kicker"><i class="mdi mdi-shield-check"></i> CENTRO DE CONTROL</span>
              <h1>Tracto Pensión<br><strong>San Leonardo</strong></h1>
              <p>Control y administración de tu pensión de tractocamiones</p>
            </div>
            <img src="public/assets/images/Dashboar1.png" alt="" class="dashboard-truck" aria-hidden="true" loading="eager" decoding="async">
          </section>

          <section class="dashboard-heading">
            <div>
              <span class="dashboard-section-icon"><i class="mdi mdi-chart-bar"></i></span>
              <div>
                <h2>Dashboard</h2>
                <p>Resumen general del estado del sistema</p>
              </div>
            </div>
            <div class="dashboard-welcome"><i class="mdi mdi-check-circle"></i><span><strong>Bienvenido</strong><small>Tracto Pensión San Leonardo</small></span></div>
          </section>

          <section class="dashboard-stat-grid">
            <?php
            $statStyles = ['blue', 'green', 'orange', 'purple', 'cyan'];
            foreach ($panelData['stats'] as $index => $stat):
              $style = $statStyles[$index % count($statStyles)];
            ?>
              <article class="dashboard-stat <?= $style ?>">
                <div class="dashboard-stat-icon"><i class="mdi <?= htmlspecialchars($stat['icon']) ?>"></i></div>
                <div class="dashboard-stat-copy">
                  <span><?= htmlspecialchars($stat['title']) ?></span>
                  <strong><?= htmlspecialchars($stat['value']) ?></strong>
                  <small><i class="mdi mdi-information-outline"></i> <?= htmlspecialchars($stat['subtitle']) ?></small>
                </div>
              </article>
            <?php endforeach; ?>
          </section>

          <section class="dashboard-chart-grid">
            <article class="dashboard-panel-card occupancy-card">
              <header><h3><i class="mdi mdi-chart-donut"></i> Ocupación Actual</h3></header>
              <div class="occupancy-content">
                <div class="occupancy-doughnut">
                  <canvas id="ocupacionChart" width="190" height="190"></canvas>
                  <div><strong><?= $totalEspacios ?></strong><span>Espacios</span></div>
                </div>
                <div class="occupancy-legend">
                  <p><i class="legend-dot occupied"></i><span>Ocupado</span><strong><?= $ocupados ?></strong></p>
                  <p><i class="legend-dot available"></i><span>Disponible</span><strong><?= $disponibles ?></strong></p>
                  <hr>
                  <small><i class="mdi mdi-layers"></i> Total de espacios <b><?= $totalEspacios ?></b></small>
                </div>
              </div>
            </article>

            <article class="dashboard-panel-card activity-card">
              <header>
                <h3><i class="mdi mdi-chart-line"></i> Actividad Reciente</h3>
                <div class="chart-filter"><span class="active">Últimos 7 días</span><span>30 días</span><span>Trimestre</span></div>
              </header>
              <div class="activity-chart"><canvas id="actividadChart"></canvas></div>
            </article>
          </section>

          <section class="dashboard-bottom">
            <div><i class="mdi mdi-calendar-month"></i><strong><?= date('d') ?></strong><span><?= $meses[(int) date('n')] ?> <?= date('Y') ?><small>Fecha actual</small></span></div>
            <div><i class="mdi mdi-clock-outline"></i><strong><?= date('H:i') ?></strong><span>Hora del sistema</span></div>
            <div><i class="mdi mdi-circle"></i><strong>Sistema en línea</strong><span>Sin incidencias</span></div>
            <div class="dashboard-bottom-message">Tu pensión,<br><strong>nuestra prioridad</strong></div>
          </section>
        </div>
        <footer class="footer">
          <div class="footer-inner-wraper">
            <div class="d-flex justify-content-start">
              <span class="text-muted text-left d-block">Copyright © 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.</span>
            </div>
          </div>
        </footer>
      </main>
    </div>
  </div>
  <script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="public/assets/vendors/chart.js/Chart.min.js"></script>
  <script src="public/assets/js/off-canvas.js"></script>
  <script src="public/assets/js/hoverable-collapse.js"></script>
  <script src="public/assets/js/misc.js"></script>
  <script>
    new Chart(document.getElementById('ocupacionChart'), {
      type: 'doughnut',
      data: { datasets: [{ data: [<?= $ocupados ?>, <?= $disponibles ?>], backgroundColor: ['#16dbc3', '#1974e8'], borderWidth: 0 }] },
      options: { responsive: false, cutoutPercentage: 72, legend: { display: false }, tooltips: { enabled: true } }
    });
    new Chart(document.getElementById('actividadChart'), {
      type: 'line',
      data: { labels: <?= json_encode($panelData['activity']['labels'] ?? [], JSON_THROW_ON_ERROR) ?>, datasets: [{
        data: <?= json_encode($panelData['activity']['values'] ?? [], JSON_THROW_ON_ERROR) ?>, borderColor: '#13b9ff', backgroundColor: 'rgba(19,185,255,.16)',
        borderWidth: 3, pointBackgroundColor: '#ffffff', pointBorderColor: '#13b9ff', pointBorderWidth: 3, pointRadius: 4, fill: true, tension: .4
      }] },
      options: {
        responsive: true, maintainAspectRatio: false, legend: { display: false },
        scales: { yAxes: [{ ticks: { fontColor: '#8fa7c5', beginAtZero: true }, gridLines: { color: 'rgba(143,167,197,.16)' } }],
          xAxes: [{ ticks: { fontColor: '#8fa7c5' }, gridLines: { color: 'rgba(143,167,197,.08)' } }] },
        tooltips: { displayColors: false }
      }
    });
  </script>
</body>
</html>
