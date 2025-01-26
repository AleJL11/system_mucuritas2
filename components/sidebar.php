<?php

//include ("../data/session_manager.php");

session_start();
//session_write_close();

?>

<div class="sidebar">

    <div class="logo_content">
        <div class="logo">
            <i class="fa-solid fa-square-parking"></i>
            <div class="logo_name">Mucuritas 2</div>
        </div>
        <i class="fa-solid fa-bars" id="btn"></i>
    </div>

    <ul class="nav_list">
        <li>
            <a href="./dashboard.php">
                <i class="fa-solid fa-house"></i>
                <span class="links_name">Inicio</span>
            </a>
            <span class="tooltip">Inicio</span>
        </li>
        <li>
            <a href="./reports.php">
                <i class="fa-regular fa-message"></i>
                <span class="links_name">Reportar</span>
            </a>
            <span class="tooltip">Reportar</span>
        </li>
        <?php
        if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
        ?>
            <li>
                <a href="./cuentas.php">
                    <i class="fa-regular fa-address-book"></i>
                    <span class="links_name">Cuentas por cobrar</span>
                </a>
                <span class="tooltip">Cuentas por cobrar</span>
            </li>
        <?php
        }
        ?>
        <li>
            <a href="./config.php">
                <i class="fa-solid fa-gear"></i>
                <span class="links_name">Configuración</span>
            </a>
            <span class="tooltip">Configuración</span>
        </li>
    </ul>

    <div class="profile_content">
        <div class="profile">
            <div class="profile_details">
                <img src="../img/user.png" alt="">
                <div class="name_job">
                    <div class="name">
                        <?php
                        if (isset($_SESSION['user_data'])) {
                            echo $_SESSION['user_data']['nombre_completo'];
                        } else {
                            echo "Usuario";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right-from-bracket fa-flip-horizontal" id="log_out" data-bs-toggle="modal" data-bs-target="#ModalLogout"></i>

            <!-- MODAL DE CERRAR SESIÓN -->
            <div class="modal fade" id="ModalLogout" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Cerrar Sesión</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5>¿Está seguro de que desea cerrar sesión?</h5>
                            <div class="btn_logout">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                <form action="../data/logout.php" method="post">
                                    <button type="submit" class="btn btn-primary">Sí</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>