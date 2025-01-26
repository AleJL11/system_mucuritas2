<?php

include("../components/header.php");
include("../components/sidebar.php");

?>

<div class="home_content">

    <div class="text">REPORTAR PROBLEMAS</div>

    <section class="table_reports">

        <div class="report">

            <h5>Por favor llene el formulario para informar sobre su problema:</h5>

            <div class="container_form_report">

                <div class="form_report">

                    <form action="" id="form_report" class="formulario_reporte">

                        <div class="input-box">
                            <span class="icon"><i class="las la-user"></i></span>
                            <input type="text" id="name_report" name="name_report">
                            <label for="name_report">Nombre</label>
                        </div>

                        <div class="input-box">
                            <span class="icon"><i class="fa-regular fa-address-card"></i></span>
                            <input type="text" id="tlf_report" name="tlf_report">
                            <label for="tlf_report">Número de teléfono</label>
                        </div>

                        <div class="input-box">
                            <input type="file" id="file_report" name="file_report" class="custom-file-input">
                            <label for="file_report" class="custom-file-label">Imagen del problema</label>
                        </div>

                        <div class="input-box">
                            <textarea id="detail_report" name="detail_report"></textarea>
                            <label for="detail_report">Detalle del problema</label>
                        </div>
                    
                    </form>

                    <button type="submit" form="form_report" class="btn btn-secondary" id="registrarBtn_report">Enviar</button>

                    <div id="alert-container_report"></div>
                
                </div>

            </div>

        </div>

    </section>

</div>

<?php

include("../components/footer.php");

?>