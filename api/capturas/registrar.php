<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/database.php";
require_once "../../config/auth.php";

$usuario = obtenerUsuarioAutenticado();

$datos = json_decode(file_get_contents("php://input"), true);

$id_especie = $datos["id_especie"] ?? null;
$id_lugar = $datos["id_lugar"] ?? null;
$peso_kg = $datos["peso_kg"] ?? null;
$observaciones = $datos["observaciones"] ?? "";

if ($id_especie == null) {
    http_response_code(400);

    echo json_encode([
        "ok" => false,
        "mensaje" => "La especie es obligatoria"
    ]);

    exit;
}

$sql = "INSERT INTO capturas
        (id_usuario, id_especie, id_lugar, peso_kg, observaciones)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiids",
    $usuario->id_usuario,
    $id_especie,
    $id_lugar,
    $peso_kg,
    $observaciones
);

if ($stmt->execute()) {

    http_response_code(201);

    echo json_encode([
        "ok" => true,
        "mensaje" => "Captura registrada correctamente",
        "id_captura" => $stmt->insert_id
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo registrar la captura"
    ]);
}