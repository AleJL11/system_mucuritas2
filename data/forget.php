<?php

include("./conexion.php");

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = $_POST['email_forget'];
    $password   = $_POST['pass_forget'];

    try {

        $pass_forget = password_hash($password, PASSWORD_BCRYPT);
        $conn->begin_transaction();

        $data = $conn->prepare("UPDATE usuarios SET clave = ? WHERE usuario = ?");
        $data->bind_param("ss", $pass_forget, $email);
        $data->execute();

        $conn->commit();

        $response_forget['success'] = true;
        $response_forget['message'] = 'Cambio de clave exitoso';

        $data->close();

    } catch (Exception $e) {
        $conn->rollback();
    }
}

header('Content-Type: application/json');
echo json_encode($response_forget);
