<?php 
require_once 'views/includes/SideBarView.php'; 
require_once 'views/includes/NavBarView.php';

$camion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Camiones | Tracto Pensión</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">

    <!-- Plugin css -->
    <link rel="stylesheet" href="public/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="public/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">

    <!-- Layout styles -->
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css?v=table-text-20261121">
    <link rel="stylesheet" href="public/assets/css/dashboard.css?v=table-text-20261121">
    <link rel="stylesheet" href="public/assets/css/camiones.css?v=table-text-20261121">
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121" />
</head>

<body class="app-dashboard app-camiones">

<div class="container-scroller">

    <!-- Navbar -->
    <?=NavBarView(); ?>

    <div class="container-fluid page-body-wrapper">

        <!-- Sidebar -->
        <?=SideBarView(); ?>

        <!-- Main -->
        <div class="main-panel">

            <div class="content-wrapper">

                <section class="dashboard-hero camiones-hero">
                    <div class="dashboard-hero-copy">
                        <h1>Camiones</h1>
                        <p>Registra camiones o consulta los que ya están guardados.</p>
                    </div>
                </section>

                <?php if ($mensajeCamion !== ''): ?>
                    <div class="camiones-alert" role="status"><i class="mdi mdi-check-circle"></i><?= e($mensajeCamion) ?></div>
                <?php endif; ?>

                <nav class="camiones-toolbar" aria-label="Opciones de camiones">
                    <div class="camiones-tabs">
                        <a class="camiones-tab <?= $seccionCamiones === 'registrar' ? 'active' : '' ?>" href="index.php?menu=micamion&amp;seccion=registrar">
                            <i class="mdi mdi-plus"></i> Registrar camión
                        </a>
                        <a class="camiones-tab <?= $seccionCamiones === 'visualizar' ? 'active' : '' ?>" href="index.php?menu=micamion&amp;seccion=visualizar">
                            <i class="mdi mdi-format-list-bulleted"></i> Visualizar camiones
                        </a>
                    </div>
                    <?php if ($seccionCamiones === 'visualizar' && $camiones): ?>
                        <div class="camiones-filters">
                            <label class="camiones-search">
                                <i class="mdi mdi-magnify"></i>
                                <input type="search" id="camionesSearch" placeholder="Buscar por placa, operador o marca..." aria-label="Buscar camiones">
                            </label>
                            <label class="camiones-status-filter">
                                <i class="mdi mdi-filter-variant"></i>
                                <select id="camionesStatusFilter" aria-label="Filtrar por estado">
                                    <option value="">Todos los estados</option>
                                    <option value="en_pension">En pensión</option>
                                    <option value="fuera_de_pension">Fuera de pensión</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </label>
                        </div>
                    <?php endif; ?>
                </nav>

                <?php if ($seccionCamiones === 'registrar'): ?>
                <section class="camiones-card camiones-form-card">
                    <header class="camiones-card-header">
                        <div>
                            <h2><i class="mdi mdi-truck-plus"></i> Registrar camión</h2>
                            <p>Captura los datos de la unidad que ingresará con boleto.</p>
                        </div>
                    </header>
                    <div class="camiones-card-body">
                                <form method="POST" action="index.php?menu=micamion&amp;seccion=registrar" id="formCamion">
                                    <?= csrf_input(); ?>
                                    <input type="hidden" name="accion" value="guardar">
                                    <div class="camiones-form-grid">
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Nombre del operador o empresa</label>
                                            <input type="text" name="operador_empresa" class="form-control" maxlength="150" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Placa del tracto</label>
                                            <input type="text" name="placa" class="form-control text-uppercase" placeholder="26-AA-4U" pattern="[0-9]{2}-[A-Za-z]{2}-[0-9][A-Za-z]" title="Formato: 00-AA-0A, por ejemplo 26-AA-4U" maxlength="8" autocapitalize="characters" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Placa de caja</label>
                                            <input type="text" name="placa_caja" class="form-control text-uppercase" placeholder="000-AA-0 o 00-AA-0A" pattern="(?:[0-9]{3}-[A-Za-z]{2}-[0-9]|[0-9]{2}-[A-Za-z]{2}-[0-9][A-Za-z])" title="Formato: 000-AA-0 o 00-AA-0A" maxlength="9" autocapitalize="characters" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Marca</label>
                                            <select name="marca" class="form-control" required>
                                                <option value="">Selecciona una marca</option>
                                                <?php foreach (MiCamionModel::MARCAS_COMUNES as $marcaComun): ?>
                                                    <option value="<?= e($marcaComun) ?>"><?= e($marcaComun) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Modelo</label>
                                            <input type="text" name="modelo" class="form-control" maxlength="100" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Color</label>
                                            <select name="color" class="form-control" required>
                                                <option value="">Selecciona un color</option>
                                                <?php foreach (MiCamionModel::COLORES_COMUNES as $colorComun): ?>
                                                    <option value="<?= e($colorComun) ?>"><?= e($colorComun) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Fecha de Entrada</label>
                                            <input type="date" name="fecha" class="form-control" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Hora de Entrada</label>
                                            <input type="time" name="hora" class="form-control" required>
                                        </div>
                                        <div class="camiones-field">
                                            <label class="font-weight-bold">Estado</label>
                                            <select name="estado" class="form-control">
                                                <option value="en_pension" selected>En pensión</option>
                                                <option value="fuera_de_pension">Fuera de pensión</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="camiones-form-actions">
                                        <button type="submit" id="btnGuardar" class="camiones-primary-button">
                                            <i class="mdi mdi-content-save"></i>
                                            Guardar camión
                                        </button>
                                        <a class="camiones-secondary-button" href="index.php?menu=micamion&amp;seccion=visualizar">Cancelar</a>
                                    </div>
                                </form>
                    </div>
                </section>

                <?php else: ?>
                <section class="camiones-card">
                    <header class="camiones-table-header">
                        <div>
                            <h2><i class="mdi mdi-truck"></i> Camiones registrados</h2>
                            <span>Total: <?= count($camiones) ?> camiones</span>
                        </div>
                    </header>
                    <?php if ($camiones): ?>
                        <div class="camiones-table-wrap">
                            <table class="camiones-table" id="camionesTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Placa</th>
                                        <th>Placa de caja</th>
                                        <th>Operador / Empresa</th>
                                        <th>Marca y modelo</th>
                                        <th>Línea</th>
                                        <th>Color</th>
                                        <th>Año</th>
                                        <th>Entrada</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($camiones as $indiceCamion => $camionRegistrado):
                                        $estadoCamion = strtolower((string) $camionRegistrado['estado']);
                                        $nombreEstado = ucfirst(str_replace('_', ' ', (string) $camionRegistrado['estado']));
                                        $claseEstado = $estadoCamion === 'en_pension'
                                            ? 'success'
                                            : ($estadoCamion === 'mantenimiento' ? 'warning' : 'info');
                                        $fechaEntradaCamion = trim(
                                            (string) ($camionRegistrado['fecha_entrada'] ?? '')
                                            . ' '
                                            . (string) ($camionRegistrado['hora_entrada'] ?? '')
                                        );
                                    ?>
                                        <tr
                                            id="camion-<?= (int) $camionRegistrado['id_camion'] ?>"
                                            data-camion-row
                                            data-estado="<?= e($estadoCamion) ?>"
                                            <?= $idCamionRegistrado === (int) $camionRegistrado['id_camion'] ? 'class="camion-highlight"' : '' ?>
                                        >
                                            <td><?= $indiceCamion + 1 ?></td>
                                            <td><strong><?= e((string) $camionRegistrado['placa']) ?></strong></td>
                                            <td><?= e((string) ($camionRegistrado['placa_caja'] ?? '—')) ?></td>
                                            <td><?= e((string) ($camionRegistrado['operador_empresa'] ?? '—')) ?></td>
                                            <td><?= e(trim((string) ($camionRegistrado['marca'] ?? '') . ' ' . (string) $camionRegistrado['modelo'])) ?></td>
                                            <td><?= e((string) ($camionRegistrado['nom_linea'] ?? '—')) ?></td>
                                            <td><?= e((string) ($camionRegistrado['color'] ?? '—')) ?></td>
                                            <td><?= e((string) ($camionRegistrado['anio'] ?? '—')) ?></td>
                                            <td><?= e($fechaEntradaCamion !== '' ? $fechaEntradaCamion : '—') ?></td>
                                            <td><span class="camiones-status <?= $claseEstado ?>"><?= e($nombreEstado) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="camiones-empty-filter" id="camionesEmptyFilter" hidden>No hay camiones que coincidan con la búsqueda.</div>
                        </div>
                        <footer class="camiones-table-footer">
                            <span id="camionesSummary" aria-live="polite"></span>
                            <nav class="camiones-pagination" id="camionesPagination" aria-label="Paginación de camiones"></nav>
                        </footer>
                    <?php else: ?>
                        <div class="camiones-empty-state">
                            <i class="mdi mdi-truck-outline"></i>
                            <strong>Todavía no hay camiones registrados</strong>
                            <span>Registra una unidad para verla en esta lista.</span>
                            <a class="camiones-primary-button" href="index.php?menu=micamion&amp;seccion=registrar"><i class="mdi mdi-plus"></i> Registrar camión</a>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

<!-- SCRIPT -->
<script>
const formularioCamion = document.getElementById('formCamion');
if (formularioCamion) {
    formularioCamion.addEventListener('submit', async function(event) {
        event.preventDefault();
        const boton = document.getElementById('btnGuardar');
        boton.disabled = true;

        try {
            const respuesta = await fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: { 'Accept': 'application/json' }
            });
            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.exito) {
                alert(resultado.mensaje || 'No fue posible guardar el camión.');
                return;
            }

            window.location.href = 'index.php?menu=micamion&seccion=visualizar&camion_registrado='
                + encodeURIComponent(resultado.id_camion)
                + '#camion-' + encodeURIComponent(resultado.id_camion);
        } catch (error) {
            alert('No se pudo completar el registro. Verifica tu conexión e inténtalo nuevamente.');
        } finally {
            boton.disabled = false;
        }
    });
}

const camionesTabla = document.getElementById('camionesTable');
if (camionesTabla) {
    const filasCamiones = Array.from(camionesTabla.querySelectorAll('[data-camion-row]'));
    const busquedaCamiones = document.getElementById('camionesSearch');
    const filtroEstadoCamiones = document.getElementById('camionesStatusFilter');
    const resumenCamiones = document.getElementById('camionesSummary');
    const paginacionCamiones = document.getElementById('camionesPagination');
    const vacioCamiones = document.getElementById('camionesEmptyFilter');
    const filasPorPagina = 10;
    let paginaCamiones = 1;

    const renderizarCamiones = function() {
        const termino = busquedaCamiones.value.trim().toLocaleLowerCase();
        const estadoSeleccionado = filtroEstadoCamiones.value;
        const filasFiltradas = filasCamiones.filter(function(fila) {
            const coincideTexto = fila.textContent.toLocaleLowerCase().includes(termino);
            const coincideEstado = !estadoSeleccionado || fila.dataset.estado === estadoSeleccionado;
            return coincideTexto && coincideEstado;
        });
        const totalPaginas = Math.max(1, Math.ceil(filasFiltradas.length / filasPorPagina));
        paginaCamiones = Math.min(paginaCamiones, totalPaginas);
        const inicio = (paginaCamiones - 1) * filasPorPagina;
        const fin = Math.min(inicio + filasPorPagina, filasFiltradas.length);

        filasCamiones.forEach(function(fila) {
            fila.hidden = true;
        });
        filasFiltradas.slice(inicio, fin).forEach(function(fila) {
            fila.hidden = false;
        });

        resumenCamiones.textContent = filasFiltradas.length
            ? 'Mostrando ' + (inicio + 1) + ' a ' + fin + ' de ' + filasFiltradas.length + ' camiones'
            : 'No hay camiones para mostrar';
        vacioCamiones.hidden = filasFiltradas.length > 0;
        paginacionCamiones.replaceChildren();

        if (totalPaginas <= 1) {
            return;
        }

        const agregarBoton = function(etiqueta, destino, textoAlternativo, deshabilitado, activo) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'camiones-page-button' + (activo ? ' active' : '');
            boton.textContent = etiqueta;
            boton.setAttribute('aria-label', textoAlternativo);
            if (activo) {
                boton.setAttribute('aria-current', 'page');
            }
            boton.disabled = deshabilitado;
            boton.addEventListener('click', function() {
                paginaCamiones = destino;
                renderizarCamiones();
            });
            paginacionCamiones.appendChild(boton);
        };

        agregarBoton('‹', paginaCamiones - 1, 'Página anterior', paginaCamiones === 1, false);
        const primeraPagina = Math.max(1, Math.min(paginaCamiones - 2, totalPaginas - 4));
        const ultimaPagina = Math.min(totalPaginas, primeraPagina + 4);
        for (let pagina = primeraPagina; pagina <= ultimaPagina; pagina++) {
            agregarBoton(String(pagina), pagina, 'Página ' + pagina, false, pagina === paginaCamiones);
        }
        agregarBoton('›', paginaCamiones + 1, 'Página siguiente', paginaCamiones === totalPaginas, false);
    };

    busquedaCamiones.addEventListener('input', function() {
        paginaCamiones = 1;
        renderizarCamiones();
    });
    filtroEstadoCamiones.addEventListener('change', function() {
        paginaCamiones = 1;
        renderizarCamiones();
    });
    renderizarCamiones();
}

</script>

            <!-- Footer -->
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

<!-- plugins:js -->
<script src="public/assets/vendors/js/vendor.bundle.base.js"></script>

<!-- Plugin js -->
<script src="public/assets/vendors/chart.js/Chart.min.js"></script>
<script src="public/assets/vendors/jquery-circle-progress/js/circle-progress.min.js"></script>

<!-- inject:js -->
<script src="public/assets/js/off-canvas.js"></script>
<script src="public/assets/js/hoverable-collapse.js"></script>
<script src="public/assets/js/misc.js"></script>
<script src="public/assets/js/settings.js"></script>
<script src="public/assets/js/todolist.js"></script>

<!-- Custom js -->
<script src="public/assets/js/dashboard.js"></script>

</body>
</html>