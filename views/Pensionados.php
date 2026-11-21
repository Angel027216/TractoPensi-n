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
		<link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
	</head>
	<body>
		<div class="container-scroller">
			<?= NavBarView(); ?>

			<div class="container-fluid page-body-wrapper">
				<div id="settings-trigger"><i class="mdi mdi-settings"></i></div>

				<?= SideBarView(); ?>

				<div class="main-panel">
					<div class="content-wrapper">
						<div class="page-header">
							<h3 class="page-title">
								<i class="mdi mdi-account-group text-dark mr-2"></i>
								Pensionados
							</h3>
						</div>

						<div class="card border-0 shadow-sm">
							<div class="card-body">
								<h4 class="font-weight-bold mb-1">Consultar pensionados por línea</h4>
								<p class="text-muted mb-4">Las personas pensionadas aparecen agrupadas por su línea de transporte.</p>

								<form method="get" action="index.php" class="form-row mb-4">
									<input type="hidden" name="menu" value="pensionados">
									<div class="col-md-9 mb-2">
										<label for="buscarLinea" class="font-weight-bold">Nombre de la línea</label>
										<input id="buscarLinea" type="search" name="buscar" class="form-control" value="<?= e($busquedaPensionados) ?>" placeholder="Escribe el nombre de una línea">
									</div>
									<div class="col-md-3 d-flex align-items-end mb-2">
										<button type="submit" class="btn btn-primary btn-block"><i class="mdi mdi-magnify mr-1"></i>Buscar</button>
									</div>
								</form>

								<?php if ($pensionadosPorLinea): ?>
									<?php foreach ($pensionadosPorLinea as $nombreLinea => $camionesLinea): ?>
										<div class="border rounded p-3 mb-3">
											<div class="d-flex justify-content-between align-items-center mb-3">
												<h5 class="font-weight-bold mb-0"><?= e($nombreLinea) ?></h5>
												<span class="badge badge-light"><?= count($camionesLinea) ?> pensionados</span>
											</div>
											<div class="table-responsive">
												<table class="table table-sm table-hover mb-0">
													<thead><tr><th>Placa</th><th>Modelo</th><th>Pensionado</th><th>Estado</th><th>Próximo pago</th></tr></thead>
													<tbody>
														<?php foreach ($camionesLinea as $pensionado): ?>
															<tr>
																<td><strong><?= e((string) $pensionado['placa']) ?></strong></td>
																<td><?= e((string) $pensionado['modelo']) ?></td>
																<td><?= e((string) ($pensionado['pensionado'] ?? 'Sin usuario')) ?></td>
																<td><span class="badge badge-info"><?= e((string) $pensionado['estado_camion']) ?></span></td>
																<td><?= e((string) ($pensionado['proximo_pago'] ?? 'Pendiente de asignar')) ?></td>
															</tr>
														<?php endforeach; ?>
													</tbody>
												</table>
											</div>
										</div>
									<?php endforeach; ?>
								<?php else: ?>
									<div class="text-center text-muted py-4">No hay pensionados para esa búsqueda.</div>
								<?php endif; ?>
							</div>
						</div>
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
