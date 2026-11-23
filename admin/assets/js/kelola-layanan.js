document.addEventListener('DOMContentLoaded', function () {
    const tombolTambah = document.getElementById('bukaModalLayanan');
    const tombolPesan = document.querySelectorAll('.btn-pesan-layanan');
    
    // Tombol tambah layanan
    if (tombolTambah) {
        tombolTambah.addEventListener('click', function () {
            alert('Modal Tambah Layanan akan dibuat pada tahap berikutnya.');
        });
    }

    // Tombol pesan layanan
    tombolPesan.forEach(function (tombol) {
        tombol.addEventListener('click', function (event) {
            const idLayanan = tombol.dataset.id;
            const namaLayanan = tombol.dataset.nama;

            if (!idLayanan || !namaLayanan) {
                event.preventDefault();
                alert('Data layanan tidak ditemukan. Silakan periksa kembali.');
                return;
            }

            const konfirmasi = confirm('Apakah kamu ingin memesan layanan ' + namaLayanan + '?');

            if (!konfirmasi) {
                event.preventDefault();
            }
        });
    });
});