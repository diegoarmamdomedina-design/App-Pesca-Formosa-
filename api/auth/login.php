<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/jwt.php";

manejarPreflight();

$datos = leerJson();

$email = trim($datos["email"] ?? "");
$password = $datos["password"] ?? "";

if ($email === "" || $password === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "Email o contraseña incorrectos"
    ], 401);
}

$sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Email o contraseña incorrectos"
    ], 401);
}

$usuario = $resultado->fetch_assoc();

if (
    empty($usuario["password_hash"]) ||
    !password_verify($password, $usuario["password_hash"])
) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Email o contraseña incorrectos"
    ], 401);
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
