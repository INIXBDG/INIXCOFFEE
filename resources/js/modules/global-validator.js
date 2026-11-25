const ButtonValidator = {
    /**
     * Mengunci elemen tombol dan menampilkan modal pemuatan SweetAlert.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     * @returns {boolean} - Mengembalikan false jika sistem dalam status terkunci.
     */
    lock: function(buttonElement) {
        // Validasi status penguncian
        if (buttonElement && buttonElement.getAttribute('data-is-submitting') === 'true') {
            return false;
        }

        // Penguncian elemen tombol (jika referensi tersedia)
        if (buttonElement) {
            buttonElement.setAttribute('data-is-submitting', 'true');
            buttonElement.setAttribute('disabled', 'disabled');
        }

        // Pemanggilan antarmuka modal SweetAlert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Memproses Data...',
                text: 'Mohon tunggu sejenak.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        } else {
            console.warn('Peringatan Sistem: Pustaka SweetAlert tidak ditemukan.');
        }

        return true;
    },

    /**
     * Membuka kunci elemen tombol dan menyembunyikan modal pemuatan SweetAlert.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     */
    unlock: function(buttonElement) {
        // Pelepasan penguncian elemen tombol
        if (buttonElement) {
            buttonElement.removeAttribute('data-is-submitting');
            buttonElement.removeAttribute('disabled');
        }

        // Penyembunyian antarmuka modal SweetAlert
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    }
};

// --- INTERSEPSI PENGIRIMAN FORMULIR GLOBAL ---
document.addEventListener('submit', function(e) {
    // 1. Abaikan jika peristiwa pengiriman telah dibatalkan
    if (e.defaultPrevented) return;

    // 2. Identifikasi elemen formulir dan kecualikan halaman/form login
    const formElement = e.target;

    // Abaikan & batalkan jika masih ada proses kompresi file async yang sedang berlangsung
    if (
        formElement.dataset.isCompressing === 'true' ||
        formElement.querySelector('input[type="file"][data-is-processing="true"]') ||
        (window.CompressValidator && window.CompressValidator.isProcessing(formElement))
    ) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return;
    }
    const formAction = (formElement.getAttribute('action') || '').toLowerCase();
    const isLoginForm =
        formElement.hasAttribute('data-no-loading') ||
        window.location.pathname.toLowerCase().includes('/login') ||
        formAction.endsWith('/login') ||
        Boolean(formElement.querySelector('input[name="username"]') && formElement.querySelector('input[name="password"]'));

    if (isLoginForm) return;

    const submitButton = e.submitter || formElement.querySelector('button[type="submit"], input[type="submit"]');

    if (submitButton) {
        // 3. Eksekusi fungsi penguncian konstan
        if (!ButtonValidator.lock(submitButton)) {
            // Hentikan proses jika status sedang terkunci
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }
});

window.ButtonValidator = ButtonValidator;
