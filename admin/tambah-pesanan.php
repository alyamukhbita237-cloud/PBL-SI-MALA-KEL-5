<?php
$page_title = "Tambah Pesanan";
$active_page = "kelola-pesanan";

require_once __DIR__ . '/../includes/koneksi.php';

// Mengambil data pelanggan dari database
$queryPelanggan = "SELECT id_user, nama_user, no_hp, alamat
                FROM users
                WHERE LOWER(role) = 'pelanggan'
                ORDER BY nama_user ASC";

$stmtPelanggan = $pdo->prepare($queryPelanggan);
$stmtPelanggan->execute();
$pelanggan = $stmtPelanggan->fetchAll(PDO::FETCH_ASSOC);

// Mengambil data layanan dari database
$queryLayanan = "SELECT id_layanan, jenis_layanan, harga_per_layanan, satuan, estimasi_hari
                FROM layanan
                ORDER BY id_layanan ASC";

$stmtLayanan = $pdo->prepare($queryLayanan);
$stmtLayanan->execute();
$layanan = $stmtLayanan->fetchAll(PDO::FETCH_ASSOC);

// Mengubah data layanan database menjadi JSON
$dataLayananJson = json_encode($layanan, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

foreach ($layanan as $data) {
    $dataLayanan[$data['id_layanan']] = [
        'nama' => $data['jenis_layanan'],
        'harga' => $data['harga_per_layanan'],
        'satuan' => $data['satuan'],
        'estimasi_hari' => $data['estimasi_hari']
    ];
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="main-content halaman-tambah">
    <!-- JUDUL HALAMAN -->
    <section class="judul-halaman">
        <div>
            <div class="judul-dengan-panah">
                <a href="kelola-pesanan.php" class="btn-kembali">
                    <span class="panah-kembali">&lsaquo;</span>
                </a>
                <h1>Tambah Pesanan</h1>
            </div>
            <p>Lengkapi data pelanggan dan data pesanan</p>
        </div>
    </section>

    <!-- FORM TAMBAH PESANAN -->
        <section class="form-pesanan-card">
            <form id="formTambahPesanan" method="POST" action="simpan-pesanan.php">
                <input type="hidden" name="jenis_pesanan" id="jenisPesanan" value="Walk-in">
                <input type="hidden" name="layanan_json" id="layananJson">

                <!-- DATA PELANGGAN -->
                <div class="bagian-form">
                    <h2>Data Pelanggan</h2>
                    <div class="pelanggan-baris">
                        <div class="form-group">
                            <label for="pelanggan">Nama Pelanggan</label>

                            <select id="pelanggan" name="pelanggan" required>
                                <option value="">Pilih pelanggan</option>

                                <?php foreach ($pelanggan as $p): ?>
                                    <option
                                        value="<?= htmlspecialchars((string) $p['id_user']) ?>"
                                        data-hp="<?= htmlspecialchars($p['no_hp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-alamat="<?= htmlspecialchars($p['alamat'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($p['nama_user']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="btn-tambah-pelanggan" id="btnTambahPelanggan">+ Tambah Pelanggan</button>
                    </div>

                    <div class="pelanggan-detail">
                        <div class="form-group">
                            <label for="noHp">No HP</label>
                            <input type="text" id="noHp" name="no_hp" placeholder="Nomor HP" readonly>
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <input type="text" id="alamat" name="alamat" placeholder="Alamat pelanggan" readonly>
                        </div>
                    </div>
                </div>

                <hr class="garis-form">

                <!-- TAMBAH LAYANAN -->
                <div class="bagian-form">
                    <h2>Tambah Layanan</h2>
                    <div class="tabel-layanan-wrapper">
                        <table class="tabel-layanan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Jenis Layanan</th>
                                    <th>Berat/Satuan</th>
                                    <th>Harga/Satuan</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="daftarLayanan"></tbody>
                        </table>
                    </div>
                    <button type="button" id="btnTambahLayanan" class="btn-tambah-layanan">+ Tambah Layanan</button>
                </div>

                <hr class="garis-form">

                <!-- PEMBAYARAN -->
                <div class="bagian-form">
                    <h2>Pembayaran</h2>
                    <div class="total-tagihan">
                        <span>Total Tagihan:</span>
                        <strong id="totalTagihan">Rp0</strong>
                    </div>

                    <div class="pilihan-pembayaran">

                        <label class="opsi-pembayaran">
                            <input type="radio" name="waktu_bayar" value="sekarang" checked>Bayar Sekarang
                        </label>

                        <label class="opsi-pembayaran">
                            <input type="radio" name="waktu_bayar" value="nanti">Bayar Nanti
                        </label>
                    </div>

                    <div class="metode-pembayaran" id="metodePembayaran">
                        <p>Metode Pembayaran</p>
                        <label>
                            <input type="radio" name="metode_bayar" value="Tunai" checked>Tunai
                        </label>

                        <label>
                            <input type="radio" name="metode_bayar" value="QRIS">QRIS
                        </label>

                        <label>
                            <input type="radio" name="metode_bayar" value="Transfer Bank">Transfer Bank
                        </label>
                    </div>
                </div>


                <!-- TOMBOL AKSI -->
                <div class="form-actions">
                    <a href="kelola-pesanan.php" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-simpan">Simpan Pesanan</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        // Data layanan dari PHP ke JavaScript
        const dataLayanan = <?= $dataLayananJson ?: '[]' ?>;

        const daftarLayanan = document.getElementById('daftarLayanan');
        const pelangganSelect = document.getElementById('pelanggan');
        const formPesanan = document.getElementById('formTambahPesanan');

        // Format angka menjadi Rupiah
        function formatRupiah(angka) {
            return 'Rp' + Number(angka).toLocaleString('id-ID');
        }

        // Mengisi nomor HP dan alamat pelanggan
        pelangganSelect.addEventListener('change', function () {
            const pilihan = this.options[this.selectedIndex];
            document.getElementById('noHp').value = pilihan.dataset.hp || '';
            document.getElementById('alamat').value = pilihan.dataset.alamat || '';
        });

        // Membuat satu baris layanan 
        function tambahBarisLayanan(jumlah = 1, idLayanan = '') {
            const baris = document.createElement('tr');
            let pilihanLayanan = '<option value="">Pilih layanan</option>';
            dataLayanan.forEach(function (info) {
                pilihanLayanan += `
                    <option value="${info.id_layanan}">
                        ${info.jenis_layanan}
                    </option>
                `;
            });

            baris.innerHTML = `
                <td class="nomor-baris"></td>
                <td>
                    <select class="pilih-layanan" required>
                        ${pilihanLayanan}
                    </select>
                </td>

                <td>
                    <div class="input-jumlah">
                        <input type="number" class="jumlah-layanan" min="1" step="1" value="${jumlah}" required>
                        <span class="satuan-layanan">-</span>
                    </div>
                </td>

                <td class="harga-layanan">Rp0</td>
                <td class="subtotal-layanan">Rp0</td>

                <td>
                    <button
                        type="button"
                        class="btn-hapus-layanan"
                    >Hapus</button>
                </td>
            `;
            daftarLayanan.appendChild(baris);
            const selectLayanan = baris.querySelector('.pilih-layanan');

            // Memilih layanan berdasarkan ID dari URL
            if (idLayanan) {
                selectLayanan.value = String(idLayanan);
            }

            selectLayanan.addEventListener('change', hitungTotal);

            baris.querySelector('.jumlah-layanan')
                .addEventListener('input', hitungTotal);

            baris.querySelector('.btn-hapus-layanan')
                .addEventListener('click', function () {
                    if (daftarLayanan.rows.length === 1) {
                        alert('Minimal harus ada satu layanan.');
                        return;
                    }
                    baris.remove();
                    hitungTotal();
                });
            hitungTotal();
        }

        // Menghitung subtotal dan total tagihan
        function hitungTotal() {
            let total = 0;
            Array.from(daftarLayanan.rows).forEach(function (baris, index) {
                baris.querySelector('.nomor-baris').textContent = index + 1;
                const idLayanan = baris.querySelector('.pilih-layanan').value;
                const jumlah = Number(baris.querySelector('.jumlah-layanan').value) || 0;
                const info = dataLayanan.find(function (layanan) {
                    return String(layanan.id_layanan) === String(idLayanan);
                });

                const harga = info ? Number(info.harga_per_layanan) : 0;
                const subtotal = harga * jumlah;

                baris.querySelector('.satuan-layanan').textContent = info ? info.satuan : '-';
                baris.querySelector('.harga-layanan').textContent = formatRupiah(harga);
                baris.querySelector('.subtotal-layanan').textContent = formatRupiah(subtotal);
                total += subtotal;
            });
            document.getElementById('totalTagihan').textContent = formatRupiah(total);
        }

        // Tombol tambah layanan
        document.getElementById('btnTambahLayanan')
            .addEventListener('click', function () {
                tambahBarisLayanan();
            });

        // Tombol tambah pelanggan (masih dummy)
        document.getElementById('btnTambahPelanggan')
            .addEventListener('click', function () {
                alert('Fitur tambah pelanggan akan dibuat pada halaman Data Pelanggan.');
            });

        // Pilihan bayar sekarang atau nanti
        document.querySelectorAll('input[name="waktu_bayar"]')
            .forEach(function (radio) {
                radio.addEventListener('change', function () {
                    const metode = document.getElementById('metodePembayaran');
                    metode.classList.toggle('metode-nonaktif', this.value === 'nanti');
                });
            });

        // Konfirmasi simpan pesanan
        document.getElementById('formTambahPesanan')
            .addEventListener('submit', function (event) {
                event.preventDefault();

                if (!pelangganSelect.value) {
                    alert('Silakan pilih pelanggan terlebih dahulu.');
                    pelangganSelect.focus();
                    return;
                }

                const jenisPesanan = document.getElementById('jenisPesanan');

                if (!jenisPesanan.value) {
                    alert('Silakan pilih jenis pesanan.');
                    jenisPesanan.focus();
                    return;
                }
                
                const layananDipilih = [];

                Array.from(daftarLayanan.rows).forEach(function (baris) {
                    const idLayanan = baris.querySelector('.pilih-layanan').value;
                    const jumlah = Number(
                        baris.querySelector('.jumlah-layanan').value
                    );

                    if (idLayanan && jumlah > 0) {
                        layananDipilih.push({
                            id_layanan: Number(idLayanan),
                            jumlah: jumlah
                        });
                    }
                });

                if (layananDipilih.length === 0) {
                    alert('Silakan pilih minimal satu layanan.');
                    return;
                }

                const waktuBayar = document.querySelector(
                    'input[name="waktu_bayar"]:checked'
                );

                const metodeBayar = document.querySelector(
                    'input[name="metode_bayar"]:checked'
                );

                if (!waktuBayar || !metodeBayar) {
                    alert('Silakan lengkapi pilihan pembayaran.');
                    return;
                }

                const yakin = confirm('Apakah data pesanan sudah benar?');

                if (yakin) {
                    document.getElementById('layananJson').value =
                        JSON.stringify(layananDipilih);

                    formPesanan.submit();
                }
            });
            
        // Tampilkan satu baris layanan kosong saat halaman dibuka
        const parameterURL = new URLSearchParams(window.location.search);
        const idLayananTerpilih = parameterURL.get('layanan');

        if (idLayananTerpilih) {
            tambahBarisLayanan(1, idLayananTerpilih);
        } else {
            tambahBarisLayanan();
        }

        // Tampilkan ikon Lucide jika tersedia
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>