<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = requiereRol("admin");

enviarJson([
    "ok" => true,
    "mensaje" => "Acceso de administrador autorizado",
    "usuario" => [
        "id_usuario" => $usuario->id_usuario,
        "email" => $usuario->email,
        "rol" => $usuario->rol
    ]
]);
