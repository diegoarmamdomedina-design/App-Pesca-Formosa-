<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();
$datos = leerJson();

$id_camino = $datos["camino_id"] ?? $datos["id_camino"] ?? null;
$estado = trim($datos["estado"] ?? $datos["estado_reportado"] ?? "");
$descripcion = $datos["descripcion"] ?? "";

if ($id_camino === null || $id_camino === "" || $estado === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "camino_id y estado son obligatorios"
    ], 400);
}

$id_camino = (int) $id_camino;
$id_usuario = (int) $usuario->id_usuario;
$created_at = date("Y-m-d H:i:s");
$tipo_reporte = "camino";

$sql = "SELECT id_camino FROM caminos WHERE id_camino = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_camino);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Camino no encontrado"
    ], 404);
}

$estados_validos = ["transitable", "barro", "cortado", "precaucion"];
if (in_array($estado, $estados_validos, true)) {
    $sql = "UPDATE caminos SET estado_actual = ?, actualizado_en = ? WHERE id_camino = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $estado, $created_at, $id_camino);
    $stmt->execute();
}

$sql = "INSERT INTO reportes
        (id_usuario, tipo_reporte, id_referencia, estado_reportado, descripcion, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isisss",
    $id_usuario,
    $tipo_reporte,
    $id_camino,
    $estado,
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
    "mensaje" => "Reporte de camino registrado",
    "id" => $stmt->insert_id
], 201);
