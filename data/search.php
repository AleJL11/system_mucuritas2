<?php

include("./conexion.php");

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$query = $_GET['query'];

$stmt = $conn->prepare("SELECT
                        id,
                        nombre_completo,
                        cedula,
                        correo
                        FROM
                            personas
                        WHERE
                            nombre_completo
                        LIKE ?
                        OR cedula
                        LIKE ?");
$searchQuery = "%$query%";
$stmt->bind_param("ss", $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

$searchResults = array();
while ($row = $result->fetch_assoc()) {
    $searchResults[] = $row;
}

header('Content-Type: application/json');
echo json_encode($searchResults);

$stmt->close();
$conn->close();