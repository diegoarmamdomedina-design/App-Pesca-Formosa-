<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/clima.php";

manejarPreflight();

[$lat, $lng] = coordenadasClima();
$actual = obtenerClimaActual($lat, $lng);

if (!$actual) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo obtener el clima actual"
    ], 502);
}

enviarJson([
    "temp" => $actual["temp"],
    "descripcion" => $actual["descripcion"],
    "viento" => $actual["viento"]
]);
