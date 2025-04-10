<?php
include 'db.php';

// Obtener datos del cuerpo de la petición
$data = json_decode(file_get_contents("php://input"), true);

// Validar datos recibidos
if (
    !isset($data['nombre']) || 
    !isset($data['apellidoPaterno']) || 
    !isset($data['apellidoMaterno']) ||
    !isset($data['usuario']) ||
    !isset($data['password'])
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$nombre = $conn->real_escape_string($data['nombre']);
$apellidoPaterno = $conn->real_escape_string($data['apellidoPaterno']);
$apellidoMaterno = $conn->real_escape_string($data['apellidoMaterno']);
$usuario = $conn->real_escape_string($data['usuario']);
$password = $conn->real_escape_string($data['password']);

// Verificar si el usuario ya existe
$sqlCheck = "SELECT id FROM usuarios WHERE usuario = '$usuario'";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "El nombre de usuario ya está en uso"
    ]);
    exit;
}

// Insertar nuevo usuario
$sql = "INSERT INTO usuarios (
    nombre, 
    apellido_paterno, 
    apellido_materno, 
    usuario, 
    password
) VALUES (
    '$nombre', 
    '$apellidoPaterno', 
    '$apellidoMaterno', 
    '$usuario', 
    '$password'
)";

if ($conn->query($sql) === TRUE) {
    $usuarioId = $conn->insert_id;
    
    // Obtener el usuario recién creado
    $sqlUsuario = "SELECT * FROM usuarios WHERE id = $usuarioId";
    $resultUsuario = $conn->query($sqlUsuario);
    $usuarioRegistrado = $resultUsuario->fetch_assoc();
    
    echo json_encode([
        "success" => true,
        "message" => "Usuario registrado exitosamente",
        "usuario" => $usuarioRegistrado
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al registrar usuario: " . $conn->error
    ]);
}

$conn->close();
?>