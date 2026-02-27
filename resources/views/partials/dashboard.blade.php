<div class="card" style="margin-bottom: 8px">
    <div class="card-body d-flex justify-content-start">
        <div class="col-md-12 mx-1">
            <label for="tahun" class="form-label">Tahun</label>
            <select id="tahun" class="form-select" aria-label="tahun">
                <option disabled selected>Pilih Tahun</option>
                @php
                    $tahun_sekarang = now()->year;
                    for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                        $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                        echo "<option value=\"$tahun\" $selected>$tahun</option>";
                    }
                @endphp
            </select>
        </div>
    </div>
</div>
<div class="card" style="margin-bottom: 8px">
    <div class="card-body">
        <div class="row">
            <div class="col-6 d-flex justify-content-start">
                <p style="margin: 0">Total Saat ini adalah: <span id="totalSalesDisplay">Rp 0</span></p>
            </div>
            <div class="col-6 d-flex justify-content-end">

            </div>
        </div>
        <div id="progress-container" style="position: relative;">
            <div id="progress-bar" class="progress-bar"></div>
            <div id="car" class="car"></div>
            <div class="target-label-right">Goal
                <img src="{{ asset('css/finish-flag.png') }}" alt="finish" style="width: 20px">
            </div>
        </div>

        <div class="horizontal-ruler-labels" style="position: relative; width: 100%; height: 20px;">
            <div class="horizontal-ruler-labels" style="position: relative; width: 100%;"></div>
        </div>
    </div>
</div>
<div class="card" style="margin-bottom: 8px">
    <div class="card-body" style="overflow-x:auto">
        @php
        $divisi = auth()->user()->karyawan->divisi;
        $manager = auth()->user()->jabatan;

        if (in_array($manager, ['Education Manager', 'GM', 'SPV Sales', 'Office Manager', 'Koordinator Office'])) {
        $salesDisabled = true;
        $officeDisabled = true;
        $instrukturDisabled = true;
        $itsmDisable = true;
        } elseif ($divisi == 'Office') {
        $salesDisabled = false;
        $officeDisabled = true;
        $instrukturDisabled = false;
        $itsmDisable = false;
        } elseif ($divisi == 'Education') {
        $salesDisabled = false;
        $officeDisabled = false;
        $instrukturDisabled = true;
        $itsmDisable = false;
        } elseif ($divisi == 'Sales & Marketing') {
        $salesDisabled = true;
        $officeDisabled = false;
        $instrukturDisabled = false;
        $itsmDisable = false;
        } elseif ($divisi == 'IT Service Management') {
        $officeDisabled = false;
        $instrukturDisabled = false;
        $itsmDisable = true;
        $salesDisabled = false;
        } else {
        $salesDisabled = true;
        $officeDisabled = true;
        $instrukturDisabled = true;
        $itsmDisable = true;
        }
        @endphp
        <ul class="nav nav-tabs" id="chart" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inix-tab" data-bs-toggle="tab" data-bs-target="#inix-tab-pane"
                    type="button" role="tab" aria-controls="inix-tab-pane" aria-selected="true">Inixindo Dalam
                    Angka</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-tab-pane"
                    type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="true" {{ $salesDisabled ? '' : 'disabled' }}>Sales</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="office-tab" data-bs-toggle="tab" data-bs-target="#office-tab-pane"
                    type="button" role="tab" aria-controls="office-tab-pane" aria-selected="false" {{ $officeDisabled ? '' : 'disabled' }}>Office</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="instruktur-tab" data-bs-toggle="tab" data-bs-target="#instruktur-tab-pane"
                    type="button" role="tab" aria-controls="instruktur-tab-pane" aria-selected="false" {{ $instrukturDisabled ? '' : 'disabled' }}>Instruktur</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="itsm-tab" data-bs-toggle="tab" data-bs-target="#itsm-tab-pane"
                    type="button" role="tab" aria-controls="itsm-tab-pane" aria-selected="false" {{ $itsmDisable ? '' : 'disabled' }}>ITSM</button>
            </li>
        </ul>
        <div class="tab-content" id="chartContent" style="">
            <div class="tab-pane fade" id="inix-tab-pane" role="tabpanel" aria-labelledby="inix-tab" tabindex="0">
                <div class="container-fluid">
                    <div class="row my-1">
                        <div class="col-md-12" style="height: auto; padding:0">
                            <div class="card" style="height: auto">
                                <div class="card-body">
                                    <div class="row justify-content-center text-center">
                                        <h5 class="position-header">Keterlambatan</h5>
                                    </div>
                                    <div class="row justify-content-center align-items-end modern-ranking">

                                        <!-- First Place -->
                                        <div
                                            class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center text-center podium-card mb-3 order-1 order-md-2">
                                            <div class="card-podium rank-1">
                                                <div class="circle-satu first-position shadow">
                                                    <img src="{{ asset('css/b1.png') }}" alt="Keterlambatan ke-1"
                                                        class="present-photo-satu rounded-circle border border-white"
                                                        id="present-photo-satu">
                                                </div>
                                                <p class="position-label">Peringkat 1</p>
                                                <img src="{{ asset('images/medal-1.png') }}" class="medal-bawah"
                                                    alt="Medal">
                                            </div>
                                        </div>

                                        <!-- Second Place -->
                                        <div
                                            class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center text-center podium-card mb-3 order-2 order-md-1">
                                            <div class="card-podium rank-2">
                                                <div class="circle second-position shadow-sm">
                                                    <img src="{{ asset('storage/photos/pemain2.jpg') }}"
                                                        alt="Keterlambatan ke-2"
                                                        class="present-photo rounded-circle border border-white"
                                                        id="present-photo-dua">
                                                </div>
                                                <p class="position-label">Peringkat 2</p>
                                                <img src="{{ asset('images/medal-2.png') }}" alt="Medali Perak"
                                                    class="medal-bawah">
                                            </div>
                                        </div>

                                        <!-- Third Place -->
                                        <div
                                            class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center text-center podium-card mb-3 order-3 order-md-3">
                                            <div class="card-podium rank-3">
                                                <div class="circle third-position shadow-sm">
                                                    <img src="{{ asset('storage/photos/pemain3.jpg') }}"
                                                        alt="Keterlambatan ke-3"
                                                        class="present-photo rounded-circle border border-white"
                                                        id="present-photo-tiga">
                                                </div>
                                                <p class="position-label">Peringkat 3</p>
                                                <img src="{{ asset('images/medal-3.png') }}" alt="Medali Perunggu"
                                                    class="medal-bawah">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row my-1">
                        <div class="col-md-12" style="padding:0">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <h5>Terbaik</h5>
                                    </div>
                                    <div class="row">
                                        <!-- SALES TERBAIK -->
                                        <div
                                            class="col-12 col-sm-6 col-md-3 col-lg-3 text-center d-flex justify-content-center mb-4">
                                            <div class="card_foto">
                                                <div class="imgbox">
                                                    <img src="{{ asset('images/download.png') }}" alt="Sales Image"
                                                        class="dynamic-image" id="foto_sales">
                                                </div>
                                                <div class="details">
                                                    <span class="caption">Sales Terbaik</span>
                                                    <h4 class="title" id="nama_sales">John doe</h4>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- INSTRUKTUR TERBAIK -->
                                        <div
                                            class="col-12 col-sm-6 col-md-3 col-lg-3 text-center d-flex justify-content-center mb-4">
                                            <div class="card_foto">
                                                <div class="imgbox">
                                                    <img src="{{ asset('images/download.png') }}" alt="Instruktur Image"
                                                        class="dynamic-image" id="foto_instruktur">
                                                </div>
                                                <div class="details">
                                                    <span class="caption mb-30">Instruktur Terbaik</span>
                                                    <h4 class="title" id="nama_instruktur">John doe</h4>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- OFFICE TERBAIK -->
                                        <div
                                            class="col-12 col-sm-6 col-md-3 col-lg-3 text-center d-flex justify-content-center mb-4">
                                            <div class="card_foto">
                                                <div class="imgbox">
                                                    <img src="{{ asset('images/download.png') }}" alt="Office Image"
                                                        class="dynamic-image" id="foto_office">
                                                </div>
                                                <div class="details">
                                                    <span class="caption">Office Terbaik</span>
                                                    <h4 class="title" id="nama_office">John doe</h4>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ITSM TERBAIK -->
                                        <div
                                            class="col-12 col-sm-6 col-md-3 col-lg-3 text-center d-flex justify-content-center mb-4">
                                            <div class="card_foto">
                                                <div class="imgbox">
                                                    <img src="{{ asset('images/download.png') }}" alt="ITSM Image"
                                                        class="dynamic-image" id="foto_itsm">
                                                </div>
                                                <div class="details">
                                                    <span class="caption">ITSM Terbaik</span>
                                                    <h4 class="title" id="nama_itsm">John doe</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row my-1">
                        <div class="col-md-12" style="padding:0">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <h5>Kelas</h5>
                                    </div>
                                    <div class="row justify-content-center">
                                        <!-- Baris pertama dengan 3 kolom -->
                                        <div class="col-12 col-md-4 text-center d-flex justify-content-center mb-2">
                                            <div class="card-uiverse" style="width: 100%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2>Total Kelas</h2>
                                                    <p id="total_kelas"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 text-center d-flex justify-content-center mb-2">
                                            <div class="card-uiverse" style="width: 100%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2>Jumlah Peserta</h2>
                                                    <p id="jumlah_peserta"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 text-center d-flex justify-content-center mb-2">
                                            <div class="card-uiverse" style="width: 100%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2>Offline</h2>
                                                    <p id="offline"></p>
                                                    <h2>Virtual</h2>
                                                    <p id="virtual"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Baris kedua dengan 2 kolom -->
                                    <div class="row justify-content-center">
                                        <div class="col-12 col-md-6 text-center d-flex justify-content-center mb-2">
                                            <div class="card-uiverse" style="width: 100%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2>Rata-rata Kelas Per Bulan</h2>
                                                    <p id="ratarata_kelas_perbulan"></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 text-center d-flex justify-content-center mb-2">
                                            <div class="card-uiverse" style="width: 100%;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h2>Rata-rata Peserta Per Bulan</h2>
                                                    <p id="jumlah_peserta_perbulan"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-2">
                                        <div class="col">
                                            <div class="card-uiverse-feedback" style="width: 100%;">
                                                <div class="row" style="width:100%; margin:10px">
                                                    <div class="col-12 text-center">
                                                        <h2>Rata-rata Feedback</h2>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="row text-center">
                                                            <div class="col-6 col-md-3">
                                                                <h4>Materi</h4>
                                                                <span id="materi"></span>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <h4>Fasilitas</h4>
                                                                <span id="fasilitas"></span>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <h4>Pelayanan</h4>
                                                                <span id="pelayanan"></span>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <h4>Instruktur</h4>
                                                                <span id="instruktur"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade show " id="sales-tab-pane" role="tabpanel" aria-labelledby="sales-tab"
                tabindex="0" style="height: auto">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-perquartal-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-perquartal" type="button" role="tab" aria-controls="pills-perquartal"
                            aria-selected="true">Penjualan Per Sales Per Triwulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-bulan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-bulan" type="button" role="tab" aria-controls="pills-bulan"
                            aria-selected="false">Penjualan Per Sales Per Tahun</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-perquartal" role="tabpanel"
                        aria-labelledby="pills-perquartal-tab" tabindex="0">
                        <div class="col-12" id="chartjs">
                            <label for="salesKeySelect">Pilih Sales:</label>
                            <select id="salesKeySelect" class="form-select" style="width: 100px"
                                onchange="updateChart()">
                            </select>
                            <canvas id="PenjualanPerSalesPerQuartalChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-bulan" role="tabpanel"
                        aria-labelledby="pills-bulan-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPenjualanPerSalesPerTahun">
                                <canvas id="PenjualanPerSalesPerTahunChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="office-tab-pane" role="tabpanel" aria-labelledby="office-tab" tabindex="0">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-analisiskelas-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-analisiskelas" type="button" role="tab"
                            aria-controls="pills-analisiskelas" aria-selected="true">Rekap Analisa Margin</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-absen-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-absen" type="button" role="tab" aria-controls="pills-absen"
                            aria-selected="false">Rekap Absen</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-absenperbulan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-absenperbulan" type="button" role="tab"
                            aria-controls="pills-absenperbulan" aria-selected="false">Rekap Absen Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-souvenir-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-souvenir" type="button" role="tab" aria-controls="pills-souvenir"
                            aria-selected="false">Rekap Souvenir</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-analisiskelas" role="tabpanel"
                        aria-labelledby="pills-analisiskelas-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasKelasAnalisisChart">
                                <canvas id="KelasAnalisisChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-absen" role="tabpanel"
                        aria-labelledby="pills-absen-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasAbsenChart">
                                <canvas id="AbsenChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-absenperbulan" role="tabpanel"
                        aria-labelledby="pills-absenperbulan-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="ContainerCanvasAbsenPerBulan">
                                <label for="monthSelect_absenperbulan" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_absenperbulan"
                                    onchange="updateChartAbsenPerbulan(this.value)">
                                    <option value="All">Semua</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="AbsenPerBulanChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-souvenir" role="tabpanel"
                        aria-labelledby="pills-souvenir-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="SouvenirChartContainerCanvas">
                                <canvas id="SouvenirChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="instruktur-tab-pane" role="tabpanel" aria-labelledby="instruktur-tab" tabindex="0">
                <div class="row m-2">
                    {{-- <div class="card"> --}}
                        {{-- <div class="card-body"> --}}
                        <!-- From Uiverse.io by Yaya12085 --> 
                            <div class="card-dash">
                                <div class="title-dash">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M171.5 38.8C192.3 4 236.5-10 274 7.6l7.2 3.8C316 32.3 330 76.5 312.4 114l0 0-14.1 30 109.7 0 7.4 .4c36.3 3.7 64.6 34.4 64.6 71.6 0 13.2-3.6 25.4-9.8 36 6.1 10.6 9.7 22.8 9.8 36 0 18.3-6.9 34.8-18 47.5 1.3 5.3 2 10.8 2 16.5 0 25.1-12.9 47-32.2 59.9-1.9 35.5-29.4 64.2-64.4 67.7l-7.4 .4-104.1 0c-18 0-35.9-3.4-52.6-9.9l-7.1-3-.7-.3-6.6-3.2-.7-.3-12.2-6.5c-12.3-6.5-23.3-14.7-32.9-24.1-4.1 26.9-27.3 47.4-55.3 47.4l-32 0c-30.9 0-56-25.1-56-56L0 200c0-30.9 25.1-56 56-56l32 0c10.8 0 20.9 3.1 29.5 8.5l50.1-106.5 .6-1.2 2.7-5 .6-.9zM56 192c-4.4 0-8 3.6-8 8l0 224c0 4.4 3.6 8 8 8l32 0c4.4 0 8-3.6 8-8l0-224c0-4.4-3.6-8-8-8l-32 0zM253.6 51c-14.8-6.9-32.3-1.6-40.7 12l-2.2 4-56.8 120.9c-3.5 7.5-5.5 15.5-6 23.7l-.1 4.2 0 112.9 .2 7.9c2.4 32.7 21.4 62.1 50.7 77.7l11.5 6.1 6.3 3.1c12.4 5.6 25.8 8.5 39.4 8.5l104.1 0 2.4-.1c12.1-1.2 21.6-11.5 21.6-23.9l-.2-2.6c-.1-.9-.2-1.7-.4-2.6-2.7-12.1 4.3-24.2 16-28 9.7-3.1 16.6-12.2 16.6-22.8 0-4.3-1.1-8.2-3.1-11.8-6.3-11.1-2.8-25.2 8-32 6.8-4.3 11.2-11.8 11.2-20.2 0-7.1-3.1-13.5-8.2-18-5.2-4.6-8.2-11.1-8.2-18s3-13.4 8.2-18c5.1-4.5 8.2-10.9 8.2-18l-.1-2.4c-1.1-11.3-10.1-20.3-21.4-21.4l-2.4-.1-147.5 0c-8.2 0-15.8-4.2-20.2-11.1-4.4-6.9-5-15.7-1.5-23.1L269 93.6c7-15 1.4-32.7-12.5-41L253.6 51z"/></svg>
                                    </span>
                                    <p class="title-text-dash">
                                        Skor CSAT
                                    </p>
                                </div>
                                <div class="data-dash">
                                    <p class="datacsat" id="csatValue">-</p>
                                    <div class="range-dash">
                                        <div class="fill-dash" id="csatBar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-dash">
                                <div class="title-dash">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M464 256a208 208 0 1 0 -416 0 208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0 256 256 0 1 1 -512 0zm372.2 46.3c11.8-3.6 23.7 6.1 19.6 17.8-19.8 55.9-73.1 96-135.8 96-62.7 0-116-40-135.8-95.9-4.1-11.6 7.8-21.4 19.6-17.8 34.7 10.6 74.2 16.5 116.1 16.5 42 0 81.5-6 116.3-16.6zM144 208a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm192-32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>
                                    </span>
                                    <p class="title-text-dash">
                                        Rekomendasi Materi
                                    </p>
                                </div>
                                <div class="data-dash">
                                    <p class="datacsat" id="rekomendasiValue">-</p>
                                    <div class="range-dash">
                                        <div class="fill-dash" id="rekomendasiBar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-dash">
                                <div class="title-dash">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M96 64c-35.3 0-64 28.7-64 64l0 256c-17.7 0-32 14.3-32 32s14.3 32 32 32l512 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-256c0-35.3-28.7-64-64-64L96 64zM480 384l-64 0 0-32c0-17.7-14.3-32-32-32l-96 0c-17.7 0-32 14.3-32 32l0 32-160 0 0-256 384 0 0 256z"/></svg>
                                    </span>
                                    <p class="title-text-dash">
                                        Sesi Sharing Knowledge
                                    </p>
                                </div>
                                <div class="data-dash">
                                    <p class="datacsat" id="sharingValue">-</p>
                                    <div class="range-dash">
                                        <div class="fill-dash" id="sharingBar">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-dash">
                                <div class="title-dash">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M96 64c-35.3 0-64 28.7-64 64l0 256c-17.7 0-32 14.3-32 32s14.3 32 32 32l512 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-256c0-35.3-28.7-64-64-64L96 64zM480 384l-64 0 0-32c0-17.7-14.3-32-32-32l-96 0c-17.7 0-32 14.3-32 32l0 32-160 0 0-256 384 0 0 256z"/></svg>
                                    </span>
                                    <p class="title-text-dash">
                                        Materi Baru
                                    </p>
                                </div>
                                <div class="data-dash">
                                    <p class="datacsat" id="materiValue">-</p>
                                    <div class="range-dash">
                                        <div class="fill-dash" id="materiBar">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-dash">
                                <div class="title-dash">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                            <path
                                                d="M96 64c-35.3 0-64 28.7-64 64l0 256c-17.7 0-32 14.3-32 32s14.3 32 32 32l512 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l0-256c0-35.3-28.7-64-64-64L96 64zM480 384l-64 0 0-32c0-17.7-14.3-32-32-32l-96 0c-17.7 0-32 14.3-32 32l0 32-160 0 0-256 384 0 0 256z" />
                                        </svg>
                                    </span>
                                    <p class="title-text-dash">Silabus Baru</p>
                                </div>
                                <div class="data-dash">
                                    <p class="datacsat" id="silabusValue">-</p>
                                    <div class="range-dash">
                                        <div class="fill-dash" id="silabusBar"></div>
                                    </div>
                                </div>
                            </div>
                            {{--
                        </div> --}}
                        {{-- </div> --}}
                </div>
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-nilaifeedbackperbulan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-nilaifeedbackperbulan" type="button" role="tab"
                            aria-controls="pills-nilaifeedbackperbulan" aria-selected="true">Nilai Feedback Per
                            Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalmengajar-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-totalmengajar" type="button" role="tab"
                            aria-controls="pills-totalmengajar" aria-selected="false">Total Mengajar Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalMateri-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-totalMateri" type="button" role="tab"
                            aria-controls="pills-totalMateri" aria-selected="false">Total Materi Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalMengajarPerMateri-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-totalMengajarPerMateri" type="button" role="tab"
                            aria-controls="pills-totalMengajarPerMateri" aria-selected="false">Total Mengajar Per Materi
                            Per Bulan</button>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlahUpdateMateriPerbulan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-jumlahUpdateMateriPerbulan" type="button" role="tab"
                            aria-controls="pills-jumlahUpdateMateriPerbulan" aria-selected="false">
                            Jumlah Update Materi Perbulan
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-silabusPerInstrukturPerTahun-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-silabusPerInstrukturPerTahun" type="button" role="tab"
                            aria-controls="pills-silabusPerInstrukturPerTahun" aria-selected="false"
                            onclick="loadSilabusPerInstrukturPerTahunChart()">
                            Silabus Per Instruktur Per Tahun
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-feedbackInstrukturPerTahun-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-feedbackInstrukturPerTahun" type="button" role="tab">
                            Feedback Instruktur Per Tahun
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-hariMengajarInstrukturPerTahun-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-hariMengajarInstrukturPerTahun" type="button" role="tab"
                            aria-controls="pills-hariMengajarInstrukturPerTahun" aria-selected="false">
                            Hari Mengajar Instruktur per tahun
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-nilaifeedbackperbulan"
                        role="tabpanel" aria-labelledby="pills-nilaifeedbackperbulan-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasNilaiFeedback">
                                <label for="bulan" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="bulan" onchange="updateChartNilaiFeedback(this.value)">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="NilaiFeedbackChart" width="400" height="200"></canvas>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalmengajar" role="tabpanel"
                        aria-labelledby="pills-totalmengajar-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMengajarChart">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalmengajar"
                                    onchange="updateChartTotalMengajar(this.value)">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="totalMengajarChart" width="400" height="200"></canvas>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMateri" role="tabpanel"
                        aria-labelledby="pills-totalMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMateri"
                                    onchange="updateChartTotalMateri(this.value)">
                                    <option value="All">Semua</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="totalMateriChart" width="400" height="200"></canvas>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMateri" role="tabpanel"
                        aria-labelledby="pills-totalMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMateri"
                                    onchange="updateChartTotalMateri(this.value)">
                                    <option value="All">Semua</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="totalMateriChart" width="400" height="200"></canvas>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMengajarPerMateri"
                        role="tabpanel" aria-labelledby="pills-totalMengajarPerMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMengajarPerMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMengajarPerMateri"
                                    onchange="updateChartTotalMengajarPerMateri(this.value)">
                                    <option value="All">Semua</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <canvas id="totalMengajarPerMateriChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-jumlahUpdateMateriPerbulan" role="tabpanel"
                        aria-labelledby="pills-jumlahUpdateMateriPerbulan-tab">
                        <div
                            class="card shadow-sm p-4 position-relatived-flex flex-column align-items-center text-center">
                            <label for="monthSelect_JumlahUpdateMateriPerbulan" class="form-label fw-medium mb-2">
                                Pilih Bulan
                            </label>

                            <select class="form-select shadow-sm mb-4" id="monthSelect_JumlahUpdateMateriPerbulan"
                                style="max-width: 260px;">
                                <option value="All">Semua Bulan</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                            <div class="position-relative w-100 d-flex justify-content-center"
                                style="min-height: 500px;">
                                <canvas id="jumlahUpdateMateriPerbulanChart" style="max-width: 1500px;"></canvas>
                                <div id="chartLoading_JumlahUpdateMateriPerbulan"
                                    class="position-absolute top-50 start-50 translate-middle text-center d-none">
                                    <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                                    <p class="mt-2 text-muted fw-medium">Memuat data chart...</p>
                                </div>
                                <div id="chartEmpty_JumlahUpdateMateriPerbulan"
                                    class="position-absolute top-50 start-50 translate-middle text-center text-muted d-none">
                                    <p class="fw-medium mt-2 mb-0">
                                        Tidak ada data update materi pada periode ini
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-silabusPerInstrukturPerTahun" role="tabpanel"
                        aria-labelledby="pills-silabusPerInstrukturPerTahun-tab">
                        <div class="card d-flex flex-column p-3">
                            <h5 class="text-center mb-3">Silabus Per Instruktur Per Tahun</h5>

                            <div style="height: 500px">
                                <canvas id="silabusPerInstrukturPerTahunChart"></canvas>
                            </div>

                            <div id="silabusPerInstrukturPerTahunEmpty" class="d-none text-center text-muted mt-3">
                                Tidak ada data silabus per instruktur
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-feedbackInstrukturPerTahun" role="tabpanel">
                        <div class="card d-flex flex-column p-3">
                            <h5 class="text-center mb-3">Rata-Rata Feedback Instruktur</h5>

                            <div style="height: 450px">
                                <canvas id="feedbackChart"></canvas>
                            </div>

                            <div id="nilaiInstrukturEmpty" class="d-none text-center text-muted mt-3">
                                Tidak ada data feedback
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-hariMengajarInstrukturPerTahun" role="tabpanel"
                        aria-labelledby="pills-hariMengajarInstrukturPerTahun-tab">
                        <div class="card d-flex flex-column p-3">
                            <h5 class="text-center mb-3">Hari Mengajar Instruktur Per Tahun</h5>

                            <div style="height: 500px; position: relative;">
                                <canvas id="hariMengajarInstrukturPerTahunChart"></canvas>
                            </div>

                            <div id="hariMengajarInstrukturPerTahunEmpty" class="d-none text-center text-muted mt-3">
                                Tidak ada data hari mengajar instruktur
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="tab-pane fade" id="itsm-tab-pane" role="tabpanel" aria-labelledby="itsm-tab" tabindex="0">
                <ul class="nav nav-pills mb-3" id="itsm-pills-tab" role="tablist">

                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-pic-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-jumlah-pic" type="button" role="tab" aria-controls="pills-jumlah-pic"
                            aria-selected="false">Jumlah PIC</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-ticketing-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-jumlah-ticketing" type="button" role="tab"
                            aria-controls="pills-jumlah-ticketing" aria-selected="true">Jumlah Ticketing</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-rerata-durasi-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-rerata-durasi" type="button" role="tab"
                            aria-controls="pills-rerata-durasi" aria-selected="false">Rata-rata Durasi
                            Pengerjaan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-rerata-ketepatan-response-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-rerata-ketepatan-response" type="button" role="tab"
                            aria-controls="pills-rerata-ketepatan-response" aria-selected="false">Rata-rata Kecepatan
                            Respon</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-permintaan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-jumlah-permintaan" type="button" role="tab"
                            aria-controls="pills-jumlah-permintaan" aria-selected="false">Jumlah Permintaan Per
                            Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-permintaan-sering-diajukan-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-permintaan-sering-diajukan" type="button" role="tab"
                            aria-controls="pills-permintaan-sering-diajukan" aria-selected="false">Permintaan Sering
                            Diajukan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link sla-tab-trigger" id="pills-sla-programmer-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-sla-programmer"
                            type="button"
                            role="tab"
                            aria-controls="pills-sla-programmer"
                            aria-selected="false"
                            data-team="programmer" data-loaded="false">SLA Programmer
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link sla-tab-trigger" id="pills-sla-tech-support-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-sla-tech-support"
                            type="button"
                            role="tab"
                            aria-controls="pills-sla-tech-support"
                            aria-selected="false"
                            data-team="tech-support" data-loaded="false">SLA Technical Support
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link sla-tab-trigger" id="pills-sla-event-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-sla-event"
                            type="button"
                            role="tab"
                            aria-controls="pills-sla-event"
                            aria-selected="false"
                            data-loaded="false">
                            SLA Webinar
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link sla-tab-trigger" id="pills-sla-digital-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-sla-digital"
                            type="button"
                            role="tab"
                            aria-controls="pills-sla-digital"
                            aria-selected="false"
                            data-loaded="false">
                            SLA Digital
                        </button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link sla-tab-trigger" id="pills-uptime-presentase-tab"
                            data-bs-toggle="pill"
                            data-bs-target="#pills-uptime-presentase"
                            type="button"
                            role="tab"
                            aria-controls="pills-uptime-presentase"
                            aria-selected="false"
                            data-loaded="false">
                            Presentase Uptime
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-pic" role="tabpanel"
                        aria-labelledby="pills-jumlah-pic-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPicChart">
                                <label for="filterMonthPIC" class="form-label">Pilih Bulan:</label>
                                <select id="filterMonthPIC" name="filterMonthPIC">
                                    <option value="all">Semua Bulan</option>
                                </select>
                                <canvas id="jumlahPICChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-ticketing" role="tabpanel"
                        aria-labelledby="pills-jumlah-ticketing-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasjumlahTicketing">
                                <label for="filterBulan" class="form-label">Pilih Bulan:</label>
                                <select id="filterBulan" name="filterBulan">
                                    <option value="all">Semua Bulan</option>
                                </select>
                                <canvas id="jumlahTicketingChart" width="1000" height="800"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-rerata-durasi" role="tabpanel"
                        aria-labelledby="pills-rerata-durasi-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasRerataDurasi">
                                <label for="filterMonth" class="form-label">Pilih Bulan:</label>
                                <select id="filterMonth" name="filterMonth">
                                    <option value="all">Semua Bulan</option>
                                </select>
                                <canvas id="rerataDurasiChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-rerata-ketepatan-response"
                        role="tabpanel" aria-labelledby="pills-rerata-ketepatan-response-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasKetepatanRespond">
                                <label for="filterMonthKetepatan" class="form-label">Pilih Bulan:</label>
                                <select id="filterMonthKetepatan" name="filterMonthKetepatan">
                                    <option value="all">Semua Bulan</option>
                                </select>
                                <canvas id="rerataKetepatanResponseChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-permintaan" role="tabpanel"
                        aria-labelledby="pills-jumlah-permintaan" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPermintaanPerbulan">
                                <h3>Jumlah Permintaan Per Bulan</h3>
                                <canvas id="jumlahPermintaanPerBulanChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-permintaan-sering-diajukan"
                        role="tabpanel" aria-labelledby="pills-permintaan-sering-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPermintaanSering">
                                <h3>Permintaan Yang Sering Diajukan</h3>
                                <canvas id="permintaanSeringDiajukanChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" style="height:auto;" id="pills-sla-programmer" role="tabpanel"
                        aria-labelledby="pills-sla-programmer-tab" tabindex="0">
                        <div class="container-fluid" id="sla-programmer-container">

                            <div id="sla-period-display" class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-primary" role="alert">
                                        <h4 class="alert-heading mb-0 fs-5" id="sla_current_period">
                                            Memuat periode data...
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold">
                                            <i class="bi bi-bar-chart-line-fill me-2"></i>
                                            Dashboard Kinerja SLA Tim Programmer
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="tim-sla-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Respon</h6>
                                                        <div class="fs-2 fw-bold" id="tim-sla-response">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Avg.
                                                            Waktu Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="tim-avg-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Total
                                                            Tiket</h6>
                                                        <div class="fs-2 fw-bold" id="tim-total-tickets">...</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-12">
                                                    <canvas id="slaTimPriorityChart"
                                                        style="width: 100%; height: 300px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold">
                                            <i class="bi bi-people-fill me-2"></i>
                                            Dashboard Kinerja SLA Per Programmer
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Nama Programmer</th>
                                                            <th>SLA Resolusi</th>
                                                            <th>SLA Respon</th>
                                                            <th>Avg. Resolusi (Jam)</th>
                                                            <th>Total Tiket</th>
                                                            <th>Detail (H/M/L/O)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sla-user-table-body">
                                                        <tr>
                                                            <td colspan="6" class="text-center">Memuat data...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold text-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            Dashboard Kinerja SLA Insiden Kritis
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Resolusi Kritis</h6>
                                                        <div class="fs-2 fw-bold" id="kritis-sla-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Respon Kritis</h6>
                                                        <div class="fs-2 fw-bold" id="kritis-sla-response">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Avg.
                                                            Waktu Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="kritis-avg-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Total
                                                            Insiden</h6>
                                                        <div class="fs-2 fw-bold" id="kritis-total-insiden">...</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <h4 class="mt-4">Detail Insiden Kritis</h4>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Laporan</th>
                                                            <th>SLA Met?</th>
                                                            <th>Waktu Resolusi (Jam)</th>
                                                            <th>Waktu Respon (Jam)</th>
                                                            <th>Responder</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="sla-kritis-table-body">
                                                        <tr>
                                                            <td colspan="6" class="text-center">Memuat data...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" style="height:auto;" id="pills-sla-tech-support" role="tabpanel"
                        aria-labelledby="pills-sla-tech-support-tab" tabindex="0">
                        <div class="container-fluid" id="sla-tech-support-container">

                            <div id="sla-period-display-ts" class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-primary" role="alert">
                                        <h4 class="alert-heading mb-0 fs-5" id="ts_sla_current_period">
                                            Memuat periode data...
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold">
                                            <i class="bi bi-bar-chart-line-fill me-2"></i>
                                            Dashboard Kinerja SLA Tim Technical Support
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="ts-tim-sla-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Respon</h6>
                                                        <div class="fs-2 fw-bold" id="ts-tim-sla-response">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Avg.
                                                            Waktu Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="ts-tim-avg-resolution">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Total
                                                            Tiket</h6>
                                                        <div class="fs-2 fw-bold" id="ts-tim-total-tickets">...</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <canvas id="tsSlaTimPriorityChart"
                                                        style="width: 100%; height: 300px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold">
                                            <i class="bi bi-people-fill me-2"></i>
                                            Dashboard Kinerja SLA Per Technical Support
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Nama Technical Support</th>
                                                            <th>SLA Resolusi</th>
                                                            <th>SLA Respon</th>
                                                            <th>Avg. Resolusi (Jam)</th>
                                                            <th>Total Tiket</th>
                                                            <th>Detail (H/M/L/O)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="ts-sla-user-table-body">
                                                        <tr>
                                                            <td colspan="6" class="text-center">Memuat data...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header fs-5 fw-semibold text-danger">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            Dashboard Kinerja SLA Insiden Kritis (TS)
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-4">
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Resolusi Kritis</h6>
                                                        <div class="fs-2 fw-bold" id="ts-kritis-sla-resolution">...
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">SLA
                                                            Respon Kritis</h6>
                                                        <div class="fs-2 fw-bold" id="ts-kritis-sla-response">...</div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Avg.
                                                            Waktu Resolusi</h6>
                                                        <div class="fs-2 fw-bold" id="ts-kritis-avg-resolution">...
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-6 mb-3">
                                                    <div class="card card-body text-center h-100 shadow-sm">
                                                        <h6 class="card-title text-muted text-uppercase small">Total
                                                            Insiden</h6>
                                                        <div class="fs-2 fw-bold" id="ts-kritis-total-insiden">...</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h4 class="mt-4">Detail Insiden Kritis</h4>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Laporan</th>
                                                            <th>SLA Met?</th>
                                                            <th>Waktu Resolusi (Jam)</th>
                                                            <th>Waktu Respon (Jam)</th>
                                                            <th>Responder</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="ts-sla-kritis-table-body">
                                                        <tr>
                                                            <td colspan="6" class="text-center">Memuat data...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-sla-event" role="tabpanel"
                        aria-labelledby="pills-sla-event-tab" tabindex="0">
                        <div class="container-fluid">

                            <div class="row mb-4 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Pilih Bulan Webinar:</label>
                                    <select id="eventSlaFilter" class="form-select">
                                        <option value="" selected disabled>-- Pilih Event --</option>
                                        @foreach(\App\Models\YearMapping::where('year', date('Y'))->orderBy('month')->get() as $map)
                                        <option value="{{ $map->id }}">
                                            Bulan {{ \Carbon\Carbon::createFromDate(null, $map->month)->translatedFormat('F') }} - {{ $map->theme ?? 'Tema Belum Set' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="event-sla-empty" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-event fs-1"></i>
                                <p class="mt-2">Silakan pilih bulan webinar terlebih dahulu.</p>
                            </div>

                            <div id="event-sla-content" style="display: none;">

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-info d-flex justify-content-between align-items-center"
                                            role="alert">
                                            <div>
                                                <h4 class="alert-heading mb-0 fs-5" id="event-title">...</h4>
                                                <small id="event-date" class="font-monospace">...</small>
                                            </div>
                                            <span class="badge bg-light text-dark border">Target: H-Min Timeline</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div
                                            class="card card-body text-center h-100 shadow-sm border-start border-4 border-primary">
                                            <h6 class="text-muted text-uppercase small">Kelengkapan</h6>
                                            <div class="fs-2 fw-bold" id="event-kpi-completion">0%</div>
                                            <small class="text-muted">Item Selesai / Total</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div
                                            class="card card-body text-center h-100 shadow-sm border-start border-4 border-success">
                                            <h6 class="text-muted text-uppercase small">Tepat Waktu (SLA)</h6>
                                            <div class="fs-2 fw-bold" id="event-kpi-compliance">0%</div>
                                            <small class="text-muted">Dari item yang selesai</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div
                                            class="card card-body text-center h-100 shadow-sm border-start border-4 border-warning">
                                            <h6 class="text-muted text-uppercase small">Terlambat</h6>
                                            <div class="fs-2 fw-bold text-warning" id="event-kpi-late">0</div>
                                            <small class="text-muted">Selesai tapi lewat deadline</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div
                                            class="card card-body text-center h-100 shadow-sm border-start border-4 border-danger">
                                            <h6 class="text-muted text-uppercase small">Overdue</h6>
                                            <div class="fs-2 fw-bold text-danger" id="event-kpi-overdue">0</div>
                                            <small class="text-muted">Belum selesai & lewat deadline</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-header fs-5 fw-semibold bg-white">
                                    <i class="bi bi-list-check me-2"></i> Rincian Timeline & Realisasi
                                </div>

                                <div class="card shadow-sm">
                                    <div class="table">
                                        <table class="table table-hover mb-0 align-middle w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 45%">Aktivitas</th>

                                                    <th>PIC</th>

                                                    <th class="text-center">Aturan SLA</th>

                                                    <th class="text-center">Deadline</th>

                                                    <th class="text-center">Tgl Selesai</th>

                                                    <th class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="event-sla-table-body">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="tab-pane fade" style="height:auto;" id="pills-sla-digital" role="tabpanel" aria-labelledby="pills-sla-digital-tab" tabindex="0">
                        <div class="container-fluid" id="sla-digital-container">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info" role="alert">
                                        <h4 class="alert-heading mb-0 fs-5" id="digital_sla_period">
                                            Memuat periode data...
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-primary border-start border-4">
                                        <div class="card-header bg-white fw-bold">
                                            <i class="bi bi-camera-reels-fill me-2 text-primary"></i> SLA Jadwal Konten
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-6 mb-3">
                                                    <h6 class="text-muted small text-uppercase">Kepatuhan Upload (Min 3/Minggu)</h6>
                                                    <div class="fs-1 fw-bold" id="digital-content-sla">...</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <h6 class="text-muted small text-uppercase">Total Konten Uploaded</h6>
                                                    <div class="fs-1 fw-bold text-dark" id="digital-content-total">...</div>
                                                </div>
                                                <div class="col-12">
                                                    <span class="badge bg-light text-dark border p-2">
                                                        Target Terpenuhi: <span id="digital-weeks-met" class="fw-bold">...</span> dari <span id="digital-weeks-total">...</span> Minggu
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-4">
                                    <div class="card shadow-sm h-100 border-warning border-start border-4">
                                        <div class="card-header bg-white fw-bold">
                                            <i class="bi bi-ticket-detailed-fill me-2 text-warning"></i> SLA Ticketing (Support)
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-4 mb-3">
                                                    <h6 class="text-muted small text-uppercase">SLA Resolusi</h6>
                                                    <div class="fs-2 fw-bold" id="digital-ticket-res-sla">...</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <h6 class="text-muted small text-uppercase">SLA Respon</h6>
                                                    <div class="fs-2 fw-bold" id="digital-ticket-resp-sla">...</div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <h6 class="text-muted small text-uppercase">Avg Resolusi</h6>
                                                    <div class="fs-2 fw-bold text-secondary" id="digital-ticket-avg">...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="card-header">
                                        <i class="bi bi-calendar-week me-2"></i> Detail Pencapaian Mingguan
                                    </div>
                                    <div class="card shadow-sm">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Periode Minggu</th>
                                                            <th class="text-center">Jumlah Upload</th>
                                                            <th class="text-center">Target</th>
                                                            <th class="text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="digital-weekly-table-body">
                                                        <tr>
                                                            <td colspan="4" class="text-center py-3">Memuat data...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-uptime-presentase" role="tabpanel"
                        aria-labelledby="pills-uptime-presentase-tab" tabindex="0">
                        <div class="container-fluid" id="sla-digital-container">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-4 text-primary fw-semibold">
                                        <i class="fas fa-chart-line me-2"></i>Presentase Uptime
                                    </h4>
                                </div>
                            </div>

                            <div id="uptime-loading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Memuat data uptime...</p>
                            </div>

                            <div id="uptime-content" class="d-none">

                                <h5 class="mb-4 text-primary fw-bold">Inixcoffee</h5>
                                <div class="row mb-5">

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body p-4">
                                                <h6 class="card-title text-muted mb-3">Uptime Minggu Ini (7 Hari Terakhir)</h6>

                                                <h2 class="mb-3 fw-bold" id="coffee-weekly-uptime">0.00%</h2>

                                                <div class="progress mb-4" style="height: 12px;">
                                                    <div class="progress-bar" role="progressbar" id="coffee-weekly-uptime-bar"
                                                        style="width: 0%"></div>
                                                </div>

                                                <small class="text-muted d-block mb-2">Downtime: <span id="coffee-weekly-downtime-mins">0</span> menit</small>

                                                <div class="progress" style="height:12px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" id="coffee-weekly-downtime-bar"
                                                        style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body p-4">
                                                <h6 class="card-title text-muted mb-3">Uptime Bulan Ini</h6>

                                                <h2 class="mb-3 fw-bold" id="coffee-monthly-uptime">0.00%</h2>

                                                <div class="progress mb-4" style="height: 12px;">
                                                    <div class="progress-bar" role="progressbar" id="coffee-monthly-uptime-bar"
                                                        style="width: 0%"></div>
                                                </div>

                                                <small class="text-muted d-block mb-2">Downtime: <span id="coffee-monthly-downtime-mins">0</span> menit</small>

                                                <div class="progress" style="height: 12px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" id="coffee-monthly-downtime-bar"
                                                        style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-4 text-primary fw-bold">Inixlatte</h5>
                                <div class="row mb-4">

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body p-4">
                                                <h6 class="card-title text-muted mb-3">Uptime Minggu Ini (7 Hari Terakhir)</h6>

                                                <h2 class="mb-3 fw-bold" id="latte-weekly-uptime">0.00%</h2>

                                                <div class="progress mb-4" style="height: 12px;">
                                                    <div class="progress-bar" role="progressbar" id="latte-weekly-uptime-bar"
                                                        style="width: 0%"></div>
                                                </div>

                                                <small class="text-muted d-block mb-2">Downtime: <span id="latte-weekly-downtime-mins">0</span> menit</small>

                                                <div class="progress" style="height: 12px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" id="latte-weekly-downtime-bar"
                                                        style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm rounded-4 h-100">
                                            <div class="card-body p-4">
                                                <h6 class="card-title text-muted mb-3">Uptime Bulan Ini</h6>

                                                <h2 class="mb-3 fw-bold" id="latte-monthly-uptime">0.00%</h2>

                                                <div class="progress mb-4" style="height: 12px;">
                                                    <div class="progress-bar" role="progressbar" id="latte-monthly-uptime-bar"
                                                        style="width: 0%"></div>
                                                </div>

                                                <small class="text-muted d-block mb-2">Downtime: <span id="latte-monthly-downtime-mins">0</span> menit</small>

                                                <div class="progress" style="height: 12px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" id="latte-monthly-downtime-bar"
                                                        style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media only screen and (min-width:501px) {
        #containerCanvasPenjualanPerSalesPerTahun {
            width: 75vw;
            height: auto;
        }

        #SouvenirChartContainerCanvas {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #ContainerCanvasAbsenPerBulan {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasAbsenChart {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasKelasAnalisisChart {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasNilaiFeedback {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMengajarChart {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMengajarPerMateri {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMateri {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPicChart {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }

        #containerCanvasjumlahTicketing {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }

        #containerCanvasRerataDurasi {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }

        #containerCanvasKetepatanRespond {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }

        #containerCanvasPermintaanPerbulan {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }

        #containerCanvasPermintaanSering {
            width: 100%;
            max-width: 70vw;
            /* (Opsional, 100% lebih baik) */
            height: auto;
            margin: 0 auto;
            /* (Opsional, untuk center) */
        }
    }

    @media only screen and (max-width:500px) {
        #containerCanvasPenjualanPerSalesPerTahun {
            width: 100%;
            height: auto;
        }

        #PenjualanPerSalesPerTahunChart {
            height: 200vw;
        }

        #SouvenirChartContainerCanvas {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            font-size: 20px;
        }

        #ContainerCanvasAbsenPerBulan {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasAbsenChart {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasKelasAnalisisChart {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasNilaiFeedback {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMengajarChart {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMengajarPerMateri {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasTotalMateri {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPicChart {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasjumlahTicketing {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasRerataDurasi {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasKetepatanRespond {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPermintaanPerbulan {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPermintaanSering {
            height: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }
    }

    .nav-tabs {
        display: flex;
        flex-wrap: nowrap;
        /* Mencegah tab terbungkus ke bawah */
        overflow-x: auto;
        /* Menambahkan scroll horizontal jika diperlukan */
    }

    .nav-item {
        white-space: nowrap;
        /* Menjaga teks tetap dalam satu baris */
    }

    .tab-pane {
        position: relative;
        transition: opacity 0.5s ease-in-out;
    }

    .container {
        padding: 0;
    }

    .profile-container {
        width: 100%;
        max-width: 700px;
        height: 500px;
        background-size: cover;
        background-position: center;
        background-image: url('/css/podiumkorea.png');
        background-color: #f0f0f0;
        /* Optional background for visual aid */
        margin: 0 auto;
        position: relative;
        overflow-x: auto;
        /* Allow horizontal scrolling when screen is too small */
    }

    /* Circle styles */
    .circle,
    .circle-satu {
        position: relative;
        width: 130px;
        height: 130px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(145deg, #1c1b2f, #282740);
        border: 4px solid #fff;
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.05),
            0 10px 20px rgba(0, 0, 0, 0.3),
            inset 0 0 8px rgba(255, 255, 255, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .circle:hover,
    .circle-satu:hover {
        transform: scale(1.05);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.1),
            0 20px 25px rgba(0, 0, 0, 0.5),
            inset 0 0 12px rgba(255, 255, 255, 0.2);
    }

    /* Default sizes */
    .circle {
        width: 180px;
        height: 180px;
    }


    .circle-satu {
        width: 200px;
        height: 200px;
    }

    /* present photo adjustments for each circle */
    .present-photo {
        width: 170px;
        height: 170px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .present-photo-satu {
        width: 190px;
        height: 190px !important;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .modern-ranking .col-4 {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .first-position {
        margin-top: 60px;
    }

    .second-position {
        margin-top: 40px;
    }

    .third-position {
        margin-top: 0px;
    }

    .medal-bawah {
        margin-top: 5px;
        width: 70px;
        height: auto;
    }

    /* ANIMASI MUNCUL */
    @keyframes slideFadeUp {
        0% {
            transform: translateY(40px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* ANIMASI MELAYANG */
    @keyframes floatCard {
        0% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }

        100% {
            transform: translateY(0);
        }
    }


    /* PODIUM CARD */
    .podium-card {
        /* background: linear-gradient(to bottom right, #f5f7fa, #b7cceeff); */
        background-image: url('/images/pixel1.jpg');
        background-size: cover;
        background-repeat: repeat;
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(6px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        /* Tambahkan ini: */
        animation: slideFadeUp 1s ease-out forwards, floatCard 6s ease-in-out infinite;
    }



    /* GLOW HOVER EFFECT */
    .podium-card:hover {
        box-shadow: 0 0 25px rgba(255, 255, 255, 0.3);
        transform: scale(1.03);
    }

    /* OPTIONAL: BACKGROUND BINTANG */
    .podium-section {
        background: radial-gradient(ellipse at center, #0d1a2d 0%, #0b0f20 100%);
        position: relative;
        overflow: hidden;
    }


    /* Peringkat label warna */
    .rank-1 .position-label {
        color: #FFD93D;
        /* Menambahkan outline hitam setebal 1px */
        text-shadow:
            -1px -1px 0 #000,
            1px -1px 0 #000,
            -1px 1px 0 #000,
            1px 1px 0 #000;
    }

    .rank-2 .position-label {
        color: #c0aaff;
        /* Menambahkan outline putih setebal 1px agar kontras dengan teks gelap */
        text-shadow:
            -1px -1px 0 #000,
            1px -1px 0 #000,
            -1px 1px 0 #000,
            1px 1px 0 #000;
    }

    .rank-3 .position-label {
        color: #ff914d;
        /* Menambahkan outline hitam setebal 1px */
        text-shadow:
            -1px -1px 0 #000,
            1px -1px 0 #000,
            -1px 1px 0 #000,
            1px 1px 0 #000;
    }




    /* Position badge */
    .position-badge {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 40px;
        height: 40px;
    }

    /* Label Peringkat di Bawah */
    .position-label {
        font-size: 1.4rem;
        font-weight: bold;
        margin-top: 12px;
        letter-spacing: 1px;
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.1);
    }

    .position-header {
        font-size: 1.4rem;
        font-weight: bold;
        margin-top: 12px;
        letter-spacing: 1px;
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.1);
    }

    /* Warna Spesifik Tiap Peringkat */
    /* .first-position+.position-label {
        color: #ffd700;
    }

    .second-position+.position-label {
        color: #c0aaff;
    }

    .third-position+.position-label {
        color: #ff914d;
    } */

    /* Responsive Optimization */
    @media (max-width: 768px) {

        .circle,
        .circle-satu {
            width: 100px;
            height: 100px;
        }

        .position-badge {
            width: 30px;
            height: 30px;
        }

        .position-label {
            font-size: 1.1rem;
        }
    }


    /* From Uiverse.io by bhaveshxrawat */
    .card-uiverse {
        width: auto;
        height: 254px;
        background: #182F51;
        position: relative;
        display: flex;
        place-content: center;
        place-items: center;
        overflow: hidden;
        border-radius: 20px;
    }


    .card-uiverse-feedback span {
        z-index: 1;
        color: white;
        font-size: 2em;
    }

    .card-uiverse-feedback h4 {
        z-index: 1;
        color: white;
        font-size: 2em;
    }

    .card-uiverse h2 {
        z-index: 1;
        color: white;
        font-size: 2em;
    }

    .card-uiverse p {
        z-index: 1;
        color: white;
        font-size: 2em;
    }

    .card-uiverse::before {
        content: '';
        position: absolute;
        width: 100px;
        background: linear-gradient(180deg, rgb(0, 136, 255) 50%, rgba(255, 0, 0, 1) 50%);
        height: 400%;
        animation: rotBGimg 3s linear infinite;
        transition: all 0.2s linear;
    }

    .card-uiverse::after {
        content: '';
        position: absolute;
        background: #182f51;
        inset: 5px;
        border-radius: 15px;
    }

    .card-uiverse-feedback {
        position: relative;
        color: #fff;
        height: 254px;
        background: #182F51;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        border-radius: 20px;
        z-index: 99;
        /* Set higher z-index for the text content */
    }

    .card-uiverse-feedback::before {
        content: '';
        position: absolute;
        width: 100px;
        background: linear-gradient(180deg, rgb(0, 136, 255) 50%, rgba(255, 0, 0, 1) 50%);
        height: 700%;
        animation: rotBGimg 3s linear infinite;
        transition: all 0.2s linear;
        /* opacity: 0.3; Lower opacity for better text contrast */
        z-index: -2;
        /* Lower z-index for the background effect */
    }

    .card-uiverse-feedback::after {
        content: '';
        position: absolute;
        background: #182f51;
        inset: 5px;
        border-radius: 15px;
        z-index: -1;
    }

    @keyframes rotBGimg {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .card_foto {
        /* background-image: url('/images/pixel1.jpg');
        background-size: cover;
        background-repeat: repeat; */
        overflow: hidden;
        position: relative;
        width: 100%;
        max-width: 330px;
        height: 325px;
        background: #fff;
        border-radius: 15px;
        padding: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3 ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .2);
        text-align: center;
        /* animation: floatCard 6s ease-in-out infinite; */
    }

    .card_foto:before,
    .card_foto:after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        border-radius: 4px;
        transition: 0.5s ease;
        z-index: -99;
    }

    .card_foto:hover:before {
        transform: rotate(20deg);
        box-shadow: 0 2px 20px rgba(0, 0, 0, .2);
    }

    .card_foto:hover:after {
        transform: rotate(10deg);
        box-shadow: 0 2px 20px rgba(0, 0, 0, .2);
    }

    /* Bagian details hanya untuk title */
    .details {
        margin-top: 10px;
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        height: 60px;
        text-align: center;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.8);
        padding-top: 5px;
    }

    .title {
        font-weight: 500;
        font-size: 18px;
        color: #777;
        opacity: 0;
        /* Sembunyikan title awalnya */
        transition: opacity 0.3s ease-in-out;
    }

    /* Tampilkan title hanya saat di-hover */
    .card_foto:hover .title {
        opacity: 1;
    }

    .caption {
        font-style: italic;
        font-weight: 500;
        font-size: 0.9rem;
        color: #4158D0;
        display: block;
        margin-top: 0px;
        /* Pastikan caption selalu terlihat tanpa efek hover */
        opacity: 1;
    }

    /* Bagian imgbox dan dynamic-image */
    .imgbox {
        /* background:linear-gradient(to bottom right, #f5f7fa, #a0bbe7ff); */
        background-image: url('/images/pixel1.jpg');
        background-size: cover;
        background-repeat: repeat;
        padding: 8px;
        border-radius: 12px;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        transition: 0.3s ease-in-out;
        bottom: 30px;
        animation: slideFadeUp 1s ease-out forwards, floatCard 6s ease-in-out infinite;
    }

    .dynamic-image {
        width: 100%;
        height: auto;
        border-radius: 10px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .card_foto:hover .dynamic-image {
        transform: scale(1.05);
    }

    /* Hover pada imgbox */
    .card_foto:hover .imgbox {
        bottom: 20px;
    }

    .chart-wrapper {
        position: relative;
        height: 500px;
        /* 🔥 FIXED, TIDAK NGE-GROW */
        width: 100%;
    }


    /* Responsive adjustments for mobile screens */
    @media (max-width: 576px) {
        .card {
            padding: 8px !important;
        }

        .card-body {
            padding: 8px !important;
        }

        .card-uiverse-feedback {
            padding: 15px;
            background-color: #182F51;
            /* Sesuaikan warna latar belakang sesuai keinginan */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            color: #ffffff;
        }

        .card-uiverse-feedback h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .card-uiverse-feedback h4 {
            font-size: 1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .card-uiverse-feedback span {
            font-size: 1rem;
            font-weight: bold;
        }


        .card-uiverse h2,
        .card-uiverse p {
            z-index: 1;
            color: white;
            font-size: 1em;
        }

        .card-uiverse {
            /* background: linear-gradient(to bottom right, #182F51, #c3cfe2); */
            background-color: #182F51;
            /* Sesuaikan dengan tema Anda */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #222;
            opacity: 0;
            margin-top: 5px;
            /* Sembunyikan title awalnya */
            transition: opacity 0.3s ease-in-out;
        }

        .caption {

            font-weight: 200;
            font-size: 13px;
            color: #4158D0;
            display: block;
            margin-top: 5px;
            /* Pastikan caption selalu terlihat tanpa efek hover */
            opacity: 1;
        }

        .second-position {
            position: relative;
            bottom: 5%;
            left: -13%;
        }

        .first-position {
            position: relative;
            bottom: 3%;
            left: 51%;
            transform: translateX(-50%);
        }

        .third-position {
            position: relative;
            bottom: 14%;
            right: 23%;
        }

        .card {
            width: auto;
            height: auto;
        }

        /* Resize circles for mobile */
        .circle,
        .circle-satu {
            width: 100px;
            height: 100px;
        }

        /* Resize present photos for mobile */
        .present-photo {
            width: 90px;
            height: 90px !important;
        }

        .present-photo-satu {
            width: 90px;
            height: 90px !important;
        }

        /* Adjust position badge size for mobile */
        .position-badge {
            top: -10px;
            right: -40px;
            padding: 3px;
            font-size: 0.7rem;
        }
    }

    /* From Uiverse.io by Yaya12085 */
    .card-dash {
        padding: 1rem;
        margin: 1rem;
        background-color: #fff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        max-width: 320px;
        border-radius: 20px;
    }

    .title-dash {
        display: flex;
        align-items: center;
    }

    .title-dash span {
        position: relative;
        padding: 0.5rem;
        background-color: #10B981;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
    }

    .title-dash span svg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ffffff;
        height: 1rem;
    }

    .title-text-dash {
        margin-left: 0.5rem;
        color: #374151;
        margin-bottom: 0px;
        font-size: 18px;
    }

    .percent-dash {
        margin-left: 0.5rem;
        color: #02972f;
        font-weight: 600;
        display: flex;
    }

    .data-dash {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .data-dash p {
        margin-top: 1rem;
        margin-bottom: 1rem;
        color: #1F2937;
        font-size: 2.25rem;
        line-height: 2.5rem;
        font-weight: 700;
        text-align: left;
    }

    .data-dash .range-dash {
        position: relative;
        background-color: #E5E7EB;
        width: 100%;
        height: 0.5rem;
        border-radius: 0.25rem;
    }

    .data-dash .range-dash .fill-dash {
        position: absolute;
        top: 0;
        left: 0;
        background-color: #10B981;
        width: 76%;
        height: 100%;
        border-radius: 0.25rem;
    }
</style>
<style>
    @media (max-width: 576px) {
        .feedback-card .dekorasi {
            transform: none !important;
            position: static !important;
            max-width: 100% !important;
            height: auto !important;
        }

        .feedback-card {
            overflow: hidden !important;
        }
    }
</style>
@push('scripts')
    <script>
        // Variable global untuk API URL Feedback Instruktur
        window.FEEDBACK_API_URL = "{{ route('office.feedback.get') }}";
    </script>
    <script src="{{ asset('js/dashboard .js') }}"></script>
@endpush
