<?php

include("./conexion.php");

session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

//session_write_close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = $_POST['email'];
    $password   = $_POST['pass'];

    try {

        $conn->begin_transaction();

        $data = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $data->bind_param("s", $email);
        $data->execute();
        $result = $data->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $stored_password = $row['clave'];

            if (password_verify($password, $stored_password)) {
                $user_id = $row['id'];
                $user_data = [];

                // Consulta para obtener todos los datos del usuario
                $user_info = $conn->prepare("SELECT
                    personas.id,
                    personas.cedula,
                    personas.nombre_completo,
                    personas.correo,
                    GROUP_CONCAT(DISTINCT puesto.tipo_puesto ORDER BY puesto.tipo_puesto SEPARATOR ', ') AS tipo_puesto,
                    GROUP_CONCAT(DISTINCT puesto.n_puesto ORDER BY puesto.n_puesto SEPARATOR ', ') AS n_puesto,
                    GROUP_CONCAT(DISTINCT cxc.meses ORDER BY cxc.meses SEPARATOR ', ') AS meses,
                    GROUP_CONCAT(DISTINCT cxc.cantidad_meses ORDER BY cxc.cantidad_meses SEPARATOR ', ') AS cantidad_meses,
                    GROUP_CONCAT(DISTINCT cxc.saldo ORDER BY cxc.saldo SEPARATOR ', ') AS saldo,
                    COUNT(DISTINCT CASE WHEN puesto.n_puesto != 'No aplica' THEN puesto.id END) AS total_puestos,
                    usuarios.clave,
                    usuarios.roles_id,
                    cxc.id AS cxc_id
                FROM
                    personas
                LEFT JOIN
                    puesto ON personas.id = puesto.personas_id
                LEFT JOIN
                    cxc ON personas.id = cxc.personas_id
                LEFT JOIN
                    usuarios ON personas.id = usuarios.personas_id
                WHERE
                    personas.id = ?
                GROUP BY
                    personas.id, usuarios.clave, usuarios.roles_id;");
                $user_info->bind_param("i", $user_id);
                $user_info->execute();
                $user_result = $user_info->get_result();
                
                if ($user_result->num_rows === 1) {
                    $user_data = $user_result->fetch_assoc();
                }

                if (isset($_POST['recordar']) && $_POST['recordar'] == 'on') {
                    $ivlen = openssl_cipher_iv_length(ENCRYPTION_METHOD);
                    $iv = openssl_random_pseudo_bytes($ivlen);

                    $encrypted_email = openssl_encrypt($email, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);

                    $cookie_value = base64_encode($encrypted_email . '::' . $iv);
                    setcookie('email', $cookie_value, time() + (86400 * 30), "/");
                }

                $_SESSION['user_data'] = $user_data;

                header("Location: ../pages/dashboard.php");
                exit;
            } else {
                $conn->rollback();
                $_SESSION['error_message'] = 'Credenciales incorrectas';
                header("Location: ../index.php");
            }
        } else {
            $conn->rollback();
            $_SESSION['error_message'] = 'El usuario no existe';
            header("Location: ../index.php");
        }

        $data->close();
        $result->close();
        $conn->commit();
        $conn->close();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }
}
