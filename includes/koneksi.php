<?php

$host = "aws-0-ap-southeast-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.ujewjbhnyrfbzwyykhay";
$password = "SI-MALA123#";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Koneksi database gagal. Silakan coba beberapa saat lagi.");
}