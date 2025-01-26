<?php
    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = htmlspecialchars($_POST['name_report']);
        $tlf    = htmlspecialchars($_POST['tlf_report']);
        $detail = htmlspecialchars($_POST['detail_report']);

        if (isset($_FILES['file_report'])) {
            $img = $_FILES['file_report']['tmp_name'];
            $nombreImg = basename($_FILES['file_report']['name']);

            $rutaDestino = "../img_problems/" . $nombreImg;
            if (!move_uploaded_file($img, $rutaDestino)) {
                echo json_encode(array("message" => "Error al subir la imagen"));
                exit;
            }
        } else {
            $rutaDestino = null;
        }

        try {
            //Enviar datos a pyhton para envio mensaje de problema
            $param1 = escapeshellarg($nombre);
            $param2 = escapeshellarg($tlf);
            $param3 = escapeshellarg($rutaDestino);
            $param4 = escapeshellarg($detail);

            $resultado = exec("python ../python/report.py $param1 $param2 $param3 $param4");

            $response = array(
                "nombre" => $nombre,
                "tlf" => $tlf,
                "img" => $img,
                "detail" => $detail,
                "message" => $resultado
            );
            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array("message" => "Error: " . $e->getMessage()));
        }

    } else {
        echo json_encode(array("message" => "Datos no recibidos"));
    }