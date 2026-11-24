
function formatRupiah(angka) {
    return 'Rp' + angka.toLocaleString('id-ID');
}

function hitungSemua() {
    let totalSemua = 0;

    document.querySelectorAll('.edit-item-layanan').forEach(detail => {
        const inputBerat = detail.querySelector('.berat');
        const inputHarga = detail.querySelector('.harga');

        const berat = parseFloat(inputBerat.value) || 0;
        const harga = parseFloat(inputHarga.dataset.harga) || 0;
        const subtotal = berat * harga;

        inputHarga.value = formatRupiah(subtotal);
        totalSemua += subtotal;
    });

    document.getElementById('totalKeseluruhan').textContent =
        formatRupiah(totalSemua);
}

// Ketika berat diubah
document.querySelectorAll('.berat').forEach(input => {
    input.addEventListener('input', hitungSemua);
});

// Hitung pertama kali halaman dibuka
hitungSemua();
