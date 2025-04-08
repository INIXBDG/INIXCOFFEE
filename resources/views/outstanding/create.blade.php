@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                <a href="{{ url()->previous() }}" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                <h5 class="card-title text-center mb-4">{{ __('Outstanding') }}</h5>
                    <form method="POST" action="{{ route('outstanding.store') }}" id="formOutstanding">
                        @csrf
                        <div class="row mb-3">
                            <label for="bulan" class="col-md-4 col-form-label text-md-start">{{ __('Pilih Bulan dan Tahun') }}</label>
                            <div class="col-md-3">
                                <select name="bulan" class="form-select" id="bulan">
                                    <option disabled>Pilih Bulan</option>
                                    @php
                                    $bulan_sekarang = now()->month;
                                    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    for ($bulan = 1; $bulan <= 12; $bulan++) {
                                        $bulan_awal = $nama_bulan[$bulan - 1];
                                        $selected = $bulan == $bulan_sekarang ? 'selected' : '';
                                        echo "<option value=\"$bulan\" $selected>$bulan_awal</option>";
                                    }
                                    @endphp
                                    
                                </select>
                                @error('bulan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <select name="tahun" class="form-select" id="tahun">
                                    <option disabled>Pilih Tahun</option>
                                    @php
                                    $tahun_sekarang = now()->year;
                                    for ($tahun = 2020; $tahun <= $tahun_sekarang + 2; $tahun++) {
                                        $selected = $tahun == $tahun_sekarang ? 'selected' : '';
                                        echo "<option value=\"$tahun\" $selected>$tahun</option>";
                                    }
                                    @endphp
                                </select>
                                @error('tahun')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <button type="button" onclick="getDataRKM()" class="btn click-primary">Cari Data</button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                            <div class="col-md-6">
                                <select name="id_rkm" id="id_rkm" class="form-select"></select>
                                @error('id_rkm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="materi" class="col-md-4 col-form-label text-md-start">{{ __('Materi') }}</label>
                            <div class="col-md-6">
                                <input disabled type="text" name="materi" id="materi" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input disabled type="text" name="perusahaan" id="perusahaan" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pax" class="col-md-4 col-form-label text-md-start">{{ __('Pax') }}</label>
                            <div class="col-md-6">
                                <input disabled type="text" name="pax" id="pax" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="sales_key" class="col-md-4 col-form-label text-md-start">{{ __('Sales') }}</label>
                            <div class="col-md-6">
                                <input readonly type="text" name="sales_key" id="sales_key" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pic" class="col-md-4 col-form-label text-md-start">{{ __('PIC') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="pic" id="pic" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="net_sales" class="col-md-4 col-form-label text-md-start">{{ __('Total Net Sales') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('net_sales') is-invalid @enderror" name="net_sales" id="net_sales" placeholder="Net Sales" required>
                                </div>
                                @error('net_sales')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="status_pembayaran" class="col-md-4 col-form-label text-md-start">{{ __('Status Pembayaran') }}</label>
                            <div class="col-md-4">
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="status_pembayaran" id="approveYes" value="1" autocomplete="off" >
                                    <label class="btn btn-outline-primary" for="approveYes">Sudah</label>
            
                                    <input type="radio" class="btn-check" name="status_pembayaran" id="approveNo" value="0" autocomplete="off" checked>
                                    <label class="btn btn-outline-danger" for="approveNo">Belum</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="#" target="_blank" id="btnregform" class="btn btn-md btn-primary d-none">Registrasi Form</a>
                            </div>
                                                      
                        </div>

                        <div class="row mb-3">
                            <label for="due_date" class="col-md-4 col-form-label text-md-start">{{ __('Tenggat Waktu') }}</label>
                            <div class="col-md-6">
                                <input type="date" name="due_date" id="due_date" class="form-control">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_bayar" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Bayar') }}</label>
                            <div class="col-md-6">
                                <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status Tracking') }}</label>
                            <div class="col-md-6">
                                {{-- <select name="status_tracking" id="status_tracking" class="form-select"> --}}
                                    <select name="status_tracking" id="status_tracking" class="form-select">
                                        <option value="" selected>Pilih Status</option>
                                        <option value="invoice">Invoice</option>
                                        <option value="faktur_pajak">Faktur Pajak</option>
                                        <option value="dokumen_tambahan">Dokumen Tambahan</option>
                                        <option value="konfir_cs">Konfirmasi Pengiriman RPX</option>
                                        <option value="no_resi">Konfirmasi Nomor Resi</option>
                                        <option value="tracking_dokumen">Status Pengiriman Dokumen</option>
                                        <option value="konfir_pic">Konfirmasi PIC</option>
                                        <option value="pembayaran">Pembayaran</option>
                                    </select>
                                {{-- </select> --}}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status_resi" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Resi') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status_resi" id="status_resi" class="form-control" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status_pic" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan PIC') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status_pic" id="status_pic" class="form-control" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status" id="status" class="form-control" >
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" id="btnsubmit" class="btn click-primary">
                                    {{ __('Simpan') }}
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        toggleTanggalBayar();
        statusTracking();
        $('input[name="status_pembayaran"]').on('change', toggleTanggalBayar);
        $('#status_tracking').on('change', statusTracking);
        $('#btnsubmit').on('click', function () {
            const net_sales = parseFloat(removeRupiahFormat($('#net_sales').val())) || 0;
            $('#net_sales').val(net_sales); // Mengisi input dengan nilai numerik tanpa format Rupiah
            $('#btnsubmit').prop('disabled', true);
            $('#formOutstanding').submit();
        });

        $('#net_sales').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        var selectedOption = $('#id_rkm').find('option:selected');
        if (selectedOption.val() && selectedOption.val() !== "") {
            $('#btnregform').removeClass('d-none'); // Tampilkan tombol
        } else {
            $('#btnregform').addClass('d-none'); // Sembunyikan tombol
        }

    });

    function toggleTanggalBayar() {
        const selectedValue = $('input[name="status_pembayaran"]:checked').val();
        console.log(selectedValue);
        if (selectedValue === "1") {
            $('#tanggal_bayar').closest('.row').show(); // Tampilkan elemen tanggal_bayar
        } else {
            $('#tanggal_bayar').closest('.row').hide(); // Sembunyikan elemen tanggal_bayar
        }
    }

    function statusTracking() {
        const selectedValue = $('#status_tracking').val();
        console.log(selectedValue);
        if (selectedValue === "no_resi") {
            $('#status_resi').closest('.row').show(); // Tampilkan elemen status_resi
        } else {
            $('#status_resi').closest('.row').hide(); // Sembunyikan elemen status_resi
        }
        if (selectedValue === "konfir_pic") {
            $('#status_pic').closest('.row').show();
            $('#status_resi').closest('.row').show(); // Tampilkan elemen status_resi
             // Tampilkan elemen status_pic
        } else {
            $('#status_pic').closest('.row').hide(); // Sembunyikan elemen status_pic
        }
    }
    
    function formatRupiah(angka) {
        let rupiah = "";
        let angkaStr = angka.toString();
        let count = 0;

        for (let i = angkaStr.length - 1; i >= 0; i--) {
            rupiah = angkaStr[i] + rupiah;
            count++;
            if (count % 3 === 0 && i !== 0) {
            rupiah = "." + rupiah;
            }
        }

        return rupiah;
    };

    function removeRupiahFormat(angka) {
        return angka.replace(/[Rp.\s]/g, '').replace(/,/g, '.');
    }

    $('#id_rkm').on('change', function() {
            // Ambil opsi yang dipilih
            var selectedOption = $(this).find('option:selected');
            
            // Jika ada pilihan, isi field 'materi' dan 'perusahaan', jika tidak kosongkan
            if (selectedOption.val()) {
                $('#materi').val(selectedOption.data('materi'));
                $('#perusahaan').val(selectedOption.data('perusahaan'));
                $('#pax').val(selectedOption.data('pax'));
                var pax = selectedOption.data('pax');
                var hargajual = selectedOption.data('hargajual');
                var total_net = pax * hargajual;
                var regform = selectedOption.data('regform');
                var sales_key = selectedOption.data('saleskey');
                var pic = selectedOption.data('pic');
                console.log(regform);

                if (regform == '-') {
                    console.log("Regform null, tombol tetap disabled");
                    $('#btnregform').addClass('disabled'); // Sembunyikan tombol

                } else {
                    const myArray = regform.split("/");
                    console.log(myArray);
                    
                    // Buat URL jika regform valid
                    var url = "{{ route('cekregisform', ['id' => 'id']) }}".replace('id', myArray[1]);
                    
                    // Update tombol
                    $('#btnregform').prop("href", url);
                    $('#btnregform').prop("disabled", false); // Aktifkan tombol
                }

                $('#sales_key').val(sales_key);
                $('#pic').val(pic);
                $('#net_sales').val(formatRupiah(total_net));
                $('#btnregform').prop("href", url);
                $('#btnregform').removeClass('d-none');

            } else {
                $('#materi').val('');
                $('#perusahaan').val('');
                $('#pax').val('');
                $('#net_sales').val('');
                $('#btnregform').prop("href", "#");
                $('#btnregform').addClass('d-none');
            }
    });

    function getDataRKM() {
        var tahun = document.getElementById('tahun').value;
        var bulan = document.getElementById('bulan').value;

        $.ajax({
            url: "{{ route('getOutstandingRKM', ['year' => 'TANGGAL_TAHUN', 'month' => 'TANGGAL_BULAN']) }}".replace('TANGGAL_TAHUN', tahun).replace('TANGGAL_BULAN', bulan),
            method: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#loadingModal').modal('show');
                $('#loadingModal').on('show.bs.modal', function () {
                    $('#loadingModal').removeAttr('inert');
                });
            },
            complete: function () {
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    $('#loadingModal').on('hidden.bs.modal', function () {
                        $('#loadingModal').attr('inert', true);
                    });
                }, 1000);
            },
            success: function(response) {
                // Initialize html variable for options
                let html = '<option value="">Pilih RKM</option>';

                // Check if there is data in the response
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        console.log(item)
                        let registrasiText = item.registrasi_form ? item.registrasi_form : '-';
                        html += `<option value="${item.id}" data-materi="${item.materi.nama_materi}" data-perusahaan="${item.perusahaan.nama_perusahaan}" data-pax="${item.pax}" data-hargajual="${item.harga_jual}" data-regform="${registrasiText}" data-saleskey="${item.sales_key}" data-pic="${item.perusahaan.cp}"">${item.materi.nama_materi} - ${item.perusahaan.nama_perusahaan}</option>`;
                    });
                } else {
                    // Handle the case where there is no data
                    html = '<option value="">No data available</option>';
                }

                // Set the options to #id_rkm
                $('#id_rkm').html(html);

                // Re-initialize select2 to apply it to the new options
                $('#id_rkm').select2({
                    placeholder: 'Pilih RKM',
                    allowClear: true
                });
            },
            error: function() {
                alert('Error retrieving data. Please try again.');
            }
        });
    }
</script>
@endsection
