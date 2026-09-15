<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$sql = "SELECT id_lugar, nombre, tipo, tipo_pesca, descripcion, latitud, longitud, puntaje_promedio
        FROM lugares_pesca
        ORDER BY nombre";
$resultado = $conn->query($sql);

$lugares = [];

while ($fila = $resultado->fetch_assoc()) {
    $lugares[] = [
        "id" => (int) $fila["id_lugar"],
        "nombre" => $fila["nombre"],
        "tipo" => $fila["tipo"],
        "tipo_pesca" => $fila["tipo_pesca"],
        "descripcion" => $fila["descripcion"],
        "lat" => (float) $fila["latitud"],
        "lng" => (float) $fila["longitud"],
        "puntaje_promedio" => $fila["puntaje_promedio"] !== null ? (float) $fila["puntaje_promedio"] : null
    ];
}

enviarJson($lugares);
