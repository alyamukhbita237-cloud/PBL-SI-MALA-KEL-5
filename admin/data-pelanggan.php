<?php
session_start();

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/koneksi.php';

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

            <form action="simpan-pelanggan.php" method="POST" id="formTambahPelanggan">

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
                    <input type="email" id="emailPelanggan" name="email" placeholder="Email diisi langsung oleh pelanggan..." required>
                </div>

                <!-- Kata Sandi -->
                <div class="form-pelanggan">
                    <label for="passwordPelanggan">Kata Sandi <span>*</span></label>
                    <input type="password" id="passwordPelanggan" name="password" placeholder="Kata Sandi diisi langsung oleh pelanggan..." required>
                </div>

                <!-- Tombol -->
                <div class="aksi-modal-pelanggan">
                    <button type="button" class="btn-batal-pelanggan" id="tutupModalPelanggan">Batal</button>
                    <button type="submit" class="btn-simpan-pelanggan">Simpan Pelanggan Baru</button>
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