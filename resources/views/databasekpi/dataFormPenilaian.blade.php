@extends('databasekpi.berandaKPI')

@section('contentKPI')
<style>
    .button-view-all {
        text-decoration: none;
    }
</style>
<div class="container mt-4 mb-4">
    <div class="card p-3">
        <div class="card-head">
            <div class="card-title text-center fw-bold">Semua Form Penilaian
            </div>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <div class="flex-fill">
                    <label for="quartal" class="form-label mb-1">Quartal</label>
                    <select class="form-select form-select-sm" name="quartal" id="quartal">
                        <option value="Q1">Q1</option>
                        <option value="Q2">Q2</option>
                        <option value="Q3">Q3</option>
                        <option value="Q4">Q4</option>
                    </select>
                </div>
                <div class="flex-fill">
                    <label for="tahun" class="form-label mb-1">Tahun</label>
                    <select class="form-select form-select-sm" name="tahun" id="tahun">
                        <option value="">Pilih Tahun</option>
                        @for($i = now()->year; $i >= 2020; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle" id="table_penilaian">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Form</th>
                            <th>Evaluated</th>
                            <th>Quartal</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="body_content">
                        <tr>
                            <td colspan="6" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEvaluated" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Evaluated</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluatedContent"></div>
        </div>
    </div>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<style>
    #table_penilaian tbody tr .action-btn {
        display: none;
    }

    #table_penilaian tbody tr:hover .action-btn {
        display: inline-block;
    }
</style>

<script>
    $(document).ready(function() {
        setDefaultQuartal();
        $("#tahun").val(new Date().getFullYear());
        loadData();
        $("#quartal, #tahun").on("change", function() {
            loadData();
        });
    });

    function setDefaultQuartal() {
        const month = new Date().getMonth() + 1;
        let quartal = "Q1";
        if (month >= 4 && month <= 6) quartal = "Q2";
        else if (month >= 7 && month <= 9) quartal = "Q3";
        else if (month >= 10 && month <= 12) quartal = "Q4";
        $("#quartal").val(quartal);
    }

    function loadData() {
        const quartal = $("#quartal").val();
        const tahun = $("#tahun").val();
        $.ajax({
            url: "{{ route('penilaian.form.get') }}",
            type: 'get',
            data: {
                quartal: quartal,
                tahun: tahun
            },
            success: function(response) {
                const data = response.data ?? [];
                const content = $('#body_content');
                content.empty();
                if (data.length === 0) {
                    content.append(`<tr><td colspan="6" class="text-center">Tidak ada Form!</td></tr>`);
                } else {
                    data.forEach(function(item, index) {
                        let evaluatedArr = [];
                        if (Array.isArray(item.evaluated)) {
                            evaluatedArr = item.evaluated.map(e => e.nama);
                        } else if (item.evaluated) {
                            evaluatedArr = [item.evaluated.nama];
                        }
                        let shortList = evaluatedArr.slice(0, 3).join(", ");
                        let moreLink = evaluatedArr.length > 3 ?
                            `<a href="javascript:void(0)" class="show-more button-view-all" data-full='${JSON.stringify(evaluatedArr)}'>
                                lihat semua
                            </a>` :
                            "";
                        content.append(`
                            <tr>
                                <td>${index+1}</td>
                                <td>${item.label_kode_form}</td>
                                <td>${shortList} ${moreLink}</td>
                                <td>${item.quartal}</td>
                                <td>${item.tahun}</td>
                                <td class="aksi-col text-center">
                                    <a class="btn btn-sm btn-warning action-btn" href="/penilaian/data-form/edit/${item.kode_form}"><i class="fa-solid fa-pen-to-square"></i></a>
                                </td>
                            </tr>
                        `);
                    });
                    if ($.fn.DataTable.isDataTable('#table_penilaian')) {
                        $('#table_penilaian').DataTable().destroy();
                    }
                    $('#table_penilaian').DataTable({
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50, 100],
                        responsive: true,
                        language: {
                            search: "Cari:",
                            lengthMenu: "Tampilkan _MENU_ data",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data",
                            zeroRecords: "Data tidak ditemukan",
                            paginate: {
                                first: "Awal",
                                last: "Akhir",
                                next: "›",
                                previous: "‹"
                            }
                        }
                    });
                }
            }
        })
    }

    $(document).on("click", ".show-more", function() {
        const full = JSON.parse($(this).attr("data-full"));
        let listHtml = "<ol>";
        full.forEach(nama => {
            listHtml += `<li>${nama}</li>`;
        });
        listHtml += "</ol>";
        $("#evaluatedContent").html(listHtml);
        $("#modalEvaluated").modal("show");
    });
</script>
@endsection