<?php
$page_title = "Tambah Pesanan";
$active_page = "kelola-pesanan";

$pelanggan = [
    [
        'nama' => 'Athia',
        'no_hp' => '0812',
        'alamat' => 'null'
    ],
    [
        'nama' => 'Budi Santoso',
        'no_hp' => '0823',
        'alamat' => 'null'
    ],
    [
        'nama' => 'Cindy Callista',
        'no_hp' => '083456789012',
        'alamat' => 'Sukun, Malang'
    ]
];
$layanan = [
    'Cuci Kering Setrika' => [
        'harga' => 6000,
        'satuan' => 'kg'
    ],
    'Setrika' => [
        'harga' => 5000,
        'satuan' => 'kg'
    ],
    'Bed Cover' => [
        'harga' => 20000,
        'satuan' => 'pcs'
    ],
    'Dry Clean' => [
        'harga' => 15000,
        'satuan' => 'pcs'
    ],
    'Cuci Sepatu' => [
        'harga' => 25000,
        'satuan' => 'pasang'
    ],
    'Cuci Karpet' => [
        'harga' => 12000,
        'satuan' => 'm²'
    ]
];

include 'includes/header.php';
include 'includes/sidebar.php';

?>

<main class="main-content halaman-tambah">

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

<!-- form tambah pesanan -->
    <section class="form-pesanan-card">

        <form id="formTambahPesanan">

            <!-- data pelanggan -->
            <div class="bagian-form">
                <h2>Data Pelanggan</h2>

                <div class="pelanggan-baris">

                    <div class="form-group">
                        <label for="pelanggan">Nama Pelanggan</label>

                        <select id="pelanggan" name="pelanggan" required>
                            <option value="">Pilih pelanggan</option>

                            <?php foreach ($pelanggan as $p): ?>
                                <option
                                    value="<?= htmlspecialchars($p['nama']) ?>"
                                    data-hp="<?= htmlspecialchars($p['no_hp']) ?>"
                                    data-alamat="<?= htmlspecialchars($p['alamat']) ?>"
                                >
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button
                        type="button"
                        class="btn-tambah-pelanggan"
                        id="btnTambahPelanggan"
                    >
                        + Tambah Pelanggan
                    </button>

                </div>

                <div class="pelanggan-detail">

                    <div class="form-group">
                        <label for="noHp">No HP</label>
                        <input
                            type="text"
                            id="noHp"
                            name="no_hp"
                            placeholder="Nomor HP"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input
                            type="text"
                            id="alamat"
                            name="alamat"
                            placeholder="Alamat pelanggan"
                            readonly
                        >
                    </div>

                </div>
            </div>

            <hr class="garis-form">

            <!-- tambah layanan -->
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

                <button
                    type="button"
                    id="btnTambahLayanan"
                    class="btn-tambah-layanan"
                >
                    + Tambah Layanan
                </button>
            </div>

            <hr class="garis-form">

            <!-- pembayaran -->
            <div class="bagian-form">
                <h2>Pembayaran</h2>

                <div class="total-tagihan">
                    <span>Total Tagihan:</span>
                    <strong id="totalTagihan">Rp0</strong>
                </div>

                <div class="pilihan-pembayaran">

                    <label class="opsi-pembayaran">
                        <input
                            type="radio"
                            name="waktu_bayar"
                            value="sekarang"
                            checked
                        >
                        Bayar Sekarang
                    </label>

                    <label class="opsi-pembayaran">
                        <input
                            type="radio"
                            name="waktu_bayar"
                            value="nanti"
                        >
                        Bayar Nanti
                    </label>

                </div>

                <div class="metode-pembayaran" id="metodePembayaran">
                    <p>Metode Pembayaran</p>

                    <label>
                        <input
                            type="radio"
                            name="metode_bayar"
                            value="Tunai"
                            checked
                        >
                        Tunai
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="metode_bayar"
                            value="QRIS"
                        >
                        QRIS
                    </label>

                    <label>
                        <input
                            type="radio"
                            name="metode_bayar"
                            value="Transfer Bank"
                        >
                        Transfer Bank
                    </label>
                </div>

            </div>

            <!-- tombol aksi -->
            <div class="form-actions">

                <a href="kelola-pesanan.php" class="btn-batal">
                    Batal
                </a>

                <button type="submit" class="btn-simpan">
                    Simpan Pesanan
                </button>

            </div>

        </form>

    </section>

</main>

<script>
    // Data layanan dari PHP ke JavaScript
    const dataLayanan = <?= json_encode(
        $layanan,
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG |
        JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const daftarLayanan = document.getElementById('daftarLayanan');
    const pelangganSelect = document.getElementById('pelanggan');

    // Format angka menjadi Rupiah
    function formatRupiah(angka) {
        return 'Rp' + Number(angka).toLocaleString('id-ID');
    }

    // Mengisi nomor HP dan alamat pelanggan
    pelangganSelect.addEventListener('change', function () {
        const pilihan = this.options[this.selectedIndex];

        document.getElementById('noHp').value =
            pilihan.dataset.hp || '';

        document.getElementById('alamat').value =
            pilihan.dataset.alamat || '';
    });

    // Membuat satu baris layanan
    function tambahBarisLayanan(namaLayanan = '', jumlah = 1) {
        const baris = document.createElement('tr');

        let pilihanLayanan = '<option value="">Pilih layanan</option>';

        Object.keys(dataLayanan).forEach(function (nama) {
            pilihanLayanan += `
                <option value="${nama}">${nama}</option>
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
                    <input
                        type="number"
                        class="jumlah-layanan"
                        min="1"
                        step="1"
                        value="${jumlah}"
                        required
                    >
                    <span class="satuan-layanan">-</span>
                </div>
            </td>

            <td class="harga-layanan">Rp0</td>

            <td class="subtotal-layanan">Rp0</td>

            <td>
                <button
                    type="button"
                    class="btn-hapus-layanan"
                    title="Hapus layanan"
                >Hapus</button>
            </td>
        `;

        daftarLayanan.appendChild(baris);

        const select = baris.querySelector('.pilih-layanan');

        if (namaLayanan) {
            select.value = namaLayanan;
        }

        select.addEventListener('change', hitungTotal);

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

            const nama = baris.querySelector('.pilih-layanan').value;
            const inputJumlah = baris.querySelector('.jumlah-layanan');
            const jumlah = Math.max(0, Number(inputJumlah.value) || 0);

            const info = dataLayanan[nama];

            const harga = info ? info.harga : 0;
            const subtotal = harga * jumlah;

            baris.querySelector('.satuan-layanan').textContent =
                info ? info.satuan : '-';

            baris.querySelector('.harga-layanan').textContent =
                formatRupiah(harga);

            baris.querySelector('.subtotal-layanan').textContent =
                formatRupiah(subtotal);

            total += subtotal;
        });

        document.getElementById('totalTagihan').textContent =
            formatRupiah(total);
    }

    // Tombol tambah layanan
    document.getElementById('btnTambahLayanan')
        .addEventListener('click', function () {
            tambahBarisLayanan();
        });

    // Tombol tambah pelanggan (masih dummy)
    document.getElementById('btnTambahPelanggan')
        .addEventListener('click', function () {
            alert(
                'Fitur tambah pelanggan akan dibuat pada halaman Data Pelanggan.'
            );
        });

    // Pilihan bayar sekarang atau nanti
    document.querySelectorAll('input[name="waktu_bayar"]')
        .forEach(function (radio) {
            radio.addEventListener('change', function () {
                const metode = document.getElementById('metodePembayaran');

                metode.classList.toggle(
                    'metode-nonaktif',
                    this.value === 'nanti'
                );
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

            const adaLayanan = Array.from(daftarLayanan.rows).some(
                baris => baris.querySelector('.pilih-layanan').value !== ''
                    && Number(baris.querySelector('.jumlah-layanan').value) > 0
            );

            if (!adaLayanan) {
                alert('Silakan pilih minimal satu layanan.');
                return;
            }

            const yakin = confirm(
                'Apakah data pesanan sudah benar?'
            );

            if (yakin) {
                alert(
                    'Data berhasil diperiksa, tetapi belum disimpan. ' +
                    'Halaman ini masih menggunakan dummy data.'
                );
            }
        });

    // Tampilkan satu baris layanan saat halaman dibuka
    tambahBarisLayanan('Cuci Kering Setrika', 3);
    tambahBarisLayanan('Setrika', 2);
    tambahBarisLayanan('Bed Cover', 1);

    // Tampilkan ikon Lucide jika tersedia
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>

</body>
</html>