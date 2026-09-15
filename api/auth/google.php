<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/jwt.php";
require_once __DIR__ . "/../../config/google.php";

manejarPreflight();

$clientId = obtenerGoogleClientId();

if ($clientId === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "Falta el Client ID de Google. Cargalo en config/google.local.php"
    ], 503);
}

$datos = leerJson();
$idToken = trim($datos["id_token"] ?? "");

if ($idToken === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "id_token es obligatorio"
    ], 401);
}

$google = verificarIdTokenGoogle($idToken);

if (
    !$google ||
    empty($google["sub"]) ||
    empty($google["email"]) ||
    ($google["aud"] ?? "") !== $clientId
) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Token de Google inválido"
    ], 401);
}

$googleId = $google["sub"];
$email = $google["email"];
$nombre = trim($google["name"] ?? $google["email"]);
$foto = $google["picture"] ?? "";

$sql = "SELECT * FROM usuarios WHERE google_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $googleId);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();

    if ($usuario) {
        $sql = "UPDATE usuarios SET google_id = ?, foto_perfil = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $googleId, $foto, $usuario["id_usuario"]);
        $stmt->execute();
        $usuario["google_id"] = $googleId;
        $usuario["foto_perfil"] = $foto;
    }
}

if (!$usuario) {
    $rol = "usuario";
    $sql = "INSERT INTO usuarios (nombre, email, password_hash, google_id, foto_perfil, rol)
            VALUES (?, ?, NULL, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $email, $googleId, $foto, $rol);

    if (!$stmt->execute()) {
        enviarJson([
            "ok" => false,
            "mensaje" => "Token de Google inválido"
        ], 401);
    }

    $idNuevo = $stmt->insert_id;
    $sql = "SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idNuevo);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
}

$token = crearTokenJWT($usuario);

enviarJson([
    "ok" => true,
    "mensaje" => "Inicio de sesión correcto",
    "token" => $token,
    "usuario" => [
        "id_usuario" => (int) $usuario["id_usuario"],
        "nombre" => $usuario["nombre"],
        "email" => $usuario["email"],
        "rol" => $usuario["rol"]
    ]
]);
