<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Configuración para Clever Cloud
$host = getenv('MYSQL_HOST_ADDON') ?: 'bulcba0sxwx5qfwkuzwq-mysql.services.clever-cloud.com';
$dbname = getenv('MYSQL_DATABASE_ADDON') ?: 'bulcba0sxwx5qfwkuzwq';
$user = getenv('MYSQL_USER_ADDON') ?: 'uxwzyzmfaiubuud6';
$password = getenv('MYSQL_PASSWORD_ADDON') ?: 'ZT624d8ccGvY5OCZ9cJm';
$port = getenv('MYSQL_PORT_ADDON') ?: '3306';

// Conexión con manejo de errores detallado
try {
    $conn = new mysqli($host, $user, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }
    
    // Verificar conexión exitosa
    if (!$conn->ping()) {
        throw new Exception("Error en la conexión a la base de datos");
    }
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos",
        "error_details" => $e->getMessage()
    ]);
    exit;
}
?>
