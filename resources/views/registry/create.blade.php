@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body" id="card">

                    <a href="{{ route('registry.index') }}" class="btn btn-outline-secondary my-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>

                    <h5 class="card-title text-center mb-4">{{ __('Buat Tugas Baru') }}</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validasi Gagal!</h5>
                            <p>Ada beberapa masalah dengan data yang Anda masukkan:</p>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('registry.store') }}">
                        @csrf

                        <input type="hidden" name="pengerja_id" value="{{ Auth::id() }}">

                        <div class="row mb-3">
                            <label for="ticket_id" class="col-md-4 col-form-label text-md-start">Nomor Ticket</label>
                            <div class="col-md-6">
                                <select name="ticket_id" id="ticket_id" class="form-select @error('ticket_id') is-invalid @enderror">
                                    <option value="" selected data-detail="" data-divisi="" data-kategori="">-- Pilih Nomor Ticket (Opsional) --</option>
                                    @foreach($tickets as $ticket)
                                        <option value="{{ $ticket->ticket_id }}"
                                                data-detail="{{ str_replace(["\r", "\n"], ' ', $ticket->detail_kendala) }}"
                                                data-divisi="{{ $ticket->divisi }}"
                                                data-kategori="{{ $ticket->kategori }}"
                                                {{ old('ticket_id') == $ticket->ticket_id ? 'selected' : '' }}>
                                            {{ $ticket->ticket_id }} - {{ \Illuminate\Support\Str::limit($ticket->detail_kendala, 60) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ticket_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tugas" class="col-md-4 col-form-label text-md-start">Nama Tugas</label>
                            <div class="col-md-6">
                                <input type="text" name="tugas" id="tugas"
                                       class="form-control @error('tugas') is-invalid @enderror"
                                       value="{{ old('tugas') }}" placeholder="cth: Error RKM" required>
                                @error('tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fitur" class="col-md-4 col-form-label text-md-start">Fitur / Modul</label>
                            <div class="col-md-6">
                                <select class="form-control @error('fitur') is-invalid @enderror" id="fitur" name="fitur" required>
                                    <option value="">Pilih atau ketik fitur baru</option>
                                    @foreach($features as $featureName)
                                        <option value="{{ $featureName }}" {{ old('fitur') == $featureName ? 'selected' : '' }}>
                                            {{ $featureName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('fitur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <label for="tipe" class="col-md-4 col-form-label text-md-start">Tipe</label>
                            <div class="col-md-6">
                                <select name="tipe" id="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('tipe') ? '' : 'selected' }}>-- Pilih Tipe --</option>
                                    <option value="Request" {{ old('tipe') == 'Request' ? 'selected' : '' }}>Request</option>
                                    <option value="Error" {{ old('tipe') == 'Error' ? 'selected' : '' }}>Error</option>
                                </select>
                                @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pemilik" class="col-md-4 col-form-label text-md-start">Pemilik</label>
                            <div class="col-md-6">
                                <select class="form-select @error('pemilik') is-invalid @enderror" id="pemilik" name="pemilik" required>
                                    <option value="">Pilih atau ketik pemilik</option>
                                    @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan }}" {{ old('pemilik') == $jabatan ? 'selected' : '' }}>
                                            {{ $jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pemilik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="fakta" class="col-md-4 col-form-label text-md-start">Fakta</label>
                            <div class="col-md-6">
                                <textarea name="fakta" id="fakta" class="form-control @error('fakta') is-invalid @enderror" rows="3" placeholder="Masukkan fakta saat ini..." required>{{ old('fakta') }}</textarea>
                                @error('fakta')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="harapan" class="col-md-4 col-form-label text-md-start">Harapan</label>
                            <div class="col-md-6">
                                <textarea name="harapan" id="harapan" class="form-control @error('harapan') is-invalid @enderror" rows="3" placeholder="Masukkan harapan sistem/fitur..." required>{{ old('harapan') }}</textarea>
                                @error('harapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if(auth()->check() && optional(auth()->user()->karyawan)->jabatan == 'Koordinator ITSM')
                            <div class="row mb-3">
                                <label for="waktu_perkiraan" class="col-md-4 col-form-label text-md-start">Waktu Perkiraan (Menit)</label>
                                <div class="col-md-6">
                                    <input type="number" name="waktu_perkiraan" id="waktu_perkiraan" class="form-control @error('waktu_perkiraan') is-invalid @enderror" value="{{ old('waktu_perkiraan') }}" placeholder="cth: 120" min="1">
                                    @error('waktu_perkiraan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="catatan" class="col-md-4 col-form-label text-md-start">Catatan (Opsional)</label>
                                <div class="col-md-6">
                                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3" placeholder="Tambahkan catatan jika perlu...">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @else
                            <div class="row mb-3">
                                <div class="col-md-6 offset-md-4">
                                    <div class="alert alert-info py-2 mb-0" style="font-size: 0.85rem;">
                                        <i class="fas fa-info-circle me-1"></i> <strong>Waktu Perkiraan</strong> dan <strong>Catatan</strong> akan ditambahkan oleh Koordinator ITSM setelah tugas ini diajukan.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan Tugas
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#fitur').select2({
            theme: 'bootstrap-5',
            tags: true,
            placeholder: "Pilih atau ketik fitur baru",
            allowClear: true,
            width: '100%'
        });

        $('#pemilik').select2({
            theme: 'bootstrap-5',
            tags: true,
            placeholder: "Pilih atau ketik pemilik",
            allowClear: true,
            width: '100%'
        });

        // Logika Otomatisasi Ticket
        $('#ticket_id').on('change', function() {
            var selectedOption = $(this).find(':selected');

            var detailKendala = selectedOption.data('detail');
            var divisi = selectedOption.data('divisi');
            var kategori = selectedOption.data('kategori');

            if (detailKendala) {
                $('#tugas').val(detailKendala);
            } else {
                $('#tugas').val('');
            }

            if (divisi) {
                $('#pemilik').val(divisi);
            } else {
                $('#pemilik').val('');
            }

            if (kategori) {
                $('#tipe').val(kategori);
            } else {
                $('#tipe').val('');
            }
        });

        if ($('#ticket_id').val() !== '') {
            $('#ticket_id').trigger('change');
        }
    });
</script>
@endpush
@endsection
