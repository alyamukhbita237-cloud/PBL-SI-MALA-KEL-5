document.addEventListener("DOMContentLoaded", function () {

    const addServiceButton =
        document.getElementById("addServiceButton");

    const servicePicker =
        document.getElementById("servicePicker");

    const serviceSelect =
        document.getElementById("serviceSelect");

    const confirmServiceButton =
        document.getElementById("confirmServiceButton");

    const selectedServices =
        document.getElementById("selectedServices");

    const pickupForm =
        document.getElementById("pickupForm");


    // ==================================================
    // TAMBAH LAYANAN
    // ==================================================

    if (
        addServiceButton &&
        servicePicker &&
        serviceSelect &&
        confirmServiceButton &&
        selectedServices
    ) {

        // Klik tombol "Tambah Layanan"
        addServiceButton.addEventListener(
            "click",
            function () {

                servicePicker.classList.toggle("hidden");

            }
        );


        // Klik tombol "Tambahkan"
        confirmServiceButton.addEventListener(
            "click",
            function () {

                const selectedOption =
                    serviceSelect.options[
                        serviceSelect.selectedIndex
                    ];


                // ------------------------------------------
                // CEK APAKAH SUDAH MEMILIH LAYANAN
                // ------------------------------------------

                if (!serviceSelect.value) {

                    alert(
                        "Silakan pilih layanan terlebih dahulu."
                    );

                    return;

                }


                // ------------------------------------------
                // CEK LAYANAN DUPLIKAT
                // ------------------------------------------

                const existingServices =
                    selectedServices.querySelectorAll(
                        'input[name="layanan[]"]'
                    );


                for (const input of existingServices) {

                    if (
                        input.value ===
                        serviceSelect.value
                    ) {

                        alert(
                            "Layanan tersebut sudah dipilih."
                        );

                        return;

                    }

                }


                // ------------------------------------------
                // HAPUS TULISAN
                // "Silakan pilih layanan."
                // ------------------------------------------

                const emptyService =
                    selectedServices.querySelector(
                        ".empty-service"
                    );


                if (emptyService) {

                    emptyService.remove();

                }


                // ------------------------------------------
                // BUAT BARIS LAYANAN
                // ------------------------------------------

                const serviceRow =
                    document.createElement("div");

                serviceRow.className =
                    "selected-service";


                // ------------------------------------------
                // HIDDEN INPUT
                // ------------------------------------------

                const hiddenInput =
                    document.createElement("input");

                hiddenInput.type = "hidden";

                hiddenInput.name =
                    "layanan[]";

                hiddenInput.value =
                    selectedOption.value;


                // ------------------------------------------
                // NOMOR LAYANAN
                // ------------------------------------------

                const number =
                    selectedServices.querySelectorAll(
                        ".selected-service"
                    ).length + 1;


                // ------------------------------------------
                // ISI BARIS LAYANAN
                // ------------------------------------------

                serviceRow.innerHTML = `

                    <span class="service-number">
                        ${number}
                    </span>

                    <span class="selected-service-name">
                        ${selectedOption.dataset.name}
                    </span>

                    <span class="selected-service-price">
                        Rp${Number(
                            selectedOption.dataset.price
                        ).toLocaleString("id-ID")}
                        /${selectedOption.dataset.satuan}
                    </span>

                    <button
                        type="button"
                        class="remove-service-button"
                        title="Hapus layanan"
                    >
                        <i data-lucide="trash-2"></i>
                    </button>

                `;


                // ------------------------------------------
                // MASUKKAN HIDDEN INPUT
                // ------------------------------------------

                serviceRow.prepend(
                    hiddenInput
                );


                // ------------------------------------------
                // MASUKKAN BARIS KE DAFTAR
                // ------------------------------------------

                selectedServices.appendChild(
                    serviceRow
                );


                // ------------------------------------------
                // AKTIFKAN ICON LUCIDE
                // ------------------------------------------

                if (typeof lucide !== "undefined") {

                    lucide.createIcons();

                }


                // ------------------------------------------
                // RESET SELECT
                // ------------------------------------------

                serviceSelect.value = "";


                // ------------------------------------------
                // TUTUP SERVICE PICKER
                // ------------------------------------------

                servicePicker.classList.add(
                    "hidden"
                );

            }
        );

    }


    // ==================================================
    // HAPUS LAYANAN
    // ==================================================

    document.addEventListener(
        "click",
        function (event) {

            const removeButton =
                event.target.closest(
                    ".remove-service-button"
                );


            // Kalau yang diklik bukan tombol hapus
            if (!removeButton) {

                return;

            }


            // Cari baris layanan
            const serviceRow =
                removeButton.closest(
                    ".selected-service"
                );


            if (serviceRow) {

                // Hapus baris layanan
                serviceRow.remove();

                // Update nomor layanan
                updateServiceNumbers();

            }

        }
    );


    // ==================================================
    // UPDATE NOMOR LAYANAN
    // ==================================================

    function updateServiceNumbers() {

        if (!selectedServices) {

            return;

        }


        const serviceRows =
            selectedServices.querySelectorAll(
                ".selected-service"
            );


        // ------------------------------------------
        // UPDATE NOMOR
        // ------------------------------------------

        serviceRows.forEach(
            function (row, index) {

                const number =
                    row.querySelector(
                        ".service-number"
                    );


                if (number) {

                    number.textContent =
                        index + 1;

                }

            }
        );


        // ------------------------------------------
        // KALAU SEMUA LAYANAN DIHAPUS
        // TAMPILKAN LAGI PESAN
        // ------------------------------------------

        if (serviceRows.length === 0) {

            const existingEmpty =
                selectedServices.querySelector(
                    ".empty-service"
                );


            // Cegah tulisan dibuat dua kali
            if (!existingEmpty) {

                const emptyService =
                    document.createElement("p");

                emptyService.className =
                    "empty-service";

                emptyService.textContent =
                    "Silakan pilih layanan.";


                selectedServices.appendChild(
                    emptyService
                );

            }

        }

    }


    // ==================================================
    // VALIDASI FORM
    // ==================================================

    if (pickupForm) {

        pickupForm.addEventListener(
            "submit",
            function (event) {

                const alamat =
                    document.getElementById(
                        "alamat"
                    );

                const tanggal =
                    document.getElementById(
                        "tanggal"
                    );


                // ------------------------------------------
                // VALIDASI ALAMAT
                // ------------------------------------------

                if (
                    !alamat ||
                    alamat.value.trim() === ""
                ) {

                    event.preventDefault();

                    alert(
                        "Alamat wajib diisi."
                    );

                    return;

                }


                // ------------------------------------------
                // VALIDASI TANGGAL
                // ------------------------------------------

                if (
                    !tanggal ||
                    tanggal.value === ""
                ) {

                    event.preventDefault();

                    alert(
                        "Tanggal penjemputan wajib dipilih."
                    );

                    return;

                }


                // ------------------------------------------
                // VALIDASI LAYANAN
                // ------------------------------------------

                const layanan =
                    selectedServices
                        ? selectedServices.querySelectorAll(
                            'input[name="layanan[]"]'
                        )
                        : [];


                if (layanan.length === 0) {

                    event.preventDefault();

                    alert(
                        "Silakan pilih minimal satu layanan."
                    );

                    return;

                }


                // ------------------------------------------
                // SEMUA VALID
                // FORM AKAN LANJUT SUBMIT
                // ------------------------------------------

            }
        );

    }

});