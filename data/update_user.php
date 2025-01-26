<?php
    include("./conexion.php");

    session_start();

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    session_write_close();

    $response = [
        'success' => false,
        'message' => 'Error desconocido.'
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $ci = $_POST['ci_config'];
        $nombre = $_POST['name_config'];
        $email = $_POST['email_config'];
        $pass = $_POST['pass_config'];

        $id = $_SESSION['user_data']['id'];

        $conn->begin_transaction();

        try {

            $sql1 = "UPDATE personas SET nombre_completo = ?, correo = ?, cedula = ? WHERE id = ?";
            $stmt = $conn->prepare($sql1);
            $stmt->bind_param("sssi", $nombre, $email, $ci, $id);
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar los datos personales');
            }

            // Actualizar contraseña
            if ($pass) {
                $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
                $sql2 = "UPDATE usuarios SET usuario = ?, clave = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ssi", $email, $hashedPass, $id);
                if (!$stmt2->execute()) {
                    throw new Exception('Error al actualizar la contraseña');
                }
            }

            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Datos actualizados exitosamente.';

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = $e->getMessage();
        } finally {
            // Cerrar las conexiones y liberar recursos
            if (isset($stmt)) $stmt->close();
            if (isset($stmt2)) $stmt2->close();
            $conn->close();

            echo json_encode($response);
        }

    }