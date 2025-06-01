<?php

$host = 'localhost';    
$dbname = 'db_cerdas_cermat'; 
$username = 'root';     
$password = '';         

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set mode error PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Opsi fetch default ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

?>
