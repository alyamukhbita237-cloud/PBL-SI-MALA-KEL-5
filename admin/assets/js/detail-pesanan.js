
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('modalPembayaran');
    const tombolBuka = document.getElementById('bukaModalPembayaran');
    const tombolTutup = document.getElementById('tutupModalPembayaran');
    const form = document.getElementById('formPembayaran');
    const jumlahDibayar = document.getElementById('jumlahDibayar');
    const pesan = document.getElementById('pesanPembayaran');

    // Jika elemen modal tidak ditemukan, hentikan JavaScript
    if (!modal || !tombolBuka || !tombolTutup || !form) {
        return;
    }

    // Total tagihan dari PHP
    const totalTagihan = Number(modal.dataset.total);

    // Membuka modal
    tombolBuka.addEventListener('click', function () {
        modal.classList.add('aktif');
    });

    // Menutup modal melalui tombol Batal
    tombolTutup.addEventListener('click', function () {
        modal.classList.remove('aktif');
        pesan.textContent = '';
    });

    // Menutup modal ketika area luar diklik
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.classList.remove('aktif');
            pesan.textContent = '';
        }
    });

    // Validasi pembayaran
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const jumlah = Number(jumlahDibayar.value);

        if (jumlah <= 0 || !Number.isFinite(jumlah)) {
            pesan.textContent = 'Jumlah pembayaran harus lebih dari 0.';
            return;
        }

        if (jumlah > totalTagihan) {
            pesan.textContent = 'Jumlah pembayaran tidak boleh melebihi total tagihan.';
            return;
        }

        const pilihanMetode = document.querySelector(
            'input[name="metode_pembayaran"]:checked'
        );

        if (!pilihanMetode) {
            pesan.textContent = 'Pilih metode pembayaran terlebih dahulu.';
            return;
        }

        const metode = pilihanMetode.value;

        const konfirmasi = confirm(
            'Apakah data pembayaran sudah benar?\n\n' +
            'Metode: ' + metode + '\n' +
            'Jumlah: Rp' + jumlah.toLocaleString('id-ID')
        );

        if (konfirmasi) {
            alert(
                'Data pembayaran berhasil divalidasi!\n' +
                'Metode: ' + metode + '\n' +
                'Jumlah: Rp' + jumlah.toLocaleString('id-ID') +
                '\n\nIni masih simulasi dan belum disimpan ke database.'
            );

            modal.classList.remove('aktif');
            form.reset();
            pesan.textContent = '';
        }
    });

});
