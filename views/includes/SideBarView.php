<?php
function SideBarView(){
    $menuActual = (string) ($_GET['menu'] ?? 'panel');
    return "
     <nav class='sidebar sidebar-offcanvas' id='sidebar'>
          <ul class='nav'>
            <li class='nav-item nav-category'>Navegación principal</li>

            <li class='nav-item " . ($menuActual === 'panel' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=panel'>
                <span class='icon-bg'>
                  <i class='mdi mdi-view-dashboard menu-icon'></i>
                </span>
                <span class='menu-title'>Dashboard</span>
              </a>
            </li>
            
            <li class='nav-item " . ($menuActual === 'micamion' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=micamion'>
                <span class='icon-bg'>
                  <i class='mdi mdi-truck menu-icon'></i>
                </span>
                <span class='menu-title'>Camiones</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'pagos' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=pagos'>
                <span class='icon-bg'>
                  <i class='mdi mdi-cash-multiple menu-icon'></i>
                </span>
                <span class='menu-title'>Pagos</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'historial' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=historial'>
                <span class='icon-bg'>
                  <i class='mdi mdi-history menu-icon'></i>
                </span>
                <span class='menu-title'>Historial</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'accesos' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=accesos'>
                <span class='icon-bg'>
                  <i class='mdi mdi-door-open menu-icon'></i>
                </span>
                <span class='menu-title'>Accesos</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'pensionados' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=pensionados'>
                <span class='icon-bg'>
                  <i class='mdi mdi-account-group menu-icon'></i>
                </span>
                <span class='menu-title'>Pensionados</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'control_diario' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=control_diario'>
                <span class='icon-bg'>
                  <i class='mdi mdi-ticket-confirmation menu-icon'></i>
                </span>
                <span class='menu-title'>Control diario</span>
              </a>
            </li>

            <li class='nav-item " . ($menuActual === 'linea' ? 'active' : '') . "'>
              <a class='nav-link' href='?menu=linea'>
                <span class='icon-bg'>
                  <i class='mdi mdi-source-branch menu-icon'></i>
                </span>
                <span class='menu-title'>Linea</span>
              </a>
            </li>

            <li class='nav-item sidebar-user-actions'>
              <div class='sidebar-user-menu'>
                <a href='#' class='nav-link'><i class='mdi mdi-settings menu-icon'></i>
                  <span class='menu-title'>Configuraciones</span>
                </a>
              </div>
            </li>
            
            
          </ul>
          <div class='sidebar-trailer' role='presentation' aria-hidden='true'></div>
        </nav>
    ";
}
?>