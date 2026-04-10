@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body" id="card">
                    <a href="{{ url()->previous() }}" class="btn click-primary my-2">
                        <img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back
                    </a>
                    <h5 class="card-title text-center mb-4">{{ __('Edit Outstanding') }}</h5>
                    <form method="POST" action="{{ route('outstanding.update', $outstanding->id) }}" id="formOutstanding" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Pilih RKM -->
                        <div class="row mb-3">
                            <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                            <div class="col-md-6">
                                <select name="id_rkm" id="id_rkm" class="form-select" disabled>
                                    <option value="{{ $outstanding->id_rkm }}">{{ $outstanding->rkm->materi->nama_materi }} - {{ $outstanding->rkm->perusahaan->nama_perusahaan }}</option>
                                </select>
                                <input type="hidden" name="id_rkm" value="{{ $outstanding->id_rkm }}">
                            </div>
                        </div>

                        <!-- Materi -->
                        <div class="row mb-3">
                            <label for="materi" class="col-md-4 col-form-label text-md-start">{{ __('Materi') }}</label>
                            <div class="col-md-6">
                                <input disabled type="text" name="materi" id="materi" class="form-control" value="{{ $outstanding->rkm->materi->nama_materi }}">
                            </div>
                        </div>

                        <!-- Perusahaan -->
                        <div class="row mb-3">
                            <label for="perusahaan" class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan') }}</label>
                            <div class="col-md-6">
                                <input disabled type="text" name="perusahaan" id="perusahaan" class="form-control" value="{{ $outstanding->rkm->perusahaan->nama_perusahaan }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="sales_key" class="col-md-4 col-form-label text-md-start">{{ __('Sales') }}</label>
                            <div class="col-md-6">
                                <input readonly type="text" name="sales_key" id="sales_key" class="form-control" value="{{ $outstanding->sales_key }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pic" class="col-md-4 col-form-label text-md-start">{{ __('PIC') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="pic" id="pic" class="form-control" value="{{ $outstanding->pic }}">
                            </div>
                        </div>

                        <!-- Net Sales -->
                        <div class="row mb-3">
                            <label for="net_sales" class="col-md-4 col-form-label text-md-start">{{ __('Net Sales') }}</label>
                            <div class="col-md-6">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control @error('net_sales') is-invalid @enderror" 
                                        name="net_sales" id="net_sales" placeholder="Net Sales" 
                                        value="{{ old('net_sales', number_format($outstanding->net_sales, 0, ',', '.')) }}" required>
                                </div>
                                @error('net_sales')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>


                        <!-- Status Pembayaran -->
                        <div class="row mb-3">
                            <label for="status_pembayaran" class="col-md-4 col-form-label text-md-start">{{ __('Status Pembayaran') }}</label>
                            <div class="col-md-6">
                                <div class="btn-group" role="group" aria-label="Approval Options">
                                    <input type="radio" class="btn-check" name="status_pembayaran" id="approveYes" value="1" autocomplete="off" {{ $outstanding->status_pembayaran == '1' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="approveYes">Sudah</label>

                                    <input type="radio" class="btn-check" name="status_pembayaran" id="approveNo" value="0" autocomplete="off" {{ $outstanding->status_pembayaran == '0' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="approveNo">Belum</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3" id="jumlah_pembayaran_row" style="display: none;">
                            <label for="jumlah_pembayaran" class="col-md-4 col-form-label text-md-start">{{ __('Jumlah Pembayaran') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="jumlah_pembayaran" id="jumlah_pembayaran" class="form-control" placeholder="Masukkan Jumlah Pembayaran">
                            </div>
                        </div>

                        <!-- Tenggat Waktu -->
                        <div class="row mb-3">
                            <label for="due_date" class="col-md-4 col-form-label text-md-start">{{ __('Tenggat Waktu') }}</label>
                            <div class="col-md-6">
                                <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', $outstanding->due_date) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_bayar" class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Bayar') }}</label>
                            <div class="col-md-6">
                                <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control" value="{{ old('tanggal_bayar', $outstanding->tanggal_bayar) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_regist" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Registrasi') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="no_regist" id="no_regist" class="form-control" value="{{ $outstanding->no_regist }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_invoice" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Invoice') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="no_invoice" id="no_invoice" class="form-control" value="{{ $outstanding->no_invoice }}">
                            </div>
                        </div>
                        <div class="row mb-3">  
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status Tracking') }}</label>  
                            <div class="col-md-6">  
                                <select name="status_tracking" id="status_tracking" class="form-select">  
                                    <option value="" selected>Pilih Status</option>  
                                    <option value="invoice" {{ $tracking_outstanding['invoice'] == '1' ? 'selected' : '' }}>Invoice</option>  
                                    <option value="faktur_pajak" {{ $tracking_outstanding['faktur_pajak'] == '1' ? 'selected' : '' }}>Faktur Pajak</option>  
                                    <option value="dokumen_tambahan" {{ $tracking_outstanding['dokumen_tambahan'] == '1' ? 'selected' : '' }}>Dokumen Tambahan</option>  
                                    <option value="konfir_cs" {{ $tracking_outstanding['konfir_cs'] == '1' ? 'selected' : '' }}>Konfirmasi Pengiriman RPX</option>  
                                    <option value="no_resi" {{ $tracking_outstanding['no_resi'] == '1' ? 'selected' : '' }}>Konfirmasi Nomor Resi</option>  
                                    <option value="tracking_dokumen" {{ $tracking_outstanding['tracking_dokumen'] == '1' ? 'selected' : '' }}>Status Pengiriman Dokumen</option>  
                                    <option value="konfir_pic" {{ $tracking_outstanding['konfir_pic'] == '1' ? 'selected' : '' }}>Konfirmasi PIC</option>  
                                    <option value="pembayaran" {{ $tracking_outstanding['pembayaran'] == '1' ? 'selected' : '' }}>Pembayaran</option>  
                                </select>                                   
                            </div>  
                        </div>  

                        <div class="row mb-3" id="faktur_pajak_row" style="display:none;">
                            <label for="faktur_pajak" class="col-md-4 col-form-label text-md-start">{{ __('Upload Faktur Pajak (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" name="faktur_pajak" id="faktur_pajak" class="form-control" accept="application/pdf">
                            </div>
                        </div>

                        <div class="row mb-3" id="dokumen_tambahan_row" style="display:none;">
                            <label for="dokumen_tambahan_files" class="col-md-4 col-form-label text-md-start">{{ __('Upload Dokumen Tambahan (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" name="dokumen_tambahan_files[]" id="dokumen_tambahan_files" class="form-control" accept="application/pdf" multiple>
                            </div>
                        </div>

                        <div class="row mb-3" id="pembayaran" style="display:none;">
                            <label for="pembayaran" class="col-md-4 col-form-label text-md-start">{{ __('Upload Bukti Pembayaran (PDF)') }}</label>
                            <div class="col-md-6">
                                <input type="file" name="pembayaran" id="pembayaran" class="form-control" accept="application/pdf">
                            </div>
                        </div>

                        @if(!empty($outstanding['path_faktur_pajak']))
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">{{ __('File Faktur Pajak') }}</label>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <a href="{{ asset('storage/' . $outstanding['path_faktur_pajak']) }}" target="_blank">Lihat PDF</a>
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if(!empty($outstanding['path_dokumen_tambahan']))
                            <div class="row mb-3">
                                <label class="col-md-4 col-form-label text-md-start">{{ __('File Dokumen Tambahan') }}</label>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <a href="{{ asset('storage/' . str_replace('public/', '', $outstanding['path_dokumen_tambahan'])) }}" target="_blank">
                                            Lihat PDF
                                        </a>
                                    </small>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <label for="status_resi" class="col-md-4 col-form-label text-md-start">{{ __('Nomor Resi') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status_resi" id="status_resi" class="form-control" value="{{ $tracking_outstanding['status_resi'] }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="status_pic" class="col-md-4 col-form-label text-md-start">{{ __('Keterangan PIC') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status_pic" id="status_pic" class="form-control" value="{{ $tracking_outstanding['status_pic'] }}">
                            </div>
                        </div>
                        
                        {{-- <div class="row mb-3">
                            <label for="status" class="col-md-4 col-form-label text-md-start">{{ __('Status') }}</label>
                            <div class="col-md-6">
                                <input type="text" name="status" id="status" class="form-control" >
                            </div>
                        </div> --}}
                        <!-- Simpan Button -->
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
        $('#btnsubmit').on('click', function () {
            const net_sales = parseFloat(removeRupiahFormat($('#net_sales').val())) || 0;
            $('#net_sales').val(net_sales);
            $('#btnsubmit').prop('disabled', true);
            $('#formOutstanding').submit();
        });
        $('#status_tracking').on('change', statusTracking);
        statusTracking();

        $('#net_sales').on('input', function () {
            let inputVal = $(this).val().replace(/[^,\d]/g, '');
            $(this).val(formatRupiah(inputVal));
        });
        optionDisabled();

        toggleJumlahPembayaran();

        $('input[name="status_pembayaran"]').on('change', toggleJumlahPembayaran);

        function toggleJumlahPembayaran() {
            const status = $('input[name="status_pembayaran"]:checked').val();
            if (status === '1') { // Sudah
                $('#jumlah_pembayaran_row').show();
            } else {
                $('#jumlah_pembayaran_row').hide();
                $('#jumlah_pembayaran').val(''); // reset nilai
            }
        }
    });
    
    function optionDisabled() {  
        // Mengubah data menjadi objek JavaScript  
        var trackingOutstanding = @json($tracking_outstanding);  
          
        const optionsToDisable = [    
            "invoice",    
            "faktur_pajak",    
            "dokumen_tambahan",    
            "konfir_cs",    
            "no_resi",    
            "tracking_dokumen",    
            "konfir_pic",    
            "pembayaran"    
        ];    
  
        optionsToDisable.forEach(function(optionValue) {    
            // Jika nilai dari trackingOutstanding sesuai dengan opsi, nonaktifkan opsi tersebut    
            if (trackingOutstanding[optionValue] === "1") {    
                $(`#status_tracking option[value="${optionValue}"]`).prop('disabled', true);    
            }    
        });    
    }  
    function statusTracking() {
        const selectedValue = $('#status_tracking').val();
        console.log(selectedValue);
        if (selectedValue === "faktur_pajak") {
                $('#faktur_pajak_row').show();
                $('#dokumen_tambahan_row').hide(); 
                $('#pembayaran').hide(); 
            } else if (selectedValue === "dokumen_tambahan") {
                $('#dokumen_tambahan_row').show();
                $('#faktur_pajak_row').hide(); 
                $('#pembayaran').hide(); 
            } else if (selectedValue === "pembayaran") {
                $('#pembayaran').show();
                $('#dokumen_tambahan_row').hide();
                $('#faktur_pajak_row').hide(); 
            } else {
                $('#faktur_pajak_row').hide();
                $('#pembayaran').hide();
                $('#dokumen_tambahan_row').hide();
            }

        if (selectedValue === "no_resi") {
            $('#no_resi').closest('.row').show(); // Tampilkan elemen no_resi
        } else {
            $('#no_resi').closest('.row').hide(); // Sembunyikan elemen no_resi
        }
    }
    function formatRupiah(value) {
        const isNegative = value.startsWith('-'); // Cek apakah nilai negatif
        value = value.replace(/[^,\d]/g, '').toString(); // Hapus karakter non-numerik kecuali koma
        const split = value.split(',');
        let rupiah = split[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        rupiah = (split[1] !== undefined) ? rupiah + ',' + split[1] : rupiah;
        return (isNegative ? '-' : '') + rupiah;
    }

    function removeRupiahFormat(angka) {
        return angka.replace(/[Rp.\s]/g, '').replace(/,/g, '.');
    }

    
</script>
@endsection
