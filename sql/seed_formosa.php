<?php

require_once __DIR__ . "/../config/database.php";

function asegurarEspecie(mysqli $conn, $nombre, $cientifico, $protegida, $cupo, $descripcion)
{
    $stmt = $conn->prepare("SELECT id_especie FROM especies WHERE nombre_comun = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if ($fila) {
        return (int) $fila["id_especie"];
    }

    $stmt = $conn->prepare(
        "INSERT INTO especies (nombre_comun, nombre_cientifico, protegida, cupo_por_persona, descripcion)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssiis", $nombre, $cientifico, $protegida, $cupo, $descripcion);
    $stmt->execute();

    return (int) $stmt->insert_id;
}

function asegurarLugar(mysqli $conn, $nombre, $tipo, $tipoPesca, $descripcion, $lat, $lng)
{
    $stmt = $conn->prepare("SELECT id_lugar FROM lugares_pesca WHERE nombre = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if ($fila) {
        return (int) $fila["id_lugar"];
    }

    $created = date("Y-m-d H:i:s");
    $stmt = $conn->prepare(
        "INSERT INTO lugares_pesca (nombre, tipo, tipo_pesca, descripcion, latitud, longitud, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssdds", $nombre, $tipo, $tipoPesca, $descripcion, $lat, $lng, $created);
    $stmt->execute();

    return (int) $stmt->insert_id;
}

function asegurarCamino(mysqli $conn, $nombre, $tipo, $estado, $descripcion, $coords)
{
    $stmt = $conn->prepare("SELECT id_camino FROM caminos WHERE nombre = ? LIMIT 1");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if ($fila) {
        return (int) $fila["id_camino"];
    }

    $actualizado = date("Y-m-d H:i:s");
    $stmt = $conn->prepare(
        "INSERT INTO caminos (nombre, tipo, estado_actual, descripcion, coordenadas_json, actualizado_en)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $nombre, $tipo, $estado, $descripcion, $coords, $actualizado);
    $stmt->execute();

    return (int) $stmt->insert_id;
}

function asegurarUsuario(mysqli $conn, $nombre, $email, $password, $rol)
{
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if ($fila) {
        return (int) $fila["id_usuario"];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $created = date("Y-m-d H:i:s");
    $stmt = $conn->prepare(
        "INSERT INTO usuarios (nombre, email, password_hash, rol, created_at)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $nombre, $email, $hash, $rol, $created);
    $stmt->execute();

    return (int) $stmt->insert_id;
}

$conn->query("ALTER TABLE usuarios MODIFY rol enum('usuario','admin') NOT NULL DEFAULT 'usuario'");
$conn->query("ALTER TABLE usuarios MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE lugares_pesca MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE caminos MODIFY actualizado_en datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE capturas MODIFY fecha_captura datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE reportes MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE valoraciones MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE fotos MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
$conn->query("ALTER TABLE info_util MODIFY created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");

$adminId = asegurarUsuario($conn, "Administrador Pesca", "admin@pescaformosa.local", "Admin1234", "admin");
asegurarUsuario($conn, "Pescador de Prueba", "pescador@pescaformosa.local", "Pescador1234", "usuario");

$dorado = asegurarEspecie($conn, "Dorado", "Salminus brasiliensis", 0, 2, "Especie emblemática del litoral. Cupo de prueba: 2 por persona.");
$surubi = asegurarEspecie($conn, "Surubí", "Pseudoplatystoma corruscans", 0, 1, "Bagre de gran tamaño. Respetar talla mínima.");
$pacu = asegurarEspecie($conn, "Pacú", "Piaractus mesopotamicus", 0, 2, "Frecuente en el Paraguay y riachos.");
$tararira = asegurarEspecie($conn, "Tararira", "Hoplias malabaricus", 0, 5, "Común en lagunas y bañados.");
$boga = asegurarEspecie($conn, "Boga", "Megaleporinus obtusidens", 0, 5, "Pesca de costa y embarcada.");
asegurarEspecie($conn, "Manguruyú", "Zungaro jahu", 1, 0, "Especie protegida. Devolución obligatoria.");

$lagunaOca = asegurarLugar(
    $conn,
    "Laguna Oca",
    "laguna",
    "costa, embarcado",
    "Laguna cercana a la ciudad de Formosa. Acceso frecuente para pesca recreativa.",
    -26.1775,
    -58.1590
);
$rioParaguay = asegurarLugar(
    $conn,
    "Río Paraguay - Puerto Formosa",
    "rio",
    "embarcado, costa",
    "Frente fluvial de la capital. Pesca de dorado, surubí y boga.",
    -26.1849,
    -58.1731
);
$hehe = asegurarLugar(
    $conn,
    "Riacho He-Hé",
    "riacho",
    "costa, señuelos",
    "Riacho del este formoseño. Atención al estado del camino tras lluvias.",
    -26.0220,
    -58.2820
);
$herradura = asegurarLugar(
    $conn,
    "Laguna Herradura",
    "laguna",
    "costa, embarcado",
    "Laguna de Herradura. Pesca de tararira y pacú.",
    -26.4870,
    -58.2820
);
$estrella = asegurarLugar(
    $conn,
    "Bañado La Estrella",
    "bañados",
    "costa",
    "Humedal de gran extensión. Consultar accesos y crecientes antes de salir.",
    -24.5000,
    -60.2000
);

$relaciones = [
    [$lagunaOca, $tararira],
    [$lagunaOca, $boga],
    [$rioParaguay, $dorado],
    [$rioParaguay, $surubi],
    [$rioParaguay, $pacu],
    [$rioParaguay, $boga],
    [$hehe, $dorado],
    [$hehe, $tararira],
    [$herradura, $pacu],
    [$herradura, $tararira],
    [$estrella, $tararira],
    [$estrella, $boga],
];

$stmtRel = $conn->prepare(
    "INSERT IGNORE INTO lugar_especie (id_lugar, id_especie) VALUES (?, ?)"
);
foreach ($relaciones as [$idLugar, $idEspecie]) {
    $stmtRel->bind_param("ii", $idLugar, $idEspecie);
    $stmtRel->execute();
}

asegurarCamino(
    $conn,
    "RN 11 Formosa - Resistencia",
    "nacional",
    "transitable",
    "Ruta nacional de ingreso a la capital. Principal acceso asfaltado.",
    json_encode([[-26.1849, -58.1731], [-27.4510, -58.9860]])
);
asegurarCamino(
    $conn,
    "RP 1 rumbo a Clorinda",
    "provincial",
    "precaucion",
    "Ruta provincial hacia Clorinda. Verificar banquinas con lluvia.",
    json_encode([[-26.1849, -58.1731], [-25.2840, -57.7180]])
);
asegurarCamino(
    $conn,
    "Camino rural Laguna Oca",
    "rural",
    "barro",
    "Acceso vecinal a Laguna Oca. Puede cortarse con lluvias intensas.",
    json_encode([[-26.1849, -58.1731], [-26.1775, -58.1590]])
);

$stmtInfo = $conn->prepare("SELECT id_info FROM info_util WHERE titulo = ? LIMIT 1");
$tituloVeda = "Veda de dorado - consulta provincial";
$stmtInfo->bind_param("s", $tituloVeda);
$stmtInfo->execute();

if ($stmtInfo->get_result()->num_rows === 0) {
    $tipo = "veda";
    $contenido = "Respetar períodos de veda y cupos por especie publicados por la autoridad provincial. El manguruyú está protegido.";
    $desde = date("Y-01-01");
    $hasta = date("Y-12-31");
    $created = date("Y-m-d H:i:s");
    $stmt = $conn->prepare(
        "INSERT INTO info_util (id_admin, tipo, titulo, contenido, vigente_desde, vigente_hasta, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issssss", $adminId, $tipo, $tituloVeda, $contenido, $desde, $hasta, $created);
    $stmt->execute();
}

echo "Seed OK: usuarios de prueba, especies, lugares, caminos e info_util cargados.\n";
