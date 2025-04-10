<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validación de campos obligatorios
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

// Conexión a la base de datos
$conn = new mysqli(
    getenv('MYSQL_HOST_ADDON'),
    getenv('MYSQL_USER_ADDON'),
    getenv('MYSQL_PASSWORD_ADDON'),
    getenv('MYSQL_DATABASE_ADDON'),
    getenv('MYSQL_PORT_ADDON')
);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos: " . $conn->connect_error
    ]);
    exit;
}

// Escapar y preparar datos
$nombre = $conn->real_escape_string($data['nombre']);
$apellidoP = $conn->real_escape_string($data['apellidoPaterno']);
$apellidoM = $conn->real_escape_string($data['apellidoMaterno']);
$usuario = $conn->real_escape_string($data['usuario']);
$password = password_hash($conn->real_escape_string($data['password']), PASSWORD_BCRYPT);

// Verificar si el usuario ya existe
$sqlCheck = "SELECT id FROM usuarios WHERE usuario = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $usuario);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "El nombre de usuario ya está en uso"
    ]);
    $stmtCheck->close();
    $conn->close();
    exit;
}
$stmtCheck->close();

// Insertar en la base de datos (usando prepared statements)
$sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, usuario, password) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $apellidoP, $apellidoM, $usuario, $password);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "Usuario registrado exitosamente",
        "data" => [
            "id" => $newId,
            "usuario" => $usuario
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al registrar usuario: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
