<?php

require_once __DIR__ . "/../../config/respuesta.php";

manejarPreflight();

$mes = (int) ($_GET["mes"] ?? date("n"));
$anio = (int) ($_GET["anio"] ?? date("Y"));

if ($mes < 1 || $mes > 12 || $anio < 2000 || $anio > 2100) {
    enviarJson([
        "ok" => false,
        "mensaje" => "mes y anio no son válidos"
    ], 400);
}

function faseLunarNombre($edad)
{
    if ($edad < 1.84566) {
        return ["Luna nueva", "luna_nueva"];
    }
    if ($edad < 5.53699) {
        return ["Creciente cóncava", "creciente_concava"];
    }
    if ($edad < 9.22831) {
        return ["Cuarto creciente", "cuarto_creciente"];
    }
    if ($edad < 12.91963) {
        return ["Creciente gibosa", "creciente_gibosa"];
    }
    if ($edad < 16.61096) {
        return ["Luna llena", "luna_llena"];
    }
    if ($edad < 20.30228) {
        return ["Menguante gibosa", "menguante_gibosa"];
    }
    if ($edad < 23.99361) {
        return ["Cuarto menguante", "cuarto_menguante"];
    }
    if ($edad < 27.68493) {
        return ["Menguante cóncava", "menguante_concava"];
    }

    return ["Luna nueva", "luna_nueva"];
}

function edadLunar($anio, $mes, $dia)
{
    if ($mes < 3) {
        $anio--;
        $mes += 12;
    }

    $a = (int) ($anio / 100);
    $b = 2 - $a + (int) ($a / 4);
    $jd = (int) (365.25 * ($anio + 4716)) + (int) (30.6001 * ($mes + 1)) + $dia + $b - 1524.5;
    $dias = $jd - 2451550.1;
    $edad = fmod($dias, 29.53058867);

    if ($edad < 0) {
        $edad += 29.53058867;
    }

    return $edad;
}

$diasMes = (int) date("t", strtotime(sprintf("%04d-%02d-01", $anio, $mes)));
$fases = [];

for ($dia = 1; $dia <= $diasMes; $dia++) {
    [$fase, $imagen] = faseLunarNombre(edadLunar($anio, $mes, $dia));
    $fases[] = [
        "fecha" => sprintf("%04d-%02d-%02d", $anio, $mes, $dia),
        "fase" => $fase,
        "imagen" => $imagen
    ];
}

enviarJson($fases);
