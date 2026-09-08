<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../../config/database.php";

$sql = "SELECT * FROM especies";

$resultado = $conn->query($sql);

$especies = [];

while ($fila = $resultado->fetch_assoc()){
    $especies[] = $fila;
}

echo json_encode($especies, JSON_UNESCAPED_UNICODE);

?>