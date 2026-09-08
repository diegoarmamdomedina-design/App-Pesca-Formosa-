<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/auth.php";

$usuario = requiereRol("admin");

echo json_encode([
    "ok" => true,
    "mensaje" => "Acceso de administrador autorizado",
    "usuario" => [
        "id_usuario" => $usuario->id_usuario,
        "email" => $usuario->email,
        "rol" => $usuario->rol
    ]
], JSON_UNESCAPED_UNICODE);