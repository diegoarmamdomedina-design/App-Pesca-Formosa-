<?php

require_once __DIR__ . "/jwt.php";
require_once __DIR__ . "/respuesta.php";

function obtenerHeaderAuthorization()
{
    $headers = function_exists("getallheaders") ? getallheaders() : [];

    foreach ($headers as $nombre => $valor) {
        if (strtolower($nombre) === "authorization") {
            return $valor;
        }
    }

    if (!empty($_SERVER["HTTP_AUTHORIZATION"])) {
        return $_SERVER["HTTP_AUTHORIZATION"];
    }

    if (!empty($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
        return $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
    }

    return "";
}

function obtenerUsuarioAutenticado()
{
    $authorization = obtenerHeaderAuthorization();

    if ($authorization === "") {
        enviarJson([
            "ok" => false,
            "mensaje" => "Token no enviado"
        ], 401);
    }

    if (!str_starts_with($authorization, "Bearer ")) {
        enviarJson([
            "ok" => false,
            "mensaje" => "Formato de token incorrecto"
        ], 401);
    }

    $token = substr($authorization, 7);
    $datos = verificarTokenJWT($token);

    if ($datos === false) {
        enviarJson([
            "ok" => false,
            "mensaje" => "Token inválido o vencido"
        ], 401);
    }

    return $datos;
}

function requiereRol($rolRequerido)
{
    $usuario = obtenerUsuarioAutenticado();

    if (($usuario->rol ?? "") !== $rolRequerido) {
        enviarJson([
            "ok" => false,
            "mensaje" => "No tienes permiso para acceder a esta sección"
        ], 403);
    }

    return $usuario;
}
