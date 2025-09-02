<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
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
            margin: 5px 0;
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

        #peserta-list,
        #signature-list {
            margin-top: 10px;
        }

        .peserta-row,
        .signature-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 10px;
        }

        .peserta-row input,
        .signature-row input {
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .logo {
            text-align: left;
        }

        .logo img {
            width: 220px;
            height: auto;
        }

        .office-info {
            text-align: right;
            font-size: 10px;
            line-height: 14px;
            max-width: 200px;
        }

        .headertext {
            text-decoration: underline;
            font-weight: bold;
            font-size: 16px;
            margin: 5px 0;
            padding: 3px 0;
            text-align: center;
        }

        .section-header {
            font-weight: bold;
            font-size: 14px;
            background-color: #f5f5f5;
            padding: 3px 0;
            margin: 5px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 5px 0;
        }

        caption {
            caption-side: top;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 3px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        thead {
            text-align: center;
        }

        th.no-column,
        td.no-column {
            width: 5%;
            min-width: 20px;
        }

        th.name-column,
        td.name-column {
            width: 35%;
        }

        th.contact-column,
        td.contact-column {
            width: 40%;
        }

        th.price-column,
        td.price-column {
            width: 20%;
        }

        .note {
            color: red;
            text-align: left;
            font-size: 10px;
            margin: 3px 0;
        }

        .syarat {
            text-align: left;
            margin-top: 5px;
            page-break-inside: avoid;
        }

        .syarat h3 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .syarat ol {
            font-size: 12px;
            padding-left: 15px;
            margin: 3px 0;
        }

        .statement {
            text-align: left;
            font-size: 12px;
            margin: 5px 0;
            page-break-inside: avoid;
        }

        .description {
            text-align: left;
            font-size: 12px;
            margin: 10px 0;
            page-break-inside: avoid;
            border: 1px solid #ccc;
            padding: 8px;
        }

        .description h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 10px;
            page-break-inside: avoid;
            align-items: flex-start;
        }

        .signature {
            text-align: center;
            width: 30%;
            position: relative;
            min-height: 120px;
        }

        .signature p {
            margin: 2px 0;
            font-size: 12px;
        }

        .signature .name {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 2px;
        }

        .signature .position {
            font-size: 10px;
            color: #555;
        }

        .signature img.signature-img {
            max-width: 100px;
            height: auto;
            margin-top: 10px;
        }

        .signature img.cap-img {
            max-width: 80px;
            height: auto;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.8;
        }

        .approval-text {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            text-align: center;
        }

        .signature-preview {
            max-width: 100px;
            margin: 5px 0;
        }

        #ppn-percentage {
            display: none;
            width: 100px;
            margin: 5px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12pt;
            }

            .container {
                max-width: 190mm;
                width: 100%;
                margin: 0;
                padding: 5mm;
            }

            .header {
                margin-bottom: 2mm;
            }

            .logo img {
                width: 100px;
            }

            .office-info {
                font-size: 10pt;
                line-height: 12pt;
                max-width: 70mm;
                line-height: 1;
            }

            .headertext {
                font-size: 14pt;
                margin: 2mm 0;
                padding: 1mm 0;
                text-align: center;
            }

            table {
                width: 100%;
                page-break-inside: avoid;
                margin: 2mm 0;
                border-collapse: collapse;
            }

            caption {
                font-size: 12pt;
                margin-bottom: 1mm;
            }

            th,
            td {
                font-size: 10pt;
                padding: 4pt 6pt;
                border: 1px solid #ccc;
                text-align: left;
                word-wrap: break-word;
            }

            th {
                background-color: #f2f2f2;
            }

            th.no-column,
            td.no-column {
                width: 5%;
                min-width: 6mm;
            }

            th.name-column,
            td.name-column {
                width: 35%;
            }

            th.contact-column,
            td.contact-column {
                width: 40%;
            }

            th.price-column,
            td.price-column {
                width: 20%;
            }

            .note {
                color: red;
                font-size: 10pt;
                margin: 1mm 0;
                text-align: left;
            }

            .syarat {
                margin-top: 2mm;
                text-align: left;
                page-break-inside: avoid;
            }

            .syarat h3 {
                font-size: 12pt;
                margin-bottom: 1mm;
            }

            .syarat ol {
                font-size: 10pt;
                margin: 1mm 0;
                padding-left: 15px;
            }

            .statement {
                font-size: 10pt;
                margin: 2mm 0;
                text-align: left;
                page-break-inside: avoid;
            }

            .description {
                font-size: 10pt;
                margin: 2mm 0;
                text-align: left;
                page-break-inside: avoid;
                border: 1px solid #ccc;
                padding: 6pt;
            }

            .description h3 {
                font-size: 12pt;
                margin-bottom: 1mm;
            }

            .signature-section {
                margin-top: 10mm;
                display: flex;
                justify-content: flex-end;
                gap: 10mm;
                page-break-inside: avoid;
                align-items: flex-start;
            }

            .signature {
                text-align: center;
                width: 30%;
                position: relative;
                min-height: 80pt;
            }

            .signature img.signature-img {
                max-width: 80pt;
                height: auto;
                margin-top: 5mm;
            }

            .signature img.cap-img {
                max-width: 60pt;
                height: auto;
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                opacity: 0.8;
            }

            .approval-text {
                font-size: 10pt;
                font-weight: bold;
                margin-bottom: 3mm;
                text-align: center;
            }

            .signature p {
                font-size: 10pt;
                margin: 2pt 0;
            }

            .signature .name {
                margin-top: 10mm;
                padding-top: 1mm;
                border-top: 1px solid #000;
            }

            .signature .position {
                font-size: 9pt;
                color: #555;
            }

            button {
                display: none;
            }

            @page {
                size: A4;
                margin: 5mm;
            }
        }
    </style>
</head>

<body>
    <h2>Input Data Registrasi</h2>
    <pre style="display: none;">{{ print_r($ketentuan->toArray(), true) }}</pre>
    <form id="regis-form">
        <h3>Data Perusahaan</h3>
        <label>Nama Perusahaan:</label>
        <input type="text" id="nama-perusahaan" class="readonly" value="{{ $lead->perusahaan->nama_perusahaan ?? '-' }}"
            readonly>
        <label>Alamat:</label>
        <input type="text" id="alamat" class="readonly" value="{{ $lead->perusahaan->alamat ?? '-' }}" readonly>
        <label>PIC Penagihan:</label>
        <input type="text" id="pic" class="readonly" value="{{ $lead->perusahaan->cp ?? '-' }}" readonly>
        <label>Telepon:</label>
        <input type="text" id="telepon" class="readonly" value="{{ $lead->perusahaan->no_telp ?? '-' }}" readonly>
        <label>Email:</label>
        <input type="text" id="email" class="readonly" value="{{ $lead->perusahaan->email ?? '-' }}" readonly>
        <label>NPWP:</label>
        <input type="text" id="npwp" class="readonly" value="{{ $lead->perusahaan->npwp ?? '-' }}" readonly>

        <label>Materi dan Tanggal Pelatihan:</label>
        <input type="text" class="readonly" id="materi"
            value="{{ $lead->materiRelation->nama_materi }} || {{ \Carbon\Carbon::parse($lead->periode_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($lead->periode_selesai)->format('d M Y') }}"
            readonly>

        <h3>Data Peserta</h3>
        <div id="peserta-list"></div>
        <button type="button" id="add-peserta">Tambah Peserta</button>

        <h3>Syarat & Ketentuan</h3>
        <label>Pilih Syarat (bisa lebih dari satu):</label>
        <select id="syarat-select" multiple required>
            @foreach ($ketentuan as $ket)
                <option value="{{ $ket->id }}" data-content="{{ $ket->ketentuan }}">{{ $ket->ketentuan }}
                </option>
            @endforeach
        </select>

        @php
            use App\Models\Karyawan;
            $sales = Karyawan::where('kode_karyawan', $lead->id_sales)->first();
            $ttdauth = Karyawan::where('id', auth()->id())->value('ttd');
            $ttdSPV = Karyawan::where('jabatan', 'SPV Sales')->value('ttd');
            $ttd = [
                'ttd_user' => $ttdauth,
                'ttd_spv' => $ttdSPV,
            ];
        @endphp

        <h3>Tanda Tangan</h3>
        <div id="signature-list">
            <div class="signature-row">
                <label>Nama Penandatangan 1:</label>
                <input type="text" placeholder="Nama Penandatangan 1" class="signature-name" required>
                <label>Jabatan Penandatangan 1:</label>
                <input type="text" placeholder="Jabatan Penandatangan 1" class="signature-position" required
                    value="Pendaftar">
                <label>Upload Tanda Tangan 1:</label>
                <input type="file" accept="image/*" class="signature-upload" id="signature-upload-1">
            </div>
            <div class="signature-row">
                <label>Nama Penandatangan 2:</label>
                <input type="text" placeholder="Nama Penandatangan 2" class="signature-name" required
                    value="{{ $sales->nama_lengkap }}">
                <label>Jabatan Penandatangan 2:</label>
                <input type="text" placeholder="Jabatan Penandatangan 2" class="signature-position" required
                    value="Account Executive">
                <label>Tanda Tangan 2 (dari database):</label>
                <input type="hidden" class="signature-data" value="{{ $ttd['ttd_user'] ?? '' }}">
                <img src="{{ asset('storage/ttd/' . ($ttd['ttd_user'] ?? '')) }}" class="signature-preview"
                    style="max-width: 100px; display: {{ $ttd['ttd_user'] ? 'block' : 'none' }};">
                <label>Upload Cap Perusahaan:</label>
                <input type="file" accept="image/*" class="cap-upload" id="cap-upload-2">
            </div>
            <div class="signature-row">
                <label>Nama Penandatangan 3:</label>
                <input type="text" placeholder="Nama Penandatangan 3" class="signature-name" required
                    value="Aryani Meitasari">
                <label>Jabatan Penandatangan 3:</label>
                <input type="text" placeholder="Jabatan Penandatangan 3" class="signature-position" required
                    value="Chief Marketing Manager">
                <label>Tanda Tangan 3 (dari database):</label>
                <input type="hidden" class="signature-data" value="{{ $ttd['ttd_spv'] ?? '' }}">
                <img src="{{ asset('storage/ttd/' . ($ttd['ttd_spv'] ?? '')) }}" class="signature-preview"
                    style="max-width: 100px; display: {{ $ttd['ttd_spv'] ? 'block' : 'none' }};">
            </div>
        </div>

        <h3>PPN</h3>
        <label><input type="checkbox" id="include-ppn"> Sertakan PPN?</label>
        <input type="number" id="ppn-percentage" placeholder="PPN (%)" min="0" step="0.01" value="11">

        <h3>Deskripsi Tambahan</h3>
        <textarea id="deskripsi-tambahan" placeholder="Masukkan deskripsi tambahan (opsional)"></textarea>

        <button type="button" id="preview-btn">Generate Preview</button>
    </form>

    <div id="preview-modal">
        <div id="preview-content"></div>
    </div>

    <script>
        let pesertaCount = 0;
        const termsData = @json($ketentuan);

        console.log('Data ketentuan:', termsData);

        const ppnCheckbox = document.getElementById('include-ppn');
        const ppnInput = document.getElementById('ppn-percentage');
        ppnCheckbox.addEventListener('change', () => {
            ppnInput.style.display = ppnCheckbox.checked ? 'block' : 'none';
        });

        document.getElementById('add-peserta').addEventListener('click', () => {
            pesertaCount++;
            const row = document.createElement('div');
            row.className = 'peserta-row';
            row.innerHTML = `
                <input type="text" placeholder="Nama Peserta" class="nama-peserta" required>
                <input type="text" placeholder="Kontak HP & Email" class="kontak-peserta" required>
                <input type="text" placeholder="Harga (Rp)" class="harga-peserta">
                <button type="button" onclick="this.parentElement.remove()">Hapus</button>
            `;
            document.getElementById('peserta-list').appendChild(row);
        });

        document.getElementById('preview-btn').addEventListener('click', () => {
            const namaPerusahaan = document.getElementById('nama-perusahaan').value;
            const alamat = document.getElementById('alamat').value;
            const pic = document.getElementById('pic').value;
            const telepon = document.getElementById('telepon').value;
            const email = document.getElementById('email').value;
            const npwp = document.getElementById('npwp').value;
            const materi = document.getElementById('materi').value;
            const deskripsiTambahan = document.getElementById('deskripsi-tambahan').value;
            const includePPN = document.getElementById('include-ppn').checked;
            const ppnPercentage = parseFloat(document.getElementById('ppn-percentage').value) || 0;

            // Proses peserta
            const pesertaRows = document.querySelectorAll('.peserta-row');
            let pesertaHTML = '';
            let totalHarga = 0;
            pesertaRows.forEach((row, index) => {
                const nama = row.querySelector('.nama-peserta').value;
                const kontak = row.querySelector('.kontak-peserta').value;
                const hargaInput = row.querySelector('.harga-peserta').value;
                const harga = hargaInput ? parseInt(hargaInput) : null;
                if (harga !== null) {
                    totalHarga += harga;
                }
                const hargaDisplay = harga !== null ? `Rp ${harga.toLocaleString('id-ID')},00` : '';
                pesertaHTML += `
                    <tr>
                        <td class="no-column">${index + 1}</td>
                        <td class="name-column">${nama}</td>
                        <td class="contact-column">${kontak}</td>
                        <td class="price-column">${hargaDisplay}</td>
                    </tr>
                `;
            });
            pesertaHTML += `
                <tr><th colspan="3">Total</th><td class="price-column">${totalHarga ? `Rp ${totalHarga.toLocaleString('id-ID')},00` : ''}</td></tr>
            `;
            if (includePPN && ppnPercentage > 0 && totalHarga > 0) {
                const ppnMultiplier = 1 + (ppnPercentage / 100);
                const totalPPN = totalHarga * ppnMultiplier;
                pesertaHTML += `
                    <tr><th colspan="3">Total Keseluruhan + PPN ${ppnPercentage}%</th><td class="price-column">Rp ${totalPPN.toLocaleString('id-ID')},00</td></tr>
                `;
            }

            // Proses syarat dan ketentuan
            const select = document.getElementById('syarat-select');
            const selectedOptions = Array.from(select.selectedOptions);
            let syaratList = '';
            if (selectedOptions.length === 0) {
                syaratList = '<li>Tidak ada syarat yang dipilih.</li>';
            } else {
                selectedOptions.forEach(option => {
                    const content = option.dataset.content || '';
                    syaratList += `<li>${content}</li>`;
                });
            }

            // Proses tanda tangan dan cap
            const signatureRows = document.querySelectorAll('.signature-row');
            let signatureHTML = '';
            signatureRows.forEach((row, index) => {
                const name = row.querySelector('.signature-name').value || 'Tidak diisi';
                const position = row.querySelector('.signature-position').value || 'Tidak diisi';
                let signatureSrc = '';
                let capSrc = '';

                // Penandatangan 1: Tanda tangan dari upload
                if (index === 0) {
                    const signatureUpload = row.querySelector('#signature-upload-1');
                    if (signatureUpload && signatureUpload.files[0]) {
                        signatureSrc = URL.createObjectURL(signatureUpload.files[0]);
                    }
                }
                // Penandatangan 2: Tanda tangan dari $ttd['ttd_user'] dan cap dari upload
                else if (index === 1) {
                    const signatureData = row.querySelector('.signature-data');
                    if (signatureData && signatureData.value) {
                        signatureSrc = `{{ asset('storage/ttd') }}/${signatureData.value}`;
                    }
                    const capUpload = row.querySelector('#cap-upload-2');
                    if (capUpload && capUpload.files[0]) {
                        capSrc = URL.createObjectURL(capUpload.files[0]);
                    }
                }
                // Penandatangan 3: Tanda tangan dari $ttd['ttd_spv']
                else if (index === 2) {
                    const signatureData = row.querySelector('.signature-data');
                    if (signatureData && signatureData.value) {
                        signatureSrc = `{{ asset('storage/ttd') }}/${signatureData.value}`;
                    }
                }

                // Tambahkan teks "Mengetahui, " untuk penandatangan ketiga di luar elemen .signature
                const approvalText = index === 2 ? '<p class="approval-text">Mengetahui, </p>' : '';

                signatureHTML += `
                    <div class="signature">
                        ${capSrc ? `<img src="${capSrc}" class="cap-img" alt="Cap Perusahaan">` : ''}
                        ${signatureSrc ? `<img src="${signatureSrc}" class="signature-img" alt="Tanda Tangan">` : '<p>Tanda tangan tidak tersedia</p>'}
                        <p class="name">${name}</p>
                        <p class="position">${position}</p>
                    </div>
                `;
            });

            // Proses deskripsi tambahan
            const deskripsiHTML = `
                <div class="description">
                    <p>${deskripsiTambahan.replace(/\n/g, '<br>') || ''}</p>
                </div>
            `;

            // Generate pratinjau
            const previewHTML = `
                <div class="container">
                    <div class="header">
                        <div class="logo"><img src="{{ asset('assets/img/inix.png') }}" alt="Inixindo Logo" /></div>
                        <div class="office-info">
                            <p>Jl. Cipaganti No.95, Bandung</p>
                            <p>Tel: 022-2032831</p>
                            <p>Web: www.inixindobdg.co.id</p>
                        </div>
                    </div>
                    <div class="headertext">REGISTRATION FORM</div>
                    <table>
                        <thead><tr><th colspan="2" style="text-align: center">DATA PELANGGAN</th></tr></thead>
                        <tbody>
                            <tr><th style="width: 25%">Nama Perusahaan</th><td style="width: 75%">${namaPerusahaan}</td></tr>
                            <tr><th style="width: 25%">Alamat</th><td style="width: 75%">${alamat}</td></tr>
                            <tr><th style="width: 25%">PIC Penagihan Pelatihan</th><td style="width: 75%">${pic}</td></tr>
                            <tr><th style="width: 25%">Telepon</th><td style="width: 75%">${telepon}</td></tr>
                            <tr><th style="width: 25%">Email</th><td style="width: 75%">${email}</td></tr>
                            <tr><th style="width: 25%">*NPWP</th><td style="width: 75%">${npwp}</td></tr>
                        </tbody>
                    </table>
                    <p class="note">*Wajib dilengkapi untuk pembuatan faktur pajak</p>
                    <table>
                        <thead><tr><th colspan="4" style="font-weight: bold; white-space: pre-line; word-wrap: break-word;">${materi}</th></tr></thead>
                        <tbody>
                            <tr><th class="no-column">No</th><th class="name-column">Nama Peserta</th><th class="contact-column">Kontak Handphone & Email & Divisi</th><th class="price-column">Harga</th></tr>
                            ${pesertaHTML}
                        </tbody>
                    </table>
                    <div class="syarat">
                        <h3>Syarat dan Ketentuan</h3>
                        <ol>${syaratList}</ol>
                    </div>
                    <div class="statement">
                        <p>Dengan ini kami menyatakan untuk mengikuti pelatihan sesuai dengan kesepakatan.<br /><br />Bandung, ${new Date().toLocaleDateString('id-ID')}</p>
                    </div>
                    <div class="signature-section">
                        ${signatureHTML}
                    </div>
                    ${deskripsiHTML}
                </div>
            `;

            const modal = document.getElementById('preview-modal');
            const content = document.getElementById('preview-content');
            content.innerHTML = previewHTML +
                '<button onclick="printPreview()">Print to PDF</button><button onclick="closeModal()">Tutup</button>';
            modal.style.display = 'flex';
        });

        function closeModal() {
            document.getElementById('preview-modal').style.display = 'none';
        }

        function printPreview() {
            const content = document.querySelector('#preview-content .container').outerHTML;
            const printWindow = window.open('', '', 'height=600, width=900');
            printWindow.document.write('<html><head><title>Print Preview</title>');
            printWindow.document.write('<style>');
            printWindow.document.write(`
                body { margin: 0; padding: 0; font-size: 12pt; font-family: Arial, sans-serif; }
                .container { max-width: 190mm; width: 100%; margin: 0; padding: 5mm; }
                .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2mm; }
                .logo img { width: 200px; }
                .office-info { font-size: 10pt; line-height: 12pt; max-width: 70mm; line-height: 1; }
                .headertext { font-size: 14pt; margin: 2mm 0; padding: 1mm 0; text-decoration: underline; font-weight: bold; text-align: center; }
                table { width: 100%; page-break-inside: avoid; margin: 2mm 0; border-collapse: collapse; }
                caption { font-size: 12pt; margin-bottom: 1mm; }
                th, td { font-size: 10pt; padding: 4pt 6pt; border: 1px solid #ccc; text-align: left; word-wrap: break-word; }
                th { background-color: #f2f2f2; }
                th.no-column, td.no-column { width: 5%; min-width: 6mm; }
                th.name-column, td.name-column { width: 35%; }
                th.contact-column, td.contact-column { width: 40%; }
                th.price-column, td.price-column { width: 20%; }
                .note { color: red; font-size: 10pt; margin: 1mm 0; text-align: left; }
                .syarat { margin-top: 2mm; text-align: left; page-break-inside: avoid; }
                .syarat h3 { font-size: 12pt; margin-bottom: 1mm; }
                .syarat ol { font-size: 10pt; margin: 1mm 0; padding-left: 15px; }
                .statement { font-size: 10pt; margin: 2mm 0; text-align: left; page-break-inside: avoid; }
                .description { font-size: 10pt; margin: 2mm 0; text-align: left; page-break-inside: avoid; border: 1px solid #ccc; padding: 6pt; }
                .description h3 { font-size: 12pt; margin-bottom: 1mm; }
                .signature-section { margin-top: 10mm; display: flex; justify-content: flex-end; gap: 10mm; page-break-inside: avoid; align-items: flex-start; }
                .signature { text-align: center; width: 30%; position: relative; min-height: 80pt; }
                .signature img.signature-img { max-width: 80pt; height: auto; margin-top: 5mm; }
                .signature img.cap-img { max-width: 60pt; height: auto; position: absolute; right: 0; top: 50%; transform: translateY(-50%); opacity: 0.4; }
                .approval-text { font-size: 10pt; font-weight: bold; margin-bottom: 3mm; text-align: center;}
                .signature p { font-size: 10pt; margin: 2pt 0; }
                .signature .name { margin-top: 10mm; padding-top: 1mm; border-top: 1px solid #000; }
                .signature .position { font-size: 9pt; color: #555; }
                button { display: none; }
                @page { size: A4; margin: 5mm; }
            `);
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
        }
    </script>
</body>

</html>