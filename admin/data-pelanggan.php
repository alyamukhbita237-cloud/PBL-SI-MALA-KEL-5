<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

// ===== PROSES TAMBAH PELANGGAN =====

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pelanggan'])) {

    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validasi data wajib
    if ($nama === '' || $no_hp === '' || $email === '') {
        die('Nama, nomor WhatsApp, dan email wajib diisi.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Format email tidak valid.');
    }

    // Membersihkan nomor WhatsApp
    $nomorWA = preg_replace('/[^0-9]/', '', $no_hp);

    // Mengubah 08 menjadi 628
    if (substr($nomorWA, 0, 1) === '0') {
        $nomorWA = '62' . substr($nomorWA, 1);
    }

    // Validasi nomor WhatsApp
    if (strlen($nomorWA) < 10 || strlen($nomorWA) > 15) {
        die('Nomor WhatsApp tidak valid.');
    }

    // Membuat password sementara
    $passwordSementara = 'Mala' . random_int(1000, 9999);

    // Mengubah password menjadi hash
    $passwordHash = password_hash(
        $passwordSementara,
        PASSWORD_DEFAULT
    );

    try {
        // Memeriksa apakah email sudah terdaftar
        $cekEmail = $pdo->prepare("SELECT id_user FROM users WHERE email = :email");

        $cekEmail->execute([':email' => $email]);

        if ($cekEmail->fetch()) {
            die('Email sudah terdaftar. Gunakan email lain.');
        }

        // Menyimpan pelanggan ke database
        $sql = "INSERT INTO users (nama_user, email, password, role, no_hp, alamat)
                VALUES (:nama, :email, :password, 'Pelanggan', :no_hp, :alamat)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nama' => $nama,
            ':email' => $email,
            ':password' => $passwordHash,
            ':no_hp' => $no_hp,
            ':alamat' => $alamat
        ]);

        // Membuat pesan WhatsApp
        $pesan = "Halo {$nama},\n\n"
            . "Akun SI-MALA Laundry kamu berhasil dibuat.\n\n"
            . "Email: {$email}\n"
            . "Password sementara: {$passwordSementara}\n\n"
            . "Silakan login ke SI-MALA dan segera ganti password sementara kamu.\n\n"
            . "Terima kasih telah menggunakan layanan SI-MALA Laundry.";

        // Membuka WhatsApp dengan pesan
        $linkWhatsApp = 'https://wa.me/'
            . $nomorWA
            . '?text='
            . urlencode($pesan);

        header('Location: ' . $linkWhatsApp);
        exit;

    } catch (PDOException $e) {
        die('Error database: ' . $e->getMessage());
    }
}

// === tampil data pelanggan ===== 


$nama = $_SESSION['nama_user'] ?? 'Admin';
$page_title = "Data Pelanggan";
$active_page = "data-pelanggan";

$sql = "SELECT id_user, nama_user, no_hp, email, alamat
        FROM public.users
        WHERE role = 'Pelanggan'
        ORDER BY id_user ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$pelanggan = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<main class="main-content halaman-pelanggan">

    <!-- Judul halaman -->
    <section class="judul-halaman">
        <div>
            <h1>Data Pelanggan</h1>
            <p>Pantau data pelanggan laundry</p>
        </div>

        <button type="button" class="btn-tambah-pelanggan" id="bukaModalPelanggan">
            <span>+</span>
            Tambah Pelanggan
        </button>
    </section>

    <!-- Tabel pelanggan -->
    <section class="kartu-tabel-pelanggan">

        <!-- Pencarian -->
        <div class="pencarian-pelanggan">
            <input type="text" id="cariPelanggan" placeholder="Cari pelanggan...">
        </div>

        <div class="tabel-responsive">
            <table class="tabel-pelanggan" id="tabelPelanggan">
                <thead>
                    <tr>
                        <th>ID Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>No HP</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Edit</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($pelanggan as $data) : ?>
                        <tr>
                            <td><?= 'P' . str_pad($data['id_user'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($data['nama_user'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($data['no_hp'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($data['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($data['alamat'] ?? '-') ?></td>
                            <td>
                                <button
                                type="button"
                                class="btn-edit-pelanggan"
                                data-id="<?= htmlspecialchars($data['id_user']) ?>"
                                data-nama="<?= htmlspecialchars($data['nama_user'] ?? '', ENT_QUOTES) ?>"
                                data-nohp="<?= htmlspecialchars($data['no_hp'] ?? '', ENT_QUOTES) ?>"
                                data-email="<?= htmlspecialchars($data['email'] ?? '', ENT_QUOTES) ?>"
                                data-alamat="<?= htmlspecialchars($data['alamat'] ?? '', ENT_QUOTES) ?>">
                                Edit &gt;
                            </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($pelanggan) === 0): ?>
                        <tr>
                            <td colspan="6" class="data-kosong">Belum ada data pelanggan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p id="pesanKosong" class="pesan-kosong" style="display: none;">Data pelanggan tidak ditemukan.</p>
        </div>
    </section>

    <!-- MODAL TAMBAH PELANGGAN -->
    <div class="modal-overlay" id="modalPelanggan">
        <div class="modal-pelanggan">
            <div class="modal-header-pelanggan">
                <h2>Tambah Pelanggan Baru</h2>
            </div>

            <form method="POST" id="formTambahPelanggan">

                <!-- Nama Pelanggan -->
                <div class="form-pelanggan">
                    <label for="namaPelanggan">Nama Pelanggan <span>*</span></label>
                    <input type="text" id="namaPelanggan" name="nama" placeholder="Masukkan nama pelanggan baru..." required>
                </div>

                <!-- Nomor WhatsApp -->
                <div class="form-pelanggan">
                    <label for="noWhatsapp">No WhatsApp <span>*</span></label>
                    <input type="tel" id="noWhatsapp" name="no_hp" placeholder="Masukkan no WhatsApp pelanggan..." required>
                </div>

                <!-- Alamat -->
                <div class="form-pelanggan">
                    <label for="alamatPelanggan">Alamat</label>
                    <input type="text" id="alamatPelanggan" name="alamat" placeholder="Masukkan alamat pelanggan...">
                </div>

                <!-- Email -->
                <div class="form-pelanggan">
                    <label for="emailPelanggan">Email <span>*</span></label>
                    <input type="email" id="emailPelanggan" name="email" placeholder="Masukkan email pelanggan..." required>
                </div>

    

                <!-- Tombol -->
                <div class="aksi-modal-pelanggan">
                    <button type="button" class="btn-batal-pelanggan" id="tutupModalPelanggan">Batal</button>
                    <button type="submit" name="tambah_pelanggan" value="1" class="btn-simpan-pelanggan">Simpan dan Kirim WhatsApp</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PELANGGAN -->
    <div class="modal-overlay" id="modalEditPelanggan">
        <div class="modal-pelanggan">
            <div class="modal-header-pelanggan">
                <h2>Edit Data Pelanggan</h2>
            </div>

            <form id="formEditPelanggan">

                <!-- ID Pelanggan -->
                <div class="form-pelanggan">
                    <label for="editId">ID Pelanggan</label>
                    <input type="text" id="editId" name="id" readonly>
                </div>

                <!-- Nama -->
                <div class="form-pelanggan">
                    <label for="editNama">Nama Pelanggan <span>*</span></label>
                    <input type="text" id="editNama" name="nama" required>
                </div>

                <!-- No HP -->
                <div class="form-pelanggan">
                    <label for="editNoHp">No WhatsApp <span>*</span></label>
                    <input type="tel" id="editNoHp" name="no_hp" required>
                </div>

                <!-- Email -->
                <div class="form-pelanggan">
                    <label for="editEmail">Email </label>
                    <input type="email" id="editEmail" name="email" readonly>
                </div>

                <!-- Alamat -->
                <div class="form-pelanggan">
                    <label for="editAlamat">Alamat</label>
                    <input type="text" id="editAlamat" name="alamat">
                </div>

                <!-- Tombol -->
                <div class="aksi-modal-pelanggan">
                    <button type="button" class="btn-batal-pelanggan" id="tutupModalEdit">Batal</button>
                    <button type="submit" class="btn-simpan-pelanggan">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="assets/js/data-pelanggan.js"></script>
</body>
</html>