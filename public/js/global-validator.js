const ButtonValidator = {
    /**
     * Mengunci elemen tombol dan mengubah antarmuka pengguna.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     * @returns {boolean} - Mengembalikan false jika tombol dalam status terkunci.
     */
    lock: function(buttonElement) {
        if (buttonElement.getAttribute('data-is-submitting') === 'true') {
            return false;
        }

        buttonElement.setAttribute('data-is-submitting', 'true');
        buttonElement.setAttribute('disabled', 'disabled');
        buttonElement.dataset.originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = 'Memproses...';

        return true;
    },

    /**
     * Membuka kunci elemen tombol dan mengembalikan teks operasional.
     * @param {HTMLElement} buttonElement - Referensi elemen DOM tombol.
     */
    unlock: function(buttonElement) {
        buttonElement.removeAttribute('data-is-submitting');
        buttonElement.removeAttribute('disabled');

        if (buttonElement.dataset.originalText) {
            buttonElement.innerHTML = buttonElement.dataset.originalText;
        }
    }
};
