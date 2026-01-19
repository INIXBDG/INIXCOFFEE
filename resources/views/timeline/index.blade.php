<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Webinar - Triwulan {{ $quarter }}</title>

    {{-- CDN Tailwind CSS & Alpine.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #94a3b8; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background-color: #f1f5f9; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 font-sans" x-data="webinarApp()">

    {{-- CEK PERMISSION --}}
    @php
        $isTimDigital = auth()->check() && auth()->user()->jabatan === 'Tim Digital';
    @endphp

    <div class="max-w-[1800px] mx-auto p-6" x-data="{ canEdit: {{ $isTimDigital ? 'true' : 'false' }} }">

        {{-- HEADER --}}
        <div class="mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tight">Timeline Webinar</h1>
                    <p class="text-slate-500 text-sm mt-1">
                        Tahun {{ $year }} -
                        @if($isSingleView)
                            Bulan {{ $monthsData[request('month')]['name'] }}
                        @else
                            Triwulan {{ $quarter }}
                        @endif
                    </p>
                </div>

                <div class="mt-4 md:mt-0 flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Home
                    </a>

                    <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                        @foreach(range(1, 4) as $q)
                            <a href="?quarter={{ $q }}"
                            class="px-4 py-2 text-xs font-bold rounded-md transition {{ $quarter == $q && !$isSingleView ? 'bg-white text-blue-700 shadow' : 'text-slate-500 hover:text-slate-700' }}">
                            Q{{ $q }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex gap-2 justify-center md:justify-start border-t border-gray-100 pt-4">
                <a href="?quarter={{ $quarter }}"
                class="px-3 py-1.5 text-xs font-bold rounded-full border {{ !$isSingleView ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Tampilkan Semua (Q{{ $quarter }})
                </a>

                @php $startM = ($quarter - 1) * 3 + 1; @endphp
                @for($i = 0; $i < 3; $i++)
                    @php
                        $mNum = $startM + $i;
                        $mName = Carbon\Carbon::create(2026, $mNum, 1)->translatedFormat('F');
                    @endphp
                    <a href="?month={{ $mNum }}"
                    class="px-3 py-1.5 text-xs font-bold rounded-full border {{ request('month') == $mNum ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    {{ $mName }}
                    </a>
                @endfor
            </div>
        </div>

        {{-- GRID KALENDER --}}
        <div class="grid gap-6 {{ $isSingleView ? 'grid-cols-1 w-full' : 'grid-cols-1 xl:grid-cols-3' }}">

            @foreach($monthsData as $monthNum => $data)
                <div class="bg-white rounded-xl shadow-lg border border-slate-200 flex flex-col h-full overflow-hidden transition-all duration-300">

                    {{-- HEADER BULAN --}}
                    <div class="bg-slate-900 text-white p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-bold uppercase">{{ $data['name'] }}</h2>
                                <span class="inline-block bg-yellow-500 text-slate-900 text-[10px] font-bold px-2 py-0.5 rounded mt-1">
                                    TEMA: {{ strtoupper($data['theme']) }}
                                </span>
                            </div>

                            <button @click="openEventModal(
                                    {{ $data['mapping_id'] ?? 'null' }},
                                    '{{ $data['name'] }}',
                                    {{ json_encode($data['event_detail']) }},
                                    '{{ $data['theme'] }}',
                                    '{{ $data['planned_date_raw'] }}',
                                    {{ $data['duration'] }}
                                )"
                                class="{{ $isTimDigital ? 'bg-blue-600 hover:bg-blue-500' : 'bg-slate-600 hover:bg-slate-500' }} text-white text-xs px-3 py-1.5 rounded shadow transition flex items-center gap-1">

                                @if($isTimDigital)
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Kelola Event
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Detail Event
                                @endif
                            </button>
                        </div>

                        @if($data['event_detail'])
                            <div class="mt-4 bg-slate-800 p-3 rounded border border-slate-700">
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Judul Webinar</div>
                                <div class="text-sm font-bold leading-tight text-blue-200">
                                    {{ $data['event_detail']->title }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- HEADER HARI (5 KOLOM) --}}
                    <div class="grid grid-cols-5 bg-gray-100 text-gray-500 text-[10px] font-bold py-3 text-center border-b border-gray-200">
                        <div>SEN</div><div>SEL</div><div>RAB</div><div>KAM</div><div>JUM</div>
                    </div>

                    {{-- BODY KALENDER --}}
                    <div class="grid grid-cols-5 auto-rows-fr bg-white flex-grow">

                        {{-- 1. PADDING SEL KOSONG --}}
                        @for($i = 0; $i < $data['start_padding']; $i++)
                            <div class="bg-gray-50/50 border-b border-r border-gray-100"></div>
                        @endfor

                        {{-- 2. LOOP TANGGAL KERJA --}}
                        @foreach($data['dates'] as $date)
                            <div
                                @click="canEdit ? openDailyModal(
                                        '{{ $date['full_date'] }}',
                                        {{ $data['mapping_id'] }},
                                        {{ json_encode($date['item']->content ?? '') }}
                                    ) : null"
                                class="border-b border-r border-gray-100 p-2 relative group flex flex-col justify-between
                                {{ $isTimDigital ? 'cursor-pointer hover:bg-blue-50' : 'cursor-default' }}
                                {{ $isSingleView ? 'min-h-[140px]' : 'min-h-[90px]' }}
                                {{ $date['is_dday'] ? 'bg-blue-600' : 'bg-white' }}">

                                {{-- NOMOR TANGGAL --}}
                                <div class="text-right text-xs font-bold {{ $date['is_dday'] ? 'text-white' : ($date['item'] ? 'text-blue-600' : 'text-slate-300') }}">
                                    {{ $date['day'] }}
                                </div>

                                {{-- TAMPILAN D-DAY --}}
                                @if($date['is_dday'])
                                    <div class="flex-grow flex flex-col items-center justify-center text-center">
                                        <span class="text-yellow-300 font-black text-sm tracking-tighter drop-shadow-sm">D-DAY</span>
                                    </div>
                                @endif

                                {{-- KONTEN AKTIVITAS --}}
                                @if($date['item'])
                                    <div class="mt-1 p-1 text-[9px] font-bold leading-tight rounded border
                                        {{ $date['is_dday'] ? 'bg-blue-700 text-white border-blue-400' : 'bg-blue-50 text-blue-800 border-blue-100' }}">
                                        <div class="whitespace-pre-line truncate-custom">
                                            {{ $date['item']->content }}
                                        </div>
                                    </div>
                                @endif

                                {{-- ICON TAMBAH (TIM DIGITAL ONLY) --}}
                                @if($isTimDigital && !$date['item'] && !$date['is_dday'])
                                    <div class="hidden group-hover:flex absolute inset-0 items-center justify-center text-blue-300 opacity-40">
                                        <span class="text-xl">+</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- MODAL A: HARIAN (Hanya bisa dibuka Tim Digital karena trigger klik dimatikan utk yg lain) --}}
        <div x-show="modals.daily.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition>
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl overflow-hidden p-6" @click.away="modals.daily.open = false">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-600 p-1 rounded">📅</span>
                    Aktivitas Harian
                </h3>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase">Tanggal</label>
                    <div class="text-sm font-bold text-slate-800" x-text="formatDate(modals.daily.date)"></div>
                </div>
                <div class="mb-4">
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Rincian Kegiatan</label>
                    <textarea x-model="modals.daily.content" class="w-full h-32 border-slate-300 rounded p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Isi kegiatan..."></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">* Kosongkan dan simpan untuk menghapus.</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button @click="modals.daily.open = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded">Batal</button>
                    <button @click="saveDailyItem()" class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded font-bold shadow">
                        <span x-show="!isLoading">Simpan</span>
                        <span x-show="isLoading">...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL B: EVENT UTAMA & CHECKLIST --}}
        <div x-show="modals.event.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" x-transition>
            <div class="bg-white w-full max-w-6xl h-[85vh] rounded-2xl shadow-2xl flex overflow-hidden" @click.away="modals.event.open = false">

                {{-- KIRI: FORM EVENT --}}
                <div class="w-1/3 bg-slate-50 border-r border-slate-200 p-8 flex flex-col overflow-y-auto custom-scroll">
                    <div class="mb-6 border-b border-slate-200 pb-4">
                        <div class="text-xs font-bold text-slate-400 uppercase">Pengaturan Bulan</div>
                        <h2 class="text-2xl font-black text-slate-800" x-text="modals.event.monthName"></h2>
                    </div>

                    <div class="space-y-5">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 space-y-3">
                            <h4 class="text-xs font-bold text-blue-800 uppercase flex items-center gap-1">⚙️ Master Plan</h4>
                            <div>
                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Tema Utama</label>
                                <input type="text" x-model="modals.event.form.theme" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full border-blue-200 rounded p-2 text-sm font-semibold text-blue-900 outline-none">
                            </div>
                            <div class="flex gap-2">
                                <div class="w-2/3">
                                    <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Tgl Pelaksanaan</label>
                                    <input type="date" x-model="modals.event.form.planned_date" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full border-blue-200 rounded p-2 text-sm outline-none">
                                </div>
                                <div class="w-1/3">
                                    <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Durasi</label>
                                    <input type="number" x-model="modals.event.form.duration" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full border-blue-200 rounded p-2 text-sm outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-400 uppercase border-t pt-2">📝 Detail Acara</h4>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Judul Webinar</label>
                                <input type="text" x-model="modals.event.form.title" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full border-slate-300 rounded p-2.5 text-sm outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Narasumber</label>
                                <input type="text" x-model="modals.event.form.speaker" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full border-slate-300 rounded p-2.5 text-sm outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Keterangan</label>
                                <textarea x-model="modals.event.form.desc" :disabled="!canEdit" :class="!canEdit ? 'bg-gray-100' : 'bg-white'" class="w-full h-20 border-slate-300 rounded p-2.5 text-sm outline-none shadow-sm"></textarea>
                            </div>
                        </div>

                        <button x-show="canEdit" @click="saveEventDetails()" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-lg font-bold text-sm shadow transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

                {{-- KANAN: CHECKLIST OTOMATIS (READ ONLY UNTUK SELAIN TIM DIGITAL) --}}
                <div class="w-2/3 flex flex-col bg-white">
                    <div class="bg-slate-800 text-white px-6 py-4 flex justify-between items-center shadow-md z-10">
                        <div>
                            <h3 class="font-bold text-lg">CHECKLIST BULANAN</h3>
                            <p class="text-xs text-slate-400">List pekerjaan otomatis digenerate</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-700 px-3 py-1 rounded">
                            <span class="text-xs text-slate-300">Progress</span>
                            <span class="font-bold text-emerald-400" x-text="progressPercent + '%'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 bg-slate-100 text-slate-600 font-bold text-[10px] uppercase py-2 px-6 border-b border-slate-200">
                        <div class="col-span-1 text-center">Done</div>
                        <div class="col-span-4">Nama Pekerjaan</div>
                        <div class="col-span-3">PJ</div>
                        <div class="col-span-4">Catatan / Link</div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 py-2 custom-scroll bg-white relative">
                        <div x-show="isLoadingChecklist" class="absolute inset-0 bg-white/80 z-20 flex justify-center items-center">
                            <span class="text-slate-500 text-sm animate-pulse">Memuat Checklist...</span>
                        </div>

                        <template x-for="(items, category) in groupedChecklists" :key="category">
                            <div>
                                <div class="bg-slate-200 px-2 py-1.5 text-[10px] font-extrabold text-slate-700 uppercase tracking-wider border-y border-slate-300 mt-2 mb-1 sticky top-0 z-10">
                                    <span x-text="category"></span>
                                </div>

                                <template x-for="item in items" :key="item.id">
                                    <div class="grid grid-cols-12 gap-3 items-center py-2 border-b border-slate-100 hover:bg-slate-50 transition text-sm group">

                                        {{-- Checkbox Read-Only Logic --}}
                                        <div class="col-span-1 flex justify-center">
                                            <input type="checkbox" :checked="item.is_checked"
                                                @change="canEdit ? toggleChecklist(item) : null"
                                                :disabled="!canEdit"
                                                class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer disabled:cursor-not-allowed disabled:opacity-60">
                                        </div>

                                        <div class="col-span-4 font-medium text-slate-700" :class="item.is_checked ? 'line-through text-slate-400' : ''">
                                            <span x-text="item.todo.task_name"></span>
                                        </div>

                                        {{-- Input PJ Read-Only Logic --}}
                                        <div class="col-span-3">
                                            <input type="text" x-model="item.pic"
                                                @blur="canEdit ? updateChecklistDetail(item) : null"
                                                :disabled="!canEdit"
                                                class="w-full bg-transparent border-b border-transparent hover:border-slate-300 focus:border-blue-500 outline-none text-xs py-1 text-center disabled:bg-transparent disabled:text-slate-600"
                                                placeholder="-">
                                        </div>

                                        {{-- Input Note Read-Only Logic --}}
                                        <div class="col-span-4">
                                            <input type="text" x-model="item.notes"
                                                @blur="canEdit ? updateChecklistDetail(item) : null"
                                                :disabled="!canEdit"
                                                class="w-full bg-transparent border-b border-transparent hover:border-slate-300 focus:border-blue-500 outline-none text-xs py-1 text-blue-600 disabled:bg-transparent disabled:text-blue-800"
                                                placeholder="Keterangan...">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 text-right">
                        <button @click="modals.event.open = false" class="text-sm font-bold text-slate-500 hover:text-slate-800 underline">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>2
        function webinarApp() {
            return {
                isLoading: false,
                isLoadingChecklist: false,
                checklists: [],
                modals: {
                    daily: { open: false, date: null, content: '', mappingId: null },
                    event: { open: false, mappingId: null, monthName: '', form: { title: '', speaker: '', desc: '' } }
                },
                get progressPercent() {
                    if (this.checklists.length === 0) return 0;
                    return Math.round((this.checklists.filter(i => i.is_checked).length / this.checklists.length) * 100);
                },
                get groupedChecklists() {
                    const groups = {};
                    this.checklists.forEach(item => {
                        const cat = item.todo.category || 'Lainnya';
                        if (!groups[cat]) groups[cat] = [];
                        groups[cat].push(item);
                    });
                    return groups;
                },
                openDailyModal(date, mappingId, content) {
                    this.modals.daily.date = date;
                    this.modals.daily.mappingId = mappingId;
                    this.modals.daily.content = content;
                    this.modals.daily.open = true;
                },
                async saveDailyItem() {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    try {
                        const response = await fetch('/api/timeline-item', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                item_date: this.modals.daily.date,
                                year_mapping_id: this.modals.daily.mappingId,
                                content: this.modals.daily.content
                            })
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const error = await response.json();
                            alert('Gagal menyimpan: ' + (error.message || 'Terjadi kesalahan sistem'));
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Koneksi gagal');
                    } finally {
                        this.isLoading = false;
                    }
                },
                openEventModal(mappingId, monthName, eventData, theme, plannedDate, duration) {
                    this.modals.event.mappingId = mappingId;
                    this.modals.event.monthName = monthName;
                    this.modals.event.open = true;
                    this.modals.event.form.theme = theme;
                    this.modals.event.form.planned_date = plannedDate;
                    this.modals.event.form.duration = duration;
                    this.modals.event.form.title = eventData ? eventData.title : '';
                    this.modals.event.form.speaker = eventData ? eventData.speaker_name : '';
                    this.modals.event.form.desc = eventData ? eventData.description : '';
                    this.fetchChecklists(mappingId);
                },
                async saveEventDetails() {
                    const id = this.modals.event.mappingId;
                    try {
                        const res = await fetch(`/api/event/${id}/update`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({
                                theme: this.modals.event.form.theme, planned_date: this.modals.event.form.planned_date,
                                duration_minutes: this.modals.event.form.duration, title: this.modals.event.form.title,
                                speaker_name: this.modals.event.form.speaker, description: this.modals.event.form.desc
                            })
                        });
                        if (!res.ok) { alert('Gagal menyimpan'); return; }
                        window.location.reload();
                    } catch (e) { alert('System Error'); }
                },
                async fetchChecklists(mappingId) {
                    this.isLoadingChecklist = true;
                    try {
                        const res = await fetch(`/api/checklist/${mappingId}`);
                        this.checklists = await res.json();
                    } catch (e) { console.error(e); }
                    this.isLoadingChecklist = false;
                },
                async toggleChecklist(item) {
                    item.is_checked = !item.is_checked;
                    await fetch(`/api/checklist/${item.id}/toggle`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                },
                async updateChecklistDetail(item) {
                    await fetch(`/api/checklist/${item.id}/detail`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ pic: item.pic, notes: item.notes })
                    });
                },
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    return new Date(dateStr).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
                }
            }
        }
    </script>
</body>
</html>
