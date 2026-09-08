<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/database.php";

$datos = json_decode(file_get_contents("php://input"), true);

$nombre = $datos["nombre"] ?? "";
$email = $datos["email"] ?? "";
$password = $datos["password"] ?? "";

if ($nombre == "" || $email == "" || $password == "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Faltan datos obligatorios"
    ]);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, email, password_hash)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $nombre,
    $email,
    $password_hash
);

if ($stmt->execute()) {

    echo json_encode([
        "ok" => true,
        "mensaje" => "Usuario registrado correctamente"
    ]);

} else {

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo registrar el usuario"
    ]);
}