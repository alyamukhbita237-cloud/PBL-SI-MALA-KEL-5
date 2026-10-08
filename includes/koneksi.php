<?php

$host = "aws-0-ap-southeast-1.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";
$user = "postgres.ujewjbhnyrfbzwyykhay";
$password = "SI-MALA123#";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Koneksi database berhasil!";
} catch (PDOException $e) {
    echo "Koneksi database gagal: " . $e->getMessage();
}