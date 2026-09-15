<?php

require_once __DIR__ . "/../config/respuesta.php";

manejarPreflight();

$uri = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH);
$base = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "/api"));
$ruta = substr($uri, strlen($base));
$ruta = trim($ruta, "/");

$metodo = $_SERVER["REQUEST_METHOD"] ?? "GET";

function despachar($archivo)
{
    require $archivo;
    exit;
}

if ($ruta === "auth/register" && $metodo === "POST") {
    despachar(__DIR__ . "/auth/register.php");
}

if ($ruta === "auth/login" && $metodo === "POST") {
    despachar(__DIR__ . "/auth/login.php");
}

if ($ruta === "auth/forgot-password" && $metodo === "POST") {
    despachar(__DIR__ . "/auth/forgot-password.php");
}

if ($ruta === "auth/google" && $metodo === "POST") {
    despachar(__DIR__ . "/auth/google.php");
}

if ($ruta === "lugares" && $metodo === "GET") {
    despachar(__DIR__ . "/lugares/listar.php");
}

if (preg_match("#^lugares/([0-9]+)$#", $ruta, $m) && $metodo === "GET") {
    $_GET["id"] = $m[1];
    despachar(__DIR__ . "/lugares/detalle.php");
}

if ($ruta === "especies" && $metodo === "GET") {
    despachar(__DIR__ . "/especies/listar.php");
}

if ($ruta === "caminos" && $metodo === "GET") {
    despachar(__DIR__ . "/caminos/listar.php");
}

if (preg_match("#^caminos/([0-9]+)$#", $ruta, $m) && $metodo === "GET") {
    $_GET["id"] = $m[1];
    despachar(__DIR__ . "/caminos/detalle.php");
}

if ($ruta === "capturas" && $metodo === "POST") {
    despachar(__DIR__ . "/capturas/registrar.php");
}

if ($ruta === "capturas/mis" && $metodo === "GET") {
    despachar(__DIR__ . "/capturas/mis.php");
}

if ($ruta === "reportes/camino" && $metodo === "POST") {
    despachar(__DIR__ . "/reportes/camino.php");
}

if ($ruta === "reportes/agua" && $metodo === "POST") {
    despachar(__DIR__ . "/reportes/agua.php");
}

if ($ruta === "valoraciones" && $metodo === "POST") {
    despachar(__DIR__ . "/valoraciones/crear.php");
}

if ($ruta === "admin/info-util" && $metodo === "GET") {
    despachar(__DIR__ . "/admin/info_util_listar.php");
}

if ($ruta === "admin/info-util" && $metodo === "POST") {
    despachar(__DIR__ . "/admin/info_util_crear.php");
}

if (preg_match("#^admin/info-util/([0-9]+)$#", $ruta, $m) && $metodo === "PUT") {
    $_GET["id"] = $m[1];
    despachar(__DIR__ . "/admin/info_util_actualizar.php");
}

if (preg_match("#^admin/info-util/([0-9]+)$#", $ruta, $m) && $metodo === "DELETE") {
    $_GET["id"] = $m[1];
    despachar(__DIR__ . "/admin/info_util_eliminar.php");
}

if ($ruta === "clima/actual" && $metodo === "GET") {
    despachar(__DIR__ . "/clima/actual.php");
}

if ($ruta === "clima/pronostico" && $metodo === "GET") {
    despachar(__DIR__ . "/clima/pronostico.php");
}

if ($ruta === "clima/alertas" && $metodo === "GET") {
    despachar(__DIR__ . "/clima/alertas.php");
}

if ($ruta === "luna/fases" && $metodo === "GET") {
    despachar(__DIR__ . "/luna/fases.php");
}

enviarJson([
    "ok" => false,
    "mensaje" => "Ruta no encontrada"
], 404);
