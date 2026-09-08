<?php

header("Content_Type: application/json; charsert=UTF-8");

require_once "../../config/database.php";

$sql = "SELECT * FROM lugares_pesca";

$resultado = $conn->query($sql);

$lugares = [];

while ($fila = $resultado->fetch_assoc()){
    $lugares[] = $fila;
}

echo json_encode($lugares, JSON_UNESCAPED_UNICODE);

?>