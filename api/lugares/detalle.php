<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El id del lugar es obligatorio"
    ], 400);
}

$sql = "SELECT id_lugar, nombre, tipo, tipo_pesca, descripcion, latitud, longitud, puntaje_promedio
        FROM lugares_pesca
        WHERE id_lugar = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$lugar = $stmt->get_result()->fetch_assoc();

if (!$lugar) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Lugar no encontrado"
    ], 404);
}

$sql = "SELECT e.id_especie, e.nombre_comun, e.nombre_cientifico, e.protegida, e.cupo_por_persona
        FROM lugar_especie le
        INNER JOIN especies e ON e.id_especie = le.id_especie
        WHERE le.id_lugar = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$especiesResultado = $stmt->get_result();

$especies = [];
while ($fila = $especiesResultado->fetch_assoc()) {
    $especies[] = [
        "id" => (int) $fila["id_especie"],
        "nombre" => $fila["nombre_comun"],
        "nombre_cientifico" => $fila["nombre_cientifico"],
        "protegida" => (int) $fila["protegida"] === 1,
        "cupo" => $fila["cupo_por_persona"] !== null ? (int) $fila["cupo_por_persona"] : null
    ];
}

$sql = "SELECT id_foto, url, created_at
        FROM fotos
        WHERE tipo_foto = 'lugar' AND referencia_id = ?
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$fotosResultado = $stmt->get_result();

$fotos = [];
while ($fila = $fotosResultado->fetch_assoc()) {
    $fotos[] = [
        "id" => (int) $fila["id_foto"],
        "url" => $fila["url"],
        "created_at" => $fila["created_at"]
    ];
}

enviarJson([
    "id" => (int) $lugar["id_lugar"],
    "nombre" => $lugar["nombre"],
    "tipo" => $lugar["tipo"],
    "tipo_pesca" => $lugar["tipo_pesca"],
    "descripcion" => $lugar["descripcion"],
    "lat" => (float) $lugar["latitud"],
    "lng" => (float) $lugar["longitud"],
    "puntaje_promedio" => $lugar["puntaje_promedio"] !== null ? (float) $lugar["puntaje_promedio"] : null,
    "especies" => $especies,
    "fotos" => $fotos
]);
