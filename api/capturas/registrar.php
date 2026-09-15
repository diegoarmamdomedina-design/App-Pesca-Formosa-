<?php

require_once __DIR__ . "/../../config/respuesta.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

manejarPreflight();

$usuario = obtenerUsuarioAutenticado();
$datos = leerJson();

$id_especie = $datos["especie_id"] ?? $datos["id_especie"] ?? null;
$id_lugar = $datos["lugar_id"] ?? $datos["id_lugar"] ?? null;
$peso_kg = $datos["peso"] ?? $datos["peso_kg"] ?? null;
$observaciones = (string) ($datos["observaciones"] ?? "");

if ($id_especie === null || $id_especie === "") {
    enviarJson([
        "ok" => false,
        "mensaje" => "La especie es obligatoria"
    ], 400);
}

$id_usuario = (int) $usuario->id_usuario;
$id_especie = (int) $id_especie;
$fecha_captura = date("Y-m-d H:i:s");
$tiene_lugar = !($id_lugar === null || $id_lugar === "");
$tiene_peso = !($peso_kg === null || $peso_kg === "");

if ($tiene_lugar && $tiene_peso) {
    $id_lugar = (int) $id_lugar;
    $peso_kg = (float) $peso_kg;
    $sql = "INSERT INTO capturas
            (id_usuario, id_especie, id_lugar, peso_kg, observaciones, fecha_captura)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidss", $id_usuario, $id_especie, $id_lugar, $peso_kg, $observaciones, $fecha_captura);
} elseif ($tiene_lugar) {
    $id_lugar = (int) $id_lugar;
    $sql = "INSERT INTO capturas
            (id_usuario, id_especie, id_lugar, peso_kg, observaciones, fecha_captura)
            VALUES (?, ?, ?, NULL, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $id_usuario, $id_especie, $id_lugar, $observaciones, $fecha_captura);
} elseif ($tiene_peso) {
    $peso_kg = (float) $peso_kg;
    $sql = "INSERT INTO capturas
            (id_usuario, id_especie, id_lugar, peso_kg, observaciones, fecha_captura)
            VALUES (?, ?, NULL, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidss", $id_usuario, $id_especie, $peso_kg, $observaciones, $fecha_captura);
} else {
    $sql = "INSERT INTO capturas
            (id_usuario, id_especie, id_lugar, peso_kg, observaciones, fecha_captura)
            VALUES (?, ?, NULL, NULL, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $id_usuario, $id_especie, $observaciones, $fecha_captura);
}

if (!$stmt->execute()) {
    enviarJson([
        "ok" => false,
        "mensaje" => "No se pudo registrar la captura"
    ], 500);
}

enviarJson([
    "ok" => true,
    "mensaje" => "Captura registrada correctamente",
    "id" => $stmt->insert_id
], 201);
