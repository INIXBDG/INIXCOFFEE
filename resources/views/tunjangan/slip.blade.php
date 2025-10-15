<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Slip Gaji</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        input,
        select {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 12px;
            margin: 12px 0;
            border-radius: 4px;
        }

        .salary-row {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 12px;
        }

        .salary-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .tunjangan-list-container,
        .potongan-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .readonly {
            background-color: #f0f0f0;
        }

        .container {
            max-width: 190mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12pt;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5mm;
        }

        .logo img {
            width: 200px;
            height: auto;
        }

        .office-info {
            text-align: right;
            font-size: 10pt;
            line-height: 12pt;
            max-width: 70mm;
        }

        .headertext {
            text-decoration: underline;
            font-weight: bold;
            font-size: 14pt;
            margin: 2mm 0;
            padding: 1mm 0;
            text-align: center;
        }

        .employee-table {
            border-collapse: collapse;
            width: 100%;
            margin: -3mm 0;
        }

        .employee-table th,
        .employee-table td {
            border: 1px solid #ccc;
            padding: 4pt 6pt;
            text-align: left;
            font-size: 10pt;
        }

        .employee-table th {
            background-color: #f2f2f2;
        }

        .combined-table {
            width: 100%;
            margin: 3mm 0;
            border-collapse: separate;
            border-spacing: 0 6px;
            font-size: 10pt;
        }

        .combined-table th,
        .combined-table td {
            text-align: left;
            padding: 6pt 8pt;
            font-weight: normal;
        }

        .combined-table .pendapatan-header {
            background-color: #006A67;
            color: white;
            font-weight: bold;
            border-radius: 6px 0 0 0;
            padding: 8pt;
        }

        .combined-table .potongan-header {
            background-color: #FF2929;
            color: white;
            font-weight: bold;
            border-radius: 0 6px 0 0;
            padding: 8pt;
        }

        .combined-table thead tr:nth-child(2) th {
            background-color: #f7f7f7;
            font-weight: bold;
            border-bottom: 2px solid #eaeaea;
        }

        .combined-table tbody tr {
            background-color: #fafafa;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border-radius: 4px;
        }

        .combined-table tbody tr td:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }

        .combined-table tbody tr td:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .combined-table tbody tr:hover {
            background-color: #f0f8f8;
        }

        .take-home-pay {
            margin-top: 5mm;
            padding: 8pt;
            border: none;
            background-color: #e6f3fa;
            text-align: right;
            font-weight: bold;
            font-size: 11pt;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            gap: 10mm;
            margin-top: 10mm;
            page-break-inside: avoid;
            align-items: flex-start;
        }

        .signature {
            text-align: center;
            width: 30%;
            position: relative;
            min-height: 80pt;
        }

        .signature p {
            margin: 2pt 0;
            font-size: 10pt;
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

        .signature img.signature-img {
            width: 80pt;
            height: auto;
            margin-top: 5mm;
        }

        .error {
            color: red;
            font-size: 10px;
            margin-top: 2px;
            display: none;
        }

        @media screen and (max-width: 991px) {
            .form-container .d-flex.justify-content-center {
                flex-direction: column !important;
                align-items: center !important;
            }

            .form-container .col-md-4 {
                width: 100% !important;
                margin-bottom: 15px;
            }

            .form-container .col-md-4 label,
            .form-container .col-md-4 select,
            .form-container .col-md-4 button {
                width: 100%;
            }

            .card-title {
                font-size: 18px;
                text-align: center;
            }

            .salary-list-container,
            .tunjangan-list-container,
            .potongan-list-container {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 576px) {
            .container-fluid {
                padding: 10px;
            }

            .form-container {
                margin: 0 !important;
            }

            .form-container .col-md-4 {
                padding: 0;
                margin-bottom: 15px;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .card-title {
                font-size: 16px;
            }

            .btn.click-primary {
                font-size: 14px;
                padding: 8px 12px;
            }

            .employee-table thead th,
            .employee-table tbody td,
            .combined-table thead th,
            .combined-table tbody td {
                font-size: 9pt;
            }
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

            button {
                display: none;
            }

            .form-container {
                display: none;
            }

            .take-home-pay {
                border: none;
                background-color: #e6f3fa;
            }

            @page {
                size: A4;
                margin: 5mm;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="form-container">
            <h2 class="card-title text-2xl font-bold mb-6 text-center text-blue-800">Slip Gaji</h2>
            <form id="payslip-form" class="bg-white p-8 rounded-lg shadow-xl">
                <h3 class="text-lg font-semibold mb-4">Data Karyawan</h3>
                <label class="block text-sm font-medium text-gray-700">Nama Karyawan:</label>
                <input type="text" id="nama-karyawan" disabled value="{{ $user->karyawan->nama_lengkap }}"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <p id="nama-karyawan-error" class="error">Nama karyawan wajib diisi</p>
                <label class="block text-sm font-medium text-gray-700">Jabatan:</label>
                <input type="text" id="jabatan" disabled value="{{ $user->karyawan->jabatan }}"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <p id="jabatan-error" class="error">Jabatan wajib diisi</p>
                <label class="block text-sm font-medium text-gray-700">Rekening MayBank:</label>
                <input type="text" id="maybank" disabled value="{{$user->karyawan->rekening_maybank}}"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <p id="maybank-error" class="error">Rekening MayBank wajib diisi</p>
                <label class="block text-sm font-medium text-gray-700">Rekening BCA:</label>
                <input type="text" id="bca" disabled value="{{ $user->karyawan->rekening_bca }}"
                    class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <p id="bca-error" class="error">Rekening BCA wajib diisi</p>
                <label class="block text-sm font-medium text-gray-700">Periode Gaji:</label>
                <div class="d-flex justify-content-center">
                    <div class="col-md-4 mx-1">
                        <select id="bulan" required
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10" selected>Oktober</option>
                            <option value="11" disabled>November</option>
                            <option value="12" disabled>Desember</option>
                        </select>
                    </div>
                    <div class="col-md-4 mx-1">
                        <input type="number" id="tahun" min="2000" max="9999" required value="{{ date('Y') }}"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            disabled>
                    </div>
                </div>
                <p id="tahun-error" class="error">Tahun harus antara 2000 dan 9999</p>

                <h3 class="text-lg font-semibold mb-4 mt-6">Komponen Gaji</h3>
                <div id="salary-list" class="salary-list-container">
                    <div class="salary-row">
                        <label class="block text-sm font-medium text-gray-700">Gaji Pokok (Fixed):</label>
                        <input type="text" value="Gaji Pokok" disabled
                            class="nama-komponen mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <input type="number" value="{{ $user->karyawan->gaji }}" disabled id="gaji_pokok"
                            class="jumlah-komponen mt-1 block w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            min="0">
                    </div>
                </div>
                <div id="tunjangan-list" class="tunjangan-list-container"></div>
                <div id="potongan-list" class="potongan-list-container"></div>

                <div class="mt-6">
                    <button type="button" id="preview-btn"
                        class="w-full bg-green-600 text-white p-3 rounded-md hover:bg-green-700">Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const authId = {{ Auth::id() }};
        const hrdSignature = "{{ asset('storage/ttd/' . $HRD->karyawan->ttd) }}";
        const hrdName = "{{$HRD->karyawan->nama_lengkap}}";
        const hrdDivisi = "{{$HRD->karyawan->divisi}}";
        const signatureImagePath = "{{ asset('storage/ttd/' . $user->karyawan->ttd) }}";
        const namaUser = "{{$user->karyawan->nama_lengkap}}";
        const divisiUser = "{{$user->karyawan->divisi}}";
        let tunjanganData = [];

        document.addEventListener('DOMContentLoaded', () => {
            const bulanSelect = document.getElementById('bulan');
            const currentMonth = new Date().getMonth() + 1; // 1-based index (October 2025 = 10)
            Array.from(bulanSelect.options).forEach(option => {
                const monthValue = parseInt(option.value);
                if (monthValue > currentMonth) {
                    option.disabled = true;
                }
            });
            bulanSelect.value = currentMonth;
        });

        function formatRupiah(angka, prefix = 'Rp') {
            var isNegative = angka < 0;
            var number_string = Math.abs(angka).toString();
            var split = number_string.split('.');
            var bulat = split[0];
            var desimal = split[1] || '';
            var sisa = bulat.length % 3;
            var rupiah = bulat.substr(0, sisa);
            var ribuan = bulat.substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                var separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            desimal = desimal.substr(0, 2);
            rupiah = rupiah + (desimal ? ',' + desimal : '');
            rupiah = prefix ? prefix + ' ' + rupiah : rupiah;
            return isNegative ? '-' + rupiah : rupiah;
        }

        function validateForm() {
            let isValid = true;
            const namaKaryawan = document.getElementById('nama-karyawan').value.trim();
            const jabatan = document.getElementById('jabatan').value.trim();
            const maybank = document.getElementById('maybank').value.trim();
            const bca = document.getElementById('bca').value.trim();
            const tahun = parseInt(document.getElementById('tahun').value);
            const gajiPokok = parseFloat(document.getElementById('gaji_pokok').value);

            document.getElementById('nama-karyawan-error').style.display = namaKaryawan ? 'none' : 'block';
            document.getElementById('jabatan-error').style.display = jabatan ? 'none' : 'block';
            document.getElementById('maybank-error').style.display = maybank ? 'none' : 'block';
            document.getElementById('bca-error').style.display = bca ? 'none' : 'block';
            document.getElementById('tahun-error').style.display = (tahun >= 2000 && tahun <= 9999) ? 'none' : 'block';

            if (!namaKaryawan || !jabatan || !maybank || !bca || tahun < 2000 || tahun > 9999 || isNaN(gajiPokok) || gajiPokok < 0) {
                isValid = false;
            }

            document.getElementById('preview-btn').disabled = !isValid;
            return isValid;
        }

        async function fetchTunjangan() {
            const bulan = document.getElementById('bulan').value;
            const tahun = document.getElementById('tahun').value;

            try {
                const response = await fetch(`/getTunjanganSaya/${authId}/${bulan}/${tahun}`);
                const result = await response.json();
                if (result.success) {
                    tunjanganData = result.data;
                    const tunjanganList = document.getElementById('tunjangan-list');
                    const potonganList = document.getElementById('potongan-list');
                    tunjanganList.innerHTML = '';
                    potonganList.innerHTML = '';
                    tunjanganData.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'salary-row';
                        const jumlah = parseFloat(item.total);
                        const targetList = item.keterangan === 'Tunjangan' ? tunjanganList : potonganList;
                        row.innerHTML = `
                            <label class="block text-sm font-medium text-gray-700">${item.jenistunjangan.nama_tunjangan}:</label>
                            <input type="number" value="${jumlah}" disabled
                                class="jumlah-komponen mt-1 block w-full p-3 border border-gray-300 rounded-md">
                        `;
                        targetList.appendChild(row);
                    });
                } else {
                    alert('Gagal memuat data tunjangan: ' + result.message);
                    tunjanganData = [];
                }
            } catch (error) {
                alert('Error: ' + error.message);
                tunjanganData = [];
            }
        }

        document.getElementById('preview-btn').addEventListener('click', async () => {
            if (!validateForm()) {
                alert('Harap isi semua kolom yang diperlukan dengan data yang valid!');
                return;
            }
            await fetchTunjangan();
            const printWindow = window.open('', '', 'height=600, width=900');
            if (!printWindow) {
                alert('Jendela cetak diblokir. Silakan izinkan pop-up untuk situs ini.');
                return;
            }
            const previewHTML = generatePayslipHTML();
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Slip Gaji</title>
                        <style>
                            body { margin: 0; padding: 0; font-size: 12pt; font-family: Arial, sans-serif; }
                            .container { max-width: 190mm; width: 100%; margin: 0; padding: 5mm; }
                            .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2mm; }
                            .logo img { width: 200px; }
                            .office-info { font-size: 10pt; line-height: 12pt; max-width: 70mm; line-height: 1; }
                            .headertext { font-size: 14pt; margin: 3mm 0; padding: 1mm 0; text-decoration: underline; font-weight: bold; text-align: center; }
                            .employee-table { border-collapse: collapse; width: 100%; margin: -3mm 0; }
                            .employee-table th, .employee-table td { border: 1px solid #ccc; padding: 4pt 6pt; text-align: left; font-size: 10pt; }
                            .employee-table th { background-color: #f2f2f2; }
                            .combined-table { width: 100%; page-break-inside: avoid; margin: 3mm 0; border-collapse: separate; border-spacing: 0 6px; font-size: 10pt; }
                            .combined-table th, .combined-table td { text-align: left; padding: 6pt 8pt; font-weight: normal; }
                            .combined-table .pendapatan-header { background-color: #006A67; color: white; font-weight: bold; border-radius: 6px 0 0 0; padding: 8pt; }
                            .combined-table .potongan-header { background-color: #FF2929; color: white; font-weight: bold; border-radius: 0 6px 0 0; padding: 8pt; }
                            .combined-table thead tr:nth-child(2) th { background-color: #f7f7f7; font-weight: bold; border-bottom: 2px solid #eaeaea; }
                            .combined-table tbody tr { background-color: #fafafa; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-radius: 4px; }
                            .combined-table tbody tr td:first-child { border-top-left-radius: 4px; border-bottom-left-radius: 4px; }
                            .combined-table tbody tr td:last-child { border-top-right-radius: 4px; border-bottom-right-radius: 4px; }
                            .combined-table tbody tr:hover { background-color: #f0f8f8; }
                            .take-home-pay { margin-top: 5mm; padding: 8pt; border: none; background-color: #e6f3fa; text-align: right; font-weight: bold; font-size: 11pt; }
                            .statement { font-size: 10pt; margin: 2mm 0; text-align: left; page-break-inside: avoid; }
                            .signature-section { margin-top: -4mm; display: flex; justify-content: flex-end; gap: 77mm; page-break-inside: avoid; align-items: flex-start; }
                            .signature { text-align: center; width: 30%; position: relative; min-height: 80pt; }
                            .signature img.signature-img { width: 62pt; height: auto; margin-top: -6mm; }
                            .signature p { font-size: 10pt; margin: 2pt 0; }
                            .signature .name { margin-top: 10mm; padding-top: 1mm; border-top: 1px solid #000; }
                            .signature .position { font-size: 9pt; color: #555; }
                            @page { size: A4; margin: 5mm; }
                        </style>
                    </head>
                    <body>${previewHTML}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        });

        function generatePayslipHTML() {
            const namaKaryawan = document.getElementById('nama-karyawan').value;
            const jabatan = document.getElementById('jabatan').value;
            const maybank = document.getElementById('maybank').value;
            const bca = document.getElementById('bca').value;
            const bulanValue = document.getElementById('bulan').value;
            const bulan = document.getElementById('bulan').options[document.getElementById('bulan').selectedIndex].text;
            const tahun = document.getElementById('tahun').value;
            const gajiPokok = parseFloat(document.getElementById('gaji_pokok').value) || 0;

            let tunjanganRows = '';
            let totalTunjangan = 0;
            let totalPotongan = 0;
            let indexTunjangan = 2; // Start from 2 since Gaji Pokok is 1
            let indexPotongan = 1;

            // Collect tunjangan and potongan items
            const tunjanganItems = [];
            const potonganItems = [];
            tunjanganData.forEach(item => {
                const jumlah = parseFloat(item.total) || 0;
                const namaTunjangan = item.jenistunjangan.nama_tunjangan;
                if (item.keterangan === 'Tunjangan' && jumlah !== 0) {
                    tunjanganItems.push({ nama: namaTunjangan, jumlah: jumlah, index: indexTunjangan++ });
                    totalTunjangan += jumlah;
                } else if (item.keterangan === 'Potongan' && jumlah !== 0) {
                    potonganItems.push({ nama: namaTunjangan, jumlah: Math.abs(jumlah), index: indexPotongan++ });
                    totalPotongan += Math.abs(jumlah);
                }
            });

            // Include Gaji Pokok in totalTunjangan
            totalTunjangan += gajiPokok;

            // Determine the maximum number of rows needed
            const maxRows = Math.max(tunjanganItems.length + 2, potonganItems.length + 1); // +2 for Gaji Pokok and Total Pendapatan, +1 for Total Potongan

            // Build rows for the combined table
            for (let i = 0; i < maxRows; i++) {
                let pendapatanNo = '';
                let pendapatanNama = '';
                let pendapatanJumlah = '';
                let potonganNo = '';
                let potonganNama = '';
                let potonganJumlah = '';

                // Pendapatan column
                if (i === 0) {
                    // Gaji Pokok at the top with index 1
                    pendapatanNo = '1';
                    pendapatanNama = 'Gaji Pokok';
                    pendapatanJumlah = formatRupiah(gajiPokok);
                } else if (i - 1 < tunjanganItems.length) {
                    // Other tunjangan
                    const item = tunjanganItems[i - 1];
                    pendapatanNo = item.index;
                    pendapatanNama = item.nama;
                    pendapatanJumlah = formatRupiah(item.jumlah);
                } else if (i === maxRows - 1) {
                    // Total Pendapatan
                    pendapatanNo = '';
                    pendapatanNama = 'Total Pendapatan';
                    pendapatanJumlah = formatRupiah(totalTunjangan);
                }

                // Potongan column
                if (i < potonganItems.length) {
                    const item = potonganItems[i];
                    potonganNo = item.index;
                    potonganNama = item.nama;
                    potonganJumlah = formatRupiah(item.jumlah);
                } else if (i === maxRows - 1) {
                    // Total Potongan
                    potonganNo = '';
                    potonganNama = 'Total Potongan';
                    potonganJumlah = formatRupiah(totalPotongan);
                }

                tunjanganRows += `
                    <tr>
                        <td style="width: 5%">${pendapatanNo}</td>
                        <td style="width: 30%">${pendapatanNama}</td>
                        <td style="width: 20%">${pendapatanJumlah}</td>
                        <td style="width: 5%">${potonganNo}</td>
                        <td style="width: 30%">${potonganNama}</td>
                        <td style="width: 20%">${potonganJumlah}</td>
                    </tr>
                `;
            }

            const totalBersih = totalTunjangan - totalPotongan;

            const combinedTable = `
                <table class="combined-table">
                    <thead>
                        <tr>
                            <th colspan="3" class="pendapatan-header">Pendapatan</th>
                            <th colspan="3" class="potongan-header">Potongan</th>
                        </tr>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 30%">Pendapatan</th>
                            <th style="width: 20%">Jumlah</th>
                            <th style="width: 5%">No</th>
                            <th style="width: 30%">Potongan</th>
                            <th style="width: 20%">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tunjanganRows}
                    </tbody>
                </table>
            `;

            const takeHomePayBox = `
                <div class="take-home-pay">
                    TAKE HOME PAY: ${formatRupiah(totalBersih)}
                </div>
            `;

            const signatureHTML = `
                <div class="signature">
                    <div style="padding-top: 20.4mm;">
                        <img class="signature-img" src="${signatureImagePath}" alt="User Signature">
                        <p class="name">${namaUser}</p>
                        <p class="position">${divisiUser}</p>
                    </div>
                </div>
                <div class="signature">
                    <div style="padding-top: 20.4mm;">
                        <img class="signature-img" src="${hrdSignature}" alt="HR Signature">
                        <p class="name">${hrdName}</p>
                        <p class="position">${hrdDivisi}</p>
                    </div>
                </div>
            `;

            return `
                <div class="container">
                    <div class="header">
                        <div class="logo"><img src="{{ asset('assets/img/inix.png') }}" alt="Logo Perusahaan"></div>
                        <div class="office-info">
                            <p class="font-bold">INIXINDO BANDUNG</p>
                            <p>Jl Cipaganti No 95 Bandung</p>
                            <p>Telepon : 022 2032 831</p>
                            <p><a href="http://www.inixindobdg.co.id" style="text-decoration:none; color: black;">www.inixindobdg.co.id</a></p>
                        </div>
                    </div>
                    <div class="headertext">SLIP GAJI</div>
                    <table class="employee-table">
                        <thead><tr><th colspan="3">DETAIL KARYAWAN</th></tr></thead>
                        <tbody>
                            <tr><th style="width: 25%">Nama Karyawan</th><td style="width: 75%" colspan="2">${namaKaryawan}</td></tr>
                            <tr><th style="width: 25%">Jabatan</th><td style="width: 75%" colspan="2">${jabatan}</td></tr>
                            <tr><th style="width: 25%">MayBank</th><td style="width: 75%" colspan="2">${maybank}</td></tr>
                            <tr><th style="width: 25%">BCA</th><td style="width: 75%" colspan="2">${bca}</td></tr>
                            <tr><th style="width: 25%">Periode Gaji</th><td style="width: 75%" colspan="2">${bulan} ${tahun}</td></tr>
                        </tbody>
                    </table>
                    ${combinedTable}
                    ${takeHomePayBox}
                    <div class="statement">
                        <p>Slip gaji ini bersifat rahasia dan hanya untuk keperluan karyawan.</p> <br />
                        <p style="margin-left: 80%;">Bandung, ${new Date().toLocaleDateString('id-ID')}</p>
                    </div>
                    <div class="signature-section">${signatureHTML}</div>
                </div>
            `;
        }

        document.querySelectorAll('#payslip-form input, #payslip-form select').forEach(input => input.addEventListener(
            'input', validateForm));
        validateForm();
    </script>
</body>

</html>