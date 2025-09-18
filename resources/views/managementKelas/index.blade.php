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
                @php
                $ruangs = ['1','2','3','4','5','6','ADOC'];
                @endphp
                @foreach($ruangs as $r)
                <div id="container-ruang-{{ $r }}" class="col-md-4 col-sm-6 ruang-container">
                    <div class="ruang">
                        <div class="ruang-title mb-4" style="font-size: 35px;">Ruang {{ $r }}</div>
                        <input type="hidden" class="ruang-nama" value="{{ $r }}">

                        <div class="row align-items-center g-2">
                            <div class="col-6">
                                <span class="w-100 py-2">-</span>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm ruang-filter">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="fw-bold">Kosong</div>
                            <div class="small text-muted"></div>
                        </div>
                        <div class="mt-2 text-end tombol-container">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailRKM" tabindex="-1" aria-labelledby="modalDetailRKMLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailRKMLabel">Detail RKM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bodyContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKelolaManagement" tabindex="-1" aria-labelledby="modalKelolaManagementLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('managementKelas.store') }}" method="post">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKelolaManagementLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
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
        cursor: pointer;
        animation: zoomFade 0.8s ease both;
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

    .ruang-ada {
        color: #fff !important;
    }

    .ruang-ada.manajemen {
        background: #dc3545 !important;
        color: #ffffff !important;
    }

    .ruang-ada.rkm {
        background: #dc3545 !important;
        color: #ffffff !important;
    }
</style>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script>
    let containerFilters = {};

    function getContainerId(d) {
        if (d.ruang === "-") return d.ruangan.replace('Ruang ', '');
        if (d.ruangan === "-") return d.ruang.replace('Ruang ', '');
        return '';
    }

    $(document).ready(function() {
        const filterUtama = $("#filterStart").val();
        loadAllContainers(filterUtama);

        $("#filterStart").on("change", function() {
            loadAllContainers($(this).val());
        });

        $(document).on("change", ".ruang-filter", function() {
            let parent = $(this).closest(".ruang-container");
            let ruang = parent.find(".ruang-nama").val();
            let tanggalRuang = $(this).val();

            if (!containerFilters[ruang]) containerFilters[ruang] = [];
            if (tanggalRuang && !containerFilters[ruang].includes(tanggalRuang)) containerFilters[ruang].push(tanggalRuang);

            loadContainer(parent, ruang, containerFilters[ruang]);
        });

        $(document).on("click", ".ruang:not(.ruang-ada)", function() {
            let ruang = $(this).find(".ruang-nama").val();
            openModalKelola(ruang);
        });
    });

    function loadAllContainers(filterUtama) {
        $.ajax({
            url: "{{ route('managementKelas.get') }}",
            type: "GET",
            data: {
                filter_utama: filterUtama
            },
            success: function(response) {
                $(".ruang-container").each(function() {
                    const tanggalUtama = $("#filterStart").val();
                    $(this).find(".row .col-6 span").text(tanggalUtama);
                    $(this).find(".fw-bold").text("Kosong");
                    $(this).find(".small").text("");
                    $(this).find(".tombol-container").empty();
                    $(this).find(".ruang").removeClass("ruang-ada manajemen rkm").css("background", "");
                });

                response.forEach(d => {
                    let containerId = getContainerId(d);
                    if (!containerId) return;
                    let container = $(`#container-ruang-${containerId}`);
                    updateContainer(container, [d]);
                });
            }
        });
    }

    function loadContainer(container, ruang, tanggalArray = null) {
        $.ajax({
            url: "{{ route('managementKelas.get') }}",
            type: "GET",
            traditional: true,
            data: {
                ruang: ruang,
                tanggal_ruang: tanggalArray
            },
            success: function(response) {
                updateContainer(container, response);
            },
            error: function() {
                updateContainer(container, []);
            }
        });
    }

    function updateContainer(container, data) {
        if (!container || container.length === 0) return;

        let materi = container.find(".fw-bold");
        let info = container.find(".small");
        let kelola = container.find(".tombol-container");
        let ruangBox = container.find(".ruang");
        let tanggalSpan = container.find(".row .col-6 span");
        const filterUtama = $("#filterStart").val();

        if (!data || data.length === 0) {
            materi.text("Kosong");
            info.text("");
            kelola.empty();
            tanggalSpan.text(filterUtama);
            ruangBox.removeClass("ruang-ada manajemen rkm").css("background", "");
            return;
        }

        let manajemen = data.find(d => d.ruang === "-");
        let rkm = data.find(d => d.ruangan === "-");

        if (manajemen) {
            ruangBox.addClass("ruang-ada manajemen").css("background", "#dc3545");
            materi.html(`<ul><li>${manajemen.tanggal} - ${manajemen.jam_mulai} s/d ${manajemen.jam_selesai}</li></ul>`);
            info.text("Kebutuhan: " + (manajemen.kebutuhan || "-"));
            tanggalSpan.text(manajemen.tanggal || filterUtama);
            kelola.html(`<button type="button" class="btn btn-primary btn-kelola" data-bs-toggle="modal" data-bs-target="#modalKelolaManagement" data-ruang="${manajemen.ruangan}">Kelola</button>`);
            return;
        }

        if (rkm) {
            ruangBox.addClass("ruang-ada rkm").css("background", "#28a745");
            materi.text(rkm.materi || "-");
            info.text(`Sales: ${rkm.sales || "-"} • Instruktur: ${rkm.instruktur || "-"}` +
                (rkm.instruktur2 ? " • Instruktur2: " + rkm.instruktur2 : "") +
                (rkm.asisten ? " • Asisten: " + rkm.asisten : ""));
            tanggalSpan.text(rkm.tanggal_awal + " s/d " + rkm.tanggal_akhir || filterUtama);
            kelola.html(`<button type="button" class="btn btn-info btn-detail" data-bs-toggle="modal" data-bs-target="#modalDetailRKM"
             data-ruang="${rkm.ruang}" 
             data-tanggal_awal="${rkm.tanggal_awal}" 
             data-tanggal_akhir="${rkm.tanggal_akhir}" 
             data-materi="${rkm.materi}" 
             data-sales="${rkm.sales}" 
             data-instruktur="${rkm.instruktur}" 
             data-instruktur2="${rkm.instruktur2}" 
             data-asisten="${rkm.asisten}" 
             data-perusahaan="${rkm.perusahaan}" 
             data-harga_jual="${rkm.harga_jual}" 
             data-pax="${rkm.pax}" 
             data-exam="${rkm.exam}" 
             data-authorize="${rkm.authorize}" 
             >Detail</button>`);
            return;
        }
    }

    function openModalKelola(ruang) {
        $("#modalKelolaManagementLabel").text("Kelola Ruang " + ruang);
        $("#modalKelolaManagement .modal-body").html(`
        <input type="hidden" name="ruang" value="${ruang}">
        <div class="form-group mt-3">
            <label>Nama Ruangan</label>
            <input type="text" class="form-control" value="Ruang ${ruang}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Tanggal</label>
            <input type="date" class="form-control" name="tanggal">
        </div>
        <div class="form-group mt-3">
            <label>Jam Mulai</label>
            <input type="time" class="form-control" name="jam_mulai" id="jamMulai">
        </div>
        <div class="form-group mt-3">
            <label>Jam Selesai</label>
            <input type="time" class="form-control" name="jam_selesai" id="jamSelesai">
        </div>
        <small id="errorMsg" class="text-danger d-none">Jam selesai harus minimal 1 jam setelah jam mulai</small>
        <div class="form-group mt-3">
            <label>Kebutuhan</label>
            <textarea name="kebutuhan" class="form-control"></textarea>
        </div>
        <div class="form-group mt-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control"></textarea>
        </div>
    `);

        var myModal = new bootstrap.Modal(document.getElementById('modalKelolaManagement'));
        myModal.show();
    }

    function openModalDetail(data) {
        $("#modalDetailRKMLabel").text("Kelola Ruang " + data.ruang);
        $("#modalDetailRKM #bodyContent").html(`
        <input type="hidden" name="ruang" value="${data.ruang}">
        <div class="form-group mt-3">
            <label>Nama Ruangan</label>
            <input type="text" class="form-control" value="Ruang ${data.ruang}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Tanggal Awal</label>
            <input type="text" class="form-control" value="${data.tanggal_awal}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Tanggal Akhir</label>
            <input type="text" class="form-control" value="${data.tanggal_akhir}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Materi</label>
            <input type="text" class="form-control" value="${data.materi}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Sales</label>
            <input type="text" class="form-control" value="${data.sales}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Instruktur 1</label>
            <input type="text" class="form-control" value="${data.instruktur}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Instruktur 2</label>
            <input type="text" class="form-control" value="${data.instruktur2}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Asisten</label>
            <input type="text" class="form-control" value="${data.asisten}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Perusahaan</label>
            <input type="text" class="form-control" value="${data.perusahaan}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Harga Jual</label>
            <input type="text" class="form-control" value="${data.harga_jual}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Pax</label>
            <input type="text" class="form-control" value="${data.pax}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Exam</label>
            <input type="text" class="form-control" value="${data.exam}" readonly>
        </div>
        <div class="form-group mt-3">
            <label>Authorize</label>
            <input type="text" class="form-control" value="${data.authorize}" readonly>
        </div>
    `);
    }

    // Validasi jam
    $(document).on("change", "#jamMulai, #jamSelesai", function() {
        let mulai = $("#jamMulai").val();
        let selesai = $("#jamSelesai").val();
        if (mulai && selesai) {
            let start = moment(mulai, "HH:mm");
            let end = moment(selesai, "HH:mm");
            let diff = end.diff(start, "minutes");
            if (diff < 60) {
                $("#errorMsg").removeClass("d-none");
                $("#jamSelesai").val("");
            } else {
                $("#errorMsg").addClass("d-none");
            }
        }
    });

    $(document).on("click", ".btn-kelola", function() {
        let ruang = $(this).data("ruang");
        openModalKelola(ruang);
    });

    $(document).on("click", ".btn-detail", function() {
        let data = {
            ruang: $(this).data("ruang"),
            tanggal_awal: $(this).data("tanggal_awal"),
            tanggal_akhir: $(this).data("tanggal_akhir"),
            materi: $(this).data("materi"),
            sales: $(this).data("sales"),
            instruktur: $(this).data("instruktur"),
            instruktur2: $(this).data("instruktur2"),
            asisten: $(this).data("asisten"),
            perusahaan: $(this).data("perusahaan"),
            harga_jual: $(this).data("harga_jual"),
            pax: $(this).data("pax"),
            exam: $(this).data("exam"),
            authorize: $(this).data("authorize")
        };
        openModalDetail(data);
    });
</script>
@endpush
@endsection