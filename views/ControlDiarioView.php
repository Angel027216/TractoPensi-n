<?php
require_once 'views/includes/SideBarView.php';
require_once 'views/includes/NavBarView.php';

$vistaControlDiario = (string) ($vistaControlDiario ?? 'entrada');
$fechaControlDiario = (string) ($fechaControlDiario ?? date('Y-m-d'));
$busquedaControlDiario = (string) ($busquedaControlDiario ?? '');
$busquedaSalidaControlDiario = (string) ($busquedaSalidaControlDiario ?? '');
$tarifasControlDiario = is_array($tarifasControlDiario ?? null) ? $tarifasControlDiario : [];
$boletosControlDiario = is_array($boletosControlDiario ?? null) ? $boletosControlDiario : [];
$activosControlDiario = is_array($activosControlDiario ?? null) ? $activosControlDiario : [];
$mensajeControlDiario = is_array($mensajeControlDiario ?? null) ? $mensajeControlDiario : null;
$fondoControlDiario = is_array($fondoControlDiario ?? null) ? $fondoControlDiario : null;
$egresosControlDiario = is_array($egresosControlDiario ?? null) ? $egresosControlDiario : [];
$turnoAbiertoControlDiario = is_array($turnoAbiertoControlDiario ?? null) ? $turnoAbiertoControlDiario : null;
$materialesTurnoAbierto = is_array($turnoAbiertoControlDiario['materiales'] ?? null)
    ? $turnoAbiertoControlDiario['materiales']
    : [];
$folioCreadoControlDiario = (int) ($folioCreadoControlDiario ?? 0);
$folioSalidaControlDiario = (int) ($folioSalidaControlDiario ?? 0);
$formatoFolioControlDiario = static fn(int $folio): string =>
    str_pad((string) $folio, 4, '0', STR_PAD_LEFT);

$tiposUnidadControlDiario = [
    'CAMIONETA' => 'Camioneta',
    'TRAILER_SENCILLO' => 'Tráiler sencillo',
    'TORTON' => 'Tortón',
    'TRAILER_FULL_DOBLE' => 'Tráiler full / doble remolque',
];
$marcasUnidadControlDiario = [
    'Freightliner',
    'Kenworth',
    'International',
    'Peterbilt',
    'Volvo',
    'Mack',
    'Western Star',
    'Scania',
    'Hino',
    'Otra',
];
$categoriasControlDiario = [
    'ESPECIAL' => 'Especial',
    'CASA' => 'Casa',
    'GENERAL' => 'General',
];
$formatoFechaControlDiario = static function (?string $fecha): string {
    return $fecha ? date('d/m/Y H:i', strtotime($fecha)) : '—';
};
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Control diario - Tracto Pensión</title>
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=nav-20261121">
    <link rel="stylesheet" href="public/assets/css/dashboard.css?v=control-20261122">
    <link rel="stylesheet" href="public/assets/css/control-diario.css?v=control-20261129">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
</head>
<body class="app-dashboard app-control-diario">
<div class="container-scroller">
    <?= NavBarView(); ?>
    <div class="container-fluid page-body-wrapper">
        <div id="settings-trigger"><i class="mdi mdi-settings"></i></div>
        <?= SideBarView(); ?>
        <div class="main-panel">
            <div class="content-wrapper">
                <section class="dashboard-hero control-diario-hero">
                    <div class="dashboard-hero-copy">
                        <span class="dashboard-kicker"><i class="mdi mdi-ticket-confirmation"></i> CONTROL DIARIO</span>
                        <h1>Control diario</h1>
                        <p>Registro de boletos para camiones no pensionados.</p>
                    </div>
                    <img src="public/assets/images/Dashboar1.png" alt="" class="dashboard-truck" aria-hidden="true" loading="eager" decoding="async">
                </section>

                <section class="control-diario-heading">
                    <a class="control-fondo-badge" href="index.php?menu=control_diario&amp;vista=fondo">
                        <i class="mdi mdi-wallet-outline"></i>
                        <span><small>Fondo actual</small><strong><?= $fondoControlDiario ? '$' . e(number_format((float) $fondoControlDiario['saldo_actual'], 2)) : 'Sin configurar' ?></strong></span>
                    </a>
                </section>

                <?php if ($mensajeControlDiario): ?>
                    <div class="alert alert-<?= e((string) $mensajeControlDiario['tipo']) ?>" role="alert">
                        <?= e((string) $mensajeControlDiario['mensaje']) ?>
                        <?php if ($folioCreadoControlDiario > 0): ?>
                            <a class="alert-link ml-2" target="_blank" rel="noopener"
                               href="index.php?menu=control_diario&amp;vista=imprimir&amp;folio=<?= $folioCreadoControlDiario ?>">
                                Imprimir boleto #<?= $folioCreadoControlDiario ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($folioSalidaControlDiario > 0): ?>
                            <a class="alert-link ml-2" target="_blank" rel="noopener"
                               href="index.php?menu=control_diario&amp;vista=imprimir&amp;folio=<?= $folioSalidaControlDiario ?>">
                                Ver boleto #<?= $folioSalidaControlDiario ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <nav class="control-diario-toolbar" aria-label="Opciones de control diario">
                    <div class="control-diario-tabs">
                        <a class="control-diario-tab <?= $vistaControlDiario === 'entrada' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=entrada"><i class="mdi mdi-ticket-plus"></i>Registrar boleto</a>
                        <a class="control-diario-tab <?= $vistaControlDiario === 'salida' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=salida"><i class="mdi mdi-cash-register"></i>Salida y cobro</a>
                        <a class="control-diario-tab <?= $vistaControlDiario === 'listado' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=listado"><i class="mdi mdi-format-list-bulleted"></i>Boletos del día</a>
                        <a class="control-diario-tab <?= $vistaControlDiario === 'reporte' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=reporte"><i class="mdi mdi-chart-bar"></i>Reporte diario</a>
                        <a class="control-diario-tab <?= $vistaControlDiario === 'fondo' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=fondo"><i class="mdi mdi-wallet"></i>Fondo de cambio</a>
                        <a class="control-diario-tab <?= $vistaControlDiario === 'material' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=material"><i class="mdi mdi-lan"></i>Material por turno</a>
                    </div>
                </nav>

                <div class="control-diario-tabs-legacy d-none">
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'entrada' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=entrada">Registrar boleto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'salida' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=salida">Salida y cobro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'listado' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=listado">Boletos del día</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'reporte' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=reporte">Reporte diario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'fondo' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=fondo">Fondo de cambio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $vistaControlDiario === 'material' ? 'active' : '' ?>"
                           href="index.php?menu=control_diario&amp;vista=material">Material por turno</a>
                    </li>
                </ul>
                </div>

                <?php if ($vistaControlDiario === 'entrada'): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title">Registrar boleto</h4>
                            <form method="post" action="index.php?menu=control_diario&amp;vista=entrada">
                                <?= csrf_input(); ?>
                                <input type="hidden" name="accion" value="registrar_entrada">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="folio">No. de folio</label>
                                        <input class="form-control" type="text" id="folio" name="folio"
                                               inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
                                               placeholder="<?= e($siguienteFolioControlDiario ?? '0001') ?>">
                                        <small class="form-text text-muted">Formato de 4 dígitos. Puedes cambiar el primero; los siguientes serán consecutivos.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="operador_empresa">Operador o empresa</label>
                                        <input class="form-control" id="operador_empresa" name="operador_empresa"
                                               maxlength="150" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tipo_unidad">Tipo de unidad</label>
                                        <select class="form-control" id="tipo_unidad" name="tipo_unidad" required>
                                            <option value="">Selecciona el tipo de unidad</option>
                                            <?php foreach ($tiposUnidadControlDiario as $valor => $etiqueta): ?>
                                                <option value="<?= e($valor) ?>"><?= e($etiqueta) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="marca_trailer">Marca del tráiler o unidad</label>
                                        <select class="form-control" id="marca_trailer" name="marca_trailer" required>
                                            <option value="">Selecciona una marca</option>
                                            <?php foreach ($marcasUnidadControlDiario as $marcaUnidad): ?>
                                                <option value="<?= e($marcaUnidad) ?>"><?= e($marcaUnidad) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6 d-none" id="grupo_placa_tracto">
                                        <label for="placa_tracto">Placa principal</label>
                                        <input class="form-control text-uppercase" id="placa_tracto" name="placa_tracto"
                                               placeholder="26-AA-4U" pattern="[0-9]{2}-[A-Za-z]{2}-[0-9][A-Za-z]"
                                               title="Formato: 00-AA-0A, por ejemplo 26-AA-4U"
                                               maxlength="8">
                                        <small class="form-text text-muted" id="ayuda_placa_principal">Ejemplo: 26-AA-4U</small>
                                    </div>
                                    <div class="form-group col-md-6 d-none" id="grupo_placa_caja_1">
                                        <label for="placa_caja_1">Placa de caja 1</label>
                                        <input class="form-control text-uppercase" id="placa_caja_1" name="placa_caja_1"
                                               placeholder="000-AA-0 o 00-AA-0A"
                                               pattern="(?:[0-9]{3}-[A-Za-z]{2}-[0-9]|[0-9]{2}-[A-Za-z]{2}-[0-9][A-Za-z])"
                                               title="Formato: 000-AA-0 o 00-AA-0A"
                                               maxlength="9">
                                        <small class="form-text text-muted">Ejemplo: 123-AA-4 o 26-AA-4U</small>
                                    </div>
                                    <div class="form-group col-md-6 d-none" id="grupo_placa_caja_2">
                                        <label for="placa_caja_2">Placa de caja 2</label>
                                        <input class="form-control text-uppercase" id="placa_caja_2" name="placa_caja_2"
                                               placeholder="000-AA-0 o 00-AA-0A"
                                               pattern="(?:[0-9]{3}-[A-Za-z]{2}-[0-9]|[0-9]{2}-[A-Za-z]{2}-[0-9][A-Za-z])"
                                               title="Formato: 000-AA-0 o 00-AA-0A"
                                               maxlength="9">
                                        <small class="form-text text-muted">Ejemplo: 123-AA-4 o 26-AA-4U. Obligatoria para full o doble remolque.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="categoria_tarifa">Categoría de tarifa</label>
                                        <select class="form-control" id="categoria_tarifa" name="categoria_tarifa" required>
                                            <option value="">Selecciona primero el tipo de unidad</option>
                                            <?php foreach ($tarifasControlDiario as $tarifa): ?>
                                                <option value="<?= e((string) $tarifa['categoria']) ?>"
                                                        data-tipo="<?= e((string) $tarifa['tipo_unidad']) ?>"
                                                        data-descripcion="<?= e((string) $tarifa['descripcion']) ?>"
                                                        data-monto="<?= e(number_format((float) $tarifa['monto'], 2, '.', '')) ?>">
                                                    <?= e($categoriasControlDiario[$tarifa['categoria']] ?? (string) $tarifa['categoria']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted" id="tarifa_seleccionada"></small>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-truck-plus mr-1"></i>Registrar boleto
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($vistaControlDiario === 'salida'): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title">Registrar salida y cobro</h4>
                            <form method="get" action="index.php" class="form-row align-items-end mb-4">
                                <input type="hidden" name="menu" value="control_diario">
                                <input type="hidden" name="vista" value="salida">
                                <div class="form-group col-md-8">
                                    <label for="buscar_activo">Buscar boleto activo por folio o placa</label>
                                    <input class="form-control" type="search" id="buscar_activo" name="buscar"
                                           maxlength="60" value="<?= e($busquedaSalidaControlDiario) ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <button class="btn btn-info btn-block" type="submit">
                                        <i class="mdi mdi-magnify mr-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive salida-cobro-table">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Entrada</th>
                                        <th>Operador / empresa</th>
                                        <th>Placas</th>
                                        <th>Tarifa</th>
                                        <th>Acción</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($activosControlDiario as $boleto): ?>
                                        <tr>
                                            <td><strong>#<?= e($formatoFolioControlDiario((int) $boleto['folio'])) ?></strong></td>
                                            <td><?= e($formatoFechaControlDiario((string) $boleto['fecha_hora_entrada'])) ?></td>
                                            <td><?= e((string) $boleto['operador_empresa']) ?></td>
                                            <td>
                                                Tracto: <?= e((string) $boleto['placa_tracto']) ?><br>
                                                Caja 1: <?= e((string) $boleto['placa_caja_1']) ?>
                                                <?php if (!empty($boleto['placa_caja_2'])): ?>
                                                    <br>Caja 2: <?= e((string) $boleto['placa_caja_2']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= e((string) $boleto['descripcion_tarifa_aplicada']) ?><br>
                                                <strong>$<?= e(number_format((float) $boleto['monto_tarifa_aplicada'], 2)) ?></strong>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php?menu=control_diario&amp;vista=salida"
                                                      data-salida-form
                                                      data-entrada="<?= e((string) $boleto['fecha_hora_entrada']) ?>"
                                                      data-tarifa="<?= e(number_format((float) $boleto['monto_tarifa_aplicada'], 2, '.', '')) ?>"
                                                      onsubmit="return validarConfirmacionSalida(this, <?= (int) $boleto['folio'] ?>);">
                                                    <?= csrf_input(); ?>
                                                    <input type="hidden" name="accion" value="registrar_salida">
                                                    <input type="hidden" name="folio" value="<?= (int) $boleto['folio'] ?>">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6 mb-2">
                                                            <label for="fecha_salida_<?= (int) $boleto['folio'] ?>">Fecha de salida</label>
                                                            <input type="date" class="form-control form-control-sm"
                                                                   id="fecha_salida_<?= (int) $boleto['folio'] ?>"
                                                                   name="fecha_salida" required
                                                                   data-fecha-salida
                                                                   value="<?= e(date('Y-m-d')) ?>">
                                                        </div>
                                                        <div class="form-group col-md-6 mb-2">
                                                            <label for="hora_salida_<?= (int) $boleto['folio'] ?>">Hora de salida</label>
                                                            <input type="time" class="form-control form-control-sm"
                                                                   id="hora_salida_<?= (int) $boleto['folio'] ?>"
                                                                   name="hora_salida" required
                                                                   data-hora-salida
                                                                   value="<?= e(date('H:i')) ?>">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2">
                                                        Cobro estimado:
                                                        <strong data-cobro-estimado>$<?= e(number_format((float) $boleto['monto_tarifa_aplicada'], 2)) ?></strong>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        Registrar salida y cobrar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$activosControlDiario): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No hay boletos activos que coincidan con la búsqueda.</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif ($vistaControlDiario === 'material'): ?>
                    <?php if ($turnoAbiertoControlDiario): ?>
                        <?php
                        $lamparasRecibidas = (int) ($materialesTurnoAbierto['lamparas']['cantidad_recibida'] ?? 0);
                        $radiosRecibidos = (int) ($materialesTurnoAbierto['radios']['cantidad_recibida'] ?? 0);
                        ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-start">
                                    <div>
                                        <h4 class="card-title">Turno abierto #<?= (int) $turnoAbiertoControlDiario['id_turno'] ?></h4>
                                        <p class="text-muted mb-0">
                                            Inició <?= e($formatoFechaControlDiario((string) $turnoAbiertoControlDiario['fecha_hora_inicio'])) ?>
                                            · <?= e((string) $turnoAbiertoControlDiario['usuario_inicio_nombre']) ?>
                                        </p>
                                    </div>
                                    <span class="badge badge-success">ABIERTO</span>
                                </div>
                                <hr>
                                <p class="mb-1">Material recibido al inicio:</p>
                                <ul class="mb-0">
                                    <li>Lámparas: <strong><?= $lamparasRecibidas ?></strong></li>
                                    <li>Radios: <strong><?= $radiosRecibidos ?></strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">Cerrar turno y registrar lo que se entrega</h4>
                                <p class="text-muted">Si la cantidad entregada no coincide con la recibida, explica la diferencia en una nota.</p>
                                <form method="post" action="index.php?menu=control_diario&amp;vista=material" id="form_cerrar_turno">
                                    <?= csrf_input(); ?>
                                    <input type="hidden" name="accion" value="cerrar_turno">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="lamparas_entregadas">Lámparas que se dejan</label>
                                            <input class="form-control" type="number" id="lamparas_entregadas"
                                                   name="lamparas_entregadas" min="0" max="4294967295" step="1"
                                                   value="<?= $lamparasRecibidas ?>"
                                                   data-cantidad-recibida="<?= $lamparasRecibidas ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="radios_entregados">Radios que se dejan</label>
                                            <input class="form-control" type="number" id="radios_entregados"
                                                   name="radios_entregados" min="0" max="4294967295" step="1"
                                                   value="<?= $radiosRecibidos ?>"
                                                   data-cantidad-recibida="<?= $radiosRecibidos ?>" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="nota_lamparas">Nota para lámparas</label>
                                            <textarea class="form-control" id="nota_lamparas" name="nota_lamparas"
                                                      maxlength="255" rows="2"></textarea>
                                            <small class="form-text text-muted" id="aviso_nota_lamparas">
                                                Obligatoria si la cantidad es distinta de <?= $lamparasRecibidas ?>.
                                            </small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="nota_radios">Nota para radios</label>
                                            <textarea class="form-control" id="nota_radios" name="nota_radios"
                                                      maxlength="255" rows="2"></textarea>
                                            <small class="form-text text-muted" id="aviso_nota_radios">
                                                Obligatoria si la cantidad es distinta de <?= $radiosRecibidos ?>.
                                            </small>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit"
                                            onclick="return confirm('¿Confirmas el cierre del turno y las cantidades entregadas?');">
                                        <i class="mdi mdi-check-circle-outline mr-1"></i>Cerrar turno
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">Iniciar turno</h4>
                                <p class="text-muted">Registra las cantidades recibidas. Como referencia, el inventario habitual es de 5 lámparas y 4 radios.</p>
                                <form method="post" action="index.php?menu=control_diario&amp;vista=material">
                                    <?= csrf_input(); ?>
                                    <input type="hidden" name="accion" value="iniciar_turno">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="lamparas_recibidas">Lámparas recibidas</label>
                                            <input class="form-control" type="number" id="lamparas_recibidas"
                                                   name="lamparas_recibidas" min="0" max="4294967295" step="1"
                                                   value="5" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="radios_recibidos">Radios recibidos</label>
                                            <input class="form-control" type="number" id="radios_recibidos"
                                                   name="radios_recibidos" min="0" max="4294967295" step="1"
                                                   value="4" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-play-circle-outline mr-1"></i>Iniciar turno
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($vistaControlDiario === 'fondo'): ?>
                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h4 class="card-title">Saldo actual del fondo</h4>
                                    <?php if ($fondoControlDiario): ?>
                                        <h2 class="text-success mb-4">
                                            $<?= e(number_format((float) $fondoControlDiario['saldo_actual'], 2)) ?>
                                        </h2>
                                        <p class="text-muted">
                                            Saldo inicial:
                                            <strong>$<?= e(number_format((float) $fondoControlDiario['saldo_inicial'], 2)) ?></strong>
                                        </p>
                                        <p class="text-muted mb-0">
                                            Egresos acumulados:
                                            <strong>$<?= e(number_format((float) $fondoControlDiario['egresos_acumulados'], 2)) ?></strong>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted">Aún no se ha configurado el saldo inicial.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h4 class="card-title">
                                        <?= $fondoControlDiario ? 'Modificar saldo inicial' : 'Configurar saldo inicial' ?>
                                    </h4>
                                    <form method="post" action="index.php?menu=control_diario&amp;vista=fondo">
                                        <?= csrf_input(); ?>
                                        <input type="hidden" name="accion" value="guardar_saldo_inicial">
                                        <div class="form-group">
                                            <label for="saldo_inicial">Saldo inicial</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input class="form-control" type="number" id="saldo_inicial" name="saldo_inicial"
                                                       min="0" max="99999999.99" step="0.01"
                                                       value="<?= $fondoControlDiario ? e(number_format((float) $fondoControlDiario['saldo_inicial'], 2, '.', '')) : '' ?>"
                                                       required>
                                            </div>
                                            <small class="form-text text-muted">
                                                El saldo no puede quedar por debajo de los egresos acumulados.
                                            </small>
                                        </div>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="mdi mdi-content-save mr-1"></i>Guardar saldo inicial
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title">Registrar egreso</h4>
                                    <form method="post" action="index.php?menu=control_diario&amp;vista=fondo">
                                        <?= csrf_input(); ?>
                                        <input type="hidden" name="accion" value="registrar_egreso_fondo">
                                        <div class="form-group">
                                            <label for="concepto_egreso">Concepto</label>
                                            <input class="form-control" type="text" id="concepto_egreso" name="concepto"
                                                   maxlength="150" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="monto_egreso">Monto</label>
                                            <input class="form-control" type="number" id="monto_egreso" name="monto"
                                                   min="0.01" max="99999999.99" step="0.01" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_egreso">Fecha y hora</label>
                                            <input class="form-control" type="datetime-local" id="fecha_egreso"
                                                   name="fecha_hora" value="<?= e(date('Y-m-d\TH:i')) ?>" required>
                                        </div>
                                        <button class="btn btn-danger" type="submit" <?= !$fondoControlDiario ? 'disabled' : '' ?>>
                                            <i class="mdi mdi-cash-minus mr-1"></i>Registrar egreso
                                        </button>
                                        <?php if (!$fondoControlDiario): ?>
                                            <small class="form-text text-muted">Configura primero el saldo inicial.</small>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title">Egresos registrados</h4>
                                    <form method="get" action="index.php" class="form-row align-items-end mb-3">
                                        <input type="hidden" name="menu" value="control_diario">
                                        <input type="hidden" name="vista" value="fondo">
                                        <div class="form-group col-md-8">
                                            <label for="fecha_egresos">Fecha</label>
                                            <input class="form-control" type="date" id="fecha_egresos" name="fecha"
                                                   value="<?= e($fechaControlDiario) ?>" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <button class="btn btn-info btn-block" type="submit">Consultar</button>
                                        </div>
                                    </form>
                                    <div class="table-responsive fondo-dia-table">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Concepto</th>
                                                <th>Registró</th>
                                                <th class="text-right">Monto</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($egresosControlDiario as $egreso): ?>
                                                <tr>
                                                    <td><?= e($formatoFechaControlDiario((string) $egreso['fecha_hora'])) ?></td>
                                                    <td><?= e((string) $egreso['concepto']) ?></td>
                                                    <td><?= e((string) $egreso['usuario']) ?></td>
                                                    <td class="text-right">$<?= e(number_format((float) $egreso['monto'], 2)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (!$egresosControlDiario): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No hay egresos para la fecha seleccionada.</td>
                                                </tr>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h4 class="card-title">Boletos registrados</h4>
                            <form method="get" action="index.php" class="form-row align-items-end mb-4">
                                <input type="hidden" name="menu" value="control_diario">
                                <input type="hidden" name="vista" value="listado">
                                <div class="form-group col-md-4">
                                    <label for="fecha">Día de entrada</label>
                                    <input class="form-control" type="date" id="fecha" name="fecha"
                                           value="<?= e($fechaControlDiario) ?>" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="buscar_boleto">Folio o placa</label>
                                    <input class="form-control" type="search" id="buscar_boleto" name="buscar"
                                           maxlength="60" value="<?= e($busquedaControlDiario) ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <button class="btn btn-info btn-block" type="submit">
                                        <i class="mdi mdi-magnify mr-1"></i>Buscar
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive boletos-dia-table">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Folio</th>
                                        <th>Operador / empresa</th>
                                        <th>Placas</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Estado</th>
                                        <th>Importe</th>
                                        <th>Boleto</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($boletosControlDiario as $boleto): ?>
                                        <tr>
                                            <td><strong>#<?= e($formatoFolioControlDiario((int) $boleto['folio'])) ?></strong></td>
                                            <td><?= e((string) $boleto['operador_empresa']) ?></td>
                                            <td>
                                                <?= e((string) $boleto['placa_tracto']) ?><br>
                                                <?= e((string) $boleto['placa_caja_1']) ?>
                                                <?php if (!empty($boleto['placa_caja_2'])): ?>
                                                    <br><?= e((string) $boleto['placa_caja_2']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e($formatoFechaControlDiario((string) $boleto['fecha_hora_entrada'])) ?></td>
                                            <td><?= e($formatoFechaControlDiario($boleto['fecha_hora_salida'] !== null ? (string) $boleto['fecha_hora_salida'] : null)) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $boleto['estado'] === 'ACTIVO' ? 'warning' : 'success' ?>">
                                                    <?= e((string) $boleto['estado']) ?>
                                                </span>
                                            </td>
                                            <td>$<?= e(number_format((float) $boleto['monto_tarifa_aplicada'], 2)) ?></td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"
                                                   href="index.php?menu=control_diario&amp;vista=imprimir&amp;folio=<?= (int) $boleto['folio'] ?>">
                                                    <i class="mdi mdi-printer mr-1"></i>Imprimir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$boletosControlDiario): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No hay boletos para los filtros seleccionados.</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <footer class="footer">
                <div class="footer-inner-wraper">
                    <div class="d-flex justify-content-start">
                        <span class="text-muted text-left d-block">Copyright © 2025 Tracto Pensión San Leonardo. Todos los derechos reservados.</span>
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
<script>
    function calcularCobroSalida(formulario) {
        const entrada = new Date(formulario.dataset.entrada.replace(' ', 'T'));
        const fecha = formulario.querySelector('[data-fecha-salida]').value;
        const hora = formulario.querySelector('[data-hora-salida]').value;
        const salida = new Date(fecha + 'T' + (hora || '00:00') + ':00');
        const tarifa = Number(formulario.dataset.tarifa);
        if (Number.isNaN(salida.getTime()) || Number.isNaN(tarifa) || salida < entrada) {
            return null;
        }
        const periodos = Math.max(1, Math.ceil((salida.getTime() - entrada.getTime()) / 86400000));
        return { periodos: periodos, monto: tarifa * periodos };
    }

    function actualizarCobroEstimado(formulario) {
        const resultado = calcularCobroSalida(formulario);
        const destino = formulario.querySelector('[data-cobro-estimado]');
        if (!destino) {
            return;
        }
        if (!resultado) {
            destino.textContent = 'Revisa la fecha y hora';
            return;
        }
        destino.textContent = '$' + resultado.monto.toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function validarConfirmacionSalida(formulario, folio) {
        const resultado = calcularCobroSalida(formulario);
        if (!resultado) {
            alert('La salida debe ser posterior a la entrada.');
            return false;
        }
        return confirm(
            '¿Confirmas la salida del boleto #' + folio
            + ' por $' + resultado.monto.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            + ' (' + resultado.periodos + ' período(s) de 24 h)?'
        );
    }

    document.querySelectorAll('[data-salida-form]').forEach(function (formulario) {
        formulario.querySelectorAll('[data-fecha-salida], [data-hora-salida]').forEach(function (campo) {
            campo.addEventListener('change', function () {
                actualizarCobroEstimado(formulario);
            });
        });
        actualizarCobroEstimado(formulario);
    });

    (function () {
        const tipo = document.getElementById('tipo_unidad');
        const categoria = document.getElementById('categoria_tarifa');
        const tracto = document.getElementById('placa_tracto');
        const grupoTracto = document.getElementById('grupo_placa_tracto');
        const caja1 = document.getElementById('placa_caja_1');
        const grupoCaja1 = document.getElementById('grupo_placa_caja_1');
        const caja2 = document.getElementById('placa_caja_2');
        const grupoCaja2 = document.getElementById('grupo_placa_caja_2');
        const tarifaSeleccionada = document.getElementById('tarifa_seleccionada');

        if (!tipo || !categoria || !tracto || !grupoTracto || !caja1 || !grupoCaja1 || !caja2 || !grupoCaja2 || !tarifaSeleccionada) {
            return;
        }

        function actualizarOpciones() {
            const esCamioneta = tipo.value === 'CAMIONETA';
            const esTorton = tipo.value === 'TORTON';
            const esTrailerSencillo = tipo.value === 'TRAILER_SENCILLO';
            const requiereCaja2 = tipo.value === 'TRAILER_FULL_DOBLE';
            const requiereCaja1 = esTrailerSencillo || requiereCaja2;

            grupoTracto.classList.toggle('d-none', tipo.value === '');
            tracto.required = tipo.value !== '';
            if (tipo.value === '') {
                tracto.value = '';
                caja1.value = '';
                caja2.value = '';
                caja1.required = false;
                caja2.required = false;
            }
            tracto.closest('.form-group').className = requiereCaja1 || requiereCaja2
                ? 'form-group col-md-6'
                : 'form-group col-md-6';

            const etiquetaTracto = grupoTracto.querySelector('label');
            const ayudaTracto = document.getElementById('ayuda_placa_principal');
            etiquetaTracto.textContent = esCamioneta
                ? 'Placa de la camioneta'
                : (esTorton ? 'Placa del torton' : 'Placa del tracto');
            tracto.placeholder = '26-AA-4U';
            if (ayudaTracto) {
                ayudaTracto.textContent = esCamioneta
                    ? 'Ejemplo camioneta: 26-AA-4U'
                    : (esTorton ? 'Ejemplo torton: 26-AA-4U' : 'Ejemplo tracto: 26-AA-4U');
            }

            grupoCaja1.classList.toggle('d-none', !requiereCaja1);
            caja1.required = requiereCaja1;
            if (!requiereCaja1) {
                caja1.value = '';
            }

            grupoCaja2.classList.toggle('d-none', !requiereCaja2);
            caja2.required = requiereCaja2;
            if (!requiereCaja2) {
                caja2.value = '';
            }

            let seleccionValida = false;
            Array.from(categoria.options).forEach(function (opcion) {
                if (!opcion.dataset.tipo) {
                    opcion.hidden = false;
                    return;
                }
                opcion.hidden = opcion.dataset.tipo !== tipo.value;
                opcion.disabled = opcion.hidden;
                if (opcion.selected && !opcion.hidden) {
                    seleccionValida = true;
                }
            });

            if (!seleccionValida) {
                categoria.value = '';
            }
            actualizarImporte();
        }

        function actualizarImporte() {
            const opcion = categoria.options[categoria.selectedIndex];
            if (!opcion || !opcion.dataset.monto) {
                tarifaSeleccionada.textContent = '';
                return;
            }
            const importe = Number(opcion.dataset.monto);
            tarifaSeleccionada.textContent = opcion.dataset.descripcion + ': $'
                + importe.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        tipo.addEventListener('change', actualizarOpciones);
        categoria.addEventListener('change', actualizarImporte);
        actualizarOpciones();
    }());
</script>
<script>
    (function () {
        const formCerrarTurno = document.getElementById('form_cerrar_turno');
        if (!formCerrarTurno) {
            return;
        }

        ['lamparas', 'radios'].forEach(function (material) {
            const sufijo = material === 'lamparas' ? '_entregadas' : '_entregados';
            const cantidad = document.getElementById(material + sufijo);
            const nota = document.getElementById('nota_' + material);
            if (!cantidad || !nota) {
                return;
            }

            const validarNota = function () {
                nota.required = cantidad.value !== cantidad.dataset.cantidadRecibida;
            };
            cantidad.addEventListener('input', validarNota);
            validarNota();
        });
    }());
</script>
</body>
</html>
