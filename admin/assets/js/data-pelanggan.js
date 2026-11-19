document.addEventListener('DOMContentLoaded', function () {
    const cariPelanggan = document.getElementById('cariPelanggan');
    const tabelPelanggan = document.getElementById('tabelPelanggan');
    const pesanKosong = document.getElementById('pesanKosong');

    if (cariPelanggan && tabelPelanggan && pesanKosong) {
        cariPelanggan.addEventListener('input', function () {
            const kataKunci = cariPelanggan.value.toLowerCase().trim();
            const baris = tabelPelanggan.querySelectorAll('tbody tr');

            let jumlahDitemukan = 0;

            baris.forEach(function (tr) {
                if (tr.cells.length < 2) {
                    return;
                }

                const namaPelanggan = tr.cells[1].textContent.toLowerCase();

                if (namaPelanggan.startsWith(kataKunci)) {
                    tr.style.display = '';
                    jumlahDitemukan++;
                } else {
                    tr.style.display = 'none';
                }
            });
            pesanKosong.style.display = jumlahDitemukan === 0 ? 'block' : 'none';
        });
    }

    // ==================================
    // MODAL TAMBAH PELANGGAN
    // ==================================
    const modalTambah = document.getElementById('modalPelanggan');
    const tombolBukaTambah = document.getElementById('bukaModalPelanggan');
    const tombolTutupTambah = document.getElementById('tutupModalPelanggan');
    const formTambah = document.getElementById('formTambahPelanggan');

    if (modalTambah && tombolBukaTambah && tombolTutupTambah && formTambah) {
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
            const nama = document.getElementById('namaPelanggan').value.trim();
            if (!confirm('Simpan data pelanggan ' + nama + '?')) {
                event.preventDefault();
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