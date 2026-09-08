<?php

require_once __DIR__ . "/jwt.php";

function obtenerUsuarioAutenticado()
{
    $headers = getallheaders();

    $authorization = $headers["Authorization"] ?? "";

    if ($authorization == "") {
        http_response_code(401);

        echo json_encode([
            "ok" => false,
            "mensaje" => "Token no enviado"
        ]);

        exit;
    }

    if (!str_starts_with($authorization, "Bearer ")) {
        http_response_code(401);

        echo json_encode([
            "ok" => false,
            "mensaje" => "Formato de token incorrecto"
        ]);

        exit;
    }

    $token = substr($authorization, 7);

    $datos = verificarTokenJWT($token);

    if ($datos === false) {
        http_response_code(401);

        echo json_encode([
            "ok" => false,
            "mensaje" => "Token inválido o vencido"
        ]);

        exit;
    }

    return $datos;
}


function requiereRol($rolRequerido)
{
    $usuario = obtenerUsuarioAutenticado();

    if ($usuario->rol !== $rolRequerido) {
        http_response_code(403);

        echo json_encode([
            "ok" => false,
            "mensaje" => "No tienes permiso para acceder a esta sección"
        ]);

        exit;
    }

    return $usuario;
}