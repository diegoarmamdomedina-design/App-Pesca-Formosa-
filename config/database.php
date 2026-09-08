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
    die("Error de conexiòn: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>