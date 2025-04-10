<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['remitente_id']) || empty($data['destinatario_id']) || empty($data['mensaje'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$remitente = (int)$data['remitente_id'];
$destinatario = (int)$data['destinatario_id'];
$mensaje = $conn->real_escape_string($data['mensaje']);

$sql = "INSERT INTO mensajes (remitente_id, destinatario_id, mensaje) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $remitente, $destinatario, $mensaje);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message_id" => $stmt->insert_id,
        "fecha_envio" => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al enviar mensaje"]);
}

$stmt->close();
$conn->close();
?>
