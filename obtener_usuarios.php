<?php
include 'db.php';

$sql = "SELECT id, nombre, apellido_paterno, apellido_materno, usuario FROM usuarios";
$result = $conn->query($sql);

$usuarios = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "usuarios" => $usuarios
]);

$conn->close();
?>