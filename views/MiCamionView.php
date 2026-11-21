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

    <title>Tracto Pensión</title>

    <!-- plugins:css -->
    <link rel="stylesheet" href="public/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="public/assets/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="public/assets/vendors/css/vendor.bundle.base.css">

    <!-- Plugin css -->
    <link rel="stylesheet" href="public/assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="public/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">

    <!-- Layout styles -->
    <link rel="stylesheet" href="public/assets/css/demo_1/style.css">

    <link rel="shortcut icon" href="public/assets/images/favicon.png" />

</head>

<body>

<div class="container-scroller">

    <!-- Navbar -->
    <?=NavBarView(); ?>

    <div class="container-fluid page-body-wrapper">

        <!-- Configuración -->
        <div id="settings-trigger">
            <i class="mdi mdi-settings"></i>
        </div>

        <div id="theme-settings" class="settings-panel">

            <i class="settings-close mdi mdi-close"></i>

            <p class="settings-heading">SIDEBAR SKINS</p>

            <div class="sidebar-bg-options selected" id="sidebar-default-theme">
                <div class="img-ss rounded-circle bg-dark border mr-3"></div>
                Default
            </div>

            <div class="sidebar-bg-options" id="sidebar-dark-theme">
                <div class="img-ss rounded-circle bg-light border mr-3"></div>
                Light
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

        <!-- Sidebar -->
        <?=SideBarView(); ?>

        <!-- Main -->
        <div class="main-panel">

            <div class="content-wrapper">

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h3 class="text-dark font-weight-bold mb-1">Camiones</h3>
                                        <p class="text-muted mb-0">Registra camiones o consulta los que ya están guardados.</p>
                                    </div>
                                </div>
                                <ul class="nav nav-pills mt-4">
                                    <li class="nav-item">
                                        <a class="nav-link <?= $seccionCamiones === 'registrar' ? 'active' : '' ?>" href="index.php?menu=micamion&amp;seccion=registrar">
                                            <i class="mdi mdi-plus"></i> Registrar camión
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $seccionCamiones === 'visualizar' ? 'active' : '' ?>" href="index.php?menu=micamion&amp;seccion=visualizar">
                                            <i class="mdi mdi-format-list-bulleted"></i> Visualizar camiones
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($mensajeCamion !== ''): ?>
                    <div class="alert alert-success" role="status"><?= e($mensajeCamion) ?></div>
                <?php endif; ?>

                <?php if ($seccionCamiones === 'registrar'): ?>
                <div class="row">
                    <div class="col-lg-5 col-md-12 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <img src="public/assets/images/samples/1280x768/12.jpg"
                                     alt="Camión"
                                     class="img-fluid rounded shadow-sm"
                                     style="max-height: 280px; object-fit: cover; width: 100%;">
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 col-md-12 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h4 class="font-weight-bold mb-4" id="formularioCamion">Registrar camión</h4>
                                <form method="POST" action="index.php?menu=micamion&amp;seccion=registrar" id="formCamion">
                                    <?= csrf_input(); ?>
                                    <input type="hidden" name="accion" value="guardar">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Nombre del operador o empresa</label>
                                            <input type="text" name="operador_empresa" class="form-control" maxlength="150" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Placa del tracto</label>
                                            <input type="text" name="placa" class="form-control" placeholder="23-45-TX o 345-RT-6" pattern="(?:[0-9]{2}-[0-9]{2}-[A-Za-z]{2}|[0-9]{3}-[A-Za-z]{2}-[0-9])" title="Usa el formato 23-45-TX o 345-RT-6" maxlength="10" autocapitalize="characters" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Marca</label>
                                            <select name="marca" class="form-control" required>
                                                <option value="">Selecciona una marca</option>
                                                <?php foreach (MiCamionModel::MARCAS_COMUNES as $marcaComun): ?>
                                                    <option value="<?= e($marcaComun) ?>"><?= e($marcaComun) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Modelo</label>
                                            <input type="text" name="modelo" class="form-control" maxlength="100" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Color</label>
                                            <select name="color" class="form-control" required>
                                                <option value="">Selecciona un color</option>
                                                <?php foreach (MiCamionModel::COLORES_COMUNES as $colorComun): ?>
                                                    <option value="<?= e($colorComun) ?>"><?= e($colorComun) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Fecha de Entrada</label>
                                            <input type="date" name="fecha" class="form-control">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="font-weight-bold">Hora de Entrada</label>
                                            <input type="time" name="hora" class="form-control">
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="font-weight-bold">Estado</label>
                                            <select name="estado" class="form-control">
                                                <option value="en_pension" selected>En pensión</option>
                                                <option value="fuera_de_pension">Fuera de pensión</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex flex-wrap">
                                        <button type="submit" id="btnGuardar" class="btn btn-success rounded-pill mr-2 mb-2">
                                            <i class="mdi mdi-content-save"></i>
                                            Guardar camión
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h4 class="font-weight-bold mb-4">Camiones registrados</h4>
                                <?php if ($camiones): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Operador o empresa</th>
                                                    <th>Placa</th>
                                                    <th>Marca y modelo</th>
                                                    <th>Línea</th>
                                                    <th>Color</th>
                                                    <th>Año</th>
                                                    <th>Entrada</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($camiones as $camionRegistrado): ?>
                                                    <tr
                                                        id="camion-<?= (int) $camionRegistrado['id_camion'] ?>"
                                                        <?= $idCamionRegistrado === (int) $camionRegistrado['id_camion'] ? 'class="table-success"' : '' ?>
                                                    >
                                                        <td><?= e((string) ($camionRegistrado['operador_empresa'] ?? '')) ?: '—' ?></td>
                                                        <td><strong><?= e((string) $camionRegistrado['placa']) ?></strong></td>
                                                        <td>
                                                            <?= e(trim((string) ($camionRegistrado['marca'] ?? '') . ' ' . (string) $camionRegistrado['modelo'])) ?>
                                                        </td>
                                                        <td><?= e((string) ($camionRegistrado['nom_linea'] ?? '—')) ?></td>
                                                        <td><?= e((string) ($camionRegistrado['color'] ?? '—')) ?></td>
                                                        <td><?= e((string) ($camionRegistrado['anio'] ?? '—')) ?></td>
                                                        <td>
                                                            <?= e((string) ($camionRegistrado['fecha_entrada'] ?? '—')) ?>
                                                            <?= e((string) ($camionRegistrado['hora_entrada'] ?? '')) ?>
                                                        </td>
                                                        <td><?= e(ucfirst(str_replace('_', ' ', (string) $camionRegistrado['estado']))) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Todavía no hay camiones registrados en tu cuenta.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
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