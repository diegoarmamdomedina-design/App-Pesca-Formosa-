<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/auth.php";

$usuario = obtenerUsuarioAutenticado();

echo json_encode([
    "ok" => true,
    "mensaje" => "Acceso autorizado",
    "usuario" => [
        "id_usuario" => $usuario->id_usuario,
        "email" => $usuario->email,
        "rol" => $usuario->rol
    ]
], JSON_UNESCAPED_UNICODE);