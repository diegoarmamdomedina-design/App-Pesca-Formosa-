<?php

$host = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "pesca_formosa";

$conn = new mysqli(
    $host,
    $usuario,
    $contraseña,    
$base_datos
);

if ($conn->connect_error) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión a la base de datos"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn->set_charset("utf8mb4");

?>