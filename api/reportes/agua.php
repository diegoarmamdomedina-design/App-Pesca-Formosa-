<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();
$datos = leerJson();

$id_lugar = $datos["lugar_id"] ?? $datos["id_lugar"] ?? null;
$nivel = trim($datos["nivel"] ?? $datos["estado"] ?? $datos["estado_reportado"] ?? "");
$descripcion = $datos["descripcion"] ?? "";

if ($id_lugar === null || $id_lugar === "" || $nivel === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "lugar_id y nivel son obligatorios"
    ], 400);
}

$id_lugar = (int) $id_lugar;
$id_usuario = (int) $usuario->id_usuario;
$created_at = date("Y-m-d H:i:s");
$tipo_reporte = "agua";

$sql = "SELECT id_lugar FROM lugares_pesca WHERE id_lugar = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_lugar);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Lugar no encontrado"
    ], 404);
}

$sql = "INSERT INTO reportes
        (id_usuario, tipo_reporte, id_referencia, estado_reportado, descripcion, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isisss",
    $id_usuario,
    $tipo_reporte,
    $id_lugar,
    $nivel,
    $descripcion,
    $created_at
);

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo registrar el reporte"
    ], 500);
}

enviarJson([
    "ok" => true,
    "mensaje" => "Reporte de agua registrado",
    "id" => $stmt->insert_id
], 201);
