<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();
$datos = leerJson();

$entidad_tipo = $datos["entidad_tipo"] ?? "";
$entidad_id = $datos["entidad_id"] ?? null;
$puntaje = $datos["puntaje"] ?? null;
$comentario = $datos["comentario"] ?? "";

if (!in_array($entidad_tipo, ["lugar", "camino"], true) || $entidad_id === null || $puntaje === null) {
    enviarJson([
        "ok" => false,
        "mensaje" => "entidad_tipo, entidad_id y puntaje son obligatorios"
    ], 400);
}

$puntaje = (int) $puntaje;

if ($puntaje < 1 || $puntaje > 5) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El puntaje debe ser un número entre 1 y 5"
    ], 400);
}

$entidad_id = (int) $entidad_id;

if ($entidad_tipo === "lugar") {
    $sql = "SELECT id_lugar FROM lugares_pesca WHERE id_lugar = ? LIMIT 1";
} else {
    $sql = "SELECT id_camino FROM caminos WHERE id_camino = ? LIMIT 1";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $entidad_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "La entidad valorada no existe"
    ], 404);
}

$id_usuario = (int) $usuario->id_usuario;
$created_at = date("Y-m-d H:i:s");

$sql = "INSERT INTO valoraciones
        (id_usuario, entidad_tipo, entidad_id, puntaje, comentario, created_at)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isiiss",
    $id_usuario,
    $entidad_tipo,
    $entidad_id,
    $puntaje,
    $comentario,
    $created_at
);

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo registrar la valoración"
    ], 500);
}

$id_valoracion = $stmt->insert_id;

if ($entidad_tipo === "lugar") {
    $sql = "SELECT ROUND(AVG(puntaje), 1) AS promedio
            FROM valoraciones
            WHERE entidad_tipo = 'lugar' AND entidad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $entidad_id);
    $stmt->execute();
    $promedio = $stmt->get_result()->fetch_assoc()["promedio"] ?? null;

    $sql = "UPDATE lugares_pesca SET puntaje_promedio = ? WHERE id_lugar = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $promedio, $entidad_id);
    $stmt->execute();
}

enviarJson([
    "ok" => true,
    "mensaje" => "Valoración registrada",
    "id" => $id_valoracion
], 201);
