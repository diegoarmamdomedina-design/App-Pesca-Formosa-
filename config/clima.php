<?php

$openweather_api_key = getenv("OPENWEATHER_API_KEY") ?: "";

if (file_exists(__DIR__ . "/clima.local.php")) {
    require __DIR__ . "/clima.local.php";
}

function obtenerClaveOpenWeather()
{
    global $openweather_api_key;

    return trim((string) $openweather_api_key);
}

function httpGetJson($url)
{
    if (function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $cuerpo = curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($cuerpo === false) {
            return [null, 502];
        }

        return [json_decode($cuerpo, true), $codigo];
    }

    $cuerpo = @file_get_contents($url);
    if ($cuerpo === false) {
        return [null, 502];
    }

    return [json_decode($cuerpo, true), 200];
}

function coordenadasClima()
{
    $lat = $_GET["lat"] ?? "-26.1849";
    $lng = $_GET["lng"] ?? "-58.1731";

    return [(float) $lat, (float) $lng];
}

function descripcionWmo($codigo)
{
    $mapa = [
        0 => "despejado",
        1 => "mayormente despejado",
        2 => "parcialmente nublado",
        3 => "nublado",
        45 => "niebla",
        48 => "niebla con rime",
        51 => "llovizna débil",
        53 => "llovizna",
        55 => "llovizna intensa",
        61 => "lluvia débil",
        63 => "lluvia",
        65 => "lluvia intensa",
        80 => "chubascos",
        81 => "chubascos fuertes",
        82 => "chubascos violentos",
        95 => "tormenta",
        96 => "tormenta con granizo",
        99 => "tormenta fuerte con granizo"
    ];

    return $mapa[$codigo] ?? "condiciones variables";
}

function obtenerClimaActual($lat, $lng)
{
    $clave = obtenerClaveOpenWeather();

    if ($clave !== "") {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lng}&units=metric&lang=es&appid=" . urlencode($clave);
        [$datos, $codigo] = httpGetJson($url);
        if ($datos && !empty($datos["weather"][0])) {
            return [
                "temp" => isset($datos["main"]["temp"]) ? round((float) $datos["main"]["temp"], 1) : null,
                "descripcion" => $datos["weather"][0]["description"] ?? "",
                "viento" => isset($datos["wind"]["speed"]) ? round((float) $datos["wind"]["speed"], 1) : null,
                "humedad" => $datos["main"]["humidity"] ?? null,
                "icono" => $datos["weather"][0]["icon"] ?? null,
                "codigo_clima" => (int) ($datos["weather"][0]["id"] ?? 0),
                "fuente" => "openweathermap",
                "lat" => $lat,
                "lng" => $lng
            ];
        }
        if ($codigo >= 400) {
            return null;
        }
    }

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current=temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m&timezone=auto";
    [$datos, $codigo] = httpGetJson($url);
    if (!$datos || empty($datos["current"])) {
        return null;
    }

    $wmo = (int) ($datos["current"]["weather_code"] ?? 0);

    return [
        "temp" => isset($datos["current"]["temperature_2m"]) ? round((float) $datos["current"]["temperature_2m"], 1) : null,
        "descripcion" => descripcionWmo($wmo),
        "viento" => isset($datos["current"]["wind_speed_10m"]) ? round((float) $datos["current"]["wind_speed_10m"], 1) : null,
        "humedad" => $datos["current"]["relative_humidity_2m"] ?? null,
        "icono" => null,
        "codigo_clima" => $wmo,
        "fuente" => "open-meteo",
        "lat" => $lat,
        "lng" => $lng
    ];
}

function obtenerPronostico($lat, $lng)
{
    $clave = obtenerClaveOpenWeather();

    if ($clave !== "") {
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lng}&units=metric&lang=es&appid=" . urlencode($clave);
        [$datos, $codigo] = httpGetJson($url);
        if ($datos && !empty($datos["list"])) {
            $porDia = [];
            foreach ($datos["list"] as $item) {
                $fecha = substr($item["dt_txt"], 0, 10);
                if (!isset($porDia[$fecha])) {
                    $porDia[$fecha] = [
                        "dia" => $fecha,
                        "temp_min" => $item["main"]["temp_min"],
                        "temp_max" => $item["main"]["temp_max"],
                        "descripcion" => $item["weather"][0]["description"] ?? "",
                        "codigo_clima" => (int) ($item["weather"][0]["id"] ?? 0)
                    ];
                    continue;
                }
                $porDia[$fecha]["temp_min"] = min($porDia[$fecha]["temp_min"], $item["main"]["temp_min"]);
                $porDia[$fecha]["temp_max"] = max($porDia[$fecha]["temp_max"], $item["main"]["temp_max"]);
            }

            $salida = [];
            foreach (array_slice(array_values($porDia), 0, 5) as $dia) {
                $salida[] = [
                    "dia" => $dia["dia"],
                    "temp_min" => round((float) $dia["temp_min"], 1),
                    "temp_max" => round((float) $dia["temp_max"], 1),
                    "descripcion" => $dia["descripcion"],
                    "codigo_clima" => $dia["codigo_clima"]
                ];
            }
            return $salida;
        }
    }

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&daily=weather_code,temperature_2m_max,temperature_2m_min&timezone=auto&forecast_days=5";
    [$datos] = httpGetJson($url);
    if (!$datos || empty($datos["daily"]["time"])) {
        return null;
    }

    $salida = [];
    foreach ($datos["daily"]["time"] as $i => $dia) {
        $wmo = (int) ($datos["daily"]["weather_code"][$i] ?? 0);
        $salida[] = [
            "dia" => $dia,
            "temp_min" => round((float) $datos["daily"]["temperature_2m_min"][$i], 1),
            "temp_max" => round((float) $datos["daily"]["temperature_2m_max"][$i], 1),
            "descripcion" => descripcionWmo($wmo),
            "codigo_clima" => $wmo
        ];
    }

    return $salida;
}

function alertasDesdePronostico($pronostico)
{
    $alertas = [];
    $vistos = [];

    foreach ($pronostico as $dia) {
        $codigo = (int) ($dia["codigo_clima"] ?? 0);
        $tipo = null;

        if (($codigo >= 200 && $codigo < 300) || $codigo >= 95) {
            $tipo = "tormenta";
        } elseif (in_array($codigo, [502, 503, 504, 65, 82], true)) {
            $tipo = "creciente";
        } elseif ($codigo >= 700 && $codigo < 800 && $codigo < 90) {
            $tipo = "incendio";
        }

        if ($tipo === null) {
            continue;
        }

        $claveAlerta = $tipo . "|" . ($dia["dia"] ?? "");
        if (isset($vistos[$claveAlerta])) {
            continue;
        }
        $vistos[$claveAlerta] = true;

        $alertas[] = [
            "tipo" => $tipo,
            "descripcion" => ucfirst($dia["descripcion"] ?? "alerta") . " (" . ($dia["dia"] ?? "") . ")"
        ];
    }

    return $alertas;
}
