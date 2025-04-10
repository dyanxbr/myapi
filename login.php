<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['usuario']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Usuario y contraseña requeridos"]);
    exit;
}

$usuario = $conn->real_escape_string($data['usuario']);

$sql = "SELECT id, nombre, apellido_paterno, apellido_materno, usuario, password FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($data['password'], $user['password'])) {
    // Eliminar password antes de devolver los datos
    unset($user['password']);
    echo json_encode(["success" => true, "user" => $user]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
}

$stmt->close();
$conn->close();
?>
