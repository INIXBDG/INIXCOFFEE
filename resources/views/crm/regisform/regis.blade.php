<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        input, select, textarea { width: 100%; margin: 5px 0; padding: 5px; }
        select[multiple] { height: 150px; }
        textarea { height: 100px; resize: vertical; }
        button { padding: 10px; margin: 10px 0; }
        #peserta-list, #signature-list { margin-top: 10px; }
        .peserta-row, .signature-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .peserta-row input, .signature-row input { flex: 1; }
        .readonly { background-color: #f0f0f0; }
        #preview-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        #preview-content { background: white; padding: 20px; max-width: 900px; overflow: auto; }
        #preview-content .container { max-width: 190mm; padding: 5mm; font-size: 12pt; }
        .container { max-width: 800px; margin: 0 auto; position: relative; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .logo { text-align: left; }
        .logo img { width: 120px; height: auto; }
        .office-info { text-align: right; font-size: 10px; line-height: 14px; max-width: 200px; }
        .headertext { text-decoration: underline; font-weight: bold; font-size: 16px; margin: 5px 0; padding: 3px 0; text-align: center; }
        .section-header { font-weight: bold; font-size: 14px; background-color: #f5f5f5; padding: 3px 0; margin: 5px 0; }
        table { border-collapse: collapse; width: 100%; margin: 5px 0; }
        caption { caption-side: top; font-weight: bold; font-size: 14px; margin-bottom: 3px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 12px; word-wrap: break-word; }
        th { background-color: #f2f2f2; }
        thead { text-align: center; }
        th.no-column, td.no-column { width: 5%; min-width: 20px; }
        th.name-column, td.name-column { width: 35%; }
        th.contact-column, td.contact-column { width: 40%; }
        th.price-column, td.price-column { width: 20%; }
        .note { color: red; text-align: left; font-size: 10px; margin: 3px 0; }
        .syarat { text-align: left; margin-top: 5px; page-break-inside: avoid; }
        .syarat h3 { font-size: 14px; margin-bottom: 3px; }
        .syarat ol { font-size: 12px; padding-left: 15px; margin: 3px 0; }
        .statement { text-align: left; font-size: 12px; margin: 5px 0; page-break-inside: avoid; }
        .description { text-align: left; font-size: 12px; margin: 10px 0; page-break-inside: avoid; border: 1px solid #ccc; padding: 8px; }
        .description h3 { font-size: 14px; margin-bottom: 5px; }
        .signature-section { display: flex; justify-content: flex-end; gap: 20px; margin-top: 10px; page-break-inside: avoid; }
        .signature { text-align: center; width: 30%; }
        .signature p { margin: 2px 0; font-size: 12px; }
        .signature .name { margin-top: 20px; border-top: 1px solid #000; padding-top: 2px; }
        .signature .position { font-size: 10px; color: #555; }
        @media print {
            body { margin: 0; padding: 0; font-size: 12pt; }
            .container { max-width: 190mm; width: 100%; margin: 0; padding: 5mm; }
            .header { margin-bottom: 2mm; }
            .logo img { width: 100px; }
            .office-info { font-size: 10pt; line-height: 12pt; max-width: 70mm; line-height: 1; }
            .headertext { font-size: 14pt; margin: 2mm 0; padding: 1mm 0; text-align: center; }
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
            .signature-section { margin-top: 10mm; display: flex; justify-content: flex-end; gap: 10mm; page-break-inside: avoid; }
            .signature { text-align: center; width: 30%; }
            .signature p { font-size: 10pt; margin: 2pt 0; }
            .signature .name { margin-top: 10mm; padding-top: 1mm; border-top: 1px solid #000; }
            .signature .position { font-size: 9pt; color: #555; }
            button { display: none; }
            @page { size: A4; margin: 5mm; }
        }
    </style>
</head>
<body>
    <h2>Input Data Registrasi</h2>
    <!-- Debug: Tampilkan $ketentuan untuk memeriksa data -->
    <pre style="display: none;">{{ print_r($ketentuan->toArray(), true) }}</pre>
    <form id="regis-form">
        <!-- Data Perusahaan (Read-only dari $lead->perusahaan) -->
        <h3>Data Perusahaan</h3>
        <label>Nama Perusahaan:</label>
        <input type="text" id="nama-perusahaan" class="readonly" value="{{ $lead->perusahaan->nama_perusahaan ?? '-' }}" readonly>
        <label>Alamat:</label>
        <input type="text" id="alamat" class="readonly" value="{{ $lead->perusahaan->alamat ?? '-' }}" readonly>
        <label>PIC Penagihan:</label>
        <input type="text" id="pic" class="readonly" value="{{ $lead->perusahaan->cp ?? '-' }}" readonly>
        <label>Telepon dan Email:</label>
        <input type="text" id="telepon-email" class="readonly" value="{{ $lead->perusahaan->no_telp ?? '-' }} & {{ $lead->perusahaan->email ?? '-' }}" readonly>
        <label>NPWP:</label>
        <input type="text" id="npwp" class="readonly" value="{{ $lead->perusahaan->npwp ?? '-' }}" readonly>

        <!-- Materi Pelatihan (dari $lead->materiRelation) -->
        <label>Materi dan Tanggal Pelatihan:</label>
        <input type="text" class="readonly" id="materi" value="{{ $lead->materiRelation->nama_materi }} || {{ \Carbon\Carbon::parse($lead->periode_mulai)->format('d M Y') }} → {{ \Carbon\Carbon::parse($lead->periode_selesai)->format('d M Y') }}" readonly>

        <!-- Data Peserta (Dynamic) -->
        <h3>Data Peserta</h3>
        <div id="peserta-list"></div>
        <button type="button" id="add-peserta">Tambah Peserta</button>

        <!-- Pilih Syarat dari $ketentuan (Multi-Select) -->
        <h3>Syarat & Ketentuan</h3>
        <label>Pilih Syarat (bisa lebih dari satu):</label>
        <select id="syarat-select" multiple required>
            @foreach ($ketentuan as $ket)
                <option value="{{ $ket->id }}" data-content="{{ $ket->ketentuan }}">{{ $ket->ketentuan }}</option>
            @endforeach
        </select>

        <!-- Input Tanda Tangan -->
        <h3>Tanda Tangan</h3>
        <div id="signature-list">
            <div class="signature-row">
                <input type="text" placeholder="Nama Penandatangan 1" class="signature-name" required>
                <input type="text" placeholder="Jabatan Penandatangan 1" class="signature-position" required>
            </div>
            <div class="signature-row">
                <input type="text" placeholder="Nama Penandatangan 2" class="signature-name" required>
                <input type="text" placeholder="Jabatan Penandatangan 2" class="signature-position" required>
            </div>
            <div class="signature-row">
                <input type="text" placeholder="Nama Penandatangan 3" class="signature-name" required>
                <input type="text" placeholder="Jabatan Penandatangan 3" class="signature-position" required>
            </div>
        </div>

        <!-- Deskripsi Tambahan -->
        <h3>Deskripsi Tambahan</h3>
        <textarea id="deskripsi-tambahan" placeholder="Masukkan deskripsi tambahan (opsional)"></textarea>

        <button type="button" id="preview-btn">Generate Preview</button>
    </form>

    <!-- Modal untuk Preview -->
    <div id="preview-modal">
        <div id="preview-content"></div>
    </div>

    <script>
        let pesertaCount = 0;
        const termsData = @json($ketentuan);

        // Debug: Log data ketentuan untuk memastikan terisi
        console.log('Data ketentuan:', termsData);

        // Tambah row peserta
        document.getElementById('add-peserta').addEventListener('click', () => {
            pesertaCount++;
            const row = document.createElement('div');
            row.className = 'peserta-row';
            row.innerHTML = `
                <input type="text" placeholder="Nama Peserta" class="nama-peserta" required>
                <input type="text" placeholder="Kontak HP & Email & Divisi" class="kontak-peserta" required>
                <input type="text" placeholder="Harga (Rp)" class="harga-peserta" required>
                <button type="button" onclick="this.parentElement.remove()">Hapus</button>
            `;
            document.getElementById('peserta-list').appendChild(row);
        });

        // Generate Preview
        document.getElementById('preview-btn').addEventListener('click', () => {
            // Ambil data perusahaan dari input read-only
            const namaPerusahaan = document.getElementById('nama-perusahaan').value;
            const alamat = document.getElementById('alamat').value;
            const pic = document.getElementById('pic').value;
            const teleponEmail = document.getElementById('telepon-email').value;
            const npwp = document.getElementById('npwp').value;
            const materi = document.getElementById('materi').value;
            const deskripsiTambahan = document.getElementById('deskripsi-tambahan').value;

            // Ambil data peserta
            const pesertaRows = document.querySelectorAll('.peserta-row');
            let pesertaHTML = '';
            let totalHarga = 0;
            pesertaRows.forEach((row, index) => {
                const nama = row.querySelector('.nama-peserta').value;
                const kontak = row.querySelector('.kontak-peserta').value;
                const harga = parseInt(row.querySelector('.harga-peserta').value) || 0;
                totalHarga += harga;
                pesertaHTML += `
                    <tr>
                        <td class="no-column">${index + 1}</td>
                        <td class="name-column">${nama}</td>
                        <td class="contact-column">${kontak}</td>
                        <td class="price-column">Rp ${harga.toLocaleString('id-ID')},00</td>
                    </tr>
                `;
            });
            const totalPPN = totalHarga * 1.11;
            pesertaHTML += `
                <tr><th colspan="3">Total</th><td class="price-column">Rp ${totalHarga.toLocaleString('id-ID')},00</td></tr>
                <tr><th colspan="3">Total Keseluruhan + PPN 11%</th><td class="price-column">Rp ${totalPPN.toLocaleString('id-ID')},00</td></tr>
            `;

            // Ambil syarat yang dipilih (multi-select)
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

            // Ambil tanda tangan
            const signatureRows = document.querySelectorAll('.signature-row');
            let signatureHTML = '';
            signatureRows.forEach(row => {
                const name = row.querySelector('.signature-name').value || 'Tidak diisi';
                const position = row.querySelector('.signature-position').value || 'Tidak diisi';
                signatureHTML += `
                    <div class="signature">
                        <p class="name">${name}</p>
                        <p class="position">${position}</p>
                    </div>
                `;
            });

            // Generate deskripsi tambahan
            const deskripsiHTML = deskripsiTambahan ? `
                <div class="description">
                    <p>${deskripsiTambahan.replace(/\n/g, '<br>')}</p>
                </div>
            ` : '';

            // Generate HTML preview
            const previewHTML = `
                <div class="container">
                    <div class="header">
                        <div class="logo"><img src="{{asset('assets/img/inix.png')}}" alt="Inixindo Logo" /></div>
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
                            <tr><th style="width: 25%">Telepon dan Email</th><td style="width: 75%">${teleponEmail}</td></tr>
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

            // Tampilkan preview di modal
            const modal = document.getElementById('preview-modal');
            const content = document.getElementById('preview-content');
            content.innerHTML = previewHTML + '<button onclick="printPreview()">Print to PDF</button><button onclick="closeModal()">Tutup</button>';
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
                .signature-section { margin-top: 10mm; display: flex; justify-content: flex-end; gap: 10mm; page-break-inside: avoid; }
                .signature { text-align: center; width: 30%; }
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
            // printWindow.print();
        }
    </script>
</body>
</html>