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
                        <form method="POST" action="{{ route('outstanding.update', $outstanding->id) }}"
                            id="formOutstanding" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Pilih RKM -->
                            <div class="row mb-3">
                                <label for="id_rkm" class="col-md-4 col-form-label text-md-start">{{ __('RKM') }}</label>
                                <div class="col-md-6">
                                    <select name="id_rkm" id="id_rkm" class="form-select" disabled>
                                        <option value="{{ $outstanding->id_rkm }}">
                                            {{ $outstanding->rkm->materi->nama_materi }} -
                                            {{ $outstanding->rkm->perusahaan->nama_perusahaan }}
                                        </option>
                                    </select>
                                    <input type="hidden" name="id_rkm" value="{{ $outstanding->id_rkm }}">
                                </div>
                            </div>

                            <!-- Materi -->
                            <div class="row mb-3">
                                <label for="materi" class="col-md-4 col-form-label text-md-start">{{ __('Materi') }}</label>
                                <div class="col-md-6">
                                    <input disabled type="text" name="materi" id="materi" class="form-control"
                                        value="{{ $outstanding->rkm->materi->nama_materi }}">
                                </div>
                            </div>

                            <!-- Perusahaan -->
                            <div class="row mb-3">
                                <label for="perusahaan"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Perusahaan') }}</label>
                                <div class="col-md-6">
                                    <input disabled type="text" name="perusahaan" id="perusahaan" class="form-control"
                                        value="{{ $outstanding->rkm->perusahaan->nama_perusahaan }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="sales_key"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Sales') }}</label>
                                <div class="col-md-6">
                                    <input readonly type="text" name="sales_key" id="sales_key" class="form-control"
                                        value="{{ $outstanding->sales_key }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="pic" class="col-md-4 col-form-label text-md-start">{{ __('PIC') }}</label>
                                <div class="col-md-6">
                                    <input type="text" name="pic" id="pic" class="form-control"
                                        value="{{ old('pic', $outstanding->pic) }}">
                                </div>
                            </div>

                            <!-- Net Sales -->
                            <div class="row mb-3">
                                <label for="net_sales"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Net Sales') }}</label>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control @error('net_sales') is-invalid @enderror"
                                            name="net_sales" id="net_sales" placeholder="Net Sales"
                                            value="{{ old('net_sales', number_format($outstanding->net_sales, 0, ',', '.')) }}"
                                            required>
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
                                <label for="status_pembayaran"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Status Pembayaran') }}</label>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group" aria-label="Approval Options">
                                        <input type="radio" class="btn-check" name="status_pembayaran" id="approveYes"
                                            value="1" autocomplete="off" {{ old('status_pembayaran', $outstanding->status_pembayaran) == '1' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="approveYes">Sudah</label>

                                        <input type="radio" class="btn-check" name="status_pembayaran" id="approveNo"
                                            value="0" autocomplete="off" {{ old('status_pembayaran', $outstanding->status_pembayaran) == '0' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="approveNo">Belum</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3" id="jumlah_pembayaran_row" style="display: none;">
                                <label for="jumlah_pembayaran"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Jumlah Pembayaran') }}</label>
                                <div class="col-md-6">
                                    <input type="text"
                                        value="{{ old('jumlah_pembayaran', number_format($outstanding->jumlah_pembayaran ?? 0, 0, ',', '.')) }}"
                                        name="jumlah_pembayaran" id="jumlah_pembayaran" class="form-control"
                                        placeholder="Masukkan Jumlah Pembayaran">
                                </div>
                            </div>

                            <div id="potongan_section" class="row mb-3" style="display:none;">
                                <label class="col-md-4 col-form-label">{{ __('Potongan') }}</label>
                                <div class="col-md-6" id="potongan_container">

                                    <div class="row mb-2 potongan-item">
                                        <div class="col-5">
                                            <input type="text" name="jenis_potongan[]" class="form-control"
                                                value="Admin Transfer" readonly>
                                        </div>
                                        <div class="col-7">
                                            <input type="text" name="jumlah_potongan[]" class="form-control jumlah-potongan"
                                                placeholder="Jumlah Admin Transfer"
                                                value="{{ old('jumlah_potongan.0', isset($potongan[0]) ? number_format($potongan[0]['jumlah'], 0, ',', '.') : '') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-2 potongan-item">
                                        <div class="col-5">
                                            <input type="text" name="jenis_potongan[]" class="form-control"
                                                value="Nominal PPH23" readonly>
                                        </div>
                                        <div class="col-7">
                                            <input type="text" name="jumlah_potongan[]" class="form-control jumlah-potongan"
                                                placeholder="Jumlah PPH23"
                                                value="{{ old('jumlah_potongan.1', isset($potongan[1]) ? number_format($potongan[1]['jumlah'], 0, ',', '.') : '') }}">
                                        </div>
                                    </div>

                                    <div class="row mb-2 potongan-item">
                                        <div class="col-5">
                                            <input type="text" name="jenis_potongan[]" class="form-control"
                                                value="Nominal PPN" readonly>
                                        </div>
                                        <div class="col-7">
                                            <input type="text" name="jumlah_potongan[]" class="form-control jumlah-potongan"
                                                placeholder="Jumlah PPN"
                                                value="{{ old('jumlah_potongan.2', isset($potongan[2]) ? number_format($potongan[2]['jumlah'], 0, ',', '.') : '') }}">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Tenggat Waktu -->
                            <div class="row mb-3">
                                <label for="due_date"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Tenggat Waktu') }}</label>
                                <div class="col-md-6">
                                    <input type="date" name="due_date" id="due_date" class="form-control"
                                        value="{{ old('due_date', $outstanding->due_date) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="tanggal_bayar"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Tanggal Bayar') }}</label>
                                <div class="col-md-6">
                                    <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control"
                                        value="{{ old('tanggal_bayar', $outstanding->tanggal_bayar) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_regist"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Nomor Registrasi') }}</label>
                                <div class="col-md-6">
                                    <input type="text" name="no_regist" id="no_regist" class="form-control"
                                        value="{{ old('no_regist', $outstanding->no_regist) }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="no_invoice"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Nomor Invoice') }}</label>
                                <div class="col-md-6">
                                    <input type="text" name="no_invoice" id="no_invoice" class="form-control"
                                        value="{{ old('no_invoice', $outstanding->no_invoice) }}">
                                </div>
                            </div>

                            <!-- Status Tracking -->
                            <div class="row mb-3">
                                <label for="status_tracking"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Status Tracking') }}</label>
                                <div class="col-md-6">
                                    @if($outstanding->status_pembayaran == '1')
                                        <!-- Jika sudah lunas, tampilkan readonly -->
                                        <input type="text" class="form-control"
                                            value="{{ ucfirst(str_replace('_', ' ', $currentStatus)) }}" readonly>
                                        <input type="hidden" name="status_tracking" value="{{ $currentStatus }}">
                                        <small class="text-danger">Status sudah selesai, tidak bisa diubah</small>
                                    @else
                                        <!-- Jika belum lunas, tampilkan dropdown -->
                                        <select name="status_tracking" id="status_tracking" class="form-select">
                                            <option value="">Pilih Status</option>
                                            <option value="invoice" {{ $currentStatus == 'invoice' ? 'selected' : '' }}>Invoice
                                            </option>
                                            <option value="faktur_pajak" {{ $currentStatus == 'faktur_pajak' ? 'selected' : '' }}>
                                                Faktur Pajak</option>
                                            <option value="dokumen_tambahan" {{ $currentStatus == 'dokumen_tambahan' ? 'selected' : '' }}>Dokumen Tambahan</option>
                                            <option value="konfir_cs" {{ $currentStatus == 'konfir_cs' ? 'selected' : '' }}>
                                                Konfirmasi Pengiriman RPX</option>
                                            <option value="no_resi" {{ $currentStatus == 'no_resi' ? 'selected' : '' }}>Nomor Resi
                                            </option>
                                            <option value="tracking_dokumen" {{ $currentStatus == 'tracking_dokumen' ? 'selected' : '' }}>Tracking Dokumen</option>
                                            <option value="konfir_pic" {{ $currentStatus == 'konfir_pic' ? 'selected' : '' }}>
                                                Konfirmasi PIC</option>
                                            <option value="pembayaran" {{ $currentStatus == 'pembayaran' ? 'selected' : '' }}>
                                                Pembayaran</option>
                                        </select>
                                    @endif
                                </div>
                            </div>

                            @if($outstanding->status_pembayaran == '1')
                                <div class="row mb-3" id="download_files_section">
                                    <label class="col-md-4 col-form-label text-md-start">{{ __('Lihat Dokumen') }}</label>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-wrap gap-2">
                                            @if(!empty($outstanding->path_faktur_pajak))
                                                <a href="{{ asset('storage/' . $outstanding->path_faktur_pajak) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Faktur Pajak
                                                </a>
                                            @endif

                                            @if(!empty($outstanding->path_dokumen_tambahan))
                                                <a href="{{ asset('storage/' . str_replace('public/', '', $outstanding->path_dokumen_tambahan)) }}"
                                                    target="_blank" class="btn btn-sm btn-outline-info">
                                                    Dokumen Tambahan
                                                </a>
                                            @endif

                                            @if(empty($outstanding->path_faktur_pajak) && empty($outstanding->path_dokumen_tambahan))
                                                <small class="text-muted fst-italic">Tidak ada dokumen yang tersedia.</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row mb-3" id="faktur_pajak_row" style="display:none;">
                                <label for="faktur_pajak"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Upload Faktur Pajak (PDF)') }}</label>
                                <div class="col-md-6">
                                    <input type="file" name="faktur_pajak" id="faktur_pajak" class="form-control"
                                        accept="application/pdf">
                                    @if(!empty($outstanding->path_faktur_pajak))
                                        <small class="text-muted mt-1 d-block">
                                            File saat ini: <a href="{{ asset('storage/' . $outstanding->path_faktur_pajak) }}"
                                                target="_blank">Lihat PDF</a>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3" id="dokumen_tambahan_row" style="display:none;">
                                <label for="dokumen_tambahan_files"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Upload Dokumen Tambahan (PDF)') }}</label>
                                <div class="col-md-6">
                                    <input type="file" name="dokumen_tambahan_files[]" id="dokumen_tambahan_files"
                                        class="form-control" accept="application/pdf" multiple>
                                    @if(!empty($outstanding->path_dokumen_tambahan))
                                        <small class="text-muted mt-1 d-block">
                                            File saat ini: <a
                                                href="{{ asset('storage/' . $outstanding->path_dokumen_tambahan) }}"
                                                target="_blank">Lihat PDF</a>
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3" id="pembayaran_row" style="display:none;">
                                <label for="pembayaran"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Upload Bukti Pembayaran (PDF)') }}</label>
                                <div class="col-md-6">
                                    <input type="file" name="pembayaran" id="pembayaran" class="form-control"
                                        accept="application/pdf">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="status_resi"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Nomor Resi') }}</label>
                                <div class="col-md-6">
                                    <input type="text" name="status_resi" id="status_resi" class="form-control"
                                        value="{{ old('status_resi', $tracking_outstanding['status_resi'] ?? '-') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="status_pic"
                                    class="col-md-4 col-form-label text-md-start">{{ __('Keterangan PIC') }}</label>
                                <div class="col-md-6">
                                    <input type="text" name="status_pic" id="status_pic" class="form-control"
                                        value="{{ old('status_pic', $tracking_outstanding['status_pic'] ?? '-') }}">
                                </div>
                            </div>

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
    <script>
        $(document).ready(function () {
            // Initialize
            setTimeout(() => {
                toggleJumlahPembayaran();
                togglePotongan();
                statusTracking();
            }, 100);

            // Submit Handler
            $('#btnsubmit').on('click', function (e) {
                e.preventDefault();

                const net_sales = parseFloat(removeRupiahFormat($('#net_sales').val())) || 0;
                const jumlah_pembayaran = parseFloat(removeRupiahFormat($('#jumlah_pembayaran').val())) || 0;

                // Hapus format rupiah sebelum submit untuk semua input potongan
                $('.jumlah-potongan').each(function () {
                    let rawVal = removeRupiahFormat($(this).val());
                    $(this).val(rawVal);
                });

                $('#net_sales').val(net_sales);
                $('#jumlah_pembayaran').val(jumlah_pembayaran);

                $('#btnsubmit').prop('disabled', true);
                $('#formOutstanding').submit();
            });

            // Event Listeners
            $('#status_tracking').on('change', statusTracking);

            $('input[name="status_pembayaran"]').on('change', toggleJumlahPembayaran);

            $('#net_sales, #jumlah_pembayaran').on('input', function () {
                let inputVal = $(this).val().replace(/[^,\d]/g, '');
                $(this).val(formatRupiah(inputVal));
                togglePotongan();
            });

            $('#potongan_container').on('input', '.jumlah-potongan', function () {
                let inputVal = $(this).val().replace(/[^,\d]/g, '');
                $(this).val(formatRupiah(inputVal));
            });

            function toggleJumlahPembayaran() {
                const status = $('input[name="status_pembayaran"]:checked').val();
                if (status === '1') { // Sudah
                    $('#jumlah_pembayaran_row').show();
                    togglePotongan();
                } else {
                    $('#jumlah_pembayaran_row').hide();
                    $('#jumlah_pembayaran').val('');
                    $('#potongan_section').hide();
                }
            }

            function togglePotongan() {
                const netSales = parseFloat(removeRupiahFormat($('#net_sales').val())) || 0;
                const jumlahBayar = parseFloat(removeRupiahFormat($('#jumlah_pembayaran').val())) || 0;

                if (jumlahBayar > 0 && jumlahBayar < netSales) {
                    $('#potongan_section').show();
                } else {
                    $('#potongan_section').hide();
                }
            }

            function statusTracking() {
                const selectedValue = $('#status_tracking').val();

                // Reset semua
                $('#faktur_pajak_row, #dokumen_tambahan_row, #pembayaran_row').hide();

                if (selectedValue === "faktur_pajak") {
                    $('#faktur_pajak_row').show();
                } else if (selectedValue === "dokumen_tambahan") {
                    $('#dokumen_tambahan_row').show();
                } else if (selectedValue === "pembayaran") {
                    $('#pembayaran_row').show();
                }
            }

            function formatRupiah(value) {
                const isNegative = value.startsWith('-');
                value = value.replace(/[^,\d]/g, '').toString();
                const split = value.split(',');
                let rupiah = split[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                rupiah = (split[1] !== undefined) ? rupiah + ',' + split[1] : rupiah;
                return (isNegative ? '-' : '') + rupiah;
            }

            function removeRupiahFormat(angka) {
                return angka.replace(/[Rp.\s]/g, '').replace(/,/g, '.');
            }
        });
    </script>
@endsection