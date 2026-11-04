
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

        tombol.addEventListener('click', function () {

            const namaLayanan = tombol.dataset.nama;

            const konfirmasi = confirm(
                'Apakah kamu ingin memesan layanan ' + namaLayanan + '?'
            );

            if (konfirmasi) {
                window.location.href =
                    'tambah-pesanan.php?layanan=' +
                    encodeURIComponent(namaLayanan);
            }

        });

    });

});
