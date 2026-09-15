<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

requiereRol("admin");

$sql = "SELECT id_info, id_admin, tipo, titulo, contenido, vigente_desde, vigente_hasta, created_at
        FROM info_util
        ORDER BY created_at DESC";
$resultado = $conn->query($sql);

$items = [];

while ($fila = $resultado->fetch_assoc()) {
    $items[] = [
        "id" => (int) $fila["id_info"],
        "tipo" => $fila["tipo"],
        "titulo" => $fila["titulo"],
        "contenido" => $fila["contenido"],
        "vigente_desde" => $fila["vigente_desde"],
        "vigente_hasta" => $fila["vigente_hasta"],
        "created_at" => $fila["created_at"]
    ];
}

enviarJson($items);
