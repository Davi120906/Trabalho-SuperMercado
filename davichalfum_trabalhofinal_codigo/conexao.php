<?php

$servername = "127.0.0.1";
$username = "a2023951407@teiacoltec.org";
$password = "@Coltec2024";
$dbname = "a2023951407@teiacoltec.org";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}
?>
