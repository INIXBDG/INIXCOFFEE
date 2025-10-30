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

                    <h5 class="card-title text-center mb-4">{{ __('Souvenir Khusus Inhouse / Online') }}</h5>

                    <form method="POST" action="{{ route('storeSouvenirInhouse') }}">
                        @csrf
                        <input type="hidden" name="id_rkm" value="{{ $id }}">

                        {{-- Dropdown kategori --}}
                        <div class="row mb-3">
                            <label for="nama_souvenir" class="col-md-4 col-form-label text-md-start">Kategori Souvenir</label>
                            <div class="col-md-6">
                                <select name="kategori_souvenir" id="nama_souvenir" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="All Item">All Item</option>
                                    <option value="Jaket">Jaket</option>
                                    <option value="Diffuser">Diffuser</option>
                                    <option value="Pouch">Pouch</option>
                                    <option value="Tas">Tas</option>
                                    <option value="Kaos">Kaos</option>
                                    <option value="Tumblr">Tumblr</option>
                                    <option value="Botol">Botol</option>
                                    <option value="Polo">Polo</option>
                                </select>
                            </div>
                        </div>

                        {{-- Dropdown hasil pencarian (multiple) --}}
                        <div class="row mb-3">
                            <label for="detail_souvenir" class="col-md-4 col-form-label text-md-start">Nama Souvenir</label>
                            <div class="col-md-6">
                                <select name="nama_souvenir[]" id="detail_souvenir" class="form-select" multiple size="6">
                                    <option value="">-- Pilih Nama Souvenir --</option>
                                </select>
                                <small class="text-muted">Tekan <kbd>Ctrl</kbd> (Windows) atau <kbd>Cmd</kbd> (Mac) untuk memilih lebih dari satu.</small>
                            </div>
                        </div>

                        {{-- Tombol submit --}}
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn click-primary">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kategoriSelect = document.getElementById('nama_souvenir');
    const detailSelect = document.getElementById('detail_souvenir');

    kategoriSelect.addEventListener('change', function() {
        const keyword = this.value;
        detailSelect.innerHTML = '<option value="">Memuat data...</option>';

        if (keyword !== '') {
            fetch(`{{ url('/souvenir/filter') }}/${keyword}`)
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error ' + response.status);
                    return response.json();
                })
                .then(data => {
                    detailSelect.innerHTML = ''; // kosongkan dropdown

                    if (data.length > 0) {
                        data.forEach(item => {
                            let opt = document.createElement('option');
                            opt.value = item.id;
                            opt.text = item.nama_souvenir;
                            detailSelect.appendChild(opt);
                        });
                    } else {
                        let opt = document.createElement('option');
                        opt.text = 'Tidak ada hasil ditemukan';
                        opt.value = '';
                        detailSelect.appendChild(opt);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    detailSelect.innerHTML = '<option value="">Terjadi kesalahan memuat data</option>';
                });
        } else {
            detailSelect.innerHTML = '<option value="">-- Pilih Nama Souvenir --</option>';
        }
    });
});
</script>
@endsection
