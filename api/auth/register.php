<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$datos = leerJson();

$nombre = trim($datos["nombre"] ?? "");
$email = trim($datos["email"] ?? "");
$password = $datos["password"] ?? "";

if ($nombre === "" || $email === "" || $password === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "Faltan datos obligatorios"
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El email no es válido"
    ], 400);
}

$sql = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El email ya está registrado"
    ], 400);
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$rol = "usuario";
$created_at = date("Y-m-d H:i:s");

$sql = "INSERT INTO usuarios (nombre, email, password_hash, rol, created_at)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $email, $password_hash, $rol, $created_at);

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo registrar el usuario"
    ], 400);
}

enviarJson([
    "ok" => true,
    "mensaje" => "Usuario registrado correctamente",
    "id_usuario" => $stmt->insert_id
], 201);
