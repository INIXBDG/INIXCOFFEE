<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
            padding: 6pt;
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
            width: 100px;
            height: auto;
            margin-top: 10px;
        }

        .signature img.cap-img {
            width: 80px;
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

        #ppn-percentage {
            display: none;
            width: 100px;
            margin: 5px 0;
        }

        #pdf-container {
            position: absolute;
            left: -9999px;
            top: -9999px;
            width: 190mm;
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
                width: 200px;
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
                width: 80pt;
                height: auto;
                margin-top: 5mm;
            }

            .signature img.cap-img {
                width: 60pt;
                height: auto;
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                opacity: 0.4;
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
        <input type="text" id="alamat" value="{{ $lead->perusahaan->alamat ?? '-' }}">
        <label>PIC Penagihan:</label>
        <input type="text" id="pic">
        <label>Telepon:</label>
        <input type="text" id="telepon">
        <label>Email:</label>
        <input type="text" id="email">
        <label>NPWP:</label>
        <input type="text" id="npwp" value="{{ $lead->perusahaan->npwp ?? '-' }}">

        <label>Materi dan Tanggal Pelatihan:</label>
        <input type="text" class="readonly" id="materi"
            value="{{ $lead->materiRelation->nama_materi }} || {{ $lead->rkm->metode_kelas }} || {{ \Carbon\Carbon::parse($lead->periode_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($lead->periode_selesai)->format('d M Y') }}"
            readonly>

        <h3>Data Peserta</h3>
        <div id="peserta-list"></div>
        <button type="button" id="add-peserta">Tambah Peserta</button>

        <h3>Syarat & Ketentuan</h3>
        <label>Pilih Syarat (bisa lebih dari satu):</label>
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

        @php
            use App\Models\Karyawan;
            $sales = Karyawan::where('kode_karyawan', $lead->id_sales)->first();
        @endphp

        <h3>Tanda Tangan</h3>
        <div id="signature-list">
            <div class="signature-row">
                <label>Nama Penandatangan 1:</label>
                <input type="text" placeholder="Nama Penandatangan 1" class="signature-name" required>
                <label>Jabatan Penandatangan 1:</label>
                <input type="text" placeholder="Jabatan Penandatangan 1" class="signature-position" required
                    value="Pendaftar">
            </div>
            <div class="signature-row">
                <label>Nama Penandatangan 2:</label>
                <input type="text" placeholder="Nama Penandatangan 2" class="signature-name" required
                    value="{{ $sales->nama_lengkap }}">
                <label>Jabatan Penandatangan 2:</label>
                <input type="text" placeholder="Jabatan Penandatangan 2" class="signature-position" required
                    value="Account Executive">
            </div>
            <div class="signature-row">
                <label>Nama Penandatangan 3:</label>
                <input type="text" placeholder="Nama Penandatangan 3" class="signature-name" required
                    value="Aryani Meitasari">
                <label>Jabatan Penandatangan 3:</label>
                <input type="text" placeholder="Jabatan Penandatangan 3" class="signature-position" required
                    value="SPV Marketing Manager">
            </div>
        </div>

        <h3>PPN</h3>
        <label><input type="checkbox" id="include-ppn"> Sertakan PPN?</label>
        <input type="number" id="ppn-percentage" placeholder="PPN (%)" min="0" step="0.01" value="11">

        <h3>Deskripsi Tambahan</h3>
        <textarea id="deskripsi-tambahan" placeholder="Masukkan deskripsi tambahan (opsional)"></textarea>

        <button type="button" id="preview-btn">Generate PDF</button>
        <button type="button" id="download-word-btn">Download Word</button>

    </form>

    <div id="pdf-container"></div>

    <script>
        let pesertaCount = 0;
        const termsData = @json($ketentuan);
        const signatureUrl = "{{ asset('storage/ttd/' . ($sales->ttd ?? '')) }}";
        // const signatureUrl = "data:image/png;base64,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";

        console.log('Data ketentuan:', termsData);
        console.log('Signature URL:', signatureUrl);

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

            // Proses tanda tangan
            const signatureRows = document.querySelectorAll('.signature-row');
            let signatureHTML = '';
            signatureRows.forEach((row, index) => {
                const name = row.querySelector('.signature-name').value || 'Tidak diisi';
                const position = row.querySelector('.signature-position').value || 'Tidak diisi';
                let extraText = '';
                let style = '';
                let signatureImg = '';

                // Signature 1: Kosong (untuk tinta basah)
                if (index === 0) {
                    extraText = '';
                    style = 'padding-top: 20.4mm;';
                }
                // Signature 2: Use database-fetched signature
                else if (index === 1) {
                    if (signatureUrl && signatureUrl !== '') {
                        signatureImg =
                            `<img class="signature-img" src="${signatureUrl}" alt="Signature ${name}">`;
                        // Dynamically calculate padding based on image height
                        const img = new Image();
                        img.src = signatureUrl;
                        img.onload = function() {
                            const height = img.height;
                            const adjustedPadding = Math.max(20.4 - (height / 10),
                                5); // Convert px to mm, ensure minimum padding
                            const signatureDiv = document.querySelector(
                                `#pdf-container .signature:nth-child(${index + 1}) div`);
                            if (signatureDiv) {
                                signatureDiv.style.paddingTop = `${adjustedPadding}mm`;
                            }
                        };
                    } else {
                        signatureImg = `<p>Tanda Tangan Tidak Tersedia</p>`;
                        style = 'padding-top: 20.4mm;';
                    }
                }
                // Signature 3: Kosong (untuk tinta basah), tambah "Mengetahui"
                else if (index === 2) {
                    extraText = '<p>Mengetahui</p>';
                    style = 'padding-top: 15mm';
                }

                signatureHTML += `
                    <div class="signature">
                        ${extraText}
                        <div style="${style}">
                            ${signatureImg}
                            <p class="name">${name}</p>
                            <p class="position">${position}</p>
                        </div>
                    </div>
                `;
            });

            // Proses deskripsi tambahan
            const deskripsiHTML = `
                <div class="description">
                    <p>${deskripsiTambahan.replace(/\n/g, '<br>') || ''}</p>
                </div>
            `;

            // Generate HTML untuk PDF
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

            // Render HTML ke container tersembunyi
            const pdfContainer = document.getElementById('pdf-container');
            pdfContainer.innerHTML = `
                <style>
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
                    .signature img.signature-img { width: auto; height: 41pt; margin-top: 5mm; }
                    .signature img.cap-img { width: 60pt; height: auto; position: absolute; right: 0; top: 50%; transform: translateY(-50%); opacity: 0.4; }
                    .approval-text { font-size: 10pt; font-weight: bold; margin-bottom: 3mm; text-align: center; }
                    .signature p { font-size: 10pt; margin: 2pt 0; }
                    .signature .name { margin-top: 10mm; padding-top: 1mm; border-top: 1px solid #000; }
                    .signature .position { font-size: 9pt; color: #555; }
                </style>
                ${previewHTML}
            `;

            // Gunakan html2canvas untuk merender ke canvas
            html2canvas(pdfContainer, {
                scale: 2, // Tingkatkan resolusi
                useCORS: true, // Untuk mendukung gambar dari URL eksternal
                width: 800, // 210mm * 3.78 pixels/mm (72 DPI)
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                // Hitung dimensi gambar untuk PDF
                const imgWidth = 213; // 210mm - 2 * 5mm margin
                const pageHeight = 297; // A4 height in mm
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                // Tambahkan halaman pertama
                pdf.addImage(imgData, 'PNG', 5, 5, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Tambahkan halaman tambahan jika konten lebih panjang
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 5, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Simpan PDF
                pdf.save('registration_form.pdf');

                // Bersihkan container
                pdfContainer.innerHTML = '';
            }).catch(error => {
                console.error('Error generating PDF:', error);
                alert('Gagal menghasilkan PDF. Silakan coba lagi.');
            });
        });
    </script>

    <script type="module">
        import * as docx from 'https://cdn.jsdelivr.net/npm/docx@9.5.1/+esm';

        const {
            Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
            WidthType, BorderStyle, AlignmentType, HeadingLevel, VerticalAlign,
            ImageRun, Header, Footer
        } = docx;

        // helper buat gambarnya
        async function fetchToBase64(url) {
            try {
                const response = await fetch(url, { mode: 'cors' });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const blob = await response.blob();
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        const base64String = reader.result;
                        resolve(base64String.includes(',') ? base64String.split(',')[1] : base64String);
                    };
                    reader.readAsDataURL(blob);
                });
            } catch (err) {
                console.warn('Gagal load gambar:', url);
                return null;
            }
        }

        document.getElementById('download-word-btn').addEventListener('click', async () => {
            try {
                // 1. Ambil Data Input
                const namaPerusahaan = document.getElementById('nama-perusahaan')?.value || '';
                const alamat = document.getElementById('alamat')?.value || '';
                const pic = document.getElementById('pic')?.value || '';
                const telepon = document.getElementById('telepon')?.value || '';
                const email = document.getElementById('email')?.value || '';
                const npwp = document.getElementById('npwp')?.value || '';
                const materi = document.getElementById('materi')?.value || '';
                const deskripsiTambahan = document.getElementById('deskripsi-tambahan')?.value || '';
                
                // 2. Ambil Logika PPN
                const includePPN = document.getElementById('include-ppn').checked;
                const ppnPercentage = parseFloat(document.getElementById('ppn-percentage').value) || 0;

                // 3. Persiapkan Gambar
                const logoBase64 = await fetchToBase64("{{ asset('assets/img/inix.png') }}");
                const signatureUrl = "{{ asset('storage/ttd/' . ($sales->ttd ?? '')) }}"; 
                const ttdBase64 = (signatureUrl && signatureUrl.length > 20) ? await fetchToBase64(signatureUrl) : null;

                // 4. Buat Tabel Header
                const headerTable = new Table({
                    width: { size: 100, type: WidthType.PERCENTAGE },
                    borders: { top: { style: BorderStyle.NONE }, bottom: { style: BorderStyle.NONE }, left: { style: BorderStyle.NONE }, right: { style: BorderStyle.NONE }, insideVertical: { style: BorderStyle.NONE }, insideHorizontal: { style: BorderStyle.NONE } },
                    rows: [
                        new TableRow({
                            children: [
                                new TableCell({
                                    width: { size: 50, type: WidthType.PERCENTAGE },
                                    children: [
                                        logoBase64 ? new Paragraph({
                                            children: [new ImageRun({
                                                data: Uint8Array.from(atob(logoBase64), c => c.charCodeAt(0)),
                                                transformation: { width: 170, height: 60 }
                                            })]
                                        }) : new Paragraph("LOGO")
                                    ]
                                }),
                                new TableCell({
                                    width: { size: 50, type: WidthType.PERCENTAGE },
                                    verticalAlign: VerticalAlign.TOP,
                                    children: [
                                        new Paragraph({ text: "Jl. Cipaganti No.95, Bandung", alignment: AlignmentType.RIGHT, style: "NormalFont" }),
                                        new Paragraph({ text: "Tel: 022-2032831", alignment: AlignmentType.RIGHT, style: "NormalFont" }),
                                        new Paragraph({ text: "Web: www.inixindobdg.co.id", alignment: AlignmentType.RIGHT, style: "NormalFont" })
                                    ]
                                })
                            ]
                        })
                    ]
                });

                // 5. Konfigurasi Tabel Peserta
                const pesertaRowsElement = document.querySelectorAll('.peserta-row');
                let totalHarga = 0;
                const tableRows = [];

                if (materi) {
                    tableRows.push(
                        new TableRow({
                            children: [
                                new TableCell({
                                    columnSpan: 4,
                                    shading: { fill: "E0E0E0" },
                                    children: [
                                        new Paragraph({
                                            text: materi,
                                            bold: true,
                                            alignment: AlignmentType.LEFT
                                        })
                                    ]
                                })
                            ]
                        })
                    );
                }

                // Header Kolom Peserta
                tableRows.push(
                    new TableRow({
                        tableHeader: true,
                        children: [
                            new TableCell({ children: [new Paragraph({ text: "No", bold: true, alignment: AlignmentType.CENTER })], shading: { fill: "F2F2F2" } }),
                            new TableCell({ children: [new Paragraph({ text: "Nama Peserta", bold: true })], shading: { fill: "F2F2F2" } }),
                            new TableCell({ children: [new Paragraph({ text: "Kontak Handphone & Email & Divisi", bold: true })], shading: { fill: "F2F2F2" } }),
                            new TableCell({ children: [new Paragraph({ text: "Harga", bold: true, alignment: AlignmentType.CENTER })], shading: { fill: "F2F2F2" } })
                        ]
                    })
                );

                // Isi Peserta
                pesertaRowsElement.forEach((row, index) => {
                    const nama = row.querySelector('.nama-peserta')?.value || '';
                    const kontak = row.querySelector('.kontak-peserta')?.value || '';
                    const harga = parseInt(row.querySelector('.harga-peserta')?.value || '0') || 0;
                    totalHarga += harga;

                    tableRows.push(new TableRow({
                        children: [
                            new TableCell({ children: [new Paragraph({ text: String(index + 1), alignment: AlignmentType.CENTER })] }),
                            new TableCell({ children: [new Paragraph(nama)] }),
                            new TableCell({ children: [new Paragraph(kontak)] }),
                            new TableCell({ children: [new Paragraph({ text: `Rp ${harga.toLocaleString('id-ID')},00`, alignment: AlignmentType.RIGHT })] })
                        ]
                    }));
                });

                // Baris Total
                tableRows.push(new TableRow({
                    children: [
                        new TableCell({ columnSpan: 3, children: [new Paragraph({ text: "Total", bold: true })] }),
                        new TableCell({ children: [new Paragraph({ text: `Rp ${totalHarga.toLocaleString('id-ID')},00`, bold: true, alignment: AlignmentType.RIGHT })] })
                    ]
                }));

                // Baris PPN
                if (includePPN && ppnPercentage > 0 && totalHarga > 0) {
                    const ppnMultiplier = 1 + (ppnPercentage / 100);
                    const totalPPN = totalHarga * ppnMultiplier;
                    tableRows.push(new TableRow({
                        children: [
                            new TableCell({ columnSpan: 3, children: [new Paragraph({ text: `Total Keseluruhan + PPN ${ppnPercentage}%`, bold: true })] }),
                            new TableCell({ children: [new Paragraph({ text: `Rp ${totalPPN.toLocaleString('id-ID')},00`, bold: true, alignment: AlignmentType.RIGHT })] })
                        ]
                    }));
                }

                const pesertaTable = new Table({
                    width: { size: 100, type: WidthType.PERCENTAGE },
                    rows: tableRows
                });

                // 6. Buat Bagian Tanda Tangan (REVISI: ALIGNMENT & BORDER)
                const sigRows = document.querySelectorAll('.signature-row');
                
                const name1 = sigRows[0]?.querySelector('.signature-name')?.value || '';
                const post1 = sigRows[0]?.querySelector('.signature-position')?.value || '';

                const name2 = sigRows[1]?.querySelector('.signature-name')?.value || '';
                const post2 = sigRows[1]?.querySelector('.signature-position')?.value || '';

                const name3 = sigRows[2]?.querySelector('.signature-name')?.value || '';
                const post3 = sigRows[2]?.querySelector('.signature-position')?.value || '';

                const signatureTable = new Table({
                    width: { size: 100, type: WidthType.PERCENTAGE },
                    borders: { 
                        top: { style: BorderStyle.NONE }, 
                        bottom: { style: BorderStyle.NONE }, 
                        left: { style: BorderStyle.NONE }, 
                        right: { style: BorderStyle.NONE }, 
                        insideVertical: { style: BorderStyle.NONE }, 
                        insideHorizontal: { style: BorderStyle.NONE } 
                    },
                    rows: [
                        new TableRow({
                            children: [
                                // KOLOM 1 (KIRI): Manual
                                new TableCell({
                                    width: { size: 33, type: WidthType.PERCENTAGE },
                                    verticalAlign: VerticalAlign.BOTTOM, // Align ke bawah agar sejajar
                                    children: [
                                        new Paragraph({ text: " ", spacing: { before: 1400 } }), 
                                        new Paragraph({ 
                                            children: [new TextRun({ text: name1, bold: true })], 
                                            alignment: AlignmentType.CENTER, 
                                            border: { top: { style: BorderStyle.SINGLE, size: 6 } },
                                            indent: { left: 720, right: 720 } // Perpendek garis (1 inch total indent)
                                        }),
                                        new Paragraph({ text: post1, alignment: AlignmentType.CENTER })
                                    ]
                                }),

                                // KOLOM 2 (TENGAH): TTD Database (Gambar)
                                new TableCell({
                                    width: { size: 33, type: WidthType.PERCENTAGE },
                                    verticalAlign: VerticalAlign.BOTTOM, // Align ke bawah agar sejajar
                                    children: [
                                        new Paragraph({ 
                                            alignment: AlignmentType.CENTER,
                                            children: ttdBase64 ? [
                                                new ImageRun({
                                                    data: Uint8Array.from(atob(ttdBase64), c => c.charCodeAt(0)),
                                                    transformation: { width: 100, height: 50 },
                                                })
                                            ] : [new TextRun({ text: "", size: 20 })],
                                            spacing: { before: 200, after: 100 }
                                        }),
                                        new Paragraph({ 
                                            children: [new TextRun({ text: name2, bold: true })], 
                                            alignment: AlignmentType.CENTER, 
                                            border: { top: { style: BorderStyle.SINGLE, size: 6 } },
                                            indent: { left: 720, right: 720 } // Perpendek garis
                                        }),
                                        new Paragraph({ text: post2, alignment: AlignmentType.CENTER })
                                    ]
                                }),

                                // KOLOM 3 (KANAN): Mengetahui
                                new TableCell({
                                    width: { size: 33, type: WidthType.PERCENTAGE },
                                    verticalAlign: VerticalAlign.BOTTOM, // Align ke bawah agar sejajar
                                    children: [
                                        new Paragraph({ 
                                            children: [new TextRun({ text: "Mengetahui", bold: true })], 
                                            alignment: AlignmentType.CENTER,
                                            spacing: { after: 1400 } // Spasi ditaruh dibawah "Mengetahui"
                                        }),
                                        new Paragraph({ 
                                            children: [new TextRun({ text: name3, bold: true })], 
                                            alignment: AlignmentType.CENTER, 
                                            border: { top: { style: BorderStyle.SINGLE, size: 6 } },
                                            indent: { left: 720, right: 720 } // Perpendek garis
                                        }),
                                        new Paragraph({ text: post3, alignment: AlignmentType.CENTER })
                                    ]
                                })
                            ]
                        })
                    ]
                });

                // 7. Styles Document
                const doc = new Document({
                    styles: {
                        default: {
                            document: {
                                run: { font: "Arial", size: 20 },
                                paragraph: { spacing: { line: 276 } }
                            }
                        },
                        paragraphStyles: [
                            { id: "NormalFont", name: "Normal Font", run: { font: "Arial", size: 20 } }
                        ]
                    },
                    sections: [{
                        properties: {
                            page: { margin: { top: 567, bottom: 567, left: 567, right: 567 } }
                        },
                        children: [
                            headerTable,
                            
                            new Paragraph({
                                text: "REGISTRATION FORM",
                                heading: HeadingLevel.HEADING_1,
                                alignment: AlignmentType.CENTER,
                                spacing: { before: 200, after: 200 },
                                color: "000000",
                            }),

                            // Data Pelanggan Table
                            new Table({
                                width: { size: 100, type: WidthType.PERCENTAGE },
                                rows: [
                                    new TableRow({ children: [new TableCell({ columnSpan: 2, shading: { fill: "F2F2F2" }, children: [new Paragraph({ text: "DATA PELANGGAN", bold: true, alignment: AlignmentType.CENTER })] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "Nama Perusahaan", bold: true })] }), new TableCell({ children: [new Paragraph(namaPerusahaan)] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "Alamat", bold: true })] }), new TableCell({ children: [new Paragraph(alamat)] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "PIC Penagihan Pelatihan", bold: true })] }), new TableCell({ children: [new Paragraph(pic)] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "Telepon", bold: true })] }), new TableCell({ children: [new Paragraph(telepon)] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "Email", bold: true })] }), new TableCell({ children: [new Paragraph(email)] })] }),
                                    new TableRow({ children: [new TableCell({ children: [new Paragraph({ text: "*NPWP", bold: true })] }), new TableCell({ children: [new Paragraph(npwp)] })] }),
                                ]
                            }),

                            new Paragraph({
                                children: [
                                    new TextRun({
                                        text: "*Wajib dilengkapi untuk pembuatan faktur pajak",
                                        color: "C3110C", 
                                        size: 16,
                                        italics: true
                                    })
                                ],
                                spacing: { after: 200 }
                            }),

                            // Materi sudah tidak di sini (karena masuk tabel)

                            pesertaTable,

                            new Paragraph({
                                text: "Syarat dan Ketentuan",
                                bold: true,
                                spacing: { before: 400, after: 100 }
                            }),

                            ...Array.from(document.getElementById('syarat-select').selectedOptions).map((opt, i) => 
                                new Paragraph({
                                    text: `${i+1}. ${opt.dataset.content}`,
                                    spacing: { after: 50 }
                                })
                            ),

                            new Paragraph({
                                text: `Dengan ini kami menyatakan untuk mengikuti pelatihan sesuai dengan kesepakatan.\n\nBandung, ${new Date().toLocaleDateString('id-ID')}`,
                                spacing: { before: 400, after: 400 }
                            }),

                            signatureTable,

                            ...(deskripsiTambahan ? [
                                new Paragraph({ text: "", spacing: { before: 200 } }),
                                new Table({
                                    width: { size: 100, type: WidthType.PERCENTAGE },
                                    rows: [ new TableRow({ children: [new TableCell({ children: [new Paragraph(deskripsiTambahan)] })] }) ]
                                })
                            ] : [])
                        ]
                    }]
                });

                // 8. Generate & Download
                const blob = await Packer.toBlob(doc);
                const wordBlob = new Blob([blob], { type: "application/vnd.openxmlformats-officedocument.wordprocessingml.document" });
                const url = URL.createObjectURL(wordBlob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Registration_Form_${namaPerusahaan.replace(/\s+/g, '_') || 'Download'}.docx`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);

            } catch (error) {
                console.error('Error DOCX:', error);
                alert('Gagal generate Word: ' + error.message);
            }
        });
    </script>
</body>

</html>
