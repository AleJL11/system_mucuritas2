<?php

    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
    
        $conn->begin_transaction();
        $user_info = $conn->prepare("SELECT id,
            nombre_completo,
            cedula,
            correo
        FROM personas
        WHERE id = ?;");
        /*$user_info = $conn->prepare("SELECT personas.id,
            personas.nombre_completo,
            personas.cedula,
            personas.correo,
            GROUP_CONCAT(puesto.n_puesto ORDER BY puesto.n_puesto SEPARATOR ', ') AS n_puesto
        FROM personas
        INNER JOIN
            puesto ON personas.id = puesto.personas_id
        WHERE
            personas.id = ?;");*/
        $user_info->bind_param("i", $user_id);
        $user_info->execute();
        $result = $user_info->get_result();
    
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            echo json_encode($user_data);
        } else {
            echo json_encode(['error' => 'No se encontró el usuario']);
        }

        $user_info->close();

        $conn->commit();
    }
    
    $conn->close();
