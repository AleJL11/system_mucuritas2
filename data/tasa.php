<?php
    include("./conexion.php");

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $deuda = $_POST['deuda_bsd'];

        try {
            $conn->begin_transaction();

            $sql = "INSERT INTO tasacambio(tasa, origen) VALUES (?,?)";

            $stmt = $conn->prepare($sql);
            $origen = "Banco Central de Venezuela";
            $stmt->bind_param("ds", $deuda, $origen);

            if ($stmt->execute()) {
                echo 'Tasa de cambio registrada correctamente';
            } else {
                echo 'Error al registrar la tasa de cambio: ' . $stmt->error;
            }

            $conn->commit();

            $stmt->close();
            $conn->close();

        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }

    }