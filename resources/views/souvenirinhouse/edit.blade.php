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
                        <h5 class="card-title text-center mb-4">{{ __('Edit Souvenir Khusus Inhouse / Online') }}</h5>

                        <form method="POST" action="{{ route('updateSouvenirInhouse', $souvenirInhouse->id) }}">
                            @csrf
                            @method('PUT')

                            {{-- Pilih kategori souvenir --}}
                            <div class="row mb-3">
                                <label for="filter_souvenir" class="col-md-4 col-form-label text-md-start">
                                    {{ __('Kategori Souvenir') }}
                                </label>
                                <div class="col-md-6">
                                    <input type="hidden" name="id_rkm" value="{{ $id }}">
                                    <select id="filter_souvenir" class="form-select">
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

                            {{-- Hasil filter souvenir --}}
                            <div class="row mb-3">
                                <label for="id_souvenir" class="col-md-4 col-form-label text-md-start">
                                    {{ __('Daftar Souvenir') }}
                                </label>
                                <div class="col-md-6">
                                    <select name="id_souvenir[]" id="id_souvenir" class="form-select" multiple
                                        style="height: 150px;">
                                        @foreach ($souvenirs as $item)
                                            <option value="{{ $item->id }}"
                                                {{ in_array($item->id, (array) $souvenirInhouse->pluck('id_souvenir')->toArray() ?? [$souvenirInhouse->id_souvenir]) ? 'selected' : '' }}>
                                                {{ $item->nama_souvenir }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small class="text-muted">Tekan CTRL / CMD untuk memilih lebih dari satu
                                        souvenir.</small>

                                    @error('id_souvenir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn click-primary">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('filter_souvenir');
            const souvenirSelect = document.getElementById('id_souvenir');

            filterSelect.addEventListener('change', function() {
                const keyword = this.value;
                souvenirSelect.innerHTML = ''; // kosongkan isi daftar souvenir

                if (keyword !== '') {
                    fetch(`{{ url('/souvenir/filter') }}/${keyword}`)
                        .then(response => {
                            if (!response.ok) throw new Error('HTTP Error ' + response.status);
                            return response.json();
                        })
                        .then(data => {
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item.id;
                                    option.textContent = item.nama_souvenir;
                                    souvenirSelect.appendChild(option);
                                });
                            } else {
                                const opt = document.createElement('option');
                                opt.textContent = 'Tidak ada data ditemukan';
                                souvenirSelect.appendChild(opt);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            const opt = document.createElement('option');
                            opt.textContent = 'Terjadi kesalahan saat memuat data';
                            souvenirSelect.appendChild(opt);
                        });
                } else {
                    const opt = document.createElement('option');
                    opt.textContent = '-- Pilih kategori terlebih dahulu --';
                    souvenirSelect.appendChild(opt);
                }
            });
        });
    </script>
@endsection
