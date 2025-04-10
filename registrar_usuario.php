<?php
require 'db.php';

// Encabezados CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Obtener y validar JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "JSON inválido",
        "error_details" => json_last_error_msg()
    ]);
    exit;
}

// Validar campos obligatorios
$required = ['nombre', 'apellidoPaterno', 'apellidoMaterno', 'usuario', 'password'];
$missing = array_diff($required, array_keys($data));

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Campos requeridos faltantes: " . implode(', ', $missing)
    ]);
    exit;
}

// Procesar datos
try {
    // Validar longitud de usuario
    if (strlen($data['usuario']) > 20) {
        throw new Exception("El usuario no puede exceder 20 caracteres");
    }

    // Preparar statement seguro
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, usuario, password) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }

    // Hash de contraseña
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    
    // Vincular parámetros
    $stmt->bind_param("sssss", 
        $data['nombre'],
        $data['apellidoPaterno'],
        $data['apellidoMaterno'],
        $data['usuario'],
        $passwordHash
    );

    // Ejecutar consulta
    if ($stmt->execute()) {
        // Éxito
       echo json_encode([
        "success" => true,
        "message" => "Usuario registrado exitosamente",
        "data" => [
        "id" => $stmt->insert_id,  // Asegúrate que sea "id" y no "user_id"
        "usuario" => $data['usuario']
            ]
        ]);
    } else {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en el servidor",
        "error_details" => $e->getMessage(),
        "received_data" => $data // Para debugging
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>
