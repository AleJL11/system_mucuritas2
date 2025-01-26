<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if (isset($_GET['id']) && isset($_GET['mes'])) {
        $mes = $_GET['mes'];
        $id = $_GET['id'];
        
        //echo "Mes: $mes, ID: $id<br>";

        // Consulta para obtener las tarifas
        $stmt = $conn->prepare("SELECT
                detalle.monto,
                tarifas.tipo,
                detalle.id
            FROM cxc_detalle AS detalle
            INNER JOIN
                tarifas ON detalle.tarifa_id = tarifas.id
            WHERE detalle.mes = ?
            AND detalle.cxc_id = ?
            AND detalle.fecha_pago IS NULL;
        ");
        $stmt->bind_param("si", $mes, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Genera el select de tarifas
        echo '<label for="tarifas">Selecciona una tarifa:</label>';
        echo '<select id="tarifas" name="tarifas[]" class="meses_dashboard" multiple>';

        // Revisa si hay resultados antes de iterar
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                //var_dump($row);
                echo '<option value="' . $row['monto'] . '">' . $row['tipo'] . ' - ' . $row['monto'] . '</option>';
            }
        } else {
            echo '<option value="">No se encontraron tarifas</option>';
        }
    
        echo '</select>';

        $stmt->close();
        $conn->close();

        //var_dump($result);
    }