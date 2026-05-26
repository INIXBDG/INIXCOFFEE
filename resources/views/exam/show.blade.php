
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-labelledby="uploadInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadInvoiceModalLabel">Upload Invoice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formUploadInvoice" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="file_invoice" class="form-label">Pilih File Invoice (Bisa lebih dari 1 file. Max 10MB/file)</label>

                                <input type="file" class="form-control" id="file_invoice" name="file_invoice[]" required accept=".pdf,.jpg,.jpeg,.png" multiple>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editKursModal" tabindex="-1" aria-labelledby="editKursModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editKursModalLabel">Update Kurs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formEditKurs" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <label for="update_kurs" class="col-md-4 col-form-label text-md-start">Kurs Harga</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" class="form-control @error('update_kurs') is-invalid @enderror"
                                        name="update_kurs" id="update_kurs"required>
                            </div>

                            <label for="kurs_admin" class="col-md-4 col-form-label text-md-start">Kurs Admin</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" class="form-control @error('kurs_admin') is-invalid @enderror"
                                        name="kurs_admin" id="kurs_admin"required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="col-md-12 d-flex">
                        {{-- {{ $rkm }} --}}
                        <a href="/exam" class="btn click-primary m-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                        @if ($approvalexam->technical_support == '1')
                        <form action="{{ route('exam.invoice', $rkm->id) }}" method="POST" target="_blank">
                            @csrf
                            <button type="submit" class="btn click-primary m-2">
                                <img src="{{ asset('icon/printer.svg') }}" class="img-responsive" width="20px"> Print Invoice
                            </button>
                        </form>
                        @endif
                    </div>
                    <h5 class="card-title">Detail Exam</h5>
                    <div class="row">
                        <div class="col-md-5">
                            {{-- {{ $rkm }} --}}
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>ID Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->id }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Invoice</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->invoice }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Tanggal Pengajuan Exam</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ \Carbon\Carbon::parse($rkm->tanggal_pengajuan)->translatedFormat('l, j F Y') }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Materi</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->materi }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Nama Perusahaan</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->perusahaan }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>Sales</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    <p>{{ $rkm->rkm->sales_key }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-xs-4">
                                    <p>File Invoice</p>
                                </div>
                                <div class="col-md-1 col-sm-1 col-xs-1">
                                    <p>:</p>
                                </div>
                                <div class="col-md-7 col-sm-7 col-xs-7">
                                    @if ($approvalexam && $approvalexam->spv_sales == '1')
                                        @php
                                            // Memastikan data dibaca sebagai array
                                            $files = is_string($rkm->file_invoice) ? json_decode($rkm->file_invoice, true) : $rkm->file_invoice;
                                        @endphp

                                        @if (!empty($files) && is_array($files))
                                            @foreach ($files as $index => $file)
                                                <div class="d-flex align-items-center mb-2">
                                                    <a href="{{ asset('uploads/invoices/' . $file) }}" target="_blank" class="btn btn-sm btn-primary" style="margin-right: 5px;">
                                                        <i class="fas fa-file-invoice"></i> File {{ $index + 1 }}
                                                    </a>

                                                    <form onsubmit="return confirm('Apakah Anda yakin ingin menghapus File {{ $index + 1 }} ini?');" action="{{ route('exam.deleteSpecificInvoice', ['id' => $rkm->id, 'filename' => $file]) }}" method="POST" style="margin: 0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                            <button type="button" class="btn btn-sm btn-success mt-1" onclick="openUploadModal({{ $rkm->id }})">
                                                <i class="fas fa-plus"></i> Tambah Invoice Lagi
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success mb-1" onclick="openUploadModal({{ $rkm->id }})">
                                                <i class="fas fa-upload"></i> Upload Invoice
                                            </button>
                                        @endif
                                    @else
                                        <p class="text-muted">Menunggu Approval SPV Sales</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <h5>Rincian Harga</h5>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Mata Uang</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->mata_uang }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->harga }} {{ $rkm->mata_uang }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Biaya Admin</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>$ {{ $rkm->biaya_admin }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>
                                            {{ formatRupiah(floatval($harga)) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Biaya Admin dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <p>{{ formatRupiah(floatval($biaya_admin)) }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Harga Total dalam Rupiah</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ formatRupiah($rkm->harga_rupiah) }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>PAX</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ $rkm->pax }} Peserta</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5 col-sm-5 col-xs-5">
                                        <p>Total</p>
                                    </div>
                                    <div class="col-md-1 col-sm-1 col-xs-1">
                                        <p>:</p>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <p>{{ formatRupiah($rkm->total) }}</p>
                                    </div>
                                </div>

                                @if (auth()->user()->jabatan == 'Finance & Accounting')
                                    <div class="col-md-7 col-sm-7 col-xs-7">
                                        <button type="button" class="btn btn-sm btn-primary mb-1" onclick="openKursEdit({{ $rkm->id }})">
                                            <i class="fas fa-upload"></i> Update Kurs
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-12 my-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>Approval Exam</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        @if ($rkm->rkm->exam === '1')
                                                            <th>SPV Sales</th>
                                                            <th>Accounting</th>
                                                        @endif
                                                        <th>Technical Support</th>
                                                        <th>Status</th>
                                                    </thead>
                                                    <tbody>
                                                        @if($approvalexam)
                                                            @if ($rkm->rkm->exam === '1')
                                                                @if (auth()->user()->jabatan == 'SPV Sales')
                                                                    @if ($approvalexam->spv_sales == '0')
                                                                        <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Approval</a></td>
                                                                    @elseif ($approvalexam->spv_sales == '1')
                                                                        <td>Approve</td>
                                                                    @else
                                                                        <td>{{ $approvalexam->spv_sales ? 'Approve' : 'Belum' }}</td>
                                                                    @endif
                                                                @else
                                                                    <td>{{ $approvalexam->spv_sales ? 'Approve' : 'Belum' }}</td>
                                                                @endif

                                                                @if (auth()->user()->jabatan == 'Finance & Accounting')
                                                                    @if ($approvalexam->spv_sales == '1' && $approvalexam->office_manager == '0')
                                                                        <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Konfirmasi</a></td>
                                                                    @elseif ($approvalexam->office_manager == '1')
                                                                        <td>Dikonfirmasi</td>
                                                                    @else
                                                                        <td>Belum</td>
                                                                    @endif
                                                                @else
                                                                    <td>{{ $approvalexam->office_manager ? 'Dikonfirmasi' : 'Belum' }}</td>
                                                                @endif
                                                            @endif

                                                            @if (auth()->user()->jabatan == 'Technical Support')
                                                                @if ($approvalexam->spv_sales == '1' && $approvalexam->office_manager == '1' && $approvalexam->technical_support == '0')
                                                                    <td><a href="{{ route('approvalexam', $approvalexam->id_exam) }}" class="btn btn-primary">Konfirmasi</a></td>
                                                                @elseif ($approvalexam->technical_support == '1')
                                                                    <td>Dikonfirmasi</td>
                                                                @else
                                                                    <td>Belum</td>
                                                                @endif
                                                            @else
                                                                <td>{{ $approvalexam->technical_support ? 'Dikonfirmasi' : 'Belum' }}</td>
                                                            @endif

                                                            <td>{{ $approvalexam->status }}</td>
                                                        @else
                                                            <td colspan="4">Data tidak tersedia</td>
                                                        @endif

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>Histori Pengubahan Exam</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Kode Karyawan</th>
                                                            <th>Keterangan</th>
                                                            <th>Status Terakhir</th>
                                                            <th>Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($exam as $e)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ $e->kode_karyawan }}</td>
                                                            <td>{{ $e->keterangan }}</td>
                                                            <td>{{ $e->status }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($e->created_at)->translatedFormat('d F Y H:i:s') }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

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
    @media screen and (max-width: 768px) {
        .card {
            padding: 15px;
            max-width: 100%;
        }

        .card-body  .row {
            margin-bottom: 10px;
        }

        /* .col-xs-4, */
        .col-xs-1 {
            display: none;
        }

        .col-xs-7 {
            width: 100%;
            text-align: left;
        }
    }

        .cardname {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .click-secondary-icon {
            background:    #355C7C;
            border-radius: 1000px;
            width:         45px;
            height:        45px;
            color:         #ffffff;
            display:       flex;
            justify-content: center;
            align-items:   center;
            text-align:    center;
            text-decoration: none;
        }
        .click-secondary-icon i {
            line-height: 45px;
        }

        .click-secondary {
            background:    #355C7C;
            border-radius: 1000px;
            padding:       10px 25px;
            color:         #ffffff;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }

        .click-secondary:hover {
            color:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .click-warning {
            background:    #f8be00;
            border-radius: 1000px;
            padding:       10px 20px;
            color:         #000000;
            display:       inline-block;
            font:          normal bold 18px/1 "Open Sans", sans-serif;
            text-align:    center;
            transition:    color 0.1s linear, background-color 0.2s linear; /
        }

        .click-warning:hover {
            background:         #A5C7EF;
            transition:    color 0.1s linear, background-color 0.2s linear;
        }
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: auto;
            height: auto;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(2px);
            }
            .checkmark {
        display: block;
        width: 25px;
        height: 25px;
        border: 1px solid #ccc;
        border-radius: 50%;
        position: relative;
        margin: 0 auto;
    }

    .checkmark:after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22bb33;
        display: none;
    }

    tr.selected .checkmark:after {
        display: block;
    }

</style>
@push('js')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    function openUploadModal(id) {
        var form = document.getElementById('formUploadInvoice');
        form.action = '/exam/' + id + '/upload-invoice';
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadInvoiceModal'));
        uploadModal.show();
    }


    // Format number as Rupiah
    function formatRupiah(angka, prefix) {
        var numberString = angka.toString().replace(/[^,\d]/g, ''),
            split = numberString.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return (prefix === undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : ''));
    }

    // Remove Rupiah format for calculations
    function removeRupiahFormat(angka) {
        return parseFloat(angka.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
    }

    // Validate numeric input
    function validateInput(input, fieldName) {
        let value = parseFloat(input.val()) || 0;
        if (value < 0) {
            alert(`${fieldName} tidak boleh negatif!`);
            input.val('');
            return false;
        }
        return true;
    }
    
    function openKursEdit(id) {
        var form = document.getElementById('formEditKurs');
        form.action = '/exam/' + id + '/update-kurs';
        var editModal = new bootstrap.Modal(document.getElementById('editKursModal'));
        editModal.show();

        $.ajax({
            url: '/exam/get-kurs/' + id,
            type: 'GET',
            success: function (res) {
                let kurs = res.kurs ?? 0;
                let kurs_admin = res.kurs_admin ?? 0;
                $('#update_kurs').val(formatRupiah(String(kurs)));
                $('#kurs_admin').val(formatRupiah(String(kurs_admin)));
            },
            error: function (err) {
                alert('Terjadi Kesalahan, gagal membuka modal update');
                console.log(err);
            }
        })
    }

    $(document).ready(function() {

        // Format and validate numeric inputs
        $('#update_kurs, #kurs_admin').on('input', function() {
            if (validateInput($(this), $(this).attr('name'))) {
                $(this).val(formatRupiah($(this).val()));
            }
        });

        $('#formEditKurs').on('submit', function() {
            $('#update_kurs').val(removeRupiahFormat($('#update_kurs').val()));
            $('#kurs_admin').val(removeRupiahFormat($('#kurs_admin').val()));
        })
    });
</script>
@endpush
@endsection
