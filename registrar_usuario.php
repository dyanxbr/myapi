<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"), true);

// Validación de datos requeridos
$requiredFields = ['nombre', 'apellidoPaterno', 'apellidoMaterno', 'usuario', 'password'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "El campo $field es requerido"
        ]);
        exit;
    }
}

// Escapar y preparar datos
$nombre = $conn->real_escape_string(trim($data['nombre']));
$apellidoP = $conn->real_escape_string(trim($data['apellidoPaterno']));
$apellidoM = $conn->real_escape_string(trim($data['apellidoMaterno']));
$usuario = $conn->real_escape_string(trim($data['usuario']));
$password = password_hash($conn->real_escape_string($data['password']), PASSWORD_BCRYPT);

// Validar longitud máxima según tu tabla
if (strlen($usuario) > 20) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "El usuario no puede exceder 20 caracteres"
    ]);
    exit;
}

// Verificar si el usuario ya existe
$sqlCheck = "SELECT id FROM usuarios WHERE usuario = '$usuario'";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "El nombre de usuario ya está en uso"
    ]);
    exit;
}

// Insertar en la base de datos
$sql = "INSERT INTO usuarios (
    nombre, 
    apellido_paterno, 
    apellido_materno, 
    usuario, 
    password
) VALUES (
    '$nombre', 
    '$apellidoP', 
    '$apellidoM', 
    '$usuario', 
    '$password'
)";

if ($conn->query($sql)) {
    // Obtener el ID del nuevo registro
    $newId = $conn->insert_id;
    
    echo json_encode([
        "success" => true,
        "message" => "Usuario registrado exitosamente",
        "data" => [
            "id" => $newId,
            "usuario" => $usuario,
            "nombre_completo" => "$nombre $apellidoP $apellidoM",
            "fecha_registro" => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al registrar usuario",
        "error" => $conn->error
    ]);
}

$conn->close();
?>
