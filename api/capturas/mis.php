<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();
$id_usuario = (int) $usuario->id_usuario;

$sql = "SELECT c.id_captura, e.nombre_comun, c.peso_kg, c.fecha_captura, c.foto, c.observaciones,
               l.nombre AS lugar
        FROM capturas c
        INNER JOIN especies e ON e.id_especie = c.id_especie
        LEFT JOIN lugares_pesca l ON l.id_lugar = c.id_lugar
        WHERE c.id_usuario = ?
        ORDER BY c.fecha_captura DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$capturas = [];

while ($fila = $resultado->fetch_assoc()) {
    $capturas[] = [
        "id" => (int) $fila["id_captura"],
        "especie" => $fila["nombre_comun"],
        "peso" => $fila["peso_kg"] !== null ? (float) $fila["peso_kg"] : null,
        "fecha" => $fila["fecha_captura"],
        "foto" => $fila["foto"],
        "lugar" => $fila["lugar"],
        "observaciones" => $fila["observaciones"]
    ];
}

enviarJson($capturas);
