<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El id del camino es obligatorio"
    ], 400);
}

$sql = "SELECT id_camino, nombre, tipo, estado_actual, descripcion, coordenadas_json, actualizado_en
        FROM caminos
        WHERE id_camino = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$camino = $stmt->get_result()->fetch_assoc();

if (!$camino) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Camino no encontrado"
    ], 404);
}

$sql = "SELECT id_reporte, estado_reportado, descripcion, created_at
        FROM reportes
        WHERE tipo_reporte = 'camino' AND id_referencia = ?
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reportesResultado = $stmt->get_result();

$reportes = [];
while ($fila = $reportesResultado->fetch_assoc()) {
    $reportes[] = [
        "id" => (int) $fila["id_reporte"],
        "estado" => $fila["estado_reportado"],
        "descripcion" => $fila["descripcion"],
        "created_at" => $fila["created_at"]
    ];
}

enviarJson([
    "id" => (int) $camino["id_camino"],
    "nombre" => $camino["nombre"],
    "tipo" => $camino["tipo"],
    "estado" => $camino["estado_actual"],
    "descripcion" => $camino["descripcion"],
    "coordenadas_json" => $camino["coordenadas_json"],
    "actualizado_en" => $camino["actualizado_en"],
    "reportes" => $reportes
]);
