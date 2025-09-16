@extends('layouts.app')

@section('content')
<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 text-center">
            <h2 class="mb-4 fw-bold">Daftar Ruang Kelas Offline</h2>

            <div class="row justify-content-center mb-5">
                <div class="col-md-4 mb-2">
                    <input type="date" id="filterStart"
                        name="filter"
                        class="form-control shadow-sm rounded-pill px-4"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="row g-4 justify-content-center" id="ruangList">
                <!-- Ruang 1 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-15" data-end="2025-09-18">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 1</div>
                        <input type="hidden" class="ruang-nama" value="1">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">15/09 - 18/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Networking Dasar</div>
                            <div class="small text-muted">Sales: Budi Santoso • Instruktur: Andi Pratama</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang 2 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-17" data-end="2025-09-20">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 2</div>
                        <input type="hidden" class="ruang-nama" value="2">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">17/09 - 20/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">MikroTik Lanjutan</div>
                            <div class="small text-muted">Sales: Siti Rahma • Instruktur: Dewi Lestari</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang 3 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-18" data-end="2025-09-21">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 3</div>
                        <input type="hidden" class="ruang-nama" value="3">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">18/09 - 21/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Linux Server Administration</div>
                            <div class="small text-muted">Sales: Agus Wijaya • Instruktur: Rudi Hartono</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang 4 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-19" data-end="2025-09-22">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 4</div>
                        <input type="hidden" class="ruang-nama" value="4">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">19/09 - 22/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Database MySQL</div>
                            <div class="small text-muted">Sales: Rina Marlina • Instruktur: Fajar Nugraha</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang 5 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-20" data-end="2025-09-23">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 5</div>
                        <input type="hidden" class="ruang-nama" value="5">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">20/09 - 23/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Web Development PHP</div>
                            <div class="small text-muted">Sales: Bayu Saputra • Instruktur: Arif Kurniawan</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang 6 -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-21" data-end="2025-09-24">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang 6</div>
                        <input type="hidden" class="ruang-nama" value="6">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">21/09 - 24/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Cloud Computing</div>
                            <div class="small text-muted">Sales: Dian Puspitasari • Instruktur: Hendra Gunawan</div>
                        </div>
                    </div>
                </div>

                <!-- Ruang ADoc -->
                <div class="col-md-4 col-sm-6 ruang-container" data-start="2025-09-23" data-end="2025-09-26">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang ADoc</div>
                        <input type="hidden" class="ruang-nama" value="ADOC">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">23/09 - 26/09</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">IT Project Management</div>
                            <div class="small text-muted">Sales: Nur Aini • Instruktur: Ratna Sari</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    body {
        background: #f5f7fa;
    }

    .ruang {
        user-select: none;
        background: linear-gradient(145deg, #ffffff, #f1f3f6);
        border-radius: 20px;
        padding: 40px 25px;
        font-weight: 600;
        font-size: 1.2rem;
        transition: all 0.4s ease;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        cursor: default;
        animation: zoomFade 0.8s ease both;
    }

    .ruang:hover {
        background: linear-gradient(145deg, #198754, #20c997);
        color: #fff;
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
    }

    @keyframes zoomFade {
        0% {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }

        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .ruang-filter {
        border-radius: 12px;
        font-size: 0.9rem;
    }
</style>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function() {
        loadData($("#filterStart").val());

        $("#filterStart").on("change", function() {
            loadData($(this).val());
        });

        $(document).on("change", ".ruang-filter", function() {
            let parent = $(this).closest(".ruang-container");
            let ruang = parent.find(".ruang-nama").val();
            let tanggalRuang = $(this).val();

            loadData(null, ruang, tanggalRuang, parent);
        });
    });

    function loadData(filterUtama = null, ruang = null, tanggalRuang = null, parent = null) {
        $.ajax({
            url: "{{ route('managementKelas.get') }}",
            type: "GET",
            data: {
                filter_utama: filterUtama,
                ruang: ruang,
                tanggal_ruang: tanggalRuang
            },
            success: function(res) {
                // if (ruang) {
                //     updateContainer(parent, res);
                // } else {
                //     $(".ruang-container").each(function() {
                //         let ruangName = $(this).find(".ruang-nama").val();
                //         let ruangData = res.filter(r => r.ruang === ruangName);

                //         updateContainer($(this), ruangData);
                //     });
                // }
            }
        });
    }

    function updateContainer(container, data) {
        let materi = container.find(".materi");
        let info = container.find(".info");

        if (!data || data.length === 0) {
            materi.text("");
            info.text("Sales: - • Instruktur: -");
        } else {
            materi.text(data[0].nama_materi ?? "");
            info.text(`Sales: ${data[0].sales ?? "-"} • Instruktur: ${data[0].instruktur ?? "-"}`);
        }
    }
</script>
@endpush
@endsection