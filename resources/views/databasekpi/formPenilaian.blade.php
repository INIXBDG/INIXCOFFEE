    @extends('layouts.app')

    @section('content')
    <style>
        input[type="range"].form-range {
            width: 100%;
            height: 6px;
            background: #ddd;
            border-radius: 5px;
            appearance: none;
            -webkit-appearance: none;
            transition: background 0.3s ease;
        }

        input[type="range"].form-range::-webkit-slider-thumb {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #0d6efd;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s ease;
            border: 2px solid white;
            margin-top: -7px;
        }

        input[type="range"].form-range::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #0d6efd;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid white;
        }

        input[type="range"].form-range::-moz-range-track {
            background: #ddd;
            height: 6px;
            border-radius: 5px;
        }

        input[type="range"].form-range:hover {
            background: #ccc;
        }

        input[type="range"].form-range:focus {
            outline: none;
            background: #bbb;
        }
    </style>
    <div class="container">
        @if ($status === true)
        @if (Auth()->user()->jabatan === 'HRD')
        <a href="{{ route('ketegoriKPI.get') }}" class="btn click-primary">kembali</a>
        @else
        <a href="javascript:history.back()" class="btn btn-primary">Kembali</a>
        @endif

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" id="card">
                        @if (!empty($outputData))
                        <h5 class="card-title text-center mb-4">PENILAIAN KINERJA {{ strtoupper($outputData[0]['evaluated']) }}</h5>
                        @endif
                        <form method="POST" action="{{ route('penilaianEvaluator') }}" class="mt-3" style="margin-top: 20px;">
                            @csrf

                            @foreach ($outputData as $data)
                            @foreach ($data['detail_kategori'] as $kategori)
                            <div class="mt-3 mb-3">
                                <h5>{{ $kategori['kriteria_utama'] }}</h5>
                                @foreach ($kategori['isi_kriteria'] as $sub)
                                <div class="row mb-3 ms-3">
                                    <label for="{{ $sub['sub_kriteria_judul'] }}" class="col-md-4 col-form-label text-md-start">
                                        @if ($sub['level'] === 'required')
                                        <span style="color : red;">*</span>
                                        @endif
                                        {{ $sub['sub_kriteria_judul'] }}
                                    </label>
                                    <div class="col-md-6">
                                        <input type="hidden" name="kode_form" value="{{ $data['kode_form_global'] }}">

                                        @if ($sub['tipe_kategori'] === 'radio' | $sub['tipe_kategori'] === 'checkbox')
                                        @foreach ($sub['keterangan_tipe'] as $option)
                                        <div class="form-check-inline">
                                            <label class="form-check-label">
                                                <div class="form-check me-1 mb-4">
                                                    <input class="form-check-input checkbox-group"
                                                        type="{{ $sub['tipe_kategori'] }}"
                                                        @if ($sub['tipe_kategori'] === 'checkbox')
                                                        name="field[{{ $sub['sub_kriteria_judul'] }}][]"
                                                        @else
                                                        name="field[{{ $sub['sub_kriteria_judul'] }}]"
                                                        @endif
                                                        data-group="{{ $sub['sub_kriteria_judul'] }}"
                                                        value="{{ $option['nilai'] ? $option['nilai'] : $option['ket'] }}"
                                                        @if ($sub['level']==='required' ) data-required="true" @endif>
                                                    <label class="form-check-label" for="{{ $option['id'] }}">
                                                        {{ $option['ket'] }}
                                                    </label>
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach

                                        @elseif ($sub['tipe_kategori'] === 'select')
                                        <select class="form-select" name="field[{{ $sub['sub_kriteria_judul'] }}]" {{ $sub['level'] }}>
                                            <option selected disabled>pilih {{ $sub['sub_kriteria_judul'] }}</option>
                                            @foreach ($sub['keterangan_tipe'] as $option)
                                            <option value="{{ $option['nilai'] ?? $option['ket'] }}">{{ $option['ket'] }}</option>
                                            @endforeach
                                        </select>

                                        @elseif ($sub['tipe_kategori'] === 'textarea')
                                        <textarea name="field[{{ $sub['sub_kriteria_judul'] }}]" class="form-control" id="" {{ $sub['level'] }}></textarea>
                                        @else
                                        <input type="{{ $sub['tipe_kategori'] }}" id="{{ $sub['sub_kriteria_judul'] }}" placeholder="Masukan {{ $sub['sub_kriteria_judul'] }}"
                                            @if ($sub['tipe_kategori']==='range' )
                                            min="0" max="100" value="0" class="form-control-range" autocomplete="off"
                                            @else
                                            class=" form-control"
                                            @endif
                                            name="field[{{ $sub['sub_kriteria_judul'] }}]" autocomplete="off" {{ $sub['level'] }}>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endforeach
                            @endforeach


                            <div class="text-end me-5 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Kirim') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @elseif ($status === false)
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center" id="card">
                        <h6 class="card-title text-center">Terima Kasih Telah Melakukan</h6>
                        @if (!empty($outputData))
                        <h5 class="card-title text-center mb-4">"PENILAIAN KINERJA TERHADAP {{ strtoupper($outputData[0]['evaluated']) }}"</h5>
                        @endif
                        <a href="javascript:history.back()" class="btn btn-primary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <style>

    </style>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredGroups = {};

            document.querySelectorAll('input.checkbox-group[data-required="true"]').forEach(checkbox => {
                const group = checkbox.dataset.group;
                if (!requiredGroups[group]) {
                    requiredGroups[group] = [];
                }
                requiredGroups[group].push(checkbox);
            });

            Object.keys(requiredGroups).forEach(group => {
                const checkboxes = requiredGroups[group];
                const oneChecked = checkboxes.some(chk => chk.checked);

                if (!oneChecked) {
                    isValid = false;
                    checkboxes[0].closest('.row').insertAdjacentHTML('beforeend', `
                    <small class="text-danger error-${group}">Pilih minimal satu opsi untuk ${group.replace(/_/g, ' ')}</small>
                `);
                } else {
                    document.querySelectorAll(`.error-${group}`).forEach(el => el.remove());
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
    @endsection