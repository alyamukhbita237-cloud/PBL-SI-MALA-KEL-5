<?php

require '../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $query = "SELECT * FROM users 
                  WHERE email = :email 
                  AND password = :password";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ":email" => $email,
            ":password" => $password
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "Login berhasil!";
        } else {
            echo "Email atau password salah!";
        }

    } catch (PDOException $e) {
        echo "Login gagal: " . $e->getMessage();
    }
}
?>