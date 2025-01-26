<?php

    $server     = "localhost";
    $username   = "root";
    $password   = "";
    $database   = "estacmucuritas";

    $conn = mysqli_connect($server,$username,$password,$database);

    if ($conn->connect_errno) {
        die("Conexión Fallida" . $conn->connect_errno );
    }

    if (!defined('ENCRYPTION_KEY')) {
        define('ENCRYPTION_KEY', 'mucuritas2_pass_key');
    }
    
    if (!defined('ENCRYPTION_METHOD')) {
        define('ENCRYPTION_METHOD', 'AES-256-CBC');
    }

    //define('ENCRYPTION_KEY', 'mucuritas2_pass_key');
    //define('ENCRYPTION_METHOD', 'AES-256-CBC');

?>