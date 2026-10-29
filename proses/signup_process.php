<?php

require '../includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama = $_POST["nama"];
    $no_hp = $_POST["no_hp"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $role = "pelanggan";

    try {
        $query = "INSERT INTO users 
                  (nama_user, email, password, role, no_hp)
                  VALUES 
                  (:nama, :email, :password, :role, :no_hp)";

        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ":nama" => $nama,
            ":email" => $email,
            ":password" => $password,
            ":role" => $role,
            ":no_hp" => $no_hp
        ]);

        echo "Pendaftaran berhasil!";

    } catch (PDOException $e) {
        echo "Pendaftaran gagal: " . $e->getMessage();
    }
}
?>