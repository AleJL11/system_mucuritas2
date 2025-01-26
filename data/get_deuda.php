<?php
    include("./conexion.php");

    session_start();

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    session_write_close();

    if (isset($_SESSION['user_data'])) {
        $saldo = $_SESSION['user_data']['saldo'];

        include('../data/conexion.php');

        $sql = "SELECT tasa FROM tasacambio ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $tasa_cambio = $row['tasa'];

            $deuda_bsd = $saldo * $tasa_cambio;
            echo 'Bs ' . number_format($deuda_bsd, 2, '.', ',');
        } else {
            echo 'Bs 0.00';
            $deuda_bsd = 0;
        }

    }