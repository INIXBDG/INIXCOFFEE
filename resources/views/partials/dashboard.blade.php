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
                <button class="nav-link" id="inix-tab" data-bs-toggle="tab" data-bs-target="#inix-tab-pane" type="button" role="tab" aria-controls="inix-tab-pane" aria-selected="true">Inixindo Dalam Angka</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-tab-pane" type="button" role="tab" aria-controls="sales-tab-pane" aria-selected="true"
                    {{ $salesDisabled ? '' : 'disabled' }}>Sales</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="office-tab" data-bs-toggle="tab" data-bs-target="#office-tab-pane" type="button" role="tab" aria-controls="office-tab-pane" aria-selected="false"
                    {{ $officeDisabled ? '' : 'disabled' }}>Office</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="instruktur-tab" data-bs-toggle="tab" data-bs-target="#instruktur-tab-pane" type="button" role="tab" aria-controls="instruktur-tab-pane" aria-selected="false"
                    {{ $instrukturDisabled ? '' : 'disabled' }}>Instruktur</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="itsm-tab" data-bs-toggle="tab" data-bs-target="#itsm-tab-pane" type="button" role="tab" aria-controls="itsm-tab-pane" aria-selected="false"
                {{ $itsmDisable ? '' : 'disabled' }}>ITSM</button>
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
                                        <!-- Second Place -->
                                        <div class="col-4 text-center podium-card">
                                            <div class="card-podium rank-2">
                                            <div class="circle second-position shadow-sm">
                                                <!-- <img src="{{ asset('css/b2.png') }}" alt="Second Place" class="position-badge"> -->
                                                <img src="{{ asset('storage/photos/pemain2.jpg') }}" alt="Keterlambatan ke-2" class="present-photo rounded-circle border border-white" id="present-photo">
                                            </div>
                                            <p class="position-label">Peringkat 2</p>
                                            <img src="{{ asset('images/medal-2.png') }}" alt="Medali Perak" class="medal-bawah">
                                            </div>
                                        </div>

                                        <!-- First Place -->
                                        <div class="col-4 text-center podium-card">
                                            <div class="card-podium rank-1">
                                            <div class="circle-satu first-position shadow">
                                                <!-- <img src="{{ asset('css/b1.png') }}" alt="First Place" class="position-badge"> -->
                                                <img src="{{ asset('css/b1.png') }}" alt="Keterlambatan ke-1" class="present-photo-satu rounded-circle border border-white" id="present-photo-satu">
                                            </div>
                                            <p class="position-label">Peringkat 1</p>
                                            <img src="{{ asset('images/medal-1.png') }}" class="medal-bawah" alt="Medal">
                                            </div>
                                        </div>

                                        <!-- Third Place -->
                                        <div class="col-4 text-center podium-card">
                                            <div class="card-podium rank-3">
                                            <div class="circle third-position shadow-sm">
                                                <!-- <img src="{{ asset('css/b3.png') }}" alt="Third Place" class="position-badge"> -->
                                                <img src="{{ asset('storage/photos/pemain3.jpg') }}" alt="Keterlambatan ke-3" class="present-photo rounded-circle border border-white" id="present-photo">
                                            </div>
                                            <p class="position-label">Peringkat 3</p>
                                            <img src="{{ asset('images/medal-3.png') }}" alt="Medali Perunggu" class="medal-bawah">
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
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center d-flex justify-content-center">
        <div class="card_foto">
            <div class="imgbox">
                <img src="{{ asset('images/download.png') }}" alt="Sales Image" class="dynamic-image" id="foto_sales">
            </div>
            <div class="details">
                <span class="caption">Sales Terbaik</span>
                <h4 class="title" id="nama_sales">John doe</h4>
            </div>
        </div>
    </div>

    <!-- INSTRUKTUR TERBAIK -->
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center d-flex justify-content-center">
        <div class="card_foto">
            <div class="imgbox">
                <img src="{{ asset('images/download.png') }}" alt="Instruktur Image" class="dynamic-image" id="foto_instruktur">
            </div>
            <div class="details">
                <span class="caption mb-30">Instruktur Terbaik</span>
                <h4 class="title" id="nama_instruktur">John doe</h4>
            </div>
        </div>
    </div>

    <!-- OFFICE TERBAIK -->
    <div class="col-4 col-sm-4 col-md-4 col-lg-4 text-center d-flex justify-content-center">
        <div class="card_foto">
            <div class="imgbox">
                <img src="{{ asset('images/download.png') }}" alt="Office Image" class="dynamic-image" id="foto_office">
            </div>
            <div class="details">
                <span class="caption">Office Terbaik</span>
                <h4 class="title" id="nama_office">John doe</h4>
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
            <div class="tab-pane fade show " id="sales-tab-pane" role="tabpanel" aria-labelledby="sales-tab" tabindex="0" style="height: auto">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-perquartal-tab" data-bs-toggle="pill" data-bs-target="#pills-perquartal" type="button" role="tab" aria-controls="pills-perquartal" aria-selected="true">Penjualan Per Sales Per Triwulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-bulan-tab" data-bs-toggle="pill" data-bs-target="#pills-bulan" type="button" role="tab" aria-controls="pills-bulan" aria-selected="false">Penjualan Per Sales Per Tahun</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-perquartal" role="tabpanel" aria-labelledby="pills-perquartal-tab" tabindex="0">
                        <div class="col-12" id="chartjs">
                            <label for="salesKeySelect">Pilih Sales:</label>
                            <select id="salesKeySelect" class="form-select" style="width: 100px" onchange="updateChart()">
                            </select>
                            <canvas id="PenjualanPerSalesPerQuartalChart"></canvas>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-bulan" role="tabpanel" aria-labelledby="pills-bulan-tab" tabindex="0">
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
                        <button class="nav-link" id="pills-analisiskelas-tab" data-bs-toggle="pill" data-bs-target="#pills-analisiskelas" type="button" role="tab" aria-controls="pills-analisiskelas" aria-selected="true">Rekap Analisa Margin</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-absen-tab" data-bs-toggle="pill" data-bs-target="#pills-absen" type="button" role="tab" aria-controls="pills-absen" aria-selected="false">Rekap Absen</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-absenperbulan-tab" data-bs-toggle="pill" data-bs-target="#pills-absenperbulan" type="button" role="tab" aria-controls="pills-absenperbulan" aria-selected="false">Rekap Absen Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-souvenir-tab" data-bs-toggle="pill" data-bs-target="#pills-souvenir" type="button" role="tab" aria-controls="pills-souvenir" aria-selected="false">Rekap Souvenir</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-analisiskelas" role="tabpanel" aria-labelledby="pills-analisiskelas-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasKelasAnalisisChart">
                                <canvas id="KelasAnalisisChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-absen" role="tabpanel" aria-labelledby="pills-absen-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasAbsenChart">
                                <canvas id="AbsenChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-absenperbulan" role="tabpanel" aria-labelledby="pills-absenperbulan-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="ContainerCanvasAbsenPerBulan">
                                <label for="monthSelect_absenperbulan" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_absenperbulan" onchange="updateChartAbsenPerbulan(this.value)">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-souvenir" role="tabpanel" aria-labelledby="pills-souvenir-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="SouvenirChartContainerCanvas">
                                <canvas id="SouvenirChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="instruktur-tab-pane" role="tabpanel" aria-labelledby="instruktur-tab" tabindex="0">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-nilaifeedbackperbulan-tab" data-bs-toggle="pill" data-bs-target="#pills-nilaifeedbackperbulan" type="button" role="tab" aria-controls="pills-nilaifeedbackperbulan" aria-selected="true">Nilai Feedback Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalmengajar-tab" data-bs-toggle="pill" data-bs-target="#pills-totalmengajar" type="button" role="tab" aria-controls="pills-totalmengajar" aria-selected="false">Total Mengajar Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalMateri-tab" data-bs-toggle="pill" data-bs-target="#pills-totalMateri" type="button" role="tab" aria-controls="pills-totalMateri" aria-selected="false">Total Materi Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-totalMengajarPerMateri-tab" data-bs-toggle="pill" data-bs-target="#pills-totalMengajarPerMateri" type="button" role="tab" aria-controls="pills-totalMengajarPerMateri" aria-selected="false">Total Mengajar Per Materi Per Bulan</button>
                    </li>
                    {{-- <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-souvenir-tab" data-bs-toggle="pill" data-bs-target="#pills-souvenir" type="button" role="tab" aria-controls="pills-souvenir" aria-selected="false">Rekap Souvenir</button>
                    </li> --}}
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-nilaifeedbackperbulan" role="tabpanel" aria-labelledby="pills-nilaifeedbackperbulan-tab" tabindex="0">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalmengajar" role="tabpanel" aria-labelledby="pills-totalmengajar-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMengajarChart">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalmengajar" onchange="updateChartTotalMengajar(this.value)">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMateri" role="tabpanel" aria-labelledby="pills-totalMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMateri" onchange="updateChartTotalMateri(this.value)">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMateri" role="tabpanel" aria-labelledby="pills-totalMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMateri" onchange="updateChartTotalMateri(this.value)">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-totalMengajarPerMateri" role="tabpanel" aria-labelledby="pills-totalMengajarPerMateri-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasTotalMengajarPerMateri">
                                <label for="monthSelect" class="form-label">Pilih Bulan:</label>
                                <select class="form-select" id="monthSelect_totalMengajarPerMateri" onchange="updateChartTotalMengajarPerMateri(this.value)">
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
                </div>
                {{-- <div class="tab-pane fade show" style="height:auto;" id="pills-souvenir" role="tabpanel" aria-labelledby="pills-souvenir-tab" tabindex="0">
                        <div class="col-12" style="height:auto; width:100%; display: flex; flex-direction: column; justify-content: center; align-items: center; ">
                            <canvas id="SouvenirChart"></canvas>
                        </div>q
                    </div> --}}
            </div>
            <div class="tab-pane fade" id="itsm-tab-pane" role="tabpanel" aria-labelledby="itsm-tab" tabindex="0">
                <ul class="nav nav-pills mb-3" id="itsm-pills-tab" role="tablist">

                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-pic-tab" data-bs-toggle="pill" data-bs-target="#pills-jumlah-pic" type="button" role="tab" aria-controls="pills-jumlah-pic" aria-selected="false">Jumlah PIC</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-ticketing-tab" data-bs-toggle="pill" data-bs-target="#pills-jumlah-ticketing" type="button" role="tab" aria-controls="pills-jumlah-ticketing" aria-selected="true">Jumlah Ticketing</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-rerata-durasi-tab" data-bs-toggle="pill" data-bs-target="#pills-rerata-durasi" type="button" role="tab" aria-controls="pills-rerata-durasi" aria-selected="false">Rata-rata Durasi Pengerjaan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-rerata-ketepatan-response-tab" data-bs-toggle="pill" data-bs-target="#pills-rerata-ketepatan-response" type="button" role="tab" aria-controls="pills-rerata-ketepatan-response" aria-selected="false">Rata-rata Kecepatan Respon</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-jumlah-permintaan-tab" data-bs-toggle="pill" data-bs-target="#pills-jumlah-permintaan" type="button" role="tab" aria-controls="pills-jumlah-permintaan" aria-selected="false">Jumlah Permintaan Per Bulan</button>
                    </li>
                    <li class="nav-item mx-1" role="presentation">
                        <button class="nav-link" id="pills-permintaan-sering-diajukan-tab" data-bs-toggle="pill" data-bs-target="#pills-permintaan-sering-diajukan" type="button" role="tab" aria-controls="pills-permintaan-sering-diajukan" aria-selected="false">Permintaan Sering Diajukan</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-pic" role="tabpanel" aria-labelledby="pills-jumlah-pic-tab" tabindex="0">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-ticketing" role="tabpanel" aria-labelledby="pills-jumlah-ticketing-tab" tabindex="0">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-rerata-durasi" role="tabpanel" aria-labelledby="pills-rerata-durasi-tab" tabindex="0">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-rerata-ketepatan-response" role="tabpanel" aria-labelledby="pills-rerata-ketepatan-response-tab" tabindex="0">
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
                    <div class="tab-pane fade show" style="height:auto;" id="pills-jumlah-permintaan" role="tabpanel" aria-labelledby="pills-jumlah-permintaan" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPermintaanPerbulan">
                                <h3>Jumlah Permintaan Per Bulan</h3>
                                <canvas id="jumlahPermintaanPerBulanChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade show" style="height:auto;" id="pills-permintaan-sering-diajukan" role="tabpanel" aria-labelledby="pills-permintaan-sering-tab" tabindex="0">
                        <div class="row justify-content-center">
                            <div class="col-12" id="containerCanvasPermintaanSering">
                                <h3>Permintaan Yang Sering Diajukan</h3>
                                <canvas id="permintaanSeringDiajukanChart" width="400" height="200"></canvas>
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
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasjumlahTicketing {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasRerataDurasi {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasKetepatanRespond {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPermintaanPerbulan {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        #containerCanvasPermintaanSering {
            height: auto;
            width: 70vw;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
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
  0%   { transform: translateY(0); }
  50%  { transform: translateY(-8px); }
  100% { transform: translateY(0); }
}


/* PODIUM CARD */
.podium-card {
    /* background: linear-gradient(to bottom right, #f5f7fa, #b7cceeff); */
  background-image: url('/images/pixel1.jpg');
  background-size: cover;
  background-repeat: repeat;
  border-radius: 15px;
  padding: 15px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
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
.rank-1 .position-label { color: #FFD93D; }
.rank-2 .position-label { color: #b3aaff; }
.rank-3 .position-label { color: #ff914d; }




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
        padding:0 4px 12px rgba(0,0,0,0.08);
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
</style>