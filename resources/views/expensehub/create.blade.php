@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-single {
        padding: 14px;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ route('expensehub.index') }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    <h5 class="card-title text-center mb-4">{{ __('Pengajuan Entertaint, Rimburst & Oleh-Oleh') }}</h5>
                    <form method="POST" action="{{ route('expensehub.store') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- ID Karyawan -->
                        <div class="row mb-3">
                            <label for="id_karyawan" class="col-md-4 col-form-label text-md-start">{{ __('Nama Karyawan') }}</label>
                            <div class="col-md-6">
                                <input type="hidden" name="id_karyawan" value="{{ $karyawan->id }}">
                                <input disabled id="nama_karyawan" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror" name="nama_karyawan" value="{{ $karyawan->nama_lengkap }}" autocomplete="nama_karyawan" autofocus>
                                @error('id_karyawan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="divisi" class="col-md-4 col-form-label text-md-start">{{ __('Divisi') }}</label>
                            <div class="col-md-6">
                                <input disabled id="divisi" type="text" placeholder="Masukan Nama karyawan" class="form-control @error('divisi') is-invalid @enderror" name="divisi" value="{{ $karyawan->divisi }}" autocomplete="divisi" autofocus>
                                @error('divisi')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('ID RKM') }}</label>
                            <div class="col-md-6">
                                <select name="id_rkm" id="id_rkm" class="form-select select2-single" required>
                                    <option selected disabled>pilih ID RKM</option>
                                    @foreach ($rkm as $rkm)
                                    <option value="{{ $rkm }}">{{ $rkm }}</option>
                                    @endforeach
                                </select>
                                @error('id_rkm')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">{{ __('Tipe') }}</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select" required>
                                    <option selected disabled>Pilih Jenis Barang</option>
                                    <option value="Entertaint">entertaint</option>
                                    <option value="Reimburst">reimburst</option>
                                    <option value="Oleh-Oleh">oleh-oleh</option>
                                </select>
                                @error('tipe')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div id="itemContainer">
                            <div class="item mb-3">
                                <div class="row">
                                    <label for="barang[nama_barang][]" class="col-md-4 col-form-label text-md-start">Nama Pengajuan</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="barang[nama_pengajuan][]" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="barang[qty][]" class="col-md-4 col-form-label text-md-start">Jumlah</label>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" name="barang[qty][]" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="barang[harga_barang][]" class="col-md-4 col-form-label text-md-start">Harga Pengajuan (Rp.)</label>
                                    <div class="col-md-6">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">Rp.</span>
                                            <input type="text" class="form-control" name="barang[harga_pengajuan][]" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="barang[keterangan][]" class="col-md-4 col-form-label text-md-start">Keterangan (Optional)</label>
                                    <div class="col-md-6">
                                        <textarea class="form-control" name="barang[keterangan][]"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4"></div>
                            <div class="col-md-6">
                                <button type="button" id="addItem" class="btn btn-primary">Tambah Item</button>
                            </div>
                        </div>

                        <div class="row mb-0 text-end">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    {{ __('Ajukan') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        setupInputFormatter('#itemContainer input[name="barang[harga_pengajuan][]"]');

        $('form').on('submit', function(e) {
            e.preventDefault();
            $('#itemContainer input[name="barang[harga_pengajuan][]"]').each(function() {
                $(this).val($(this).val().replace(/\./g, ''));
            });
            this.submit();
        });

        $('#tipe').on('change', function() {
            const tipe = $(this).val();
            updateLabels(tipe);
        });

        $('#addItem').click(function() {
            addItem();
        });

        $(document).on('click', '.removeItemButton', function() {
            $(this).closest('.item').remove();
        });

        $(document).ready(function() {
            $('.select2-single').select2({
                placeholder: "Pilih Jenis Barang",
                allowClear: true,
                width: '100%'
            });
        });
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    function setupInputFormatter(selector) {
        var $input = $(selector);
        $input.on('input', function() {
            $input.val(formatRupiah(this.value));
        });
    }

    function updateLabels(tipe) {
        let labelNama = "Nama Pengajuan";
        let labelHarga = "Harga Barang (Rp.)";

        if (tipe) {
            labelNama = "Nama " + tipe;
            labelHarga = "Harga " + tipe + " (Rp.)";
        }

        $('#itemContainer .item').each(function() {
            $(this).find('label[for^="barang[nama_pengajuan]"]').text(labelNama);
            $(this).find('label[for^="barang[harga_pengajuan]"]').text(labelHarga);
        });
    }

    let itemIndex = 1;

    function addItem() {
        const tipe = $('#tipe').val() || 'Pengajuan';
        const newItem = `
        <div class="item mb-3">
            <div class="row">
                <label for="barang[nama_pengajuan][]" class="col-md-4 col-form-label text-md-start">Nama ${tipe}</label>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="barang[nama_pengajuan][]" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger removeItemButton">Hapus</button>
                </div>
            </div>
            <div class="row">
                <label for="barang[qty][]" class="col-md-4 col-form-label text-md-start">Jumlah</label>
                <div class="col-md-6">
                    <input type="number" class="form-control" name="barang[qty][]" required>
                </div>
            </div>
            <div class="row">
                <label for="barang[harga_pengajuan][]" class="col-md-4 col-form-label text-md-start">Harga ${tipe} (Rp.)</label>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp.</span>
                        <input type="text" class="form-control" name="barang[harga_pengajuan][]" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <label for="barang[keterangan][]" class="col-md-4 col-form-label text-md-start">Keterangan (Optional)</label>
                <div class="col-md-6">
                    <textarea class="form-control" name="barang[keterangan][]"></textarea>
                </div>
            </div>
        </div>
    `;
        $('#itemContainer').append(newItem);

        $('#itemContainer').find('input[name="barang[harga_pengajuan][]"]').last().on('input', function() {
            $(this).val(formatRupiah(this.value));
        });

        itemIndex++;
    }
</script>
@endsection