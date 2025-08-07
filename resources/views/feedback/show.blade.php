@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- <div class="row justify-content-center"> --}}
        {{-- <div class="col-md-12"> --}}
            <div class="card">
                <div class="card-body">
                    <a href="/feedback" class="btn click-primary my-2"><img src="{{ asset('icon/arrow-left.svg') }}" class="img-responsive" width="20px"> Back</a>
                    @if (auth()->user()->jabatan == 'Sales')
                        <a href="{{ route('feedback.exportExcels', $id) }}" class="btn btn-success">Export to Excel</a>
                        <a href="{{ route('feedback.exportPDFs', $id) }}" class="btn btn-danger">Export to PDF</a>
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                            <section class="rating-summary">
                                <div class="card-score average-score">
                                    <p class="stars text-center">
                                        @php
                                            $maxStars = 4;
                                            $rating = $post['all_avg_feedback'];
                                            $fullStars = floor($rating);
                                            $partial = $rating - $fullStars;
                                            $emptyStars = $maxStars - ceil($rating); // total bintang kosong

                                            // Tentukan level fill untuk partial star: 0, 0.25, 0.5, 0.75, 1
                                            if ($partial > 0 && $partial < 0.125) {
                                                $partialFill = 0;
                                            } elseif ($partial >= 0.125 && $partial < 0.375) {
                                                $partialFill = 0.25;
                                            } elseif ($partial >= 0.375 && $partial < 0.625) {
                                                $partialFill = 0.5;
                                            } elseif ($partial >= 0.625 && $partial < 0.875) {
                                                $partialFill = 0.75;
                                            } elseif ($partial >= 0.875) {
                                                $partialFill = 1;
                                                $fullStars++; // hitung sebagai bintang penuh jika hampir penuh
                                                $emptyStars = $maxStars - $fullStars;
                                            } else {
                                                $partialFill = 0;
                                            }
                                        @endphp

                                        @for ($i = 1; $i <= $fullStars; $i++)
                                            <span class="star full"></span>
                                        @endfor

                                        @if ($partialFill > 0 && $partialFill < 1)
                                            <span class="star partial" data-fill="{{ $partialFill }}"></span>
                                        @elseif ($partialFill == 1)
                                            <span class="star full"></span>
                                        @endif

                                        @for ($i = 1; $i <= $emptyStars; $i++)
                                            <span class="star empty"></span>
                                        @endfor
                                        </p>

                                    <h2 class="text-center">{{ $post['all_avg_feedback'] }}</h2>
                                    <p class="label text-center">Excellent</p>
                                </div>

                                <div class="container px-3">
                                    <div class="row gx-3">
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-purple text-white position-relative shadow-sm hover-effect-1">
                                                <div class="card-body">
                                                <h3 class="card-title">{{$post['materi']}}</h3>
                                                <p class="card-text">Materi</p>
                                                <div class="icon position-absolute top-50 end-0 translate-middle-y opacity-20 transition-icon">
                                                    <i class="fas fa-book-open fa-3x"></i>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-darkblue text-white position-relative shadow-sm hover-effect-2">
                                                <div class="card-body">
                                                <h3 class="card-title">{{$post['fasilitas']}}</h3>
                                                <p class="card-text">Fasilitas</p>
                                                <div class="icon position-absolute top-50 end-0 translate-middle-y opacity-20 transition-icon">
                                                    <i class="fas fa-vials fa-3x"></i>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        if($post['data']['0']['instruktur_key2'] == '-' && $post['data']['0']['asisten_key'] == '-'){
                                            $instrukturfix = $post['data']['0']['instruktur_key'];
                                        }elseif ($post['data']['0']['asisten_key'] == '-') {
                                            $instrukturfix = $post['data']['0']['instruktur_key'] . ',' . $post['data']['0']['instruktur_key2'];
                                        }else{
                                            $instrukturfix = $post['data']['0']['instruktur_key'] . ',' . $post['data']['0']['instruktur_key2'] . ',' . $post['data']['0']['asisten_key']; 
                                        }
                                    @endphp
                                        <div class="row gx-3">
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-purple text-white position-relative shadow-sm hover-effect-1">
                                                <div class="card-body">
                                                <h3 class="card-title">{{$post['instruktur']}}</h3>
                                                <p class="card-text">Instruktur <br> {{$instrukturfix}}</p>
                                                <div class="icon position-absolute top-50 end-0 translate-middle-y opacity-20 transition-icon">
                                                    <i class="fas fa-user-astronaut fa-3x"></i>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-darkblue text-white position-relative shadow-sm hover-effect-2">
                                                <div class="card-body">
                                                <h3 class="card-title">{{$post['sales']}}</h3>
                                                <p class="card-text">Sales <br> {{$post['data']['0']['sales_key']}}</p>
                                                <div class="icon position-absolute top-50 end-0 translate-middle-y opacity-20 transition-icon">
                                                    <i class="fas fa-user-tag fa-3x"></i>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="pie-chart" aria-label="Rating Pie Chart"></div> --}}
                            </section>
                        </div>
                        <div class="col-md-8">
                            {{-- <section class="top-review"> --}}
                            <h3>🏆 {{$post['data'][0]['nama_materi']}}</h3>
                            <div class="accordion accordion-flush" id="accordionExample">
                                @foreach ($post['data'] as $index => $item)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-heading-{{ $index }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-{{ $index }}" aria-expanded="false" aria-controls="flush-collapse-{{ $index }}">
                                                Peserta {{ $item['nama_perusahaan'] }}
                                            </button>
                                        </h2>
                                        <div id="flush-collapse-{{ $index }}" class="accordion-collapse collapse" aria-labelledby="flush-heading-{{ $index }}" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                {{-- <article class="card p-2 highlighted"> --}}
                                                    <header class="d-flex align-items-center mb-3">
                                                        <img src="{{ asset('css/default-profile.jpg') }}" style="max-width: 100px" alt="Avatar {{ $item['nama_perusahaan'] }}" />
                                                        <div class="ms-3">
                                                            <h5>Peserta {{ $item['nama_perusahaan'] }}</h5>
                                                            <p>Rating: {{$item['total_feedback']}}</p>
                                                            @php
                                                                $labelMap = [];
                                                                $prefixCounts = [];

                                                                foreach ($pertanyaan as $data) {
                                                                    $prefix = substr($data->key, 0, 1); // ambil 1 huruf pertama sebagai prefix (misal 'M', 'P')
                                                                    
                                                                    // Reset atau tambah index per prefix
                                                                    if (!isset($prefixCounts[$prefix])) {
                                                                        $prefixCounts[$prefix] = 1;
                                                                    } else {
                                                                        $prefixCounts[$prefix]++;
                                                                    }

                                                                    // Buat key label dengan prefix + nomer urut per prefix
                                                                    $key = $prefix . $prefixCounts[$prefix];
                                                                    
                                                                    // Assign ke label map
                                                                    $labelMap[$key] = $data->pertanyaan;
                                                                }
                                                                $item['datafeedbacks'] = $item['datafeedbacks']->toArray();
                                                            @endphp

                                                            {{-- <pre>labelMap: {{ print_r($labelMap, true) }}</pre>
                                                            <pre>{{ print_r($item['datafeedbacks'], true) }}</pre> --}}

                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach ($item['datafeedbacks'] as $key => $value)
                                                                {{-- {{$key}} --}}
                                                                    @if (($value == "1" || $value === 1) && isset($labelMap[$key]))
                                                                        <span class="badge bg-dark">{{$labelMap[$key]}}</span>
                                                                    @endif
                                                                @endforeach
                                                            </div>


                                                        </div>
                                                    </header>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-space-between">
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Pengalaman Peserta</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['U1'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Saran dan Perbaikan dari Peserta</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['U2'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Materi</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['TextM'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Pelayanan</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['TextP'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Fasilitas</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['TextF'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Instruktur</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['TextI'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-lg-4 mb-4">
                                                            <div class="card-notes-big-shadow">
                                                                <div class="card-notes card-just-text" data-background="color" data-color="blue" data-radius="none">
                                                                    <div class="content">
                                                                        <h6 class="category">Feedback</h6>
                                                                        <h4 class="title"><a href="#">Sales</a></h4>
                                                                        <p class="description">{{ $item['datafeedbacks']['TextS'] ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div> <!-- end card -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                {{-- </article> --}}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            {{-- </section> --}}
                        </div>
                    </div>
                </div>
            </div>
        {{-- </div> --}}
    {{-- </div> --}}
</div>
<style>
    :root {
    --green: #4CAF50;
    --yellow: #FFC107;
    --red: #F44336;
    }
    .rating-summary .average-score .label {
    font-weight: bold;
    color: var(--green);
    margin-bottom: 0.5em;
    }
    .sub-ratings {
    display: flex;
    flex-direction: column;
    gap: 0.5em;
    }
    .category {
    display: flex;
    justify-content: space-between;
    align-items: center;
    }
    .category .progress {
    width: 60%;
    height: 10px;
    background: linear-gradient(to right, var(--green) var(--score)%, #eee var(--score)%);
    border-radius: 5px;
    }
    .pie-chart {
    width: 120px;
    height: 120px;
    background-image: conic-gradient(
        var(--green) 0% 70%,
        var(--yellow) 70% 90%,
        var(--red) 90% 100%
    );
    border-radius: 50%;
    margin-top: 1em;
    }
    .card.highlighted {
    border: 2px solid gold;
    background: #fffbea;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
    }

    .click-secondary-icon {
        background: #355C7C;
        border-radius: 1000px;
        width: 45px;
        height: 45px;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
    }

    .click-secondary-icon i {
        line-height: 45px;
    }

    .click-secondary {
        background: #355C7C;
        border-radius: 1000px;
        padding: 7px 20px;
        color: #ffffff;
        text-align: center;
        text-decoration: none;
        margin-left: 10px;
        margin-right: 10px;
    }

    .click-secondary-icon:hover,
    .click-secondary:hover {
        background: #4e6680;
    }

    .click-primary-icon {
        background: #2B3A55;
        border-radius: 1000px;
        width: 45px;
        height: 45px;
        color: #ffffff;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        text-decoration: none;
    }

    .click-primary-icon i {
        line-height: 45px;
    }

    .click-primary {
        background: #2B3A55;
        border-radius: 5px;
        padding: 7px 20px;
        color: #ffffff;
        text-align: center;
        text-decoration: none;
        margin-left: 10px;
        margin-right: 10px;
    }

    .click-primary-icon:hover,
    .click-primary:hover {
        background: #43587d;
    }
    .bg-purple {
  background-color: #673ab7;
}
.bg-darkblue {
  background-color: rgb(41, 49, 79);
}
.hover-effect-1:hover {
  transform: translateY(3px);
  box-shadow: 0 -8px 0px 0px #2196f3 !important;
  transition: 0.3s ease;
}
.hover-effect-2:hover {
  transform: translateY(3px);
  box-shadow: 0 -8px 0px 0px rgb(244, 67, 54) !important;
  transition: 0.3s ease;
}
.transition-icon {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.card:hover .transition-icon {
  transform: scale(1.1);
  opacity: 1 !important;
}
.star {
  display: inline-block;
  width: 2em;
  height: 2em;
  margin: 0 0.2em;
  background: #ccc; /* default abu abu (empty star) */
  clip-path: polygon(
    50% 0%,
    61% 35%,
    98% 35%,
    68% 57%,
    79% 91%,
    50% 70%,
    21% 91%,
    32% 57%,
    2% 35%,
    39% 35%
  );
  position: relative;
}

/* Full star (100%) */
.star.full {
  background-color: #FC0; /* kuning */
}

/* Empty star */
.star.empty {
  background-color: #ccc;
}

/* Partial star dengan gradient */
.star.partial {
  background-color: #ccc; /* default abu */
  position: relative;
}

.star.partial[data-fill="0.25"] {
  background: linear-gradient(to right, #FC0 25%, #ccc 25%);
}

.star.partial[data-fill="0.5"] {
  background: linear-gradient(to right, #FC0 50%, #ccc 50%);
}

.star.partial[data-fill="0.75"] {
  background: linear-gradient(to right, #FC0 75%, #ccc 75%);
}
/* From Uiverse.io by Codewithvinay */ 
.card-score {
 display: block;
 justify-content: center;
 align-content: center;
 width: 190px;
 height: 254px;
 border-radius: 50px;
 background: #e0e0e0;
 box-shadow: 20px 20px 60px #bebebe,
               -20px -20px 60px #ffffff;
    margin: 10px;

}
.rating-summary{
    display: block;
    justify-items: center;
    align-items: center;
}

.card-notes-big-shadow {
    width: 300px;
    margin: 10px;
    position: relative;
}

.coloured-cards .card-notes {
    margin-top: 30px;
}

.card-notes[data-radius="none"] {
    border-radius: 0px;
}
.card-notes {
    border-radius: 8px;
    box-shadow: 0 2px 2px rgba(204, 197, 185, 0.5);
    background-color: #FFFFFF;
    color: #252422;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}


.card-notes[data-background="image"] .title, .card-notes[data-background="image"] .stats, .card-notes[data-background="image"] .category, .card-notes[data-background="image"] .description, .card-notes[data-background="image"] .content, .card-notes[data-background="image"] .card-notes-footer, .card-notes[data-background="image"] small, .card-notes[data-background="image"] .content a, .card-notes[data-background="color"] .title, .card-notes[data-background="color"] .stats, .card-notes[data-background="color"] .category, .card-notes[data-background="color"] .description, .card-notes[data-background="color"] .content, .card-notes[data-background="color"] .card-notes-footer, .card-notes[data-background="color"] small, .card-notes[data-background="color"] .content a {
    color: #FFFFFF;
}
.card-notes.card-notes-just-text .content {
    padding: 50px 65px;
    text-align: center;
}
.card-notes .content {
    padding: 20px 20px 10px 20px;
}
.card-notes[data-color="blue"] .category {
    color: #7a9e9f;
}

.card-notes .category, .card-notes .label {
    font-size: 14px;
    margin-bottom: 0px;
}
.card-notes-big-shadow:before {
    background-image: url("http://static.tumblr.com/i21wc39/coTmrkw40/shadow.png");
    background-position: center bottom;
    background-repeat: no-repeat;
    background-size: 100% 100%;
    bottom: -12%;
    content: "";
    display: block;
    left: -12%;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 0;
}
.card-notes .description {
    font-size: 16px;
    color: #66615b;
}
.content-card{
    margin-top:30px;    
}
a:hover, a:focus {
    text-decoration: none;
}

/*======== COLORS ===========*/
.card-notes[data-color="blue"] {
    background: #b8d8d8;
}
.card-notes[data-color="blue"] .description {
    color: #506568;
}

.card-notes[data-color="green"] {
    background: #d5e5a3;
}
.card-notes[data-color="green"] .description {
    color: #60773d;
}
.card-notes[data-color="green"] .category {
    color: #92ac56;
}

.card-notes[data-color="yellow"] {
    background: #ffe28c;
}
.card-notes[data-color="yellow"] .description {
    color: #b25825;
}
.card-notes[data-color="yellow"] .category {
    color: #d88715;
}

.card-notes[data-color="brown"] {
    background: #d6c1ab;
}
.card-notes[data-color="brown"] .description {
    color: #75442e;
}
.card-notes[data-color="brown"] .category {
    color: #a47e65;
}

.card-notes[data-color="purple"] {
    background: #baa9ba;
}
.card-notes[data-color="purple"] .description {
    color: #3a283d;
}
.card-notes[data-color="purple"] .category {
    color: #5a283d;
}

.card-notes[data-color="orange"] {
    background: #ff8f5e;
}
.card-notes[data-color="orange"] .description {
    color: #772510;
}
.card-notes[data-color="orange"] .category {
    color: #e95e37;
}

/* Styling umum accordion */
.accordion {
    border: none;
    border-radius: 0.375rem; /* rounded */
    background-color: #f8f9fa;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box; /* agar padding termasuk dalam lebar */
}

.accordion-button {
    background-color: #e3f2fd; /* warna biru muda */
    color: #0d6efd; /* warna teks biru */
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 1.25rem;
    border: none;
    box-shadow: none;
}

.accordion-button:not(.collapsed) {
    background-color: #bbdefb;
    color: #064e9f;
    box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125);
}

.accordion-button::after {
    /* Ubah ikon panah default jika suka */
    font-size: 1rem;
    color: #0d6efd;
}

.accordion-body {
    background-color: #f0f7ff;
    /* padding: 1rem 1.25rem; */
    font-size: 1rem;
    color: #333;
    padding: 1rem;
    margin: 0; /* hindari margin yang membuat melebar */
    overflow-wrap: break-word; /* agar teks tidak meluber */
    flex-shrink: 1;
    min-width: 0;
}

/* Styling gambar/ avatar di dalam accordion */
.accordion-body img {
    border-radius: 50%;
    max-width: 100px;
    height: auto;
    margin-right: 1rem;
    float: left;
}

/* Konten teks di sebelah gambar */
.accordion-body .content {
    overflow: hidden;
}

/* Responsive Styling */

/* Mobile devices (small screens) */
@media (max-width: 576px) {
    .accordion {
        width: auto !important;
    }

    .accordion-button {
        font-size: 0.9rem;
        padding: 0.75rem 1rem;
    }

    .accordion-body {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        margin: 0; /* hindari margin yang membuat melebar */
        overflow-wrap: break-word; /* agar teks tidak meluber */
        flex-shrink: 1;
        min-width: 0;
    }

    .accordion-body img {
        max-width: 80px; /* sesuaikan */
        height: auto;
        display: block;
        max-width: 100%;
    }

    .card-body {
        overflow-x: scroll;
    }

    .card-notes-big-shadow {
        width: auto;    /* Full width pada mobile */
        margin: 8px 0;
    }

    .card-notes {
        font-size: 0.9rem;
        margin-bottom: 12px;
        padding: 12px 12px 10px 12px;
    }

    .accordion-button {
        font-size: 1rem;
        padding: 8px 12px;
    }
}

/* Tablet devices */
@media (min-width: 577px) and (max-width: 992px) {
    .accordion {
        width: auto !important;
    }

    .accordion-button {
        font-size: 1rem;
        padding: 0.85rem 1.15rem;
    }

    .accordion-body {
        padding: 1rem 1.25rem;
        font-size: 1rem;
        margin: 0; /* hindari margin yang membuat melebar */
        overflow-wrap: break-word; /* agar teks tidak meluber */
        flex-shrink: 1;
        min-width: 0;
    }

    .accordion-body img {
        max-width: 80px; /* sesuaikan */
        height: auto;
        display: block;
        max-width: 100%;
    }

    .card-body {
        overflow-x: scroll;
    }

    .card-notes-big-shadow {
        width: auto;    /* Full width pada tablet */
        margin: 10px 0;
    }

    .card-notes {
        font-size: 0.95rem;
        margin-bottom: 15px;
        padding: 15px 15px 12px 15px;
    }
}

/* Penyesuaian untuk monitor kecil / iPad (yang umumnya 768px atau 1024px) */
@media (min-width: 993px) and (max-width: 1240px) {
    .card-notes-big-shadow {
        width: 95%;
        margin: 10px auto;
    }

    .card-notes {
        font-size: 1rem;
        padding: 15px 20px;
        margin-bottom: 20px;
    }

    .accordion-button {
        font-size: 1.1rem;
        padding: 10px 15px;
    }

    .accordion-body {
        padding: 15px 20px;
        font-size: 1rem;
        margin: 0;
    }
}
/* Optional: buat supaya konten tidak terlalu rapat */
.accordion-item + .accordion-item {
    margin-top: 0.5rem;
}


</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/js/all.min.js" integrity="sha512-gBYquPLlR76UWqCwD06/xwal4so02RjIR0oyG1TIhSGwmBTRrIkQbaPehPF8iwuY9jFikDHMGEelt0DtY7jtvQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function renderStars(rating) {
  const maxStars = 4;
  let fullStars = Math.floor(rating);
  let halfStar = (rating - fullStars >= 0.25 && rating - fullStars < 0.75) ? 1 : 0;

  if (rating - fullStars >= 0.75) {
    fullStars++;
    halfStar = 0;
  }

  let emptyStars = maxStars - fullStars - halfStar;
  let html = '';

  for (let i = 0; i < fullStars; i++) {
    html += '<i class="star full"></i>';
  }
  if (halfStar) {
    html += '<i class="star half"></i>';
  }
  for (let i = 0; i < emptyStars; i++) {
    html += '<i class="star empty"></i>';
  }

  $('#starContainer').html(html);
}

$(document).ready(function() {
  let rating = $('#ratingRange').val();
  renderStars(rating);

  $('#ratingRange').on('input change', function() {
    renderStars($(this).val());
  });
});
</script>
@endsection
