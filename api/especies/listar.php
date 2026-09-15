<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";

manejarPreflight();

$sql = "SELECT id_especie, nombre_comun, nombre_cientifico, protegida, cupo_por_persona, descripcion, foto
        FROM especies
        ORDER BY nombre_comun";
$resultado = $conn->query($sql);

$especies = [];

while ($fila = $resultado->fetch_assoc()) {
    $especies[] = [
        "id" => (int) $fila["id_especie"],
        "nombre" => $fila["nombre_comun"],
        "nombre_cientifico" => $fila["nombre_cientifico"],
        "protegida" => (int) $fila["protegida"] === 1,
        "cupo" => $fila["cupo_por_persona"] !== null ? (int) $fila["cupo_por_persona"] : null,
        "descripcion" => $fila["descripcion"],
        "foto" => $fila["foto"]
    ];
}

enviarJson($especies);
