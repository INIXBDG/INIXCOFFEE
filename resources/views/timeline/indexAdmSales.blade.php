@extends('layouts_crm.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        important: '#timeline-app',
        corePlugins: { preflight: false }
    };
</script>

<style>
    [x-cloak] { display: none !important; }

    /* Pengganti preflight Tailwind, hanya berlaku di dalam halaman ini */
    #timeline-app,
    #timeline-app *,
    #timeline-app ::before,
    #timeline-app ::after {
        box-sizing: border-box;
        border-width: 0;
        border-style: solid;
        border-color: #e5e7eb;
    }
    #timeline-app h1,
    #timeline-app h2,
    #timeline-app h3,
    #timeline-app h4,
    #timeline-app p {
        margin: 0;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
        line-height: inherit;
    }
    #timeline-app a {
        color: inherit;
        text-decoration: none;
    }
    #timeline-app svg {
        display: block;
    }
    #timeline-app textarea,
    #timeline-app input,
    #timeline-app select {
        font-family: inherit;
    }
    #timeline-app table {
        border-collapse: collapse;
    }

    /* Pulihkan border Select2 (tertimpa reset di atas) */
    #timeline-app .select2-container--bootstrap-5 .select2-selection,
    #timeline-app .select2-container--bootstrap-5 .select2-dropdown,
    #timeline-app .select2-container--bootstrap-5 .select2-search__field {
        border: 1px solid #cbd5e1;
    }
    #timeline-app .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.25rem;
    }

    .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scroll::-webkit-scrollbar-thumb { background-color: #94a3b8; border-radius: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background-color: #f1f5f9; }
</style>
@endpush

@section('crm_contents')

@php
    $user = auth()->user();
    $canEditTimeline = $user && $user->can('edit-timeline-item');
@endphp

<div id="timeline-app" class="bg-gray-50 text-slate-800 font-sans"
     x-data="webinarApp({
        canEditTimeline: {{ $canEditTimeline ? 'true' : 'false' }},
        pesertaUrl: '{{ route('crm.timeline-webinar.peserta.index') }}'
     })">
    <div class="max-w-[1800px] mx-auto p-6">

        {{-- TAB --}}
        @if($canEditTimeline)
            <div class="mb-6 flex gap-2 border-b border-gray-200">
                <button type="button" @click="switchTab('timeline')"
                        :class="tab === 'timeline' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 text-sm font-bold border-b-2 -mb-px transition">
                    Timeline
                </button>
                <button type="button" @click="switchTab('peserta')"
                        :class="tab === 'peserta' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                        class="px-5 py-3 text-sm font-bold border-b-2 -mb-px transition">
                    Data Peserta
                </button>
            </div>
        @endif

        {{-- ================= TAB TIMELINE ================= --}}
        <div x-show="tab === 'timeline'">

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
                        <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                            @foreach(range(1, 4) as $q)
                                <a href="?year={{ $year }}&quarter={{ $q }}"
                                   class="px-4 py-2 text-xs font-bold rounded-md transition {{ $quarter == $q && !$isSingleView ? 'bg-white text-blue-700 shadow' : 'text-slate-500 hover:text-slate-700' }}">
                                    Q{{ $q }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 justify-center md:justify-start border-t border-gray-100 pt-4">
                    <a href="?year={{ $year }}&quarter={{ $quarter }}"
                       class="px-3 py-1.5 text-xs font-bold rounded-full border {{ !$isSingleView ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        Tampilkan Semua (Q{{ $quarter }})
                    </a>

                    @php $startM = ($quarter - 1) * 3 + 1; @endphp
                    @for($i = 0; $i < 3; $i++)
                        @php
                            $mNum = $startM + $i;
                            $mName = Carbon\Carbon::create($year, $mNum, 1)->translatedFormat('F');
                        @endphp
                        <a href="?year={{ $year }}&month={{ $mNum }}"
                           class="px-3 py-1.5 text-xs font-bold rounded-full border {{ request('month') == $mNum ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                            {{ $mName }}
                        </a>
                    @endfor
                </div>
            </div>

            {{-- GRID KALENDER --}}
            <div class="grid gap-6 {{ $isSingleView ? 'grid-cols-1 w-full' : 'grid-cols-1 xl:grid-cols-3' }}">

                @foreach($monthsData as $monthNum => $data)
                    @php $canEditMonth = $canEditTimeline && !empty($data['mapping_id']); @endphp

                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 flex flex-col h-full overflow-hidden transition-all duration-300">

                        {{-- HEADER BULAN: nama bulan, tema, judul webinar --}}
                        <div class="bg-slate-900 text-white p-4">
                            <h2 class="text-xl font-bold uppercase">{{ $data['name'] }}</h2>
                            <span class="inline-block bg-yellow-500 text-slate-900 text-[10px] font-bold px-2 py-0.5 rounded mt-1">
                                TEMA: {{ strtoupper($data['theme']) }}
                            </span>

                            <div class="mt-3 bg-slate-800 p-3 rounded border border-slate-700">
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Judul Webinar</div>
                                <div class="text-sm font-bold leading-tight text-blue-200">
                                    @if($data['event_title'])
                                        {{ $data['event_title'] }}
                                    @else
                                        Belum ada judul
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- HEADER HARI (5 KOLOM) --}}
                        <div class="grid grid-cols-5 bg-gray-100 text-gray-500 text-[10px] font-bold py-3 text-center border-b border-gray-200">
                            <div>SEN</div><div>SEL</div><div>RAB</div><div>KAM</div><div>JUM</div>
                        </div>

                        {{-- BODY KALENDER --}}
                        <div class="grid grid-cols-5 auto-rows-fr bg-white flex-grow">
                            @for($i = 0; $i < $data['start_padding']; $i++)
                                <div class="bg-gray-50/50 border-b border-r border-gray-100"></div>
                            @endfor

                            @foreach($data['dates'] as $date)
                                <div
                                    @if($canEditMonth)
                                        data-date="{{ $date['full_date'] }}"
                                        data-mapping="{{ $data['mapping_id'] }}"
                                        data-content="{{ $date['item']->content ?? '' }}"
                                        @click="openDailyModal($el.dataset.date, $el.dataset.mapping, $el.dataset.content)"
                                    @endif
                                    class="border-b border-r border-gray-100 p-2 relative group flex flex-col justify-between
                                    {{ $canEditMonth ? 'cursor-pointer hover:bg-blue-50' : 'cursor-default' }}
                                    {{ $isSingleView ? 'min-h-[140px]' : 'min-h-[90px]' }}
                                    {{ $date['is_dday'] ? 'bg-blue-600' : 'bg-white' }}">

                                    <div class="text-right text-xs font-bold {{ $date['is_dday'] ? 'text-white' : ($date['item'] ? 'text-blue-600' : 'text-slate-300') }}">
                                        {{ $date['day'] }}
                                    </div>

                                    @if($date['is_dday'])
                                        <div class="flex-grow flex flex-col items-center justify-center text-center">
                                            <span class="text-yellow-300 font-black text-sm tracking-tighter drop-shadow-sm">D-DAY</span>
                                        </div>
                                    @endif

                                    @if($date['item'])
                                        <div class="mt-1 p-1 text-[9px] font-bold leading-tight rounded border
                                            {{ $date['is_dday'] ? 'bg-blue-700 text-white border-blue-400' : 'bg-blue-50 text-blue-800 border-blue-100' }}">
                                            <div class="whitespace-pre-line">{{ $date['item']->content }}</div>
                                        </div>
                                    @endif

                                    @if($canEditMonth && !$date['item'] && !$date['is_dday'])
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
        </div>

        {{-- ================= TAB DATA PESERTA ================= --}}
        @if($canEditTimeline)
            <div x-show="tab === 'peserta'" x-cloak>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">

                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
                        <div class="w-full md:w-1/2">
                            <h1 class="text-2xl font-extrabold text-slate-900 uppercase tracking-tight mb-3">Data Peserta Webinar</h1>
                            <label class="text-[10px] font-bold text-slate-500 uppercase mb-1 block">Pilih Webinar</label>

                            <p x-show="metaLoaded && webinars.length === 0" class="text-sm text-slate-500">
                                Belum ada webinar (YearMapping).
                            </p>

                            <select x-show="webinars.length > 0" x-model="selectedMapping" @change="loadPeserta()"
                                    class="w-full border border-slate-300 rounded p-2.5 text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                                <template x-for="w in webinars" :key="w.id">
                                    <option :value="w.id" x-text="w.label"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex gap-2 w-full md:w-auto">
                            <input type="text" x-model="search" placeholder="Cari nama / email / perusahaan..."
                                   class="w-full md:w-72 border border-slate-300 rounded p-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="openPesertaModal()" :disabled="!selectedMapping"
                                    class="px-4 py-2.5 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded font-bold shadow whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                                + Tambah Peserta
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scroll border border-slate-200 rounded-lg">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-100 text-slate-600 text-[10px] font-bold uppercase">
                                <tr>
                                    <th class="px-4 py-3 w-12">No</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">No HP</th>
                                    <th class="px-4 py-3">Perusahaan</th>
                                    <th class="px-4 py-3">Sales</th>
                                    <th class="px-4 py-3 w-32 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr x-show="isLoadingPeserta">
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400 animate-pulse">Memuat data...</td>
                                </tr>
                                <tr x-show="!isLoadingPeserta && filteredPeserta.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data peserta.</td>
                                </tr>

                                <template x-for="(p, idx) in filteredPeserta" :key="p.id">
                                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                                        <td class="px-4 py-3 text-slate-400" x-text="idx + 1"></td>
                                        <td class="px-4 py-3 font-semibold text-slate-800" x-text="p.nama_peserta"></td>
                                        <td class="px-4 py-3 text-slate-600" x-text="p.email_peserta"></td>
                                        <td class="px-4 py-3 text-slate-600" x-text="p.no_hp || '-'"></td>
                                        <td class="px-4 py-3 text-slate-600" x-text="p.perusahaan_nama || (p.id_perusahaan ? ('ID ' + p.id_perusahaan) : '-')"></td>
                                        <td class="px-4 py-3 text-slate-600"
                                            x-text="p.id_sales ? (p.id_sales + (p.sales_nama ? ' - ' + p.sales_nama : '')) : '-'"></td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <button type="button" @click="openPesertaModal(p)" class="text-xs font-bold text-blue-600 hover:underline mr-3">Edit</button>
                                            <button type="button" @click="deletePeserta(p)" class="text-xs font-bold text-red-600 hover:underline">Hapus</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-slate-400 mt-3">
                        Total: <span class="font-bold" x-text="filteredPeserta.length"></span> peserta
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL HARIAN --}}
    <div x-show="modals.daily.open" x-cloak
         class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition>
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
                <textarea x-model="modals.daily.content" class="w-full h-32 border border-slate-300 rounded p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Isi kegiatan..."></textarea>
                <p class="text-[10px] text-slate-400 mt-1">* Kosongkan dan simpan untuk menghapus.</p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" @click="modals.daily.open = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded">Batal</button>
                <button type="button" @click="saveDailyItem()" class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded font-bold shadow">
                    <span x-show="!isLoading">Simpan</span>
                    <span x-show="isLoading">...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL PESERTA (tanpa overflow-hidden supaya dropdown Select2 tidak terpotong) --}}
    <div x-show="modals.peserta.open" x-cloak
         class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/60 backdrop-blur-sm" x-transition>
        <div id="modal-peserta" class="relative bg-white w-full max-w-md rounded-lg shadow-2xl p-6" @click.away="modals.peserta.open = false">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="bg-blue-100 text-blue-600 p-1 rounded">👤</span>
                <span x-text="modals.peserta.id ? 'Edit Peserta' : 'Tambah Peserta'"></span>
            </h3>

            <div class="space-y-3 mb-4">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Nama Peserta *</label>
                    <input type="text" x-model="modals.peserta.form.nama_peserta"
                           class="w-full border border-slate-300 rounded p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Email *</label>
                    <input type="email" x-model="modals.peserta.form.email_peserta"
                           class="w-full border border-slate-300 rounded p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">No HP</label>
                    <input type="text" x-model="modals.peserta.form.no_hp"
                           class="w-full border border-slate-300 rounded p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Perusahaan</label>
                    <select id="peserta-perusahaan" class="w-full"></select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Sales</label>
                    <select x-model="modals.peserta.form.id_sales"
                            class="w-full border border-slate-300 rounded p-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">- Tidak ada -</option>
                        <template x-for="s in salesList" :key="s.kode">
                            <option :value="s.kode" x-text="s.kode + ' - ' + s.nama"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" @click="modals.peserta.open = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded">Batal</button>
                <button type="button" @click="savePeserta()" :disabled="isSavingPeserta"
                        class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded font-bold shadow disabled:opacity-60">
                    <span x-show="!isSavingPeserta">Simpan</span>
                    <span x-show="isSavingPeserta">...</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    function webinarApp(config = {}) {
        return {
            canEditTimeline: config.canEditTimeline ?? false,
            canManageMaster: config.canManageMaster ?? false,
            canUpdateChecklist: config.canUpdateChecklist ?? false,
            isLoading: false,
            isLoadingChecklist: false,
            checklists: [],

            // Tab & peserta
            tab: 'timeline',
            pesertaUrl: config.pesertaUrl ?? '',
            webinars: [],
            salesList: [],
            selectedMapping: '',
            metaLoaded: false,
            peserta: [],
            isLoadingPeserta: false,
            isSavingPeserta: false,
            search: '',

            modals: {
                daily: { open: false, date: null, content: '', mappingId: null },
                event: { open: false, mappingId: null, monthName: '', form: { title: '', speaker: '', desc: '' } },
                peserta: { open: false, id: null, form: { nama_peserta: '', email_peserta: '', no_hp: '', id_perusahaan: '', id_sales: '' } }
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
            get filteredPeserta() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.peserta;
                return this.peserta.filter(p =>
                    (p.nama_peserta || '').toLowerCase().includes(q) ||
                    (p.email_peserta || '').toLowerCase().includes(q) ||
                    (p.perusahaan_nama || '').toLowerCase().includes(q)
                );
            },

            // ---------- TAB ----------
            async switchTab(name) {
                this.tab = name;
                if (name === 'peserta' && !this.metaLoaded) {
                    await this.loadMeta();
                    await this.loadPeserta();
                }
            },

            // ---------- PESERTA ----------
            async pesertaRequest(url, method, body) {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: body ? JSON.stringify(body) : undefined
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                    throw new Error(firstError || data.message || 'Terjadi kesalahan sistem');
                }
                return data;
            },
            async loadMeta() {
                this.isLoadingPeserta = true;
                try {
                    const data = await this.pesertaRequest(`${this.pesertaUrl}/meta`, 'GET');
                    this.webinars = data.webinars;
                    this.salesList = data.sales;
                    await this.$nextTick();
                    this.selectedMapping = data.default_mapping_id ?? '';
                    this.metaLoaded = true;
                } catch (e) {
                    alert('Gagal memuat data webinar: ' + e.message);
                } finally {
                    this.isLoadingPeserta = false;
                }
            },
            async loadPeserta() {
                if (!this.selectedMapping) { this.peserta = []; return; }
                this.isLoadingPeserta = true;
                try {
                    this.peserta = await this.pesertaRequest(
                        `${this.pesertaUrl}?year_mapping_id=${this.selectedMapping}`, 'GET'
                    );
                } catch (e) {
                    alert('Gagal memuat peserta: ' + e.message);
                } finally {
                    this.isLoadingPeserta = false;
                }
            },
            openPesertaModal(row = null) {
                // Sales yang sudah tidak aktif tetap harus muncul di pilihan agar tidak terhapus saat edit
                if (row && row.id_sales && !this.salesList.some(s => s.kode === row.id_sales)) {
                    this.salesList.push({ kode: row.id_sales, nama: row.sales_nama || '(tidak aktif)' });
                }

                this.modals.peserta.id = row ? row.id : null;
                this.modals.peserta.form = {
                    nama_peserta: row ? row.nama_peserta : '',
                    email_peserta: row ? row.email_peserta : '',
                    no_hp: row ? (row.no_hp || '') : '',
                    id_perusahaan: row ? (row.id_perusahaan || '') : '',
                    id_sales: row ? (row.id_sales || '') : ''
                };
                this.modals.peserta.open = true;
                this.$nextTick(() => this.initPerusahaanSelect(row));
            },
            initPerusahaanSelect(row) {
                const $ = window.jQuery;
                const $sel = $('#peserta-perusahaan');
                if (!$sel.length || !$.fn.select2) {
                    console.error('jQuery / Select2 belum termuat');
                    return;
                }
                if ($sel.data('select2')) $sel.select2('destroy');

                // Option kosong pertama dibutuhkan untuk placeholder + allowClear
                $sel.empty().append(new Option('', '', false, false));
                if (row && row.id_perusahaan) {
                    $sel.append(new Option(row.perusahaan_nama || ('ID ' + row.id_perusahaan), row.id_perusahaan, true, true));
                }

                const url = `${this.pesertaUrl}/perusahaan`;
                $sel.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Cari perusahaan...',
                    allowClear: true,
                    dropdownParent: $('#modal-peserta'),
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term || '', page: params.page || 1 }),
                        processResults: data => ({
                            results: data.results,
                            pagination: { more: data.more }
                        })
                    }
                });

                $sel.off('change.peserta').on('change.peserta', () => {
                    this.modals.peserta.form.id_perusahaan = $sel.val() || '';
                });
            },
            async savePeserta() {
                if (this.isSavingPeserta) return;
                this.isSavingPeserta = true;
                try {
                    const id = this.modals.peserta.id;
                    if (id) {
                        await this.pesertaRequest(`${this.pesertaUrl}/${id}`, 'PUT', this.modals.peserta.form);
                    } else {
                        await this.pesertaRequest(this.pesertaUrl, 'POST', {
                            ...this.modals.peserta.form,
                            year_mapping_id: this.selectedMapping
                        });
                    }
                    this.modals.peserta.open = false;
                    await this.loadPeserta();
                } catch (e) {
                    alert('Gagal menyimpan: ' + e.message);
                } finally {
                    this.isSavingPeserta = false;
                }
            },
            async deletePeserta(row) {
                if (!confirm(`Hapus peserta "${row.nama_peserta}"?`)) return;
                try {
                    await this.pesertaRequest(`${this.pesertaUrl}/${row.id}`, 'DELETE');
                    await this.loadPeserta();
                } catch (e) {
                    alert('Gagal menghapus: ' + e.message);
                }
            },

            // ---------- TIMELINE ----------
            openDailyModal(date, mappingId, content) {
                if (!this.canEditTimeline) return;
                this.modals.daily.date = date;
                this.modals.daily.mappingId = mappingId;
                this.modals.daily.content = content || '';
                this.modals.daily.open = true;
            },
            async saveDailyItem() {
                if (this.isLoading || !this.canEditTimeline) return;
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
                if (!this.canManageMaster) return;
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
                if (!this.canUpdateChecklist) return;
                item.is_checked = !item.is_checked;
                await fetch(`/api/checklist/${item.id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
            },
            async updateChecklistDetail(item) {
                if (!this.canUpdateChecklist) return;
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

    // Muat Alpine hanya jika belum ada (layout tidak memuatnya, app.js belum tentu)
    document.addEventListener('DOMContentLoaded', function () {
        if (window.Alpine) return;
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js';
        document.head.appendChild(s);
    });
</script>

@endsection