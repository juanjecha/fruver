<?php
$conn = new mysqli('localhost', 'root', '', 'fruverbd');

if ($conn->connect_error) {
    die('ERROR DE CONEXIÓN: ' . $conn->connect_error);
} else {
    echo '✅ Conexión a la base de datos OK';
    $result = $conn->query("SHOW TABLES");
    echo '<br>Tablas encontradas:<br>';
    while($row = $result->fetch_array()) {
        echo '- ' . $row[0] . '<br>';
    }
}