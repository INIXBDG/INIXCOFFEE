<!DOCTYPE html>
<html>

<head>
    <title>Stock Opname</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th,
        table tr td {
            border: 1px solid #000;
            padding: 8px;
        }

        table tr th {
            background: #f2f2f2;
        }

        h2 {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <h2>
        Laporan Stock Opname
    </h2>

    <table>

        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Stock Awal</th>
                <th>Stock Sekarang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Notes</th>
                <th>Updated</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($barang as $item)
                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $item->kode_barang }}
                    </td>

                    <td>
                        {{ $item->nama_barang }}
                    </td>

                    <td>
                        {{ $item->stock_awal }}
                    </td>

                    <td>
                        {{ $item->stock_sekarang }}
                    </td>

                    <td>
                        {{ $item->kategori }}
                    </td>

                    <td>
                        {{ $item->satuan }}
                    </td>

                    <td>
                        {{ $item->notes }}
                    </td>

                    <td>
                        {{ $item->updated_at }}
                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

</body>

</html>
