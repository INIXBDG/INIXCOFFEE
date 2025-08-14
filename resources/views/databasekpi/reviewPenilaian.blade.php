@extends('databasekpi.berandaKPI')

@section('contentKPI')
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
    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }
        .row.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            margin-left: 0 !important;
        }
        .row.align-items-center label {
            margin-bottom: 6px;
            text-align: left !important;
            padding-left: 0;
        }
        .col-md-4, .col-md-6 {
            width: 100%;
            max-width: 100%;
        }
        .text-end {
            text-align: center !important;
            margin-right: 0 !important;
        }
        .btn {
            width: 100%;
            padding: 12px;
        }
        .card {
            margin-bottom: 20px;
        }
    }
</style>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        confirmButtonColor: '#3085d6'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        confirmButtonColor: '#d33'
    });
</script>
@endif

@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        confirmButtonColor: '#d33'
    });
</script>
@endif

<div class="container mb-5">
    <a href="javascript:history.back()" class="btn text-white cl-blue mb-3">Kembali</a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if ($evaluated)
                    <h6 class="card-title text-center">Review Penilaian {{ $jenis_penilaian }} </h6>
                    <h5 class="card-title text-center mb-4">Kinerja "{{ $evaluated->nama_lengkap }}"</h5>
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

                        @php $tampilkanTombol = false; @endphp

                        @if ($statusPenilaian === true)
                        <div class="text-center mt-3 mb-3" style="border-top: 2px solid black;">
                            <p class="w-red mt-3">Anda Belum Bisa Mereview Sekarang Karena Belum Dinilai</p>
                        </div>
                        @else
                        @foreach ($penilaian as $penilaianItem)
                        <h5 class="mt-4 ml-3">{{ $penilaianItem['kriteria'] }}</h5>

                        @foreach ($penilaianItem['items'] as $sub)
                        <div class="mb-3 row align-items-center ml-1">
                            <label class="col-md-4 col-form-label ml-1">
                                {{ $sub['judul'] }} :
                            </label>
                            <div class="col-md-6">
                                @if ($sub['nilai'] !== '-' && !empty($sub['nilai']))
                                <input type="text" class="form-control" value="{{ $sub['nilai'] }}" disabled>
                                @else
                                <input type="hidden" name="id_nilai[]" value="{{ $sub['id_nilaiKPI'] }}">
                                @if ($sub['tipe'] === 'text')
                                @php $tampilkanTombol = true; @endphp
                                <input type="number" name="nilai[]" class="form-control" placeholder="Masukkan nilai...">
                                @else
                                <textarea class="form-control mb-2 auto-height" disabled>{{ $sub['pesan'] }}</textarea>
                                @endif
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @endforeach
                        @endif

                        @if ($statusPenilaian === false && $tampilkanTombol)
                        <div class="text-end me-5 mt-4">
                            <button type="submit" class="btn text-white cl-blue">
                                {{ __('Selesaikan Review') }}
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.auto-height').forEach(function(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        });
    });
</script>
@endsection
