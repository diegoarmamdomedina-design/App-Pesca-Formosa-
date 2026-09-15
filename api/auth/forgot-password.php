<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$datos = leerJson();
$email = trim($datos["email"] ?? "");

if ($email === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "El email es obligatorio"
    ], 400);
}

$sql = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se encontró un usuario con ese email"
    ], 404);
}

$token = bin2hex(random_bytes(32));

$sql = "UPDATE usuarios SET token_reset = ? WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $token, $email);
$stmt->execute();

enviarJson([
    "ok" => true,
    "mensaje" => "Si el correo está registrado, se generó un token de recuperación. En local se devuelve para probarlo en Thunder.",
    "token" => $token
]);
