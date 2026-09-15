<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$sql = "SELECT id_camino, nombre, tipo, estado_actual, descripcion, coordenadas_json, actualizado_en
        FROM caminos
        ORDER BY nombre";
$resultado = $conn->query($sql);

$caminos = [];

while ($fila = $resultado->fetch_assoc()) {
    $caminos[] = [
        "id" => (int) $fila["id_camino"],
        "nombre" => $fila["nombre"],
        "tipo" => $fila["tipo"],
        "estado" => $fila["estado_actual"],
        "descripcion" => $fila["descripcion"],
        "coordenadas_json" => $fila["coordenadas_json"],
        "actualizado_en" => $fila["actualizado_en"]
    ];
}

enviarJson($caminos);
