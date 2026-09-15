<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/clima.php";

manejarPreflight();

[$lat, $lng] = coordenadasClima();
$pronostico = obtenerPronostico($lat, $lng);

if (!$pronostico) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudieron obtener las alertas"
    ], 502);
}

enviarJson(alertasDesdePronostico($pronostico));
