<?php

include("./conexion.php");

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// MODAL CREATE
$ci_modal             = $_POST["ci"];
$name_modal           = $_POST["name"];
/*$tlf_modal            = $_POST["nroTel"];
$tVehiculo_modal      = $_POST["tVehiculo"];
$tPuesto_modal        = $_POST["tPuesto"];
$nPuesto_modal        = $_POST["nPuesto"];*/
$email_modal          = $_POST["email_modal"];
$pass_modal           = $_POST["pass_modal"];
$vehiculos            = json_decode($_POST["vehiculos"], true);

try {

    $pass_modal = password_hash($pass_modal, PASSWORD_BCRYPT);
    $conn->begin_transaction();

    $con1 = $conn->prepare("INSERT INTO personas(cedula, nombre_completo, correo) VALUES (?,?,?)");
    $con1->bind_param("sss", $ci_modal, $name_modal, $email_modal);
    $con1->execute();

    if ($con1) {

        $id = $con1->insert_id;

        // Insertar múltiples registros de vehículos y puestos
        foreach ($vehiculos as $vehiculo) {
            $tVehiculo_modal = $vehiculo['tVehiculo'];
            $tPuesto_modal   = $vehiculo['tPuesto'];
            $nPuesto_modal   = $vehiculo['nPuesto'];

            $con2 = $conn->prepare("INSERT INTO tipo_vehiculo(vehiculo, personas_id) VALUES (?, ?)");
            $con2->bind_param("ss", $tVehiculo_modal, $id);
            $con2->execute();
            $id_tVehiculo = $con2->insert_id;

            $con3 = $conn->prepare("INSERT INTO puesto(tipo_puesto, n_puesto, personas_id, tipo_vehiculo_id) VALUES (?, ?, ?, ?)");
            $con3->bind_param("ssss", $tPuesto_modal, $nPuesto_modal, $id, $id_tVehiculo);
            $con3->execute();
            $id_tPuesto = $con3->insert_id;
        }

        // Insertar en la tabla usuarios
        $con4 = $conn->prepare("INSERT INTO usuarios(usuario, clave, roles_id, personas_id, tVehiculo_id, tPuesto_id) VALUES (?, ?, 2, ?, ?, ?)");
        $con4->bind_param("sssss", $email_modal, $pass_modal, $id, $id_tVehiculo, $id_tPuesto);
        $con4->execute();

        // Insertar en la tabla cxc
        $con5 = $conn->prepare("INSERT INTO cxc(nombre_completo, cantidad_puesto, tPuesto_id, personas_id) VALUES (?, (SELECT COUNT(*) FROM puesto WHERE personas_id = ?), ?, ?)");
        $con5->bind_param("ssss", $name_modal, $id, $id_tPuesto, $id);
        $con5->execute();

        $conn->commit();

        //Enviar datos a pyhton para envios de confirmación
        $param1 = escapeshellarg($email_modal);
        $param2 = escapeshellarg($_POST["pass_modal"]);
        $param3 = escapeshellarg($name_modal);
        //$param4 = escapeshellarg($tlf_modal);

        $resultado = exec("python ../python/data.py $param1 $param2 $param3");
        $resultado2 = shell_exec("python ../python/msgWs.py $param1 $param2 $param3");

        $response = [];

        $correo_exito = $resultado == "Correo enviado exitosamente";
        $whatsapp_exito = strpos($resultado2, "Mensaje de WhatsApp enviado exitosamente") !== false;
        
        if ($correo_exito && $whatsapp_exito) {
            $response['success'] = true;
            $response['message'] = 'Registro exitoso. Se han enviado un correo electrónico y un mensaje de WhatsApp con los datos de inicio de sesión.';
        } elseif ($correo_exito && !$whatsapp_exito) {
            $response['success'] = true;
            $response['message'] = 'Registro exitoso. Se ha enviado un correo electrónico con los datos de inicio de sesión, pero hubo un problema al enviar el mensaje de WhatsApp. Error: ' . $resultado2;
        } elseif (!$correo_exito && $whatsapp_exito) {
            $response['success'] = true;
            $response['message'] = 'Registro exitoso. Se ha enviado un mensaje de WhatsApp con los datos de inicio de sesión, pero hubo un problema al enviar el correo electrónico.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Registro exitoso, pero hubo problemas al enviar tanto el correo electrónico como el mensaje de WhatsApp. Error: ' . $resultado2;
        }

    } else {
        $conn->rollback();
        $response['success'] = false;
        $response['message'] = "Error al insertar los datos.";
        //echo "Error al insertar los datos en las tablas: " . $e->getMessage();
    }

    $con1->close();
    $con2->close();
    $con3->close();
    $con4->close();
    $con5->close();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error al insertar los datos en las tablas: " . $e->getMessage();
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);