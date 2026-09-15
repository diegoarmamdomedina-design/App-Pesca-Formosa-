<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/clima.php";

manejarPreflight();

[$lat, $lng] = coordenadasClima();
$pronostico = obtenerPronostico($lat, $lng);

if (!$pronostico) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo obtener el pronóstico"
    ], 502);
}

$salida = [];
foreach ($pronostico as $dia) {
    $salida[] = [
        "dia" => $dia["dia"],
        "temp_min" => $dia["temp_min"],
        "temp_max" => $dia["temp_max"]
    ];
}

enviarJson($salida);
