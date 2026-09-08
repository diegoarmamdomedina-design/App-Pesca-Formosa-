<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;

$datos = json_decode(file_get_contents("php://input"), true);

$email = $datos["email"] ?? "";
$password = $datos["password"] ?? "";

if ($email == "" || $password == "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Email y contraseña son obligatorios"
    ]);

    exit;
}

$sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $email);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Usuario no encontrado"
    ]);

    exit;
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($password, $usuario["password_hash"])) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Contraseña incorrecta"
    ]);

    exit;
}


/* CREAR TOKEN JWT */

$clave_secreta = "AppPescaFormosa_2026_ClaveSecreta_JWT_123456789";

$ahora = time();

$payload = [
    "iat" => $ahora,
    "exp" => $ahora + 86400,
    "id_usuario" => $usuario["id_usuario"],
    "email" => $usuario["email"],
    "rol" => $usuario["rol"]
];

$token = JWT::encode(
    $payload,
    $clave_secreta,
    "HS256"
);


/* RESPUESTA DEL LOGIN */

echo json_encode([
    "ok" => true,
    "mensaje" => "Inicio de sesión correcto",
    "token" => $token,
    "usuario" => [
        "id_usuario" => $usuario["id_usuario"],
        "nombre" => $usuario["nombre"],
        "email" => $usuario["email"],
        "rol" => $usuario["rol"]
    ]
], JSON_UNESCAPED_UNICODE);