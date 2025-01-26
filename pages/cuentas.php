<?php

include("../components/header.php");
include("../components/sidebar.php");

?>

<div class="home_content">

    <div class="text">REGISTRO DE CUENTAS POR COBRAR</div>

    <div class="alert alert-info" role="alert">
        IMPORTANTE: En caso de realizar la búsqueda del usuario por N° de cédula se debe separar por puntos, ejemplo: xx.xxx.xxx
    </div>

    <section class="table_cxc">
        <div class="user_selected">
            <h5>Seleccione el usuario:</h5>

            <input type="text" id="search" placeholder="Colocar N° de cédula o nombre">
            <div id="results"></div>

        </div>

        <div id="user_form" class="user_form" style="display: none;">

            <span class="line"></span>

            <h1>Información del usuario</h1>

            <div class="container_box">

                <input type="text" id="id" name="id" hidden>

                <div class="input-box">
                    <span class="icon"><i class="las la-user"></i></span>
                    <input type="text" id="name" name="name" readonly>
                    <label for="name">Nombre</label>
                </div>

                <div class="input-box">
                    <span class="icon"><i class="fa-regular fa-address-card"></i></span>
                    <input type="text" id="ci" name="ci" readonly>
                    <label for="ci">Cédula</label>
                </div>

                <div class="input-box">
                    <span class="icon"><i class="las la-user"></i></span>
                    <input type="email" id="email" name="email" readonly>
                    <label for="email">Correo</label>
                </div>

                <!--<div class="input-box">
                    <span class="icon"><i class="fa-solid fa-list-ol"></i></span>
                    <input type="text" id="puesto" name="puesto">
                    <label for="puesto">Número de puesto</label>
                </div>-->

            </div>

        </div>

        <div class="user_regcxc" id="user_regcxc" style="display: none;">

            <span class="line"></span>

            <h1>Registrar cuenta por cobrar</h1>

            <div class="container_box">

                <form action="" class="form_cxc" id="form_cxc" method="POST">

                    <div class="input-box-selects">
                        <select name="months" id="months" class="user_months" multiple>
                            <?php
                            include("../data/conexion.php");

                            $conn->begin_transaction();
                            $users = $conn->prepare("SELECT id, anos FROM anos");
                            $users->execute();
                            $result = $users->get_result();

                            if ($result) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['id'] . '">' . $row['anos'] . '</option>';
                                }
                            } else {
                                echo '<option value="">No se encontraron los años</option>';
                            }
                            ?>
                        </select>
                        <label for="months">Seleccione los años a deber:</label>
                    </div>

                    <div class="input-box_years" id="selectedYears">
                    </div>

                    <div class="input-box-selects" id="tarifa">
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="las la-dollar-sign"></i></span>
                        <input type="text" id="total" name="total" readonly>
                        <label for="total">Total</label>
                    </div>

                </form>

                <button type="submit" form="form_cxc" class="btn btn-secondary" id="registrarBtn">Registrar</button>

                <!-- Mensaje de registro exitoso -->
                <div id="alert-container_cxc"></div>

            </div>

        </div>

    </section>

</div>

<?php

include("../components/footer.php");

?>