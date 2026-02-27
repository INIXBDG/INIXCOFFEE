<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Surat Penawaran</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        #pelatihan-list,
        #fasilitas-list,
        #keuntungan-list {
            margin-top: 10px;
        }

        .pelatihan-row,
        .fasilitas-row,
        .keuntungan-row {
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

        .signature img {
            width: 100px;
            height: auto;
            object-fit: contain;
        }

        .vendor-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            justify-content: space-between;
        }

        .vendor-images img {
            width: 80px;
            height: auto;
            object-fit: contain;
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
                page-break-inside: auto;
            }

            .closing {
                font-size: 9pt;
                margin: 1mm 0;
                line-height: 13pt;
                page-break-inside: avoid;
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

            .signature img {
                width: 30mm;
                height: auto;
                object-fit: contain;
            }

            .vendor-images {
                display: flex;
                flex-wrap: wrap;
                gap: 5mm;
                margin-top: 16mm;
                justify-content: space-between;
                page-break-inside: avoid;
            }

            .vendor-images img {
                width: 30mm;
                height: auto;
                object-fit: contain;
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
    @php
        $jabatanUser = auth()->user()->jabatan;
        $isAdmin = in_array($jabatanUser, ['Adm Sales', 'SPV Sales']);
    @endphp
    <h2>Input Data Surat Penawaran</h2>
    <form id="penawaran-form">
        <h3>Data Surat</h3>
        <label>No Surat:</label>
        <input type="text" id="no-surat" value="{{ $no }}" required>
        <label>Hal:</label>
        <input type="text" id="hal" value="Surat Penawaran Pelatihan">
        <label>Lampiran:</label>
        <input type="text" id="lampiran" value="" placeholder="Silabus, dll">

        <h3>Data Penerima (Klien)</h3>
        <label>Pilih Perusahaan:</label>
        <select id="perusahaan" class="select2-init" required>
            <option value="">Pilih Perusahaan</option>
            @if ($isAdmin)
                @foreach ($perusahaans as $p)
                    <option value="{{ $p->id }}" data-nama="{{ $p->nama_perusahaan }}">{{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            @else
                @foreach ($perusahaan as $p)
                    <option value="{{ $p->id }}" data-nama="{{ $p->nama_perusahaan }}">{{ $p->nama_perusahaan }}
                    </option>
                @endforeach
            @endif
        </select>

        <label>Nama Pimpinan/Perusahaan:</label>
        <input type="text" id="penerima">

        <h3>Deskripsi</h3>
        <label>Deskripsi Penawaran:</label>
        <textarea id="deskripsi" placeholder="Masukkan deskripsi penawaran"></textarea>

        <h3>Data Pelatihan</h3>
        <label>PPN (%):</label>
        <input type="number" id="ppn-rate" value="11" min="0" max="100" step="0.1" required>
        <label><input type="checkbox" id="include-ppn" checked> Termasuk PPN</label>
        <div id="pelatihan-list"></div>
        <select name="listexam" id="exam" style="display: none">
            @foreach ($exam as $item)
                <option value="{{ $item->nama_exam }}">{{ $item->nama_exam }}</option>
            @endforeach
        </select>
        <button type="button" id="add-pelatihan">Tambah Pelatihan</button>

        <h3>Fasilitas dan Perlengkapan</h3>
        <div id="fasilitas-list"></div>
        <button type="button" id="add-fasilitas">Tambah Fasilitas</button>

        <h3>Keuntungan</h3>
        <div id="keuntungan-list"></div>
        <button type="button" id="add-keuntungan">Tambah Keuntungan</button>

        <h3>Syarat dan Ketentuan</h3>
        <label>Pilih Syarat dan Ketentuan:</label>
        <div id="syarat-checkbox-list"
            style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow-y: auto;">
            @foreach ($ketentuan as $ket)
                <div style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 5px;">
                    <input type="checkbox" class="syarat-checkbox" id="syarat-{{ $ket->id }}"
                        data-content="{{ $ket->ketentuan }}" style="width: auto; margin-top: 4px;">
                    <label for="syarat-{{ $ket->id }}"
                        style="cursor: pointer; font-size: 13px;">{{ $ket->ketentuan }}</label>
                </div>
            @endforeach
        </div>

        <h3>Data Sales</h3>
        @php
            $jabatanUser = auth()->user()->jabatan;
            $isAdmin = in_array($jabatanUser, ['Adm Sales', 'SPV Sales']);
        @endphp

        @if ($isAdmin)
            <label>Email:</label>
            <select id="email-sales" required>
                <option value="">-- Pilih Email --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->email }}" data-nama="{{ $user->nama_lengkap }}"
                        data-jabatan="{{ $user->jabatan }}" data-wa="{{ $user->whatsapp }}"
                        data-telp="{{ $user->telepon }}">
                        {{ $user->email }}
                    </option>
                @endforeach
            </select>

            <label>Nama Sales:</label>
            <input type="text" id="nama-sales" class="readonly" readonly>

            <label>Jabatan:</label>
            <input type="text" id="jabatan-sales" class="readonly" readonly>

            <label>Whatsapp:</label>
            <input type="text" id="wa-sales" required>

            <label>Telepon:</label>
            <input type="text" id="telp-sales" required>
        @else
            <label>Nama Sales:</label>
            <input type="text" id="nama-sales" class="readonly" value="{{ $sales->nama_lengkap }}" readonly>

            <label>Jabatan:</label>
            <input type="text" id="jabatan-sales" class="readonly"
                value="{{ $sales->jabatan ?? 'Account Executive' }}" readonly>

            <label>Whatsapp:</label>
            <input type="text" id="wa-sales" value="{{ $sales->whatsapp }}" required>

            <label>Telepon:</label>
            <input type="text" id="telp-sales" value="{{ $sales->telepon }}" required>

            <label>Email:</label>
            <input type="text" id="email-sales" value="{{ $sales->email }}" readonly>
        @endif


        <button type="button" id="preview-btn">Generate PDF</button>
        <button type="button" id="download-word">Generate WORD</button>
    </form>

    <div id="preview-modal">
        <div id="preview-content"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#perusahaan').select2({
                placeholder: "Pilih Perusahaan",
                allowClear: true,
                width: '100%'
            });

            $('#perusahaan').on('select2:select', function (e) {
                const data = e.params.data.element;
                const nama = data.getAttribute('data-nama') || '';
                document.getElementById('penerima').value = nama;
            });
        });

        const {
            jsPDF
        } = window.jspdf;
        const materiData = @json($materi);
        const deskripsiData = @json($deskripsi->deskripsi);
        const backgroundUrl = "{{ asset('assets/img/backgrounds/kop.png') }}";
        const signatureUrl = "{{ asset('storage/ttd/' . (Auth::user()->karyawan->ttd ?? '')) }}";

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
            "Makan Siang dan Coffee Break",
            "Antar-jemput dari Kantor Inixindo ke hotel atau penginapan (radius 5 km)"
        ];
        const keuntunganKonstan = [
            "E-Sertifikat dari Inixindo",
            "Souvenir",
            "Hasil pre-test dan post-test (sesuai kebutuhan)",
            "Konsultasi pasca pelatihan",
            "Pembahasan studi kasus",
            "Akses Webinar Gratis"
        ];

        // Placeholder untuk vendor images
        const vendorImages = [
            "{{ asset('assets/img/vendor/aws.png') }}",
            "{{ asset('assets/img/vendor/bnsp.png') }}",
            "{{ asset('assets/img/vendor/cisco.png') }}",
            "{{ asset('assets/img/vendor/eccouncil.png') }}",
            "{{ asset('assets/img/vendor/epi.png') }}",
            "{{ asset('assets/img/vendor/itrain.png') }}",
            "{{ asset('assets/img/vendor/microsoft.png') }}",
            "{{ asset('assets/img/vendor/mikrotik.png') }}",
            "{{ asset('assets/img/vendor/pearsonvue.png') }}",
            "{{ asset('assets/img/vendor/redhat.png') }}",
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
            if (!basePrice || isNaN(basePrice)) return {
                total: 0,
                ppnAmount: 0
            };
            const price = parseFloat(basePrice);
            if (!includePPN) return {
                total: price,
                ppnAmount: 0
            };
            const ppn = (price * ppnRate) / 100;
            return {
                total: price + ppn,
                ppnAmount: ppn + price
            };
        }

        // Fungsi untuk menghitung tanggal akhir
        function calculateEndDate(startDate, duration) {
            if (!startDate || !duration || isNaN(duration) || duration <= 0) return '';
            try {
                const start = new Date(startDate);
                if (isNaN(start.getTime())) return ''; // Invalid date
                const days = parseInt(duration, 10);
                const end = new Date(start);
                end.setDate(start.getDate() + days - 1);
                return `${start.getDate()} - ${end.toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
            } catch (e) {
                console.error('Error calculating end date:', e);
                return '';
            }
        }

        // Event listener untuk memilih perusahaan
        document.getElementById('perusahaan').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const nama = selectedOption.getAttribute('data-nama') || '';
            document.getElementById('penerima').value = nama;
        });

        function updateFasilitasDanKeuntunganBasedOnMetode() {
            const metodeRows = document.querySelectorAll('.pelatihan-row .metode-select');
            let hasOnline = false;

            metodeRows.forEach(select => {
                if (select.value === 'Online') {
                    hasOnline = true;
                }
            });

            document.querySelectorAll('.fasilitas-row').forEach(row => row.remove());
            document.querySelectorAll('.keuntungan-row').forEach(row => row.remove());

            fasilitasKonstan.forEach(item => {
                if (hasOnline && !['Instruktur', 'E-Modul', 'E-Lab'].includes(item)) {
                    return;
                }
                addFasilitasRow(item);
            });

            keuntunganKonstan.forEach(item => {
                // Hilangkan Souvenir jika ada kelas Online
                if (hasOnline && item.includes('Souvenir')) {
                    return;
                }
                addKeuntunganRow(item);
            });
        }

        // Fungsi untuk menambahkan baris pelatihan
        document.getElementById('add-pelatihan').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'pelatihan-row';

            const examOptions = document.getElementById('exam').innerHTML;

          row.innerHTML = `
                <div style="display: flex; gap: 5px;">
                    <select class="materi-select" style="width: 30%;">
                        <option value="">-- Template Materi --</option>
                        ${materiData.map(m => `<option value="${m.id}" data-nama="${m.nama_materi}" data-durasi="${m.durasi}">${m.nama_materi}</option>`).join('')}
                    </select>
                    <input type="text" class="materi-text" placeholder="Nama Materi" required style="width: 70%;">
                </div>

                <div style="display: flex; gap: 5px; margin-top: 5px;">
                    <select class="exam-select" style="width: 100%;">
                        <option value="-">-- Tanpa Exam --</option>
                        ${examOptions}
                    </select>
                    <input type="text" class="harga-exam" placeholder="Harga Exam (Rp)" style="width: 45%;" inputmode="numeric">
                </div>

                <div style="display: flex; gap: 5px; margin-top: 8px; align-items: center;">
                    <select class="metode-select" style="width: 140px;" required>
                        <option value="">-- Metode --</option>
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                        <option value="Inhouse">Inhouse</option>
                    </select>
                    <input type="number" class="durasi-pelatihan" placeholder="Durasi" min="1" required style="width: 80px;">
                    <input type="date" class="tanggal-awal-pelatihan" required style="flex: 1;">
                </div>

                <input type="text" class="tanggal-pelatihan" readonly placeholder="Tanggal Akhir Otomatis" style="margin-top: 5px;">
                <input type="text" class="harga-pelatihan" placeholder="Harga (Rp)" required style="margin-top: 5px;">
                <input type="text" class="ppn-amount" readonly placeholder="PPN Amount" style="margin-top: 5px;">
                <button type="button" onclick="this.parentElement.remove()" style="background: #ff4d4d; color: white; border: none; cursor: pointer;">Hapus Baris</button>
            `;

            document.getElementById('pelatihan-list').appendChild(row);

            $(row).find('.materi-select').select2({
                placeholder: "Pilih Materi",
                width: '50%'
            });

            $(row).find('.exam-select').select2({
                placeholder: "Pilih Exam",
                width: '50%'
            });

            $(row).find('.materi-select').on('select2:select', function(e) {
                const data = e.params.data.element;
                const nama = data.getAttribute('data-nama') || '';
                const durasi = data.getAttribute('data-durasi') || '';

                const rowElement = $(this).closest('.pelatihan-row');
                rowElement.find('.materi-text').val(nama);
                rowElement.find('.durasi-pelatihan').val(durasi).trigger('input');
            });

            // Seleksi elemen
            const materiSelect = row.querySelector('.materi-select');
            const materiTextInput = row.querySelector(
                '.materi-text'); // Input baru untuk nama materi yg di customize
            const durasiInput = row.querySelector('.durasi-pelatihan');
            const tanggalAwalInput = row.querySelector('.tanggal-awal-pelatihan');
            const tanggalPelatihanInput = row.querySelector('.tanggal-pelatihan');
            const hargaInput = row.querySelector('.harga-pelatihan');
            const ppnAmountInput = row.querySelector('.ppn-amount');
            const hargaExamInput = row.querySelector('.harga-exam');

            const metodeSelect = row.querySelector('.metode-select');

            metodeSelect.addEventListener('change', () => {
                updateFasilitasDanKeuntunganBasedOnMetode();
            });

            function updatePPN() {
                const ppnRate = parseFloat(document.getElementById('ppn-rate').value) || 0;
                const includePPN = document.getElementById('include-ppn').checked;

                const pricePelatihan = parseFloat(hargaInput.value) || 0;
                const priceExam = parseFloat(hargaExamInput.value.replace(/[^0-9]/g, '')) || 0;
                const totalBeforePPN = pricePelatihan + priceExam;

                const {
                    total,
                    ppnAmount
                } = calculatePriceWithPPN(totalBeforePPN, ppnRate, includePPN);

                ppnAmountInput.value = formatRupiah(ppnAmount);
            }

            hargaInput.addEventListener('input', updatePPN);
            document.getElementById('ppn-rate').addEventListener('input', updatePPN);
            document.getElementById('include-ppn').addEventListener('change', updatePPN);

            function updateEndDate() {
                const durasi = parseInt(durasiInput.value) || 0;
                const startDate = tanggalAwalInput.value;

                if (durasi > 0 && startDate) {
                    const endDate = calculateEndDate(startDate, durasi);
                    tanggalPelatihanInput.value = endDate || 'Tanggal tidak valid';
                } else {
                    tanggalPelatihanInput.value = '';
                }
            }

            materiSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const nama = selectedOption.getAttribute('data-nama') || '';
                const durasi = selectedOption.getAttribute('data-durasi') || '';

                if (nama) materiTextInput.value = nama;
                if (durasi) durasiInput.value = durasi;

                updateEndDate();
            });

            durasiInput.addEventListener('input', updateEndDate);

            tanggalAwalInput.addEventListener('change', updateEndDate);

            hargaInput.addEventListener('input', updatePPN);
            document.getElementById('ppn-rate').addEventListener('input', updatePPN);
            document.getElementById('include-ppn').addEventListener('change', updatePPN);

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

        document.addEventListener('DOMContentLoaded', function() {
            const emailSelect = document.getElementById('email-sales');

            if (emailSelect) {
                emailSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];

                    document.getElementById('nama-sales').value = selectedOption.dataset.nama || '';
                    document.getElementById('jabatan-sales').value = selectedOption.dataset.jabatan || '';
                    document.getElementById('wa-sales').value = selectedOption.dataset.wa || '';
                    document.getElementById('telp-sales').value = selectedOption.dataset.telp || '';
                });
            }
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
            const perusahaan = document.getElementById('perusahaan').options[document.getElementById('perusahaan').selectedIndex].getAttribute('data-nama') || '';
            const constantPart =
                `<br><br>Kami mengundang Bapak/Ibu ${perusahaan}, untuk memperbarui pengetahuan dan keterampilan dalam bidang teknologi informasi, digitalisasi, serta pengembangan soft skill lainnya, melalui program-program pelatihan yang diselenggarakan oleh Inixindo Bandung. Kami menawarkan pelatihan sebagai berikut:`;
            return deskripsi + constantPart;
        }

        document.getElementById('preview-btn').addEventListener('click', async () => {
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
            let hasOnline = false;

            pelatihanRows.forEach(row => {
                const materi = row.querySelector('.materi-text').value || '';
                const metode = row.querySelector('.metode-select').value || '-';
                const examName = row.querySelector('.exam-select').value || '-';
                const hargaExamRaw = row.querySelector('.harga-exam').value.replace(/[^0-9]/g, '') ||
                    '0';
                const hargaExam = parseInt(hargaExamRaw) || 0;
                const examDisplay = examName === '-' ? '-' :
                    `${examName} ${hargaExam > 0 ? `(${formatRupiah(hargaExam)})` : ''}`;

                const durasiVal = row.querySelector('.durasi-pelatihan').value;
                const durasi = durasiVal ? `${durasiVal} Hari` : '';
                const tanggal = row.querySelector('.tanggal-pelatihan').value || '';

                const hargaPelatihan = parseFloat(row.querySelector('.harga-pelatihan').value) || 0;
                const totalBeforePPN = hargaPelatihan + hargaExam;
                if (metode === 'Online') {
                    hasOnline = true;
                }

                const {
                    total
                } = calculatePriceWithPPN(totalBeforePPN, ppnRate, includePPN);

                if (!materi || !durasi || !tanggal || !hargaPelatihan) {
                    isPelatihanValid = false;
                    return;
                }

                pelatihanHTML += `
                    <tr>
                        <td>${materi}</td>
                        <td>${metode}</td>
                        <td>${examDisplay}</td>
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

            const selectedCheckboxes = document.querySelectorAll('.syarat-checkbox:checked');
            let syaratList = '';

            if (selectedCheckboxes.length === 0) {
                syaratList = `
                    <li>Harga penawaran di atas sudah termasuk PPN ${ppnRate}%.</li>
                    <li>Form pendaftaran harus dikirim paling lambat 14 hari sebelum pelaksanaan pelatihan.</li>
                    <li>Pelatihan berlangsung pukul 09.00 hingga selesai.</li>
                    <li>Pelatihan diselenggarakan di Kantor Inixindo Bandung, Jalan Cipaganti No 95, Bandung.</li>`;
            } else {
                selectedCheckboxes.forEach(cb => {
                    const content = cb.getAttribute('data-content') || '';
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

            // Generate HTML untuk vendor images
            const vendorImagesHTML = `
                <div class="vendor-images">
                    ${vendorImages.map(url => `<img src="${url}" alt="Vendor Logo" />`).join('')}
                </div>
            `;

            const signatureHTML = signatureUrl && signatureUrl !== '' ? `
                <img src="${signatureUrl}" alt="Tanda Tangan ${namaSales}" class="signature-img" style="width: auto; height: 15mm; "/>
            ` : `<p>Tanda Tangan Tidak Tersedia</p>`;

            const keuntunganSection = `
                <p class="font-semibold mt-2" style="font-weight:bold;">Keuntungan yang Akan Anda Dapatkan:</p>
                <ul class="list-disc pl-6">${keuntunganHTML}</ul>
            `;

            // --- firstPageContent ---
            let firstPageContent = `
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
                            <p>${penerima}</p>
                            <p>Di Tempat</p>
                        </div>
                        <div class="deskripsi text-sm text-gray-800">
                            <p class="font-semibold">Dengan Hormat,</p>
                            <p>${getDeskripsi()}</p>
                            <table class="training-table">
                                <thead>
                                    <tr>
                                        <th style="width: 27%;">Materi Pelatihan</th>
                                        <th style="width: 10%;">Metode</th>
                                        <th style="width: 20%;">Exam</th>
                                        <th style="width: 15%;">Durasi</th>
                                        <th style="width: 15%;">Tanggal</th>
                                        <th style="width: 13%;">Harga ${includePPN ? `(PPN ${ppnRate}%)` : ''}</th>
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
                        </div>
            `;

            // Tambahkan keuntungan jika ada Online
            // if (hasOnline) {
            //     firstPageContent += `
            //                         <div class="terms text-sm text-gray-800">
            //                             ${keuntunganSection}
            //                         </div>
            //                 `;
            // }

            // firstPageContent += `
            //                     </div>
            //                 </div>
            //             `;

            // // --- secondPageContent (hanya jika TIDAK ada Online) ---
            // let secondPageContent = '';

            // if (!hasOnline) {
            //     secondPageContent = `
            //         <div class="container">
            //             <div class="content-container">
            //                 <img src="${backgroundUrl}" class="background-image" alt="Background">
            //                 <div class="keuntungan-closing-container">
            //                     <div class="terms text-sm text-gray-800">
            //                         ${keuntunganSection}
            //                     </div>
            //                     <div class="closing text-sm text-gray-700">
            //                         <p>Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat bekerja sama dengan Bapak/Ibu.</p>
            //                         <p style="margin-bottom:9mm;"><strong>Untuk informasi lebih lanjut dan penyesuaian harga maupun fasilitas, mohon hubungi:</strong></p>
            //                         <p class="contact-info"><span class="label">Whatsapp</span><span class="value">: ${waSales}</span></p>
            //                         <p class="contact-info"><span class="label">Telepon</span><span class="value">: ${telpSales}</span></p>
            //                         <p class="contact-info"><span class="label">Email</span><span class="value"><a href="mailto:${emailSales}" style="text-decoration:none; color: black;">: ${emailSales}</a></span></p>
            //                         <br />
            //                         <p class="mt-2">Hormat kami,</p>
            //                         <p class="font-bold">INIXINDO BANDUNG</p>
            //                         <div class="signature">
            //                             ${signatureHTML}
            //                             <p><strong>${namaSales}</strong></p>
            //                             <p>${jabatanSales},</p>
            //                             <p>Inixindo Bandung</p>
            //                         </div>
            //                         ${vendorImagesHTML}
            //                     </div>
            //                 </div>
            //             </div>
            //         </div>
            //     `;
            // } else {
            //     secondPageContent = `
            //         <div class="container">
            //             <div class="content-container">
            //                 <img src="${backgroundUrl}" class="background-image" alt="Background">
            //                 <div class="closing text-sm text-gray-700" style="margin-top: 10mm;">
            //                     <p>Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat bekerja sama dengan Bapak/Ibu.</p>
            //                     <p style="margin-bottom:9mm;"><strong>Untuk informasi lebih lanjut dan penyesuaian harga maupun fasilitas, mohon hubungi:</strong></p>
            //                     <p class="contact-info"><span class="label">Whatsapp</span><span class="value">: ${waSales}</span></p>
            //                     <p class="contact-info"><span class="label">Telepon</span><span class="value">: ${telpSales}</span></p>
            //                     <p class="contact-info"><span class="label">Email</span><span class="value"><a href="mailto:${emailSales}" style="text-decoration:none; color: black;">: ${emailSales}</a></span></p>
            //                     <br />
            //                     <p class="mt-2">Hormat kami,</p>
            //                     <p class="font-bold">INIXINDO BANDUNG</p>
            //                     <div class="signature">
            //                         ${signatureHTML}
            //                         <p><strong>${namaSales}</strong></p>
            //                         <p>${jabatanSales},</p>
            //                         <p>Inixindo Bandung</p>
            //                     </div>
            //                     ${vendorImagesHTML}
            //                 </div>
            //             </div>
            //         </div>
            //     `;
            // }

            firstPageContent += `
                                </div>
                            </div>
                        `;

            // --- secondPageContent ---
            let secondPageContent = `
                <div class="container">
                    <div class="content-container">
                        <img src="${backgroundUrl}" class="background-image" alt="Background">
                        <div class="keuntungan-closing-container">
                            <div class="terms text-sm text-gray-800">
                                ${keuntunganSection}
                            </div>
                            <div class="closing text-sm text-gray-700">
                                <p>Demikian surat penawaran ini kami sampaikan. Besar harapan kami dapat bekerja sama dengan Bapak/Ibu.</p>
                                <p style="margin-bottom:9mm;"><strong>Untuk informasi lebih lanjut dan penyesuaian harga maupun fasilitas, mohon hubungi:</strong></p>
                                <p class="contact-info"><span class="label">Whatsapp</span><span class="value">: ${waSales}</span></p>
                                <p class="contact-info"><span class="label">Telepon</span><span class="value">: ${telpSales}</span></p>
                                <p class="contact-info"><span class="label">Email</span><span class="value"><a href="mailto:${emailSales}" style="text-decoration:none; color: black;">: ${emailSales}</a></span></p>
                                <br />
                                <p class="mt-2">Hormat kami,</p>
                                <p class="font-bold">INIXINDO BANDUNG</p>
                                <div class="signature">
                                    ${signatureHTML}
                                    <p><strong>${namaSales}</strong></p>
                                    <p>${jabatanSales},</p>
                                    <p>Inixindo Bandung</p>
                                </div>
                                ${vendorImagesHTML}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Generate PDF
            await generatePDF(firstPageContent, secondPageContent);
        });

        async function generatePDF(firstPageContent, secondPageContent) {
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // CSS styles for PDF rendering
            const styles = `
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        font-size: 9pt;
                        font-family: Arial, sans-serif;
                        position: relative;
                    }
                    .container {
                        max-width: 190mm;
                        width: 100%;
                        margin: 0;
                        padding: 5mm;
                        background: transparent;
                    }
                    .header-container {
                        width: 183mm;
                        margin: 0;
                        padding: 2mm 0;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                    }
                    .content-container { margin-left:10mm;}
                    .logo img { width: 60mm; }
                    .office-info { font-size: 9pt; line-height: 4pt; max-width: 70mm; }
                    .waktu { text-align: right; font-size: 9pt; margin: 1mm 0; line-height: 12pt; }
                    .lampiran { font-size: 9pt; margin: 1mm 0; line-height: 8pt; }
                    .lampiran p { display: flex; align-items: flex-start; }
                    .lampiran p span.label { flex: 0 0 20mm; text-align: left; }
                    .lampiran p span.value { flex: 1; text-align: left; }
                    .penerima { font-size: 9pt; margin-top: 8mm; line-height: 5pt; }
                    .deskripsi { font-size: 9pt; margin: 6mm 0; line-height: 13pt; text-align: justify; }
                    .terms { font-size: 9pt; margin: 6mm 0; line-height: 13pt; display: block; }
                    .keuntungan-closing-container { }
                    .closing { font-size: 9pt; margin: 1mm 0; line-height: 5pt; }
                    .closing p.contact-info { display: flex; align-items: flex-start; }
                    .closing p.contact-info span.label { flex: 0 0 20mm; text-align: left; }
                    .closing p.contact-info span.value { flex: 1; text-align: left; }
                    .training-table { width: 100%; margin: 1mm 0; border-collapse: collapse; }
                    .training-table th, .training-table td { font-size: 9pt; padding: 3pt 5pt; border: 1px solid #ccc; text-align: center; word-wrap: break-word; line-height: 12pt; }
                    .training-table th { background-color: #f2f2f2; }
                    .list-disc { list-style-type: disc; padding-left: 15px; }
                    .list-decimal { list-style-type: decimal; padding-left: 30px; }
                    .vendor-images {
                        position: absolute;
                        bottom: 0;
                        top: 135mm;
                        left: 15mm;
                        right: 0;
                        display: grid;
                        grid-template-columns: repeat(5, 1fr);
                        gap: 5mm;
                        margin-top: 90mm;
                        margin-left: 10mm
                    }
                    .vendor-images img {
                        width: 20mm;
                        height: auto;
                        object-fit: contain;
                    }
                    img.background-image {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 210mm;
                        height: 297mm;
                        z-index: -1;
                        opacity: 1;
                    }
                </style>
            `;

            // Create temporary containers for rendering
            const tempContainer = document.createElement('div');
            tempContainer.style.position = 'absolute';
            tempContainer.style.top = '-9999px';
            tempContainer.style.width = '215mm';
            tempContainer.style.height = '297mm';
            document.body.appendChild(tempContainer);

            // Render first page
            tempContainer.innerHTML = `<html><head>${styles}</head><body>${firstPageContent}</body></html>`;
            let canvas = await html2canvas(tempContainer, {
                scale: 2,
                useCORS: true,
                width: 210 * 3.78, // Convert mm to pixels (1mm = ~3.78px at 96dpi)
                height: 297 * 3.78,
                windowWidth: 210 * 3.78,
                windowHeight: 297 * 3.78
            });
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 0, 0, 210, 297);

            // Add new page for keuntungan and closing
            doc.addPage();

            // Render second page
            tempContainer.innerHTML = `<html><head>${styles}</head><body>${secondPageContent}</body></html>`;
            canvas = await html2canvas(tempContainer, {
                scale: 2,
                useCORS: true,
                width: 210 * 3.78,
                height: 297 * 3.78,
                windowWidth: 210 * 3.78,
                windowHeight: 297 * 3.78
            });
            const imgData2 = canvas.toDataURL('image/png');
            doc.addImage(imgData2, 'PNG', 0, 0, 210, 297);

            // Clean up
            document.body.removeChild(tempContainer);

            // Download PDF
            doc.save('Surat_Penawaran.pdf');
        }

        document.getElementById('download-word').addEventListener('click', async () => {
            const pelatihan = [];
            document.querySelectorAll('.pelatihan-row').forEach(row => {
                pelatihan.push({
                    materi: row.querySelector('.materi-text').value,
                    exam: row.querySelector('.exam-select').value,
                    harga_exam: row.querySelector('.harga-exam').value,
                    metode: row.querySelector('.metode-select').value,
                    durasi: row.querySelector('.durasi-pelatihan').value,
                    tanggal_awal: row.querySelector('.tanggal-awal-pelatihan').value,
                    tanggal: row.querySelector('.tanggal-pelatihan').value,
                    harga: row.querySelector('.harga-pelatihan').value
                });
            });

            const fasilitas = [];
            document.querySelectorAll('.fasilitas-item').forEach(item => {
                fasilitas.push(item.value);
            });

            const keuntungan = [];
            document.querySelectorAll('.keuntungan-item').forEach(item => {
                keuntungan.push(item.value);
            });

            const syarat = [];
            document.querySelectorAll('.syarat-checkbox:checked').forEach(checkbox => {
                syarat.push(checkbox.getAttribute('data-content'));
            });

            const data = {
                _token: "{{ csrf_token() }}",
                no_surat: document.getElementById('no-surat').value,
                hal: document.getElementById('hal').value,
                lampiran: document.getElementById('lampiran').value,
                penerima: document.getElementById('penerima').value,
                perusahaan_id: document.getElementById('perusahaan').value,
                deskripsi: document.getElementById('deskripsi').value,
                ppn_rate: document.getElementById('ppn-rate').value,
                include_ppn: document.getElementById('include-ppn').checked,
                pelatihan: pelatihan,
                fasilitas: fasilitas,
                keuntungan: keuntungan,
                syarat: syarat,
                nama_sales: document.getElementById('nama-sales').value,
                jabatan_sales: document.getElementById('jabatan-sales').value,
                wa_sales: document.getElementById('wa-sales').value,
                telp_sales: document.getElementById('telp-sales').value,
                email_sales: document.getElementById('email-sales').value
            };

            if (!data.no_surat || !data.penerima) {
                alert('Harap isi No Surat dan Penerima');
                return;
            }

            try {
                const response = await fetch("{{ route('crm.generate.word') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Surat_Penawaran_${data.no_surat}.docx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat generate Word: ' + error.message);
            }
        });
    </script>
</body>

</html>
