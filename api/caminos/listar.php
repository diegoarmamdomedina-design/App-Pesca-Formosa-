<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/database.php";

$sql = "SELECT * FROM caminos";

$resultado = $conn->query($sql);

$caminos = [];

while ($fila = $resultado->fetch_assoc()){
    $caminos[] = $fila;
}   

echo json_encode($caminos, JSON_UNESCAPED_UNICODE);

?>;