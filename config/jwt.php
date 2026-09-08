<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$clave_secreta = "AppPescaFormosa_2026_ClaveSecreta_JWT_123456789";

function verificarTokenJWT($token)
{
    global $clave_secreta;

    try {
        $datos = JWT::decode(
            $token,
            new Key($clave_secreta, "HS256")
        );

        return $datos;

    } catch (Exception $e) {

        return false;
    }
}