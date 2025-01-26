<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        $conn->begin_transaction();
        $query = $conn->prepare("SELECT
            tipo_vehiculo.id,
            tipo_vehiculo.vehiculo,
            puesto.tipo_puesto,
            tarifas.tarifa,
            tarifas.id AS tarifa_id
            FROM
                tipo_vehiculo 
            INNER JOIN
                puesto ON tipo_vehiculo.id = puesto.id 
            INNER JOIN
                tarifas ON puesto.tipo_puesto = tarifas.tipo
            WHERE
                tipo_vehiculo.personas_id = ?
        ");
        $query->bind_param("i", $user_id);
        $query->execute();
        $result = $query->get_result();
        
        $vehiculos_data = [];
        while ($row = $result->fetch_assoc()) {
            $vehiculos_data[] = $row;
        }

        //var_dump($vehiculos_data);

        echo json_encode($vehiculos_data);

        $conn->close();

    }