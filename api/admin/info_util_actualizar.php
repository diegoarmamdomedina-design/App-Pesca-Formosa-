<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

requiereRol("admin");

$id = (int) ($_GET["id"] ?? 0);
$datos = leerJson();

if ($id <= 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "El id es obligatorio"
    ], 400);
}

$sql = "SELECT id_info FROM info_util WHERE id_info = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    enviarJson([
        "ok" => false,
        "mensaje" => "Información útil no encontrada"
    ], 404);
}

$titulo = trim($datos["titulo"] ?? "");
$contenido = trim($datos["contenido"] ?? "");

if ($titulo === "" || $contenido === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "titulo y contenido son obligatorios"
    ], 400);
}

$sql = "UPDATE info_util SET titulo = ?, contenido = ? WHERE id_info = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $titulo, $contenido, $id);

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo actualizar la información útil"
    ], 500);
}

enviarJson([
    "ok" => true,
    "mensaje" => "Información útil actualizada",
    "id" => $id
]);
