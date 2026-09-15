<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$admin = requiereRol("admin");
$datos = leerJson();

$tipos = ["reglamentacion", "aviso", "veda", "telefono", "permiso", "fase_lunar"];
$tipo = $datos["tipo"] ?? "";
$titulo = trim($datos["titulo"] ?? "");
$contenido = trim($datos["contenido"] ?? "");
$vigente_desde = $datos["vigente_desde"] ?? null;
$vigente_hasta = $datos["vigente_hasta"] ?? null;

if (!in_array($tipo, $tipos, true) || $titulo === "" || $contenido === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "tipo, titulo y contenido son obligatorios"
    ], 400);
}

$id_admin = (int) $admin->id_usuario;
$created_at = date("Y-m-d H:i:s");

if ($vigente_desde === "") {
    $vigente_desde = null;
}

if ($vigente_hasta === "") {
    $vigente_hasta = null;
}

$sql = "INSERT INTO info_util
        (id_admin, tipo, titulo, contenido, vigente_desde, vigente_hasta, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issssss",
    $id_admin,
    $tipo,
    $titulo,
    $contenido,
    $vigente_desde,
    $vigente_hasta,
    $created_at
);

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo crear la información útil"
    ], 500);
}

enviarJson([
    "ok" => true,
    "mensaje" => "Información útil creada",
    "id" => $stmt->insert_id
], 201);
