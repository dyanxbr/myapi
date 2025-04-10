<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Excluir al usuario actual si se proporciona
$excluirId = isset($_GET['excluir']) ? (int)$_GET['excluir'] : 0;

$sql = "SELECT id, nombre, apellido_paterno, apellido_materno, usuario FROM usuarios";
if ($excluirId > 0) {
    $sql .= " WHERE id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $excluirId);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $row['nombre_completo'] = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
    $usuarios[] = $row;
}

echo json_encode(["success" => true, "usuarios" => $usuarios]);

$stmt->close();
$conn->close();
?>
