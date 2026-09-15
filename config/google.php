<?php

$google_client_id = getenv("GOOGLE_CLIENT_ID") ?: "";

if (file_exists(__DIR__ . "/google.local.php")) {
    require __DIR__ . "/google.local.php";
}

function obtenerGoogleClientId()
{
    global $google_client_id;

    return trim((string) $google_client_id);
}

function verificarIdTokenGoogle($idToken)
{
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($idToken);

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

        if ($cuerpo === false || $codigo >= 400) {
            return null;
        }

        $datos = json_decode($cuerpo, true);
        return is_array($datos) ? $datos : null;
    }

    $cuerpo = @file_get_contents($url);
    if ($cuerpo === false) {
        return null;
    }

    $datos = json_decode($cuerpo, true);
    return is_array($datos) ? $datos : null;
}
