<?php
require_once 'views/includes/SideBarView.php';
require_once 'views/includes/NavBarView.php';

$reporteControlDiario = is_array($reporteControlDiario ?? null) ? $reporteControlDiario : [];
$fechaControlDiario = (string) ($fechaControlDiario ?? date('Y-m-d'));
$mensajeControlDiario = is_array($mensajeControlDiario ?? null) ? $mensajeControlDiario : null;
$entradasReporte = is_array($reporteControlDiario['entradas'] ?? null) ? $reporteControlDiario['entradas'] : [];
$salidasReporte = is_array($reporteControlDiario['salidas'] ?? null) ? $reporteControlDiario['salidas'] : [];
$desgloseTarifasReporte = is_array($reporteControlDiario['desglose_tarifas'] ?? null) ? $reporteControlDiario['desglose_tarifas'] : [];
$egresosFondoReporte = is_array($reporteControlDiario['egresos_fondo'] ?? null) ? $reporteControlDiario['egresos_fondo'] : [];
$materialesTurnoReporte = is_array($reporteControlDiario['materiales_turno'] ?? null) ? $reporteControlDiario['materiales_turno'] : [];
$fondoReporte = is_array($reporteControlDiario['fondo'] ?? null) ? $reporteControlDiario['fondo'] : null;

$tipoUnidadReporte = [
    'CAMIONETA' => 'Camioneta',
    'TRAILER_SENCILLO' => 'Tráiler sencillo',
    'TORTON' => 'Tortón',
    'TRAILER_FULL_DOBLE' => 'Tráiler full / doble remolque',
];
$formatoFechaReporte = static function (?string $fecha): string {
    return $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '—';
};
$formatoMonedaReporte = static function ($monto): string {
    return '$' . number_format((float) $monto, 2);
};
$gastosFondoDelDia = array_sum(array_map(
    static fn (array $egreso): float => (float) $egreso['monto'],
    $egresosFondoReporte
));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reporte diario - Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=nav-20261121">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
    <style>
        @media print {
            .no-print, .sidebar, .navbar, #settings-trigger, .footer {
                display: none !important;
            }
            .page-body-wrapper, .main-panel {
                margin: 0 !important;
                width: 100% !important;
            }
            .content-wrapper {
                padding: 0 !important;
            }
            .card {
                box-shadow: none !important;
                break-inside: avoid;
            }
            table {
                width: 100%;
            }
            tr {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="container-scroller">
    <?= NavBarView(); ?>
    <div class="container-fluid page-body-wrapper">
        <div id="settings-trigger"><i class="mdi mdi-settings"></i></div>
        <?= SideBarView(); ?>
        <main class="main-panel">
            <div class="content-wrapper">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="text-dark font-weight-bold">
                            <i class="mdi mdi-chart-box-outline mr-2"></i>Reporte diario
                        </h2>
                        <p class="text-muted mb-0">
                            Movimientos del <?= e(date('d/m/Y', strtotime($fechaControlDiario))) ?>.
                            El conteo de activos corresponde al estado actual del patio.
                        </p>
                    </div>
                    <button class="btn btn-primary no-print mt-2 mt-md-0" type="button" onclick="window.print()">
                        <i class="mdi mdi-file-pdf-box mr-1"></i>Imprimir / Guardar como PDF
                    </button>
                </div>

                <?php if ($mensajeControlDiario): ?>
                    <div class="alert alert-<?= e((string) $mensajeControlDiario['tipo']) ?>" role="alert">
                        <?= e((string) $mensajeControlDiario['mensaje']) ?>
                    </div>
                <?php endif; ?>

                <ul class="nav nav-tabs mb-4 no-print">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?menu=control_diario&amp;vista=entrada">Registrar boleto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?menu=control_diario&amp;vista=salida">Salida y cobro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?menu=control_diario&amp;vista=listado">Boletos del día</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?menu=control_diario&amp;vista=reporte">Reporte diario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?menu=control_diario&amp;vista=fondo">Fondo de cambio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?menu=control_diario&amp;vista=material">Material por turno</a>
                    </li>
                </ul>

                <form method="get" action="index.php" class="card border-0 shadow-sm mb-4 no-print">
                    <div class="card-body">
                        <input type="hidden" name="menu" value="control_diario">
                        <input type="hidden" name="vista" value="reporte">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-4 mb-md-0">
                                <label for="fecha_reporte">Fecha del reporte</label>
                                <input class="form-control" type="date" id="fecha_reporte" name="fecha"
                                       value="<?= e($fechaControlDiario) ?>" required>
                            </div>
                            <div class="col-md-3 mt-3 mt-md-0">
                                <button class="btn btn-info btn-block" type="submit">
                                    <i class="mdi mdi-magnify mr-1"></i>Consultar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-sm-6 col-xl">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <p class="text-muted mb-1">Activos ahora</p>
                                <h3 class="mb-0"><?= (int) ($reporteControlDiario['boletos_activos'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <p class="text-muted mb-1">Entradas del día</p>
                                <h3 class="mb-0"><?= count($entradasReporte) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <p class="text-muted mb-1">Boletos cobrados</p>
                                <h3 class="mb-0"><?= (int) ($reporteControlDiario['boletos_cobrados'] ?? 0) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <p class="text-muted mb-1">Salidas del día</p>
                                <h3 class="mb-0"><?= count($salidasReporte) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <p class="text-muted mb-1">Total cobrado</p>
                                <h3 class="mb-0"><?= e($formatoMonedaReporte($reporteControlDiario['total_cobrado'] ?? 0)) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Cobros por tipo de tarifa</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Tarifa</th>
                                    <th>Tipo de unidad</th>
                                    <th>Categoría</th>
                                    <th class="text-right">Boletos</th>
                                    <th class="text-right">Total cobrado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($desgloseTarifasReporte as $filaTarifa): ?>
                                    <tr>
                                        <td><?= e((string) $filaTarifa['descripcion_tarifa_aplicada']) ?></td>
                                        <td><?= e($tipoUnidadReporte[$filaTarifa['tipo_unidad']] ?? (string) $filaTarifa['tipo_unidad']) ?></td>
                                        <td><?= e((string) $filaTarifa['categoria_tarifa']) ?></td>
                                        <td class="text-right"><?= (int) $filaTarifa['cantidad'] ?></td>
                                        <td class="text-right"><?= e($formatoMonedaReporte($filaTarifa['total'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$desgloseTarifasReporte): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hubo boletos cobrados en esta fecha.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Entradas del día</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Hora</th>
                                    <th>Operador / empresa</th>
                                    <th>Placas</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($entradasReporte as $boleto): ?>
                                    <tr>
                                        <td>#<?= (int) $boleto['folio'] ?></td>
                                        <td><?= e($formatoFechaReporte((string) $boleto['fecha_hora_entrada'])) ?></td>
                                        <td><?= e((string) $boleto['operador_empresa']) ?></td>
                                        <td><?= e((string) $boleto['placa_tracto']) ?> / <?= e((string) $boleto['placa_caja_1']) ?></td>
                                        <td><?= e((string) $boleto['estado']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$entradasReporte): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No hay entradas para esta fecha.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Salidas del día</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Hora</th>
                                    <th>Operador / empresa</th>
                                    <th>Placa del tracto</th>
                                    <th>Tarifa</th>
                                    <th class="text-right">Cobrado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($salidasReporte as $boleto): ?>
                                    <tr>
                                        <td>#<?= (int) $boleto['folio'] ?></td>
                                        <td><?= e($formatoFechaReporte((string) $boleto['fecha_hora_salida'])) ?></td>
                                        <td><?= e((string) $boleto['operador_empresa']) ?></td>
                                        <td><?= e((string) $boleto['placa_tracto']) ?></td>
                                        <td><?= e((string) $boleto['descripcion_tarifa_aplicada']) ?></td>
                                        <td class="text-right"><?= e($formatoMonedaReporte($boleto['monto_tarifa_aplicada'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$salidasReporte): ?>
                                    <tr><td colspan="6" class="text-center text-muted">No hay salidas para esta fecha.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Fondo de cambio</h4>
                        <?php if ($fondoReporte): ?>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Saldo inicial</p>
                                    <h4><?= e($formatoMonedaReporte($fondoReporte['saldo_inicial'])) ?></h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Egresos de la fecha</p>
                                    <h4><?= e($formatoMonedaReporte($gastosFondoDelDia)) ?></h4>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-1">Saldo actual del fondo</p>
                                    <h4><?= e($formatoMonedaReporte($fondoReporte['saldo_actual'])) ?></h4>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr><th>Hora</th><th>Concepto</th><th>Registró</th><th class="text-right">Monto</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($egresosFondoReporte as $egreso): ?>
                                        <tr>
                                            <td><?= e($formatoFechaReporte((string) $egreso['fecha_hora'])) ?></td>
                                            <td><?= e((string) $egreso['concepto']) ?></td>
                                            <td><?= e((string) $egreso['usuario']) ?></td>
                                            <td class="text-right"><?= e($formatoMonedaReporte($egreso['monto'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$egresosFondoReporte): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No hay egresos del fondo en esta fecha.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">El saldo inicial del fondo aún no está configurado.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Material por turno</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Turno</th>
                                    <th>Horario</th>
                                    <th>Responsables</th>
                                    <th>Material</th>
                                    <th class="text-right">Recibido</th>
                                    <th class="text-right">Entregado</th>
                                    <th>Nota / alerta</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($materialesTurnoReporte as $turno): ?>
                                    <tr>
                                        <td>#<?= (int) $turno['id_turno'] ?> · <?= e((string) $turno['estado']) ?></td>
                                        <td>
                                            <?= e($formatoFechaReporte((string) $turno['fecha_hora_inicio'])) ?><br>
                                            <?= e($formatoFechaReporte($turno['fecha_hora_fin'] !== null ? (string) $turno['fecha_hora_fin'] : null)) ?>
                                        </td>
                                        <td>
                                            <?= e((string) $turno['usuario_inicio']) ?><br>
                                            <?= e((string) ($turno['usuario_cierre'] ?? 'Pendiente de cierre')) ?>
                                        </td>
                                        <td><?= e((string) ($turno['material'] ?? 'Sin material registrado')) ?></td>
                                        <td class="text-right"><?= $turno['cantidad_recibida'] === null ? '—' : (int) $turno['cantidad_recibida'] ?></td>
                                        <td class="text-right"><?= $turno['cantidad_entregada'] === null ? 'Pendiente' : (int) $turno['cantidad_entregada'] ?></td>
                                        <td>
                                            <?php if ((int) $turno['alerta'] === 1): ?>
                                                <span class="badge badge-danger">Diferencia</span>
                                                <?= e((string) ($turno['nota'] ?? 'Sin nota')) ?>
                                            <?php else: ?>
                                                <?= e((string) ($turno['nota'] ?? '—')) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$materialesTurnoReporte): ?>
                                    <tr><td colspan="7" class="text-center text-muted">No hay turnos que coincidan con esta fecha.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
            <footer class="footer no-print">
                <div class="footer-inner-wraper">
                    <span class="text-muted">Copyright &copy; 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.</span>
                </div>
            </footer>
        </main>
    </div>
</div>
<script src="public/assets/vendors/js/vendor.bundle.base.js"></script>
<script src="public/assets/js/off-canvas.js"></script>
<script src="public/assets/js/hoverable-collapse.js"></script>
<script src="public/assets/js/misc.js"></script>
<script src="public/assets/js/settings.js"></script>
</body>
</html>
