<?php
require_once 'views/includes/SideBarView.php';
require_once 'views/includes/NavBarView.php';

$pensionadosPorLinea = [];
foreach ($pensionados as $pensionado) {
	$nombreLinea = (string) ($pensionado['nom_linea'] ?? 'Sin línea');
	$pensionadosPorLinea[$nombreLinea][] = $pensionado;
}
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<title>Pensionados - Tracto Pensión</title>
		<link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
		<link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
		<link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=nav-20261121">
		<link rel="stylesheet" href="public/assets/css/pensionados.css?v=20261122">
		<link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
	</head>
	<body class="app-pensionados">
		<div class="container-scroller">
			<?= NavBarView(); ?>

			<div class="container-fluid page-body-wrapper">
				<div id="settings-trigger"><i class="mdi mdi-settings"></i></div>

				<?= SideBarView(); ?>

				<div class="main-panel">
					<div class="content-wrapper">
						<section class="pensionados-hero">
							<div class="pensionados-hero-copy">
								<span class="pensionados-kicker"><i class="mdi mdi-account-group"></i> ADMINISTRACIÓN DE UNIDADES</span>
								<h1>Pensionados</h1>
								<p>Consulta las personas pensionadas agrupadas por su línea de transporte.</p>
							</div>
							<img src="public/assets/images/Dashboar1.png" alt="" class="pensionados-hero-truck" aria-hidden="true" loading="eager" decoding="async">
						</section>

						<section class="pensionados-search-card">
							<div class="pensionados-section-heading">
								<span class="pensionados-section-icon"><i class="mdi mdi-magnify"></i></span>
								<div>
									<h2>Consultar pensionados por línea</h2>
									<p>Busca una línea para consultar sus unidades y el estado de sus pagos.</p>
								</div>
							</div>

								<form method="get" action="index.php" class="pensionados-search-form">
									<input type="hidden" name="menu" value="pensionados">
									<div class="pensionados-search-field">
										<label for="buscarLinea">Nombre de la línea</label>
										<div class="pensionados-input-wrap">
											<i class="mdi mdi-source-branch"></i>
											<input id="buscarLinea" type="search" name="buscar" value="<?= e($busquedaPensionados) ?>" placeholder="Escribe el nombre de una línea">
										</div>
									</div>
									<div class="pensionados-search-action">
										<button type="submit"><i class="mdi mdi-magnify"></i>Buscar</button>
									</div>
								</form>
						</section>

								<?php if ($pensionadosPorLinea): ?>
									<?php foreach ($pensionadosPorLinea as $nombreLinea => $camionesLinea): ?>
										<section class="pensionados-line-card">
											<header class="pensionados-line-header">
												<div>
													<span class="pensionados-line-icon"><i class="mdi mdi-source-branch"></i></span>
													<h2><?= e($nombreLinea) ?></h2>
												</div>
												<span class="pensionados-count"><?= count($camionesLinea) ?> pensionado<?= count($camionesLinea) === 1 ? '' : 's' ?></span>
											</header>
											<div class="pensionados-table-wrap">
												<table class="pensionados-table">
													<thead><tr><th>Placa</th><th>Modelo</th><th>Pensionado</th><th>Estado</th><th>Próximo pago</th></tr></thead>
													<tbody>
														<?php foreach ($camionesLinea as $pensionado): ?>
															<tr>
																<td><strong><?= e((string) $pensionado['placa']) ?></strong></td>
																<td><?= e((string) $pensionado['modelo']) ?></td>
																<td><?= e((string) ($pensionado['pensionado'] ?? 'Sin usuario')) ?></td>
																<td><span class="pensionados-status"><?= e((string) $pensionado['estado_camion']) ?></span></td>
																<td><?= e((string) ($pensionado['proximo_pago'] ?? 'Pendiente de asignar')) ?></td>
															</tr>
														<?php endforeach; ?>
													</tbody>
												</table>
											</div>
										</section>
									<?php endforeach; ?>
								<?php else: ?>
									<div class="pensionados-empty"><i class="mdi mdi-account-search-outline"></i><strong>No hay pensionados para esa búsqueda.</strong><span>Prueba con otro nombre de línea.</span></div>
								<?php endif; ?>
					</div>

					<footer class="footer">
						<div class="footer-inner-wraper">
							<div class="d-flex justify-content-start">
								<span class="text-muted text-left d-block">
									Copyright &copy; 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.
								</span>
							</div>
						</div>
					</footer>
				</div>
			</div>
		</div>

		<script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
		<script src="public/assets/js/off-canvas.js"></script>
		<script src="public/assets/js/hoverable-collapse.js"></script>
		<script src="public/assets/js/misc.js"></script>
		<script src="public/assets/js/settings.js"></script>
	</body>
</html>
