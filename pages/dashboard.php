<?php

include("../components/header.php");
include("../components/sidebar.php");

$saldo = isset($_SESSION['user_data']) ? $_SESSION['user_data']['saldo'] : 0;

?>

<div class="home_content">

    <div class="text">ESTADO DE CUENTA</div>

    <div class="alert alert-info" role="alert">
        IMPORTANTE: Los pagos deben realizarse a la tasa de cambio del día del Banco Central de Venezuela (BCV).
    </div>

    <div class="container_info">

        <div class="box_info">
            <i class="fa-solid fa-money-bills"></i>
            <div class="box_text">
                <p id="balance">
                    <?php
                    if (isset($_SESSION['user_data'])) {
                        echo '$ ' . $_SESSION['user_data']['saldo'];
                    }
                    ?>
                    <input type="hidden" id="user-saldo" value="<?php echo $saldo; ?>">
                </p>
                <p class="text_gray">Deuda $</p>
            </div>
        </div>

        <div class="box_info">
            <i class="fa-solid fa-money-bills"></i>
            <div class="box_text">
                <p id="balanceBs">
                    <?php
                        if (isset($_SESSION['user_data'])) {
                            $saldo = $_SESSION['user_data']['saldo'];
                    
                            include('../data/conexion.php');
                    
                            $sql = "SELECT tasa FROM tasacambio ORDER BY id DESC LIMIT 1";
                            $result = $conn->query($sql);
                    
                            if ($result && $result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $tasa_cambio = $row['tasa'];
                    
                                $deuda_bsd = $saldo * $tasa_cambio;
                                echo 'Bs ' . number_format($deuda_bsd, 2, '.', ',');
                            } else {
                                echo 'Bs 0.00';
                                $deuda_bsd = 0;
                            }
                    
                        }
                    ?>
                    <input type="hidden" id="user-saldoBs" value="<?php echo $deuda_bsd; ?>">
                </p>
                <p class="text_gray">Deuda BsD</p>
            </div>
        </div>

        <?php
            if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
        ?>
        <div class="box_info">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <div class="box_text">
                <p id="balance">
                    <input type="text" id="deuda_bsd" name="deuda_bsd">
                </p>
                <p class="text_gray">Tasa del día (BCV)</p>
            </div>
        </div>
        <?php
        }
        ?>

        <div class="box_info">
            <i class="fa-solid fa-landmark"></i>
            <div class="box_text">
                <p id="meses">
                    <?php
                    if (isset($_SESSION['user_data'])) {
                        echo $_SESSION['user_data']['cantidad_meses'];
                    }
                    ?>
                </p>
                <p class="text_gray">Total de meses</p>
            </div>
        </div>

        <div class="box_info">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <div class="box_text">
                <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Registrar Pago
                </button>
            </div>
        </div>

    </div>

    <section class="table" id="#table_dashboard">
        <table class="table_estado">
            <tr>
                <th>Nombre y Apellido</th>
                <th>Cédula</th>
                <th>Total de Puestos</th>
                <th>N° de Puesto</th>
                <th>N° de Meses</th>
                <th>Meses</th>
                <th>Monto</th>
            </tr>
            <?php
            function count_total_users() {
                include('../data/conexion.php');
            
                $sql = "SELECT COUNT(*) AS total_records FROM personas";
                $result = $conn->query($sql);
            
                $total_records = 0;
                if ($result) {
                    $row = $result->fetch_assoc();
                    $total_records = $row['total_records'];
                } else {
                    echo "Error al contar registros: " . $conn->error;
                }
            
                $conn->close();
            
                return $total_records;
            }

            function get_all_users($offset, $records_per_page) {
                include('../data/conexion.php');

                $sql = "SELECT
                    personas.id,
                    personas.cedula,
                    personas.nombre_completo,
                    COUNT(DISTINCT CASE WHEN puesto.n_puesto != 'No aplica' THEN puesto.id END) AS total_puestos,
                    GROUP_CONCAT(DISTINCT tipo_vehiculo.vehiculo ORDER BY tipo_vehiculo.vehiculo SEPARATOR ', ') AS tipo_vehiculo,
                    GROUP_CONCAT(DISTINCT puesto.tipo_puesto ORDER BY puesto.tipo_puesto SEPARATOR ', ') AS tipo_puesto,
                    GROUP_CONCAT(DISTINCT puesto.n_puesto ORDER BY puesto.n_puesto SEPARATOR ', ') AS n_puesto,
                    GROUP_CONCAT(DISTINCT cxc.cantidad_meses ORDER BY cxc.cantidad_meses SEPARATOR ', ') AS cantidad_meses,
                    GROUP_CONCAT(DISTINCT cxc.meses ORDER BY cxc.meses SEPARATOR ', ') AS meses,
                    GROUP_CONCAT(DISTINCT cxc.saldo ORDER BY cxc.saldo SEPARATOR ', ') AS saldo
                FROM
                    personas
                LEFT JOIN
                    tipo_vehiculo ON personas.id = tipo_vehiculo.personas_id
                LEFT JOIN
                    puesto ON personas.id = puesto.personas_id
                LEFT JOIN
                    cxc ON personas.id = cxc.personas_id
                GROUP BY
                    personas.id
                ORDER BY
                    personas.id
                LIMIT ? OFFSET ?;";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ii", $records_per_page, $offset);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $users = array();

                    while($row = $result->fetch_assoc()) {
                        $users[] = $row;
                    }
                    $stmt->close();
                } else {
                    echo "Error al preparar la consulta: " . $conn->error;
                }

                $conn->close();

                return $users;
            }

            // Variables para la paginación
            $records_per_page = 12;
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($current_page - 1) * $records_per_page;

            // Obtener el número total de registros
            $total_records = count_total_users();

            // Calcular el número total de páginas
            $total_pages = ceil($total_records / $records_per_page);

            if (isset($_SESSION['user_data'])) {
                $user_role = $_SESSION['user_data']['roles_id'];

                if ($user_role == 1) {
                    $all_users = get_all_users($offset, $records_per_page);

                    foreach ($all_users as $user) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['nombre_completo']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['cedula']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['total_puestos']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['n_puesto']) . '</td>';
                        echo '<td id="cnt_meses">' . (!empty($user['cantidad_meses']) ? htmlspecialchars($user['cantidad_meses']) : '0') . '</td>';
                        echo '<td id="meses_table">' . (!empty($user['meses']) ? htmlspecialchars($user['meses']) : '0') . '</td>';
                        echo '<td id="money_table">' . (!empty($user['saldo']) ? htmlspecialchars($user['saldo']) : "0.00") . '</td>';
                        echo '</tr>';
                    }
                } else if ($user_role == 2) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($_SESSION['user_data']['nombre_completo']) . '</td>';
                    echo '<td>' . htmlspecialchars($_SESSION['user_data']['cedula']) . '</td>';
                    echo '<td>' . htmlspecialchars($_SESSION['user_data']['total_puestos']) . '</td>';
                    echo '<td>' . htmlspecialchars($_SESSION['user_data']['n_puesto']) . '</td>';
                    echo '<td id="cnt_meses">' . (!empty($_SESSION['user_data']['cantidad_meses']) ? htmlspecialchars($_SESSION['user_data']['cantidad_meses']) : '0') . '</td>';
                    echo '<td id="meses_table">' . (!empty($_SESSION['user_data']['meses']) ? htmlspecialchars($_SESSION['user_data']['meses']) : '0') . '</td>';
                    echo '<td id="money_table">' . htmlspecialchars($_SESSION['user_data']['saldo']) . '</td>';
                    echo '</tr>';
                }
            }

            ?>
        </table>
        <?php
        // Enlaces de paginación
        if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
            echo '<div class="pagination">';
            for ($page = 1; $page <= $total_pages; $page++) {
                if ($page == $current_page) {
                    echo '<a href="?page=' . $page . '#table_dashboard" class="active_pagination">' . $page . '</a> ';
                } else {
                    echo '<a href="?page=' . $page . 'table_dashboard">' . $page . '</a> ';
                }
            }
            echo '</div>';
        }
        ?>
    </section>

    <section class="table_info">
        <button type="button" data-bs-toggle="modal" data-bs-target="#exampleModalTable">
            VER ESTADOS
        </button>
    </section>

    <!-- Mensaje exitoso de pago -->
    <div id="alert-container"></div>

    <!-- BOTON TABLA PAGOS REALIZADOS -->
    <div class="reg_payment">
        <p data-bs-toggle="modal" data-bs-target="#Modal_reg_payment">Registro de pagos realizados</p>
    </div>

    <?php
        if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
    ?>
    <!-- BOTON TABLA HISTORIAL TASAS DE CAMBIO -->
    <div class="historial_tasas">
        <p data-bs-toggle="modal" data-bs-target="#Modal_historial_tasas">Historial de tasas de cambio</p>
    </div>
    <?php
        }
    ?>

</div>

<!-- MODAL HISTORIAL TASAS DE CAMBIO -->
<?php
    if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
?>
    <div class="modal fade" id="Modal_historial_tasas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Historial de tasas de cambio</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <?php
                    include('../data/conexion.php');

                    // Variables para la paginación
                    $records_per_page = 10;
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($current_page - 1) * $records_per_page;

                    try {
                        $conn->begin_transaction();

                        // Obtener el número total de registros
                        $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_records FROM tasacambio");
                        $stmt_count->execute();
                        $result_count = $stmt_count->get_result();
                        $total_records = $result_count->fetch_assoc()['total_records'];
                        $stmt_count->close();

                        // Calcular el número total de páginas
                        $total_pages = ceil($total_records / $records_per_page);

                        // Obtener los registros de la página actual
                        $stmt = $conn->prepare("SELECT
                        id,
                        tasa,
                        origen,
                        DATE(creado_en) AS fecha, 
                        TIME(creado_en) AS hora 
                        FROM tasacambio
                        ORDER BY creado_en ASC
                        LIMIT ? OFFSET ?");
                        $stmt->bind_param("ii", $records_per_page, $offset);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        echo '<table class="table_tasas_modal">
                            <tr class="table_head">
                                <th></th>
                                <th>Tasa</th>
                                <th>Origen</th>
                                <th>Fecha de creación</th>
                                <th>Hora de creación</th>
                            </tr>';

                        while ($row = $result->fetch_assoc()) {
                            echo '<tr class="info_tasas">';
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['tasa']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['origen']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['fecha']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['hora']) . '</td>';
                            echo '</tr>';
                        }

                        echo '</table>';

                        $stmt->close();
                        $conn->commit();

                        // Generar los enlaces de paginación
                        $pagination_links = '<div class="pagination">';
                        for ($page = 1; $page <= $total_pages; $page++) {
                            if ($page == $current_page) {
                                $pagination_links .= '<a href="?page=' . $page . '#Modal_historial_tasas" class="active_pagination">' . $page . '</a> ';
                            } else {
                                $pagination_links .= '<a href="?page=' . $page . '#Modal_historial_tasas">' . $page . '</a> ';
                            }
                        }
                        $pagination_links .= '</div>';

                    } catch (Exception $e) {
                        $conn->rollback();
                        echo 'Error: ' . $e->getMessage();
                    }

                    ?>

                </div>
                <div class="modal-footer">
                    <?php
                    if (isset($pagination_links)) {
                        echo $pagination_links;
                    }
                    ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?>

<!-- MODAL TABLA DEUDAS -->
<div class="modal fade" id="exampleModalTable" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Estados de cuenta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table_estado_modal">
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Cédula</th>
                        <th>Total de Puestos</th>
                        <th>N° de Puesto</th>
                        <th>N° de Meses</th>
                        <th>Meses</th>
                        <th>Monto</th>
                    </tr>
                    <?php
                    if (isset($_SESSION['user_data'])) {
                        $user_role = $_SESSION['user_data']['roles_id'];

                        if ($user_role == 1) {
                            $all_users = get_all_users($offset, $records_per_page);

                            foreach ($all_users as $user) {
                                echo '<tr>';
                                echo '<td>' . $user['nombre_completo'] . '</td>';
                                echo '<td>' . $user['cedula'] . '</td>';
                                echo '<td>' . $user['total_puestos'] . '</td>';
                                echo '<td>' . $user['n_puesto'] . '</td>';
                                echo '<td id="cnt_meses">' . (!empty($user['cantidad_meses']) ? $user['cantidad_meses'] : '0') . '</td>';
                                echo '<td id="meses_table">' . (!empty($user['meses']) ? $user['meses'] : '0') . '</td>';
                                echo '<td id="money_table">' . $user['saldo'] . '</td>';
                                echo '</tr>';
                            }
                        } else if ($user_role == 2) {
                            echo '<tr>';
                            echo '<td>' . $_SESSION['user_data']['nombre_completo'] . '</td>';
                            echo '<td>' . $_SESSION['user_data']['cedula'] . '</td>';
                            echo '<td>' . $_SESSION['user_data']['total_puestos'] . '</td>';
                            echo '<td>' . $_SESSION['user_data']['n_puesto'] . '</td>';
                            echo '<td id="cnt_meses">' . (!empty($_SESSION['user_data']['cantidad_meses']) ? $_SESSION['user_data']['cantidad_meses'] : '0') . '</td>';
                            echo '<td id="meses_table">' . (!empty($_SESSION['user_data']['meses']) ? $_SESSION['user_data']['meses'] : '0') . '</td>';
                            echo '<td id="money_table">' . $_SESSION['user_data']['saldo'] . '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR PAGO -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Pago</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close_modal"></button>
            </div>
            <div class="modal-body">
                <h4>Formulario de pago</h4>
                <form action="" class="form_payment" id="form_payment">

                    <div class="input-box">
                        <span class="icon"><i class="las la-user"></i></span>
                        <input type="text" id="name_payment" name="name_payment">
                        <label for="name_payment">Nombre Completo</label>
                    </div>

                    <div class="input-box">
                        <input type="date" id="date" name="date">
                        <label for="date">Fecha del pago</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-money-bill-wave"></i></span>
                        <select name="tPago" id="tPago">
                            <option value="0" name="pMovil" selected>Pago Móvil</option>
                            <option value="1" name="transferencia">Transferencia</option>
                            <option value="2" name="efectivo">Efectivo</option>
                        </select>
                        <label for="tPago" class="label_select">Forma de pago</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-university"></i></span>
                        <select name="bank_select" id="bank_select">
                            <option name="bancos" disabled selected>Por favor, seleccione un banco</option>
                            <option value="Ninguno">Ninguno</option>
                            <?php
                            include("../data/conexion.php");

                            $conn->begin_transaction();

                            $con1 = $conn->prepare("SELECT LPAD(cod, 4, '0') AS cod, nombre FROM bancos");
                            $con1->execute();

                            $result = $con1->get_result();

                            if ($result) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['cod'] . ' - ' . $row['nombre'] . '">' . $row['cod'] . ' - ' . $row['nombre'] . '</option>';
                                }
                            } else {
                                echo '<option value="">No se encontraron bancos</option>';
                            }

                            $con1->close();

                            $conn->commit();
                            ?>
                        </select>
                        <label for="bank_select" class="label_select">Banco Emisor</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="fa-solid fa-square-parking"></i></span>
                        <input type="text" id="tPuesto" name="tPuesto">
                        <!--<select name="tPuesto" id="tPuesto">
                            <option name="fijo" value="Fijo" selected>Fijo</option>
                            <option name="flotante" value="Flotante">Flotante</option>
                            <option name="moto" value="Moto">Moto</option>
                        </select>-->
                        <label for="tPuesto" class="label_select">Tipo de puesto</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="fa-solid fa-list-ol"></i></span>
                        <input type="text" id="nPuesto" name="nPuesto">
                        <label for="nPuesto" class="label_select">N° de puesto(s)</label>
                    </div>

                    <!-- Checkboxes para meses "Sí" y "No" -->
                    <div class="input-box-checbox">
                        <label class="label_checkbox">¿Realizará el pago del mes completo?</label>
                        <div class="container_checkbox">
                            <input type="radio" id="pagoSi" name="pagoCompleto" value="si">
                            <label for="pagoSi">Sí</label>
                            
                            <input type="radio" id="pagoNo" name="pagoCompleto" value="no">
                            <label for="pagoNo">No</label>
                        </div>
                    </div>

                    <!-- Selector de meses (Si) -->
                    <div class="input-box-select" id="selectMesesCompleto" style="display: none;">
                        <select name="meses_dashboard" id="meses_dashboard" class="meses_dashboard" multiple>
                            <option value="" selected disabled>Seleccionar meses</option>
                            <?php
                            $meses = explode('<br>', $_SESSION['user_data']['meses']);

                            foreach ($meses as $mes) {
                                $mesData = explode(': ', $mes);
                                $year = $mesData[0];
                                $months = explode(', ', $mesData[1]);

                                echo '<option disabled>' . $year . '</option>';

                                foreach ($months as $month) {
                                    echo '<option value="' . $year . ': ' . $month . '">' . $month . '</option>';
                                }
                            }
                        ?>
                        </select>
                        <label for="meses">Meses pagados</label>
                    </div>

                    <!-- Selector de meses (No) -->
                    <div class="input-box-select margin" id="selectMesesDeuda" style="display: none;"> 
                        <label for="mesesPendientes">Meses pendientes de pago:</label>
                        <select name="mesesPendientes[]" id="mesesPendientes" class="meses_dashboard mesesPendientes">
                            <?php
                            include("../data/conexion.php");

                            $query = $conn->prepare("SELECT DISTINCT mes AS meses, anio FROM cxc_detalle WHERE cxc_id = ? AND fecha_pago IS NULL ORDER BY anio ASC, mes ASC");
                            $query->bind_param("s", $_SESSION['user_data']['cxc_id']);
                            $query->execute();
                            $result = $query->get_result();

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option disabled selected>Seleccionar mes</option>';
                                    echo '<option value="' . $row['meses'] . ' - ' . $_SESSION['user_data']['cxc_id'] . '">' . $row['anio'] . ': ' . $row['meses'] . '</option>';
                                }
                            } else {
                                echo '<option value="" disabled selected>No hay meses pendientes</option>';
                            }

                            // Cerrar la consulta y la conexión
                            $query->close();
                            ?>
                        </select>
                    </div>

                    <!-- Contenedor de tarifas dinamicas para el selector de meses "No" -->
                    <div class="input-box-select" id="tarifas-container"></div>
                    
                    <div class="input-box">
                        <span class="icon"><i class="las la-coins"></i></span>
                        <input type="number" id="money" name="money">
                        <label for="money" class="label_select">Monto en dólares</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-coins"></i></span>
                        <input type="number" id="money_bs" name="money_bs">
                        <label for="money_bs" class="label_select">Monto en bolívares</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-comment-dots"></i></span>
                        <input type="number" id="ref" name="ref">
                        <label for="ref">N° de Referencia</label>
                    </div>

                    <div class="input-box">
                        <input type="file" id="capture" name="capture" class="input_file">
                        <label for="capture">Foto del pago realizado</label>
                    </div>

                    <div class="requirements">
                        <p class="error-message" id="error-message"></p>
                    </div><br>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="enviar_payment">Comprobar pago</button>

                <button class="btn btn-primary" id="confirmar_payment" data-bs-toggle="modal" data-bs-target="#ModalPayment" disabled>Verificar Pago</button>

                <button type="button" class="btn btn-secondary" id="cancelar_modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VERIFICAR PAGO -->
<div class="modal fade" id="ModalPayment" tabindex="-1" aria-labelledby="ModalPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Verificar Datos</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close_modal"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="register_payment">Registrar Pago</button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Regresar</button>
                <button type="button" class="btn btn-secondary" id="close_modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRO DE PAGOS REALIZADOS -->
<div class="modal fade" id="Modal_reg_payment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Pagos Realizados</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <?php
                if (isset($_SESSION['user_data']['id']) && isset($_SESSION['user_data']['roles_id'])) {
                    $id = $_SESSION['user_data']['id'];
                    $user_role = $_SESSION['user_data']['roles_id'];

                    // Variables para la paginación
                    $records_per_page = 12;
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($current_page - 1) * $records_per_page;

                    try {
                        $conn->begin_transaction();

                        if ($user_role == 1) {
                            // Rol 1: Mostrar todos los registros
                            $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_records FROM cxc_pagos_relacion");
                        } else {
                            // Rol 2: Mostrar solo los registros del usuario actual
                            $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_records FROM cxc_pagos_relacion WHERE id_cxc = ?");
                            $stmt_count->bind_param("i", $id);
                        }

                        $stmt_count->execute();
                        $result_count = $stmt_count->get_result();
                        $total_records = $result_count->fetch_assoc()['total_records'];
                        $stmt_count->close();

                        // Calcular el número total de páginas
                        $total_pages = ceil($total_records / $records_per_page);

                        // Consulta para obtener los registros de la página actual
                        if ($user_role == 1) {
                            // Rol 1: Mostrar todos los registros
                            $stmt = $conn->prepare("SELECT
                                cxc.nombre_completo,
                                pagos.id AS pagos_id,
                                pagos.num_referencia,
                                pagos.banco_receptor,
                                pagos.monto_num,
                                pagos.monto_bs,
                                pagos.tPago,
                                pagos.meses_pagados,
                                pagos.fecha_pago,
                                pagos.tPuesto_pago,
                                pagos.nPuesto_pago
                            FROM 
                                cxc_pagos_relacion
                            INNER JOIN 
                                cxc ON cxc_pagos_relacion.id_cxc = cxc.id
                            INNER JOIN 
                                pagos ON cxc_pagos_relacion.id_pagos = pagos.id
                            LIMIT ? OFFSET ?");

                            $stmt->bind_param("ii", $records_per_page, $offset);
                        } else {
                            // Rol 2: Mostrar solo los registros del usuario actual
                            $stmt = $conn->prepare("SELECT
                                cxc.nombre_completo,
                                pagos.id AS pagos_id,
                                pagos.num_referencia,
                                pagos.banco_receptor,
                                pagos.monto_num,
                                pagos.monto_bs,
                                pagos.tPago,
                                pagos.meses_pagados,
                                pagos.fecha_pago,
                                pagos.tPuesto_pago,
                                pagos.nPuesto_pago
                            FROM 
                                cxc_pagos_relacion
                            INNER JOIN 
                                cxc ON cxc_pagos_relacion.id_cxc = cxc.id
                            INNER JOIN 
                                pagos ON cxc_pagos_relacion.id_pagos = pagos.id
                            WHERE 
                                cxc.id = ?
                            LIMIT ? OFFSET ?");

                            $stmt->bind_param("iii", $id, $records_per_page, $offset);
                        }
                        
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        echo '<table class="table_estado_modal">
                            <tr class="table_head">
                                <th>Nombre y Apellido</th>
                                <th>Fecha de pago</th>
                                <th>Banco emisor</th>
                                <th>N° de referencia</th>
                                <th>Monto dólares</th>
                                <th>Monto bolívares</th>
                                <th>Tipo de pago</th>
                                <th>Meses pagados</th>
                                <th>Tipo de puesto</th>
                                <th>N° de puesto</th>
                                <th>Recibo</th>
                            </tr>';
                        
                        while ($row = $result->fetch_assoc()) {
                            $pdf_link = "../data/generate_pdf.php?payment_id=" . htmlspecialchars($row['pagos_id']);
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['nombre_completo']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['fecha_pago']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['banco_receptor']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['num_referencia']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['monto_num']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['monto_bs']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['tPago']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['meses_pagados']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['tPuesto_pago']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nPuesto_pago']) . '</td>';
                            echo '<td><a href="' . $pdf_link . '" target="_blank" class="btn btn-primary btn_pdf">Ver Recibo</a></td>';
                            echo '</tr>';
                        }
                        
                        echo '</table>';
                        
                        $stmt->close();
                        $conn->commit();
                    } catch (Exception $e) {
                        $conn->rollback();
                        echo 'Error: ' . $e->getMessage();
                    }

                    // Guardar el código de paginación en una variable
                    $pagination_links = '<div class="pagination">';
                    for ($page = 1; $page <= $total_pages; $page++) {
                        if ($page == $current_page) {
                            $pagination_links .= '<a href="?page=' . $page . '#Modal_reg_payment" class="active_pagination">' . $page . '</a> ';
                        } else {
                            $pagination_links .= '<a href="?page=' . $page . '#Modal_reg_payment">' . $page . '</a> ';
                        }
                    }
                    $pagination_links .= '</div>';
                }
                ?>
            </div>
            <div class="modal-footer">
                <?php
                    if (isset($pagination_links)) {
                        echo $pagination_links;
                    }
                ?>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php

include("../components/footer.php");

?>