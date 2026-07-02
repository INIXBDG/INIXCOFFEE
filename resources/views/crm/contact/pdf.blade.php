<!DOCTYPE html>
<html>
<head>
    <title>Data Database Client</title>
    <style>
        @page { size: landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        th { background-color: #d9edf7; text-align: center; }
        h3 { text-align: center; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>

    <h3>Data Database Client {{ $salesName ? '- Sales: ' . $salesName : '' }}</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 12%;">Nama Perusahaan</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 8%;">Lokasi</th>
                <th style="width: 5%;">Sales</th>
                <th style="width: 5%;">Status</th>
                <th style="width: 10%;">NPWP</th>
                <th style="width: 17%;">Alamat</th>
                <th style="width: 8%;">Contact Person</th>
                <th style="width: 8%;">Email</th>
                <th style="width: 7%;">No. Telp</th>
                <th style="width: 7%;">Ditambah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $contact)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $contact->nama_perusahaan ?? '-' }}</td>
                    <td>{{ $contact->kategori_perusahaan ?? '-' }}</td>
                    <td>{{ $contact->lokasi ?? '-' }}</td>
                    <td style="text-align: center;">{{ $contact->sales_key ?? '-' }}</td>
                    <td style="text-align: center;">{{ $contact->status ?? '-' }}</td>
                    <td>{{ $contact->npwp ?? '-' }}</td>
                    <td>{{ $contact->alamat ?? '-' }}</td>
                    <td>{{ $contact->cp ?? '-' }}</td>
                    <td>{{ $contact->email ?? '-' }}</td>
                    <td>{{ $contact->no_telp ?? '-' }}</td>
                    <td>{{ $contact->created_at ? $contact->created_at->translatedFormat('d F Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
