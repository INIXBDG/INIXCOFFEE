@extends('layouts.app')

@section('content')
<div class="container-fluid" style="background: ">
    <div class="row justify-content-center">
        <div class="row">
            <div class="col-md-3">
                <nav>
                    <div class="nav nav-tabs flex-column" id="nav-tab" role="tablist">
                        @foreach($monthRanges as $index => $monthRange)
                            <button onclick="handleTabClick('{{ $monthRange['month'] }}')" class="nav-link{{ $index === 0 ? ' active' : '' }}" id="nav-tab-{{ $index }}" data-bs-toggle="tab" data-bs-target="#nav-tabContent-{{ $index }}" type="button" role="tab" aria-controls="nav-tabContent-{{ $index }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                {{ $monthRange['month'] }}
                            </button>
                        @endforeach
                    </div>
                </nav>
            </div>
            <div class="col-md-9" id="content">
                <div class="tab-content" id="myTabContent">
                    @foreach ($monthRanges as $index => $monthRange)
                        <div class="tab-pane fade{{ $index === 0 ? ' show active' : '' }}" id="nav-tabContent-{{ $index }}" role="tabpanel" aria-labelledby="nav-tab-{{ $index }}">
                            @foreach ($monthRange['weeks'] as $weekRange)
                                <div class="card m-4">
                                    <div class="card-body table-responsive">
                                        <input type="hidden" id="monthRangesData" value="{{ json_encode($monthRanges) }}">
                                        <h3 class="card-title my-1">{{ __('Rencana Kelas Mingguan') }}</h3>
                                        <p class="card-title my-1">Periode <strong>{{ \Carbon\Carbon::parse($weekRange['start'])->translatedFormat('l, d F Y') }}  - {{ \Carbon\Carbon::parse($weekRange['end'])->translatedFormat('l, d F Y') }}</strong> </p>
                                        @foreach ($rkmsByWeek as $rkms)
                                            @if ($rkms['weekRange'] ==  $weekRange )
                                                {{ $rkms['rkms'] }}
                                                {{-- @foreach ($rkms['rkms'] as $rkmsData) --}}

                                            {{-- @endforeach --}}
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
<style>
    #content{
        overflow-y:hidden;
    }
    #myTabContent{
        max-height: 100vh;
        overflow-y:scroll;
    }
    .nav-tabs {
        flex-direction: column;
        max-height: 100vh; /* Atur ketinggian maksimum untuk scroll */
        overflow-y: auto; /* Aktifkan overflow vertical */
    }

    .nav-link {
        text-align: left; /* Atur teks tab ke kiri */
    }

    .tab-content {
        overflow: auto; /* Aktifkan overflow vertical */
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }
</style>

@push('js')

</script>
@endpush
@endsection
