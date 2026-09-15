<?php

function enviarCors()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}

function enviarJson($datos, $codigo = 200)
{
    http_response_code($codigo);
    header("Content-Type: application/json; charset=UTF-8");
    enviarCors();
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function leerJson()
{
    $datos = json_decode(file_get_contents("php://input"), true);

    return is_array($datos) ? $datos : [];
}

function manejarPreflight()
{
    if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS") {
        enviarCors();
        http_response_code(204);
        exit;
    }
}
