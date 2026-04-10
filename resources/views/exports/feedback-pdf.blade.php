<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Report</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom CSS for better readability */
        table th, table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }
        .table-category {
            margin-bottom: 20px;
        }
        .category-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        @foreach ($post as $item)
            <div class="company-info mb-4">
                <h3>Nama Perusahaan: <span class="text-primary">{{ $item['nama_perusahaan'] }}</span></h3>
                <h3>Nama Materi: <span class="text-primary">{{ $item['data'][0]['nama_materi'] }}</span></h3>
                <h3>Tanggal: <span class="text-primary">{{ $item['data'][0]['tanggal_awal'] }} - {{ $item['data'][0]['tanggal_akhir'] }}</span></h3>
                <h3>Sales: <span class="text-primary">{{ $item['data'][0]['sales_key'] }}</span></h3>
                <h3>
                    Instruktur: 
                    <span class="text-primary">
                        {{ $item['data'][0]['instruktur_key'] ?? '-' }}, 
                        {{ $item['data'][0]['instruktur_key2'] ?? '-' }}, 
                        {{ $item['data'][0]['asisten_key'] ?? '-' }}
                    </span>
                </h3>
            </div>

            <!-- Materi Table -->
            <div class="table-category">
                <h4 class="category-title">Materi</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>M 1</th>
                            <th>M 2</th>
                            <th>M 3</th>
                            <th>M 4</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->M1 }}</td>
                                <td>{{ $feedback['datafeedbacks']->M2 }}</td>
                                <td>{{ $feedback['datafeedbacks']->M3 }}</td>
                                <td>{{ $feedback['datafeedbacks']->M4 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pelayanan Table -->
            <div class="table-category">
                <h4 class="category-title">Pelayanan</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>P 1</th>
                            <th>P 2</th>
                            <th>P 3</th>
                            <th>P 4</th>
                            <th>P 5</th>
                            <th>P 6</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->P1 }}</td>
                                <td>{{ $feedback['datafeedbacks']->P2 }}</td>
                                <td>{{ $feedback['datafeedbacks']->P3 }}</td>
                                <td>{{ $feedback['datafeedbacks']->P4 }}</td>
                                <td>{{ $feedback['datafeedbacks']->P5 }}</td>
                                <td>{{ $feedback['datafeedbacks']->P6 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Fasilitas Table -->
            <div class="table-category">
                <h4 class="category-title">Fasilitas</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>F 1</th>
                            <th>F 2</th>
                            <th>F 3</th>
                            <th>F 4</th>
                            <th>F 5</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->F1 }}</td>
                                <td>{{ $feedback['datafeedbacks']->F2 }}</td>
                                <td>{{ $feedback['datafeedbacks']->F3 }}</td>
                                <td>{{ $feedback['datafeedbacks']->F4 }}</td>
                                <td>{{ $feedback['datafeedbacks']->F5 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Instruktur Table -->
            <div class="table-category">
                <h4 class="category-title">Instruktur</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>I 1</th>
                            <th>I 2</th>
                            <th>I 3</th>
                            <th>I 4</th>
                            <th>I 5</th>
                            <th>I 6</th>
                            <th>I 7</th>
                            <th>I 8</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->I1 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I2 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I3 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I4 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I5 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I6 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I7 }}</td>
                                <td>{{ $feedback['datafeedbacks']->I8 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Instruktur 2 Table -->
            <div class="table-category">
                <h4 class="category-title">Instruktur 2</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>I#2 1</th>
                            <th>I#2 2</th>
                            <th>I#2 3</th>
                            <th>I#2 4</th>
                            <th>I#2 5</th>
                            <th>I#2 6</th>
                            <th>I#2 7</th>
                            <th>I#2 8</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->I1b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I2b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I3b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I4b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I5b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I6b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I7b }}</td>
                                <td>{{ $feedback['datafeedbacks']->I8b }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Asisten Table -->
            <div class="table-category">
                <h4 class="category-title">Asisten</h4>
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>AS 1</th>
                            <th>AS 2</th>
                            <th>AS 3</th>
                            <th>AS 4</th>
                            <th>AS 5</th>
                            <th>AS 6</th>
                            <th>AS 7</th>
                            <th>AS 8</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['data'] as $feedback)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $feedback['datafeedbacks']->I1as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I2as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I3as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I4as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I5as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I6as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I7as }}</td>
                                <td>{{ $feedback['datafeedbacks']->I8as }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <!-- Include Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
