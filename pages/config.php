<?php

include("../components/header.php");
include("../components/sidebar.php");

?>

<div class="home_content">

    <div class="text">INFORMACIÓN USUARIO</div>

    <section class="table_config">

        <div class="img">
            <img src="../img/user.png" alt="">
        </div>

        <div class="user_info">

            <div class="user_info_config">

                <form action="" id="form_config" class="form_config">
                    
                    <div class="input-box">
                        <span class="icon"><i class="las la-user"></i></span>
                        <input type="text" id="name_config" name="name_config" value="<?php echo $_SESSION['user_data']['nombre_completo']; ?>" disabled>
                        <label for="name_config">Nombre Completo</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="fa-regular fa-address-card"></i></span>
                        <input type="text" id="ci_config" name="ci_config" value="<?php echo $_SESSION['user_data']['cedula']; ?>" disabled>
                        <label for="ci_config">Número de cédula</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-at"></i></span>
                        <input type="text" id="email_config" name="email_config" value="<?php echo $_SESSION['user_data']['correo']; ?>" disabled>
                        <label for="email_config">Correo</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="fa-regular fa-eye"></i></span>
                        <input type="text" id="pass_config" name="pass_config" disabled>
                        <label for="pass_config">Clave</label>
                    </div>

                </form>

                <div id="alert-container_config"></div>

                <div class="container_btn">
                    <button type="button" class="btn btn-secondary" id="btn_config">Modificar</button>
                    <button type="submit" form="form_config" class="btn btn-primary" id="registrarBtn_config">Aceptar</button>
                </div>

            </div>

        </div>

    </section>

    <?php
        if (isset($_SESSION['user_data']) && $_SESSION['user_data']['roles_id'] == 1) {
    ?>
        <section class="table_config users">
            <h4>Usuarios</h4>

            <div class="table-responsive">
                <table class="table_users table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("../data/conexion.php");

                        $conn->begin_transaction();
                        // Configuración para la paginación
                        $records_per_page = 10;
                        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $offset = ($current_page - 1) * $records_per_page;

                        // Contar el número total de registros
                        $stmt_count = $conn->prepare("SELECT COUNT(*) AS total_records FROM usuarios");
                        $stmt_count->execute();
                        $result_count = $stmt_count->get_result();
                        $total_records = $result_count->fetch_assoc()['total_records'];
                        $stmt_count->close();

                        // Calcular el número total de páginas
                        $total_pages = ceil($total_records / $records_per_page);

                        // Consultar los usuarios para la página actual
                        $stmt = $conn->prepare("SELECT 
                            usuarios.id,
                            usuarios.usuario,
                            personas.nombre_completo,
                            personas.correo
                            FROM usuarios
                            INNER JOIN personas ON usuarios.personas_id = personas.id
                            LIMIT ? OFFSET ?");
                        $stmt->bind_param("ii", $records_per_page, $offset);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Mostrar los usuarios en la tabla
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                                echo "<td class='btnActions'>
                                    <button type='button' class='btn btn-primary editBtn' data-id='" . htmlspecialchars($row['id']) . "' data-bs-toggle='modal' data-bs-target='#ModalEdit'><i class='fa-regular fa-pen-to-square'></i></button>

                                    <button type='button' class='btn btn-danger deleteBtn' data-id='" . htmlspecialchars($row['id']) . "' data-name='" . htmlspecialchars($row['nombre_completo']) . "' data-bs-toggle='modal' data-bs-target='#ModalDelete'><i class='fa-solid fa-trash-can'></i></button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No se encontraron usuarios.</td></tr>";
                        }

                        $stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="pagination">
                <?php
                for ($page = 1; $page <= $total_pages; $page++) {
                    if ($page == $current_page) {
                        echo '<a href="?page=' . $page . '#users" class="active_pagination">' . $page . '</a> ';
                    } else {
                        echo '<a href="?page=' . $page . '#users">' . $page . '</a> ';
                    }
                }
                ?>
            </div>

            <!-- Mensaje de registro exitoso -->
            <div id="alert-container_users"></div>

        </section>
    <?php
        }
    ?>

    <section class="table_config tarifas">
        <h4>Tarifas</h4>

        <div class="user_info">

            <div class="user_info_configTarifas">

                <form action="" id="form_configTarifas" class="form_configTarifas">
                    
                    <?php
                    include('../data/conexion.php');

                    $sql = "SELECT id, tipo, tarifa FROM tarifas";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="input-box">';
                            echo '<span class="icon"><i class="fa-solid fa-square-parking"></i></span>';
                            echo '<label for="tarifa_' . $row['id'] . '">' . htmlspecialchars($row['tipo']) . '</label>';
                            echo '<input type="hidden" id="id_tarifa_' . $row['id'] . '" name="id_tarifa_' . $row['id'] . '" value="' . htmlspecialchars($row['id']) . '">';
                            echo '<input type="number" step="0.01" id="tarifa_' . $row['id'] . '" name="tarifa_' . $row['id'] . '" value="' . htmlspecialchars($row['tarifa']) . '">';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No hay tarifas disponibles.</p>';
                    }

                    $conn->close();
                    ?>

                </form>

                <div id="alert-container_configTarifas"></div>
                
                <button type="submit" form="form_configTarifas" class="btn btn-primary" id="registrarBtn_configTarifas">Aceptar</button>

            </div>

        </div>
    </section>

</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="ModalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalEditLabel">Editar Usuario (<span id="userNameToEdit"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="userId" name="id">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombreCompleto" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombreCompleto" name="nombre_completo" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div id="vehiculosContainer" class="mb-3"></div>
                    <button type="button" id="addVehicleBtn" class="btn btn-primary mb-3">Agregar otro vehículo</button>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="saveChangesBtn">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE ELIMINAR USUARIO -->
<div class="modal fade" id="ModalDelete" tabindex="-1" aria-labelledby="ModalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="ModalDeleteLabel">Eliminar usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="modalUserName">¿Está seguro de que desea eliminar al usuario <span id="userName"></span>?</h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <form id="deleteForm" action="../data/delete_user.php" method="post">
                    <input type="hidden" name="user_id" id="userIdToDelete">
                    <button type="submit" class="btn btn-primary">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php

include("../components/footer.php");

?>