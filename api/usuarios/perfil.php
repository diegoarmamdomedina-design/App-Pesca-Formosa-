<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();

enviarJson([
    "ok" => true,
    "mensaje" => "Acceso autorizado",
    "usuario" => [
        "id_usuario" => $usuario->id_usuario,
        "email" => $usuario->email,
        "rol" => $usuario->rol
    ]
]);
