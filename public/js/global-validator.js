const ButtonValidator = {
    /**
     * Mengunci elemen tombol dan mengubah antarmuka pengguna.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     * @returns {boolean} - Mengembalikan false jika tombol dalam status terkunci.
     */
    lock: function(buttonElement) {
        if (!buttonElement) return true; // Lewati jika tidak ada elemen tombol

        if (buttonElement.getAttribute('data-is-submitting') === 'true') {
            return false;
        }

        buttonElement.setAttribute('data-is-submitting', 'true');
        buttonElement.setAttribute('disabled', 'disabled');

        // Simpan teks asli dan ubah indikator visual
        buttonElement.dataset.originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = 'Memproses...';

        return true;
    },

    /**
     * Membuka kunci elemen tombol dan mengembalikan teks operasional.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     */
    unlock: function(buttonElement) {
        if (!buttonElement) return;

        buttonElement.removeAttribute('data-is-submitting');
        buttonElement.removeAttribute('disabled');

        if (buttonElement.dataset.originalText) {
            buttonElement.innerHTML = buttonElement.dataset.originalText;
        }
    }
};

// --- INTERSEPSI PENGIRIMAN FORMULIR GLOBAL ---
document.addEventListener('submit', function(e) {
    // 1. Abaikan jika peristiwa pengiriman telah dibatalkan oleh skrip validasi lain (contoh: validasi form HTML5 atau validasi jQuery)
    if (e.defaultPrevented) return;

    // 2. Identifikasi elemen formulir yang memicu peristiwa
    const formElement = e.target;

    // 3. Identifikasi tombol pemicu (submitter) di dalam formulir
    const submitButton = e.submitter || formElement.querySelector('button[type="submit"], input[type="submit"]');

    if (submitButton) {
        // 4. Eksekusi fungsi penguncian konstan
        if (!ButtonValidator.lock(submitButton)) {
            // Jika tombol sudah terkunci (pengiriman ganda terdeteksi), hentikan proses eksekusi formulir
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }
});
