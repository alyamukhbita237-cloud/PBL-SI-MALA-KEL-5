document.addEventListener('DOMContentLoaded', function () {
    const inputCari = document.getElementById('cariPelanggan');
    const tabel = document.getElementById('tabelPelanggan');
    const baris = tabel.querySelectorAll('tbody tr');
    const pesanKosong = document.getElementById('pesanKosong');

    inputCari.addEventListener('input', function () {

        const kataKunci = inputCari.value.toLowerCase();
        let jumlahData = 0;

        baris.forEach(function (row) {
            const isiBaris = row.textContent.toLowerCase();
            if (isiBaris.includes(kataKunci)) {
                row.style.display = '';
                jumlahData++;
            } else {
                row.style.display = 'none';
            }
        });
        pesanKosong.style.display = jumlahData === 0 ? 'block' : 'none';
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // ==================================
    // MODAL TAMBAH PELANGGAN
    // ==================================
    const modalTambah = document.getElementById('modalPelanggan');
    const tombolBukaTambah = document.getElementById('bukaModalPelanggan');
    const tombolTutupTambah = document.getElementById('tutupModalPelanggan');
    const formTambah = document.getElementById('formTambahPelanggan');

    if (modalTambah && tombolBukaTambah && tombolTutupTambah) {
        tombolBukaTambah.addEventListener('click', function () {
            modalTambah.classList.add('aktif');
        });

        tombolTutupTambah.addEventListener('click', function () {
            modalTambah.classList.remove('aktif');
        });

        modalTambah.addEventListener('click', function (event) {
            if (event.target === modalTambah) {
                modalTambah.classList.remove('aktif');
            }
        });

        formTambah.addEventListener('submit', function (event) {
            event.preventDefault();

            const nama = document.getElementById('namaPelanggan').value.trim();

            if (confirm('Apakah data pelanggan sudah benar?\n\nNama: ' + nama)) {
                alert('Data berhasil divalidasi. Fitur ini masih berupa simulasi dan belum disimpan ke database.');
                modalTambah.classList.remove('aktif');
                formTambah.reset();
            }
        });
    }
    // ==================================
    // MODAL EDIT PELANGGAN
    // ==================================
    const modalEdit = document.getElementById('modalEditPelanggan');
    const tombolTutupEdit = document.getElementById('tutupModalEdit');
    const formEdit = document.getElementById('formEditPelanggan');
    const tombolEdit = document.querySelectorAll('.btn-edit-pelanggan');
    const editId = document.getElementById('editId');
    const editNama = document.getElementById('editNama');
    const editNoHp = document.getElementById('editNoHp');
    const editEmail = document.getElementById('editEmail');
    const editAlamat = document.getElementById('editAlamat');

    let barisDipilih = null;

    if (modalEdit && formEdit) {
        // Membuka modal dan mengisi data pelanggan
        tombolEdit.forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                barisDipilih = tombol.closest('tr');
                editId.value = tombol.dataset.id;
                editNama.value = tombol.dataset.nama;
                editNoHp.value = tombol.dataset.nohp;
                editEmail.value = tombol.dataset.email;
                editAlamat.value = tombol.dataset.alamat;
                modalEdit.classList.add('aktif');
            });
        });

        // Menutup modal
        tombolTutupEdit.addEventListener('click', function () {
            modalEdit.classList.remove('aktif');
        });

        // Menutup modal jika area luar diklik
        modalEdit.addEventListener('click', function (event) {
            if (event.target === modalEdit) {
                modalEdit.classList.remove('aktif');
            }
        });

        // Menyimpan perubahan
        formEdit.addEventListener('submit', function (event) {
            event.preventDefault();
            const id = editId.value;
            const nama = editNama.value.trim();
            const noHp = editNoHp.value.trim();
            const email = editEmail.value.trim();
            const alamat = editAlamat.value.trim();
            if (!nama || !noHp || !email) {
                alert('Nama, nomor WhatsApp, dan email wajib diisi.');
                return;
            }

            const konfirmasi = confirm(
                'Apakah perubahan data pelanggan sudah benar?\n\n' +
                'ID: ' + id + '\n' +
                'Nama: ' + nama
            );

            if (!konfirmasi) {
                return;
            }

            if (barisDipilih) {
                // Memperbarui isi tabel
                const kolom = barisDipilih.querySelectorAll('td');
                kolom[1].textContent = nama;
                kolom[2].textContent = noHp;
                kolom[3].textContent = email;
                kolom[4].textContent = alamat;

                // Memperbarui data pada tombol Edit
                const tombol = barisDipilih.querySelector('.btn-edit-pelanggan');
                tombol.dataset.nama = nama;
                tombol.dataset.nohp = noHp;
                tombol.dataset.email = email;
                tombol.dataset.alamat = alamat;

                alert('Perubahan data berhasil diterapkan pada tabel.');

                modalEdit.classList.remove('aktif');
                formEdit.reset();
                barisDipilih = null;
            }
        });
    }
});