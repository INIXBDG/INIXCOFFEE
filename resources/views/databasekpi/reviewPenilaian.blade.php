@extends('layouts.app')

@section('content')
<style>
    input[type="range"].form-range {
        width: 100%;
        height: 6px;
        background: #ddd;
        border-radius: 5px;
    }

    .auto-height {
        resize: none;
        overflow: hidden;
        min-height: 38px;
    }

    input[type="range"].form-range::-webkit-slider-thumb {
        width: 20px;
        height: 20px;
        background: #0d6efd;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid white;
        margin-top: -7px;
    }
</style>

<div class="container mb-5">
    <a href="javascript:history.back()" class="btn btn-primary mb-3">Kembali</a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if ($evaluated)
                    <h6 class="card-title text-center">Review Penilaian Kinerja</h6>
                    <h5 class="card-title text-center mb-4">"{{ $evaluated->nama_lengkap }}"</h5>
                    @endif

                    <form method="POST" action="{{ route('penilaianReview') }}">
                        @csrf

                        @if ($evaluator)
                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label text-md-start">Nama Evaluator</label>
                            <div class="col-md-6">
                                : {{ $evaluator->nama_lengkap }}
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label text-md-start">Divisi Evaluator</label>
                            <div class="col-md-6">
                                : {{ $evaluator->divisi }}
                            </div>
                        </div>
                        @endif
                        @if ($statusPenilaian === true)
                         <div class="text-center mt-3 mb-3" style="border-top: 2px solid black;">
                            <p class="text-danger mt-3">Anda Belum Bisa Mereview Sekarang Karena Belum Dinilai</p>
                         </div>
                        @else
                            @foreach ($penilaian as $penilaianItem)
                            <h5 class="mt-4">{{ $penilaianItem['kriteria'] }}</h5>

                                @foreach ($penilaianItem['items'] as $sub)
                                    <div class="mb-3 row align-items-center ms-5">
                                        <label for="input-{{ $loop->parent->index }}-{{ $loop->index }}" class="col-md-4 col-form-label">
                                            {{ $sub['judul'] }}
                                        </label>
                                        <div class="col-md-6">
                                            @if ($sub['nilai'] !== '-' && !empty($sub['nilai']))
                                                <input type="text" class="form-control" value="{{ $sub['nilai'] }}" disabled>
                                            @else
                                                <input type="hidden" name="id_nilai[]" value="{{ $sub['id_nilaiKPI'] }}">
                                                <textarea class="form-control mb-2 auto-height" disabled>{{ $sub['pesan'] }}</textarea>
                                                <input type="number" name="nilai[]" class="form-control" placeholder="Masukkan nilai...">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        @endif

                        @php
                            $tampilkanTombol = false;
                        @endphp

                        @foreach ($penilaian as $penilaianItem)
                            @foreach ($penilaianItem['items'] as $sub)
                                @if (!in_array($sub['tipe'], ['radio', 'checkbox', 'select']) && ($sub['nilai'] === '-' || $sub['nilai'] === null || $sub['nilai'] === ''))
                                    @php
                                        $tampilkanTombol = true;
                                        break 2;
                                    @endphp
                                @endif
                            @endforeach
                        @endforeach

                        @if ($statusPenilaian === true)
                        @else
                            @if ($tampilkanTombol)
                            <div class="text-end me-5 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Selesaikan Review') }}
                                </button>
                            </div>
                            @else
                            <div class="text-end me-5 mt-4">
                                <button type="button" class="btn btn-secondary">
                                    {{ __('Telah Direview') }}
                                </button>
                            </div>
                            @endif
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.auto-height').forEach(function (el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        });
    });
</script>

@endsection