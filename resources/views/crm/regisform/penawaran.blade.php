<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Surat Penawaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        input,
        select,
        textarea {
            width: 100%;
            margin: 2px 0;
            padding: 5px;
        }

        select[multiple] {
            height: 150px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            padding: 10px;
            margin: 10px 0;
        }

        #pelatihan-list, #fasilitas-list, #keuntungan-list {
            margin-top: 10px;
        }

        .pelatihan-row, .fasilitas-row, .keuntungan-row {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 10px;
        }

        .pelatihan-row input,
        .pelatihan-row select,
        .fasilitas-row input,
        .keuntungan-row input {
            flex: 1;
        }

        .readonly {
            background-color: #f0f0f0;
        }

        #preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        #preview-content {
            background: white;
            padding: 20px;
            max-width: 900px;
            overflow: auto;
        }

        #preview-content .container {
            max-width: 190mm;
            padding: 5mm;
            font-size: 12pt;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .logo img {
            width: 180px;
            height: auto;
            object-fit: contain;
        }

        .office-info {
            text-align: right;
            font-size: 10px;
            line-height: 14px;
            max-width: 200px;
        }

        .waktu {
            text-align: right;
            font-size: 10px;
            margin: 2px 0;
            line-height: 14px;
        }

        .lampiran {
            font-size: 10px;
            margin: 2px 0;
            line-height: 14px;
        }

        .lampiran p {
            display: flex;
            align-items: flex-start;
        }

        .lampiran p span.label {
            flex: 0 0 100px;
            text-align: left;
        }

        .lampiran p span.value {
            flex: 1;
            text-align: left;
        }

        .penerima {
            font-size: 10px;
            margin: 2px 0;
            line-height: 14px;
        }

        .deskripsi {
            font-size: 12px;
            margin: 5px 0;
            line-height: 16px;
        }

        .training-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .training-table th,
        .training-table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
            word-wrap: break-word;
            line-height: 16px;
        }

        .training-table th {
            background-color: #f2f2f2;
        }

        .terms {
            font-size: 12px;
            margin: 5px 0;
            line-height: 16px;
        }

        .closing {
            font-size: 12px;
            margin: 5px 0;
            line-height: 16px;
        }

        .closing p.contact-info {
            display: flex;
            align-items: flex-start;
        }

        .closing p.contact-info span.label {
            flex: 0 0 100px;
            text-align: left;
        }

        .closing p.contact-info span.value {
            flex: 1;
            text-align: left;
        }

        .signature {
            margin-top: 20px;
            text-align: left;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12pt;
                position: relative;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .container {
                max-width: 190mm;
                width: 100%;
                margin: 0;
                padding: 5mm;
                background: transparent;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .header-container {
                width: 180mm;
                margin: 0;
                padding: 2mm 0;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                page-break-after: avoid;
            }

            .content-container {
                margin-top: 40mm;
                page-break-before: auto;
            }

            .logo img {
                width: 180px;
            }

            .office-info {
                font-size: 9pt;
                line-height: 12pt;
                max-width: 70mm;
            }

            .waktu {
                text-align: right;
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 12pt;
                page-break-inside: avoid;
            }

            .lampiran {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 12pt;
                page-break-inside: avoid;
            }

            .lampiran p {
                display: flex;
                align-items: flex-start;
            }

            .lampiran p span.label {
                flex: 0 0 70mm;
                text-align: left;
            }

            .lampiran p span.value {
                flex: 1;
                text-align: left;
            }

            .penerima {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 12pt;
                page-break-inside: avoid;
            }

            .deskripsi {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 13pt;
                page-break-inside: avoid;
            }

            .terms {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 13pt;
                page-break-inside: avoid;
            }

            .closing {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 13pt;
                page-break-inside: avoid;
                page-break-before: auto;
            }

            .closing p.contact-info {
                display: flex;
                align-items: flex-start;
            }

            .closing p.contact-info span.label {
                flex: 0 0 70mm;
                text-align: left;
            }

            .closing p.contact-info span.value {
                flex: 1;
                text-align: left;
            }

            .training-table {
                width: 100%;
                page-break-inside: avoid;
                margin: 1mm 0;
            }

            .training-table th,
            .training-table td {
                font-size: 9pt;
                padding: 3pt 5pt;
                border: 1px solid #ccc;
                line-height: 12pt;
            }

            .signature {
                margin-top: 8mm;
                page-break-inside: avoid;
            }

            button {
                display: none;
            }

            img.background-image {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                z-index: -1 !important;
                opacity: 0.1 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: A4;
                margin: 5mm;
                background: white;
            }

            @page :not(:first) {
                .header-container {
                    display: none;
                }
            }
        }
    </style>
</head>

<body>
    <h2>Input Data Surat Penawaran</h2>
    <form id="penawaran-form">
        <h3>Data Surat</h3>
        <label>No Surat:</label>
        <input type="text" id="no-surat" placeholder="Contoh: 088/MKT-VN-INIX/BDG/VIII/2025" required>

        <label>Hal:</label>
        <input type="text" id="hal" value="Surat Penawaran Pelatihan">

        <label>Lampiran:</label>
        <input type="text" id="lampiran" value="" placeholder="Silabus, dll">

        <h3>Data Penerima (Klien)</h3>
        <label>Pilih Perusahaan:</label>
        <select id="perusahaan" required>
            <option value="">Pilih Perusahaan</option>
            @foreach ($perusahaan as $p)
                <option value="{{ $p->id }}" data-nama="{{ $p->nama_perusahaan }}">{{ $p->nama_perusahaan }}
                </option>
            @endforeach
        </select>

        <label>Nama Pimpinan/Perusahaan:</label>
        <input type="text" id="penerima" class="readonly" readonly>

        <h3>Deskripsi</h3>
        <label>Deskripsi Penawaran:</label>
        <textarea id="deskripsi" placeholder="Masukkan deskripsi penawaran"></textarea>

        <h3>Data Pelatihan</h3>
        <label>PPN (%):</label>
        <input type="number" id="ppn-rate" value="11" min="0" max="100" step="0.1" required>
        <label><input type="checkbox" id="include-ppn" checked> Termasuk PPN</label>
        <div id="pelatihan-list"></div>
        <button type="button" id="add-pelatihan">Tambah Pelatihan</button>

        <h3>Fasilitas dan Perlengkapan</h3>
        <div id="fasilitas-list"></div>
        <button type="button" id="add-fasilitas">Tambah Fasilitas</button>

        <h3>Keuntungan</h3>
        <div id="keuntungan-list"></div>
        <button type="button" id="add-keuntungan">Tambah Keuntungan</button>

        <h3>Syarat dan Ketentuan</h3>
        <label>Pilih Syarat (bisa lebih dari satu):</label>
        <select id="syarat-select" multiple>
            @foreach ($ketentuan as $ket)
                <option value="{{ $ket->id }}" data-content="{{ $ket->ketentuan }}">{{ $ket->ketentuan }}</option>
            @endforeach
        </select>

        <h3>Data Sales</h3>
        <label>Nama Sales:</label>
        <input type="text" id="nama-sales" class="readonly" value="{{ $sales->nama_lengkap }}" readonly>
        <label>Jabatan:</label>
        <input type="text" id="jabatan-sales" class="readonly" value="Account Executive" readonly>
        <label>Whatsapp:</label>
        <input type="text" id="wa-sales" value="{{ $sales->whatsapp }}" required>
        <label>Telepon:</label>
        <input type="text" id="telp-sales" value="{{ $sales->telepon }}" required>
        <label>Email:</label>
        <input type="text" id="email-sales" value="{{ $sales->email }}" required>

        <button type="button" id="preview-btn">Generate Preview</button>
    </form>

    <div id="preview-modal">
        <div id="preview-content"></div>
    </div>

    <script>
        const materiData = @json($materi);
        const deskripsiData = @json($deskripsi->deskripsi);
        const backgroundUrl = "{{ asset('assets/img/backgrounds/kop.png') }}";

        // Set default deskripsi from controller
        document.getElementById('deskripsi').value = deskripsiData || '';

        // Data konstan untuk fasilitas dan keuntungan
        const fasilitasKonstan = [
            "Instruktur",
            "PC / Laptop",
            "Ruang Meeting",
            "Perlengkapan Alat Tulis",
            "E-Modul",
            "E-Lab",
            "Makan Siang",
            "Antar-jemput dari Kantor Inixindo ke hotel atau penginapan (radius 5 km)"
        ];
        const keuntunganKonstan = [
            "E-Sertifikat dari Inixindo",
            "Souvenir",
            "Hasil pre-test dan post-test (sesuai kebutuhan)",
            "Konsultasi dan diskusi gratis dengan instruktur",
            "Pembahasan studi kasus",
            "Akses Webinar Gratis"
        ];

        // Fungsi untuk format Rupiah
        function formatRupiah(angka) {
            if (!angka || isNaN(angka)) return 'Rp 0,-';
            let number_string = angka.toString().replace(/[^0-9]/g, '');
            let sisa = number_string.length % 3;
            let rupiah = number_string.substr(0, sisa);
            let ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return 'Rp ' + rupiah + ',-';
        }

        // Fungsi untuk menghitung harga dengan PPN
        function calculatePriceWithPPN(basePrice, ppnRate, includePPN) {
            if (!basePrice || isNaN(basePrice)) return { total: 0, ppnAmount: 0 };
            const price = parseFloat(basePrice);
            if (!includePPN) return { total: price, ppnAmount: 0 };
            const ppn = (price * ppnRate) / 100;
            return { total: price + ppn, ppnAmount: ppn };
        }

        // Fungsi untuk menghitung tanggal akhir
        function calculateEndDate(startDate, duration) {
            if (!startDate || !duration) return '';
            const start = new Date(startDate);
            const days = parseInt(duration) || 0;
            const end = new Date(start);
            end.setDate(start.getDate() + days - 1);
            return `${start.getDate()} - ${end.getDate()} ${end.toLocaleString('id-ID', { month: 'long', year: 'numeric' })}`;
        }

        // Event listener untuk memilih perusahaan
        document.getElementById('perusahaan').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const nama = selectedOption.getAttribute('data-nama') || '';
            document.getElementById('penerima').value = nama;
        });

        // Fungsi untuk menambahkan baris pelatihan
        document.getElementById('add-pelatihan').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'pelatihan-row';
            row.innerHTML = `
                <select class="materi-pelatihan" required>
                    <option value="">Pilih Materi</option>
                    ${materiData.map(m => `<option value="${m.id}" data-nama="${m.nama_materi}" data-durasi="${m.durasi}">${m.nama_materi}</option>`).join('')}
                </select>
                <input type="text" class="durasi-pelatihan" readonly>
                <input type="date" class="tanggal-awal-pelatihan" required>
                <input type="text" class="tanggal-pelatihan" readonly>
                <input type="text" class="harga-pelatihan" placeholder="Masukkan harga (contoh: 10000000)" required>
                <input type="text" class="ppn-amount" readonly placeholder="PPN Amount">
                <button type="button" onclick="this.parentElement.remove()">Hapus</button>
            `;
            document.getElementById('pelatihan-list').appendChild(row);

            // Tambahkan event listener untuk materi
            const materiSelect = row.querySelector('.materi-pelatihan');
            const durasiInput = row.querySelector('.durasi-pelatihan');
            const tanggalAwalInput = row.querySelector('.tanggal-awal-pelatihan');
            const tanggalPelatihanInput = row.querySelector('.tanggal-pelatihan');
            const hargaInput = row.querySelector('.harga-pelatihan');
            const ppnAmountInput = row.querySelector('.ppn-amount');

            function updatePPN() {
                const ppnRate = parseFloat(document.getElementById('ppn-rate').value) || 0;
                const includePPN = document.getElementById('include-ppn').checked;
                const price = parseFloat(hargaInput.value) || 0;
                const { ppnAmount } = calculatePriceWithPPN(price, ppnRate, includePPN);
                ppnAmountInput.value = formatRupiah(ppnAmount);
            }

            materiSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const durasi = selectedOption.getAttribute('data-durasi') || '';
                durasiInput.value = durasi ? `${durasi} Hari` : '';
                if (tanggalAwalInput.value) {
                    tanggalPelatihanInput.value = calculateEndDate(tanggalAwalInput.value, durasi);
                }
            });

            tanggalAwalInput.addEventListener('change', function() {
                const durasi = materiSelect.options[materiSelect.selectedIndex]?.getAttribute(
                    'data-durasi') || '';
                tanggalPelatihanInput.value = calculateEndDate(this.value, durasi);
            });

            hargaInput.addEventListener('input', updatePPN);
            document.getElementById('ppn-rate').addEventListener('input', updatePPN);
            document.getElementById('include-ppn').addEventListener('change', updatePPN);

            // Mencegah input karakter non-angka pada harga
            hargaInput.addEventListener('keypress', function(e) {
                const charCode = e.which ? e.which : e.keyCode;
                if (charCode < 48 || charCode > 57) {
                    e.preventDefault();
                }
            });
        });

        // Fungsi untuk menambahkan baris fasilitas
        function addFasilitasRow(value = '') {
            const row = document.createElement('div');
            row.className = 'fasilitas-row';
            row.innerHTML = `
                <input type="text" class="fasilitas-item" placeholder="Masukkan fasilitas" value="${value}" required>
                <button type="button" onclick="this.parentElement.remove()">Hapus</button>
            `;
            document.getElementById('fasilitas-list').appendChild(row);
        }

        // Fungsi untuk menambahkan baris keuntungan
        function addKeuntunganRow(value = '') {
            const row = document.createElement('div');
            row.className = 'keuntungan-row';
            row.innerHTML = `
                <input type="text" class="keuntungan-item" placeholder="Masukkan keuntungan" value="${value}" required>
                <button type="button" onclick="this.parentElement.remove()">Hapus</button>
            `;
            document.getElementById('keuntungan-list').appendChild(row);
        }

        // Event listener untuk tombol tambah fasilitas
        document.getElementById('add-fasilitas').addEventListener('click', () => {
            addFasilitasRow();
        });

        // Event listener untuk tombol tambah keuntungan
        document.getElementById('add-keuntungan').addEventListener('click', () => {
            addKeuntunganRow();
        });

        // Pre-populate fasilitas dan keuntungan dengan data konstan
        fasilitasKonstan.forEach(item => addFasilitasRow(item));
        keuntunganKonstan.forEach(item => addKeuntunganRow(item));

        // Tambahkan satu row default untuk pelatihan
        document.getElementById('add-pelatihan').click();

        // Deskripsi konstan
        function getDeskripsi() {
            const penerima = document.getElementById('penerima').value || '';
            const deskripsi = document.getElementById('deskripsi').value || '';
            const constantPart = `<br><br>Kami hadir untuk mendukung pengembangan sumber daya manusia di bidang teknologi informasi. Untuk mendukung program peningkatan pengetahuan, kompetensi, dan keahlian sumber daya manusia di ${penerima}, kami menawarkan pelatihan berikut:`;
            return deskripsi + constantPart;
        }

        document.getElementById('preview-btn').addEventListener('click', () => {
            const noSurat = document.getElementById('no-surat').value || '';
            const hal = document.getElementById('hal').value || '';
            const lampiran = document.getElementById('lampiran').value || '';
            const penerima = document.getElementById('penerima').value || '';
            const namaSales = document.getElementById('nama-sales').value || '';
            const jabatanSales = document.getElementById('jabatan-sales').value || '';
            const waSales = document.getElementById('wa-sales').value || '';
            const telpSales = document.getElementById('telp-sales').value || '';
            const emailSales = document.getElementById('email-sales').value || '';
            const ppnRate = parseFloat(document.getElementById('ppn-rate').value) || 0;
            const includePPN = document.getElementById('include-ppn').checked;

            // Validasi input
            if (!noSurat || !penerima) {
                alert('Harap isi No Surat dan pilih Perusahaan.');
                return;
            }

            // Proses pelatihan
            const pelatihanRows = document.querySelectorAll('.pelatihan-row');
            let pelatihanHTML = '';
            let isPelatihanValid = true;
            pelatihanRows.forEach(row => {
                const materiSelect = row.querySelector('.materi-pelatihan');
                const materi = materiSelect.options[materiSelect.selectedIndex]?.getAttribute(
                    'data-nama') || '';
                const durasi = row.querySelector('.durasi-pelatihan').value || '';
                const tanggal = row.querySelector('.tanggal-pelatihan').value || '';
                const harga = parseFloat(row.querySelector('.harga-pelatihan').value) || 0;
                const { total, ppnAmount } = calculatePriceWithPPN(harga, ppnRate, includePPN);
                if (!materi || !durasi || !tanggal || !harga) {
                    isPelatihanValid = false;
                    return;
                }
                pelatihanHTML += `
                    <tr>
                        <td>${materi}</td>
                        <td>${durasi}</td>
                        <td>${tanggal}</td>
                        <td>${formatRupiah(total)}</td>
                    </tr>
                `;
            });

            if (!isPelatihanValid) {
                alert('Harap lengkapi semua data pelatihan.');
                return;
            }

            // Proses fasilitas
            const fasilitasRows = document.querySelectorAll('.fasilitas-row');
            let fasilitasHTML = '';
            let isFasilitasValid = true;
            fasilitasRows.forEach(row => {
                const fasilitas = row.querySelector('.fasilitas-item').value || '';
                if (!fasilitas) {
                    isFasilitasValid = false;
                    return;
                }
                fasilitasHTML += `<li>${fasilitas}</li>`;
            });

            if (!isFasilitasValid) {
                alert('Harap lengkapi semua data fasilitas.');
                return;
            }

            // Proses keuntungan
            const keuntunganRows = document.querySelectorAll('.keuntungan-row');
            let keuntunganHTML = '';
            let isKeuntunganValid = true;
            keuntunganRows.forEach(row => {
                const keuntungan = row.querySelector('.keuntungan-item').value || '';
                if (!keuntungan) {
                    isKeuntunganValid = false;
                    return;
                }
                keuntunganHTML += `<li>${keuntungan}</li>`;
            });

            if (!isKeuntunganValid) {
                alert('Harap lengkapi semua data keuntungan.');
                return;
            }

            // Proses syarat
            const select = document.getElementById('syarat-select');
            const selectedOptions = Array.from(select.selectedOptions);
            let syaratList = '';
            if (selectedOptions.length === 0) {
                syaratList =
                    `<li>Harga penawaran di atas sudah termasuk PPN ${ppnRate}%.</li><li>Form pendaftaran harus dikirim paling lambat 14 hari sebelum pelaksanaan pelatihan.</li><li>Pelatihan berlangsung pukul 09.00 hingga selesai.</li><li>Pelatihan diselenggarakan di Kantor Inixindo Bandung, Jalan Cipaganti No 95, Bandung.</li>`;
            } else {
                selectedOptions.forEach(option => {
                    const content = option.dataset.content || '';
                    syaratList += `<li>${content}</li>`;
                });
            }

            // Tanggal saat ini
            const tanggalSekarang = new Date().toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Proses lampiran (hanya tampilkan jika tidak kosong)
            const lampiranHTML = lampiran ? `
                <p><span class="label">Lampiran</span><span class="value">: ${lampiran}</span></p>
            ` : '';

            const previewHTML = `
                <div class="container">
                    <img src="${backgroundUrl}" class="background-image" alt="Background">
                    <div class="header-container">
                        <div class="logo"><img src="{{ asset('assets/img/inix.png') }}" alt="Logo Inixindo"></div>
                        <div class="office-info text-sm text-gray-700 text-right">
                            <p class="font-bold">INIXINDO BANDUNG</p>
                            <p>Jl Cipaganti No 95 Bandung</p>
                            <p>Telepon : 022 2032 831</p>
                            <p>Whatsapp : ${waSales}</p>
                            <p><a href="http://www.inixindobdg.co.id" style="text-decoration:none; color: black;">www.inixindobdg.co.id</a></p>
                        </div>
                    </div>
                    <div class="content-container">
                        <div class="waktu text-sm text-gray-700 text-right">
                            <p>Bandung, ${tanggalSekarang}</p>
                        </div>

                        <div class="lampiran text-sm text-gray-700">
                            <p><span class="label">No</span><span class="value">: ${noSurat}</span></p>
                            <p><span class="label">Hal</span><span class="value">: ${hal}</span></p>
                            ${lampiranHTML}
                        </div>

                        <div class="penerima text-sm text-gray-700">
                            <p>Kepada Yth.</p>
                            <p>Pimpinan ${penerima}</p>
                            <p>Di Tempat</p>
                        </div>

                        <div class="deskripsi text-sm text-gray-800">
                            <p class="font-semibold">Dengan Hormat,</p>
                            <p>${getDeskripsi()}</p>
                            <table class="training-table">
                                <thead>
                                    <tr>
                                        <th>Materi Pelatihan</th>
                                        <th>Durasi Pelatihan</th>
                                        <th>Tanggal Pelatihan</th>
                                        <th>Harga Penawaran Per Peserta ${includePPN ? `(Termasuk PPN ${ppnRate}%)` : ''}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${pelatihanHTML}
                                </tbody>
                            </table>
                        </div>

                        <div class="terms text-sm text-gray-800">
                            <p class="font-semibold" style="font-weight:bold;">Syarat dan Ketentuan:</p>
                            <ul class="list-disc pl-6">${syaratList}</ul>
                            <p class="font-semibold mt-2" style="font-weight:bold;">Fasilitas dan Perlengkapan yang Kami Sediakan:</p>
                            <ol class="list-decimal pl-6">${fasilitasHTML}</ol>
                            <p class="font-semibold mt-2" style="font-weight:bold;">Keuntungan yang Akan Anda Dapatkan:</p>
                            <ul class="list-disc pl-6">${keuntunganHTML}</ul>
                        </div>

                        <div class="closing text-sm text-gray-700">
                            <p>Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat bekerja sama dengan Bapak/Ibu.</p>
                            <p style="margin-bottom:6mm;"><strong>Untuk informasi lebih lanjut dan penyesuaian harga maupun fasilitas, mohon hubungi:</strong></p>
                            <p class="contact-info"><span class="label">Whatsapp</span><span class="value">: ${waSales}</span></p>
                            <p class="contact-info"><span class="label">Telepon</span><span class="value">: ${telpSales}</span></p>
                            <p class="contact-info"><span class="label">Email</span><span class="value"><a href="mailto:${emailSales}" style="text-decoration:none; color: black;">: ${emailSales}</a></span></p>
                            <br />
                            <p class="mt-2">Hormat kami,</p>
                            <p class="font-bold" style="padding-bottom:4%;">INIXINDO BANDUNG</p>
                            <p class="signature"><strong>${namaSales}</strong></p>
                            <p>${jabatanSales},</p> 
                            <p>Inixindo Bandung</p>
                        </div>
                    </div>
                </div>
            `;

            // Langsung panggil printPreview dengan konten yang dihasilkan
            printPreview(previewHTML);
        });

        function printPreview(content) {
            const printWindow = window.open('', '', 'height=600, width=900');
            printWindow.document.write('<html><head><title>Print Preview</title>');
            printWindow.document.write('<style>');
            printWindow.document.write(`
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-size: 16pt; 
                    font-family: Arial, sans-serif; 
                    position: relative; 
                    -webkit-print-color-adjust: exact !important; 
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
                .container { 
                    max-width: 190mm; 
                    width: 100%; 
                    margin: 0; 
                    padding: 5mm; 
                    background: transparent; 
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .header-container {
                    width: 183mm;
                    margin: 0;
                    padding: 2mm 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    page-break-after: avoid;
                }
                .content-container { page-break-before: auto; }
                .logo img { width: 220px; }
                .office-info { font-size: 9pt; line-height: 6pt; max-width: 70mm; }
                .waktu { text-align: right; font-size: 9pt; margin: 1mm 25px; line-height: 5pt; page-break-inside: avoid; }
                .lampiran { font-size: 9pt; margin: 0 0; line-height: 5pt; page-break-inside: avoid; }
                .lampiran p { display: flex; align-items: flex-start; }
                .lampiran p span.label { flex: 0 0 20mm; text-align: left; }
                .lampiran p span.value { flex: 1; text-align: left; }
                .penerima { font-size: 9pt; margin-top: 8mm; line-height: 5pt; page-break-inside: avoid; }
                .deskripsi { font-size: 9pt; margin: 6mm 0; line-height: 13pt; text-align: justify; page-break-inside: avoid; }
                .terms { font-size: 9pt; margin: 6mm 0; line-height: 13pt; break-inside: avoid; page-break-inside: avoid; display: block; }
                .closing { font-size: 9pt; margin: 1mm 0; line-height: 5pt; break-inside: avoid; page-break-inside: avoid; page-break-before: always; display: block; }
                .closing p.contact-info { display: flex; align-items: flex-start; }
                .closing p.contact-info span.label { flex: 0 0 20mm; text-align: left; }
                .closing p.contact-info span.value { flex: 1; text-align: left; }
                .training-table { width: 100%; page-break-inside: avoid; margin: 1mm 0; border-collapse: collapse; }
                .training-table th, .training-table td { font-size: 9pt; padding: 3pt 5pt; border: 1px solid #ccc; text-align: left; word-wrap: break-word; line-height: 12pt; }
                .training-table th { background-color: #f2f2f2; }
                .list-disc { list-style-type: disc; padding-left: 15px; }
                .list-decimal { list-style-type: decimal; padding-left: 30px; }
                .signature { margin-top: 13mm; page-break-inside: avoid; }
                button { display: none; }
                img.background-image {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 206mm !important;
                    height: 288mm !important;
                    z-index: -1 !important;
                    // opacity: 0.1 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                @page { size: A4; margin: 5mm; background: white; }
                @page :not(:first) {
                    .header-container {
                        display: none;
                    }
                }
            `);
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        }
    </script>
</body>

</html>