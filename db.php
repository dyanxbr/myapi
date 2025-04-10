<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = 'bulcba0sxwx5qfwkuzwq-mysql.services.clever-cloud.com';
$user = 'uxwzyzmfaiubuud6';
$password = 'ZT624d8ccGvY5OCZ9cJm';
$database = 'bulcba0sxwx5qfwkuzwq';
$port = 3306;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Conexión fallida: " . $conn->connect_error
    ]));
}
?>