<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function obtenerClaveJwt()
{
    return "AppPescaFormosa_2026_ClaveSecreta_JWT_123456789";
}

function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function crearTokenJWT($usuario)
{
    $header = base64UrlEncode(json_encode(["typ" => "JWT", "alg" => "HS256"]));

    $ahora = time();
    $payload = base64UrlEncode(json_encode([
        "iat" => $ahora,
        "exp" => $ahora + 86400,
        "id_usuario" => (int) $usuario["id_usuario"],
        "email" => $usuario["email"],
        "rol" => $usuario["rol"]
    ]));

    $firma = base64UrlEncode(
        hash_hmac("sha256", $header . "." . $payload, obtenerClaveJwt(), true)
    );

    return $header . "." . $payload . "." . $firma;
}

function verificarTokenJWT($token)
{
    try {
        return JWT::decode(
            $token,
            new Key(obtenerClaveJwt(), "HS256")
        );
    } catch (Exception $e) {
        return false;
    }
}
