<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (empty($_GET['usuario1']) || empty($_GET['usuario2'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "IDs de usuarios requeridos"]);
    exit;
}

$usuario1 = (int)$_GET['usuario1'];
$usuario2 = (int)$_GET['usuario2'];

$sql = "SELECT m.id, m.remitente_id, m.destinatario_id, m.mensaje, m.fecha_envio, 
               u.usuario as remitente_usuario
        FROM mensajes m
        JOIN usuarios u ON m.remitente_id = u.id
        WHERE (remitente_id = ? AND destinatario_id = ?) 
           OR (remitente_id = ? AND destinatario_id = ?)
        ORDER BY m.fecha_envio ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $usuario1, $usuario2, $usuario2, $usuario1);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

echo json_encode(["success" => true, "mensajes" => $mensajes]);

$stmt->close();
$conn->close();
?>
