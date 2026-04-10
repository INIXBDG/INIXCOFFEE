@extends('layouts.app')

@section('content')
@php
    $defaultAvatarUrl = "https://placehold.co/40x40/E2E8F0/94A3B8?text=AV";
    
    // Definisi 7 Tahapan Kanban Sesuai Kesepakatan
    $boards = [
        ['key' => 'backlog', 'title' => 'Backlog', 'color' => 'gray'],
        ['key' => 'to_do', 'title' => 'To Do', 'color' => 'blue'], // FIX
        ['key' => 'in_progress', 'title' => 'In Progress', 'color' => 'indigo'],
        ['key' => 'testing', 'title' => 'Testing', 'color' => 'yellow'],
        ['key' => 'deploy', 'title' => 'Deploy', 'color' => 'purple'],
        ['key' => 'validate', 'title' => 'Validate', 'color' => 'red'],
        ['key' => 'evaluasi', 'title' => 'Evaluasi', 'color' => 'green'], // FIX
    ];
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container-fluid mx-auto py-8 px-4">
    {{-- Modal Kelola Tim --}}
    <div id="teamModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-[60]">
        <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3">
                <h5 class="text-lg font-semibold">Alokasi Anggota Tim</h5>
                <button type="button" class="close-modal text-gray-400 hover:text-gray-600" data-modal="teamModal">&times;</button>
            </div>
            <form id="formAssignTeam" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Anggota Karyawan</label>
                    <select id="select_team_members" name="employee_ids[]" class="w-full" multiple="multiple"></select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" class="close-modal bg-gray-300 px-4 py-2 rounded text-gray-700" data-modal="teamModal">Batal</button>
                    <button type="submit" class="bg-blue-500 px-4 py-2 rounded text-white font-bold">Simpan Tim</button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Modal Detail Task --}}
<div id="taskDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-start justify-center hidden z-[60]">
  <div class="relative mx-auto p-6 border w-full max-w-lg shadow-xl rounded-lg bg-white">
    <form id="taskDetailForm">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h5 class="text-xl font-bold text-gray-800">Detail Task</h5>
        <button type="button" id="closeTaskModalBtn" class="text-gray-400 hover:text-gray-600 focus:outline-none">
            &times;
        </button>
        {{-- <button type="button" class="close-modal text-gray-400 hover:text-gray-600" data-modal="teamModal"></button> --}}
      </div>

      <div class="space-y-4">
        
        {{-- Section: Dibuat Oleh (Project Manager) --}}
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Dibuat oleh</label>
          <div class="flex items-center space-x-3">
            <img id="detail_pm_photo" class="w-10 h-10 rounded-full bg-gray-200 object-cover shadow-sm" src="{{ $defaultAvatarUrl }}" alt="PM photo">
            <p id="detail_pm_name" class="text-gray-800 font-medium">N/A</p>
          </div>
        </div>
        
        {{-- Section: Ditugaskan Kepada (Assignee) --}}
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Ditugaskan Kepada</label>
          <select name="user_id" id="detail_user_id" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:bg-gray-100">
               <option value="">-- Belum Ditugaskan --</option>
          </select>
        </div>

        {{-- Section: Judul Task --}}
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
          <input type="text" name="title" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:bg-gray-100" required>
        </div>

        {{-- Section: Tanggal --}}
        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
            <div class="w-full md:w-1/2">
              <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
              <input type="date" name="startdate" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:bg-gray-100">
            </div>
            <div class="w-full md:w-1/2">
              <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Selesai</label>
              <input type="date" name="enddate" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:bg-gray-100">
            </div>
        </div>

        {{-- Section: Deskripsi --}}
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
          <textarea name="description" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all disabled:bg-gray-100" rows="3" placeholder="Tuliskan deskripsi..."></textarea>
        </div>

        {{-- Section: Aktivitas Harian Container --}}
        <div class="mt-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Aktivitas Harian</label>
            <div class="border border-gray-200 rounded bg-white overflow-hidden">
                
                {{-- List Log Aktivitas --}}
                <div id="dailyActivityContainer" class="p-4 max-h-48 overflow-y-auto space-y-3">
                    <p class="text-gray-500 text-sm italic">Memuat aktivitas...</p>
                </div>
                
                {{-- Form Tambah Aktivitas --}}
                <div class="border-t border-gray-200 p-4 bg-gray-50" id="addActivitySection">
                    <h6 class="text-gray-700 text-xs font-bold mb-3 uppercase tracking-wider">Tambah Aktivitas Harian</h6>
                    <div class="space-y-3">
                        <textarea id="new_activity_text" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" rows="2" placeholder="Deskripsi aktivitas..."></textarea>
                        
                        <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                            <select id="new_activity_status" class="shadow-sm border border-gray-300 rounded w-full sm:w-1/2 py-1.5 px-3 text-gray-700 text-sm leading-tight focus:outline-none focus:ring-1 focus:ring-green-500">
                                <option value="On Progres">On Progres</option>
                                <option value="On Progres Dilanjutkan Besok">Lanjut Besok</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Gagal">Gagal</option>
                            </select>
                            <input type="file" id="new_activity_file" class="shadow-sm border border-gray-300 rounded w-full sm:w-1/2 py-1 px-2 text-gray-700 text-sm bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        </div>
                        
                        <button type="button" id="btnSubmitActivity" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow-sm text-sm w-full transition duration-150 ease-in-out flex justify-center items-center">
                            <i class="fas fa-paper-plane mr-2"></i> Simpan Aktivitas Baru
                        </button>
                        <button type="button" command="close" commandfor="dialog" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs inset-ring inset-ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

      </div>

      {{-- Footer Actions --}}
      <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-6">
        <button type="button" id="deleteTaskBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow-sm transition duration-150 ease-in-out flex items-center">
          <i class="fas fa-trash-alt mr-2"></i> Hapus
        </button>

        <button type="submit" id="btnSaveDetail" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded shadow-sm transition duration-150 ease-in-out">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

    {{-- Header & Project Selector --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
        <h4 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-columns mr-2 text-blue-500"></i>Papan Kanban
        </h4>
        <div class="w-full md:w-1/3">
            <select class="shadow appearance-none border border-blue-400 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" id="filter_project_id" name="filter_project_id">
                <option value="">-- Pilih Proyek Fase Teknis --</option>
                @foreach($projects as $project)
                    @php
                        $admin = $project->administration;
                        $pm = $admin ? $admin->projectManager : null;
                        $pmId = $pm ? $pm->kode_karyawan : '';
                        $pmName = $pm ? $pm->nama_lengkap : 'N/A';
                        $pmPhoto = ($pm && $pm->foto) ? asset('storage/posts/' . $pm->foto) : $defaultAvatarUrl;
                    @endphp
                    <option value="{{ $project->id }}" data-pm-id="{{ $pmId }}" data-pm-name="{{ $pmName }}" data-pm-photo="{{ $pmPhoto }}">
                        {{ $project->name ?? $project->nama_projek }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex space-x-2">
            <button 
                type="button" 
                class="btn btn-md bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded shadow hidden" 
                id="btnKelolaTim">
                <i class="fas fa-users mr-2"></i> Kelola Tim
            </button>
            {{-- Tombol Tambah Tugas yang sudah ada --}}
        </div>
    </div>

    {{-- State Kosong (Ditampilkan saat proyek belum dipilih) --}}
    <div id="emptyState" class="flex items-center justify-center h-64 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
        <h5 class="text-gray-500 text-lg"><i class="fas fa-info-circle mr-2"></i>Silakan pilih proyek pada menu di atas untuk menampilkan papan tugas.</h5>
    </div>

    {{-- Papan Kanban Container --}}
    <div id="kanbanBoardWrapper" class="kanban-board-container flex space-x-4 overflow-x-auto pb-4 hidden">
        @foreach ($boards as $board)
            {{-- Kolom --}}
            <div class="kanban-column flex flex-col flex-shrink-0 w-80 flex-grow bg-gray-100 rounded-lg shadow-md h-[80vh]">
                {{-- Header Kolom --}}
                <div class="p-4 border-b-4 border-{{ $board['color'] }}-400 flex-shrink-0">
                    <h5 class="font-semibold text-gray-700">{{ $board['title'] }}</h5>
                </div>

                {{-- List Task (Kosong di awal, akan diisi oleh AJAX) --}}
                <div
                    class="kanban-task-list p-4 space-y-3 flex-grow overflow-y-auto"
                    data-state="{{ $board['key'] }}"
                    id="list-{{ $board['key'] }}"
                >
                    </div>

                {{-- Footer Quick-Add --}}
                <div class="kanban-footer p-3 border-t border-gray-200 flex-shrink-0">
                    <button class="add-card-btn text-gray-500 hover:text-gray-700 hover:bg-gray-200 w-full text-left p-2 rounded-md transition duration-150">
                        <i class="fas fa-plus mr-2"></i> Tambah Tugas Baru
                    </button>
                    <form class="add-card-form hidden space-y-2" data-state="{{ $board['key'] }}">
                        <div>
                            <input
                                type="text"
                                name="title"
                                class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan judul task..."
                                required
                            >
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-3 rounded text-sm">
                                Simpan
                            </button>
                            <button type="button" class="cancel-add-btn text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>



{{-- Script CDN --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const AppConfig = {
        storageBaseUrl: "{{ asset('storage/posts/') }}/",
        defaultAvatarUrl: "{{ $defaultAvatarUrl }}"
    };
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ==========================================
        // 1. KONFIGURASI & VARIABEL GLOBAL
        // ==========================================
        const updateStateUrl = "{{ url('/projects/kanban/tasks/update-state') }}";
        const storeTaskUrl = "{{ url('/projects/kanban/tasks') }}";
        const tasksBaseUrl = "{{ url('/projects/kanban/tasks') }}"; 
        const fetchTasksUrl = "{{ url('/projects/kanban/get-tasks') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Identitas Pengguna
        const currentUserKaryawanId = '{{ auth()->user()->karyawan->kode_karyawan ?? '' }}';
        
        let currentProjectId = null;
        let currentPmId = null;
        let activeTeamMembers = [];

        // Referensi Elemen DOM
        const projectSelect = document.getElementById('filter_project_id');
        const emptyState = document.getElementById('emptyState');
        const kanbanBoardWrapper = document.getElementById('kanbanBoardWrapper');
        const btnKelolaTim = document.getElementById('btnKelolaTim');
        const teamModal = document.getElementById('teamModal');
        const taskModal = document.getElementById('taskDetailModal');
        const taskForm = document.getElementById('taskDetailForm');

        // ==========================================
        // 2. PEMILIHAN PROYEK & OTORISASI
        // ==========================================
        projectSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            currentProjectId = this.value;
            currentPmId = selectedOption.dataset.pmId; // ID PM dari proyek yang dipilih

            if (currentProjectId) {
                emptyState.classList.add('hidden');
                kanbanBoardWrapper.classList.remove('hidden');
                
                checkAuthorization();
                loadTeamAndPopulateDropdowns();
                loadTasks(currentProjectId);
            } else {
                emptyState.classList.remove('hidden');
                kanbanBoardWrapper.classList.add('hidden');
                btnKelolaTim.classList.add('hidden');
                clearAllKanbanColumns();
            }
        });

        function checkAuthorization() {
            const isPM = (currentUserKaryawanId === currentPmId);
            
            // Tampilkan/Sembunyikan tombol Kelola Tim
            if (isPM) {
                btnKelolaTim.classList.remove('hidden');
            } else {
                btnKelolaTim.classList.add('hidden');
            }

            // Tampilkan/Sembunyikan area Quick-Add (Tambah Tugas Baru)
            document.querySelectorAll('.kanban-footer').forEach(footer => {
                footer.style.display = isPM ? 'block' : 'none';
            });
        }

        // ==========================================
        // 3. MANAJEMEN TIM (ALOKASI KARYAWAN)
        // ==========================================
        $('#select_team_members').select2({
            placeholder: "Cari dan pilih karyawan...",
            dropdownParent: $('#teamModal'),
            ajax: {
                url: "{{ route('getUserProject') }}", // Sesuaikan dengan route pencarian karyawan
                dataType: 'json',
                delay: 250,
                processResults: function(response){
                    return {
                        results: $.map(response.data, function(item){
                            return { id: item.kode_karyawan, text: item.nama_lengkap }
                        })
                    }
                }
            }
        });

        function loadTeamAndPopulateDropdowns() {
            fetch(`/projects/kanban/${currentProjectId}/team-members`)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        activeTeamMembers = res.data;
                        
                        // Isi dropdown Select2 (Kelola Tim)
                        $('#select_team_members').empty();
                        res.data.forEach(member => {
                            let option = new Option(member.text, member.id, true, true);
                            if (member.locked) option.disabled = true; // Kunci jika sudah punya task
                            $('#select_team_members').append(option);
                        });
                        $('#select_team_members').trigger('change');

                        // Isi dropdown 'Ditugaskan Kepada' di Form & Modal
                        populateTaskAssignSelects();
                    }
                })
                .catch(err => console.error("Gagal memuat tim:", err));
        }

        function populateTaskAssignSelects() {
            document.querySelectorAll('.task-assign-select').forEach(select => {
                select.innerHTML = '<option value="">-- Tugaskan ke Anggota Tim --</option>';
                activeTeamMembers.forEach(member => {
                    select.add(new Option(member.text, member.id)); 
                });
            });

            const detailSelect = document.getElementById('detail_user_id');
            if (detailSelect) {
                detailSelect.innerHTML = '<option value="">-- Belum Ditugaskan --</option>';
                activeTeamMembers.forEach(member => {
                    detailSelect.add(new Option(member.text, member.id)); 
                });
            }
        }

        btnKelolaTim.addEventListener('click', () => {
            if (currentProjectId) teamModal.classList.remove('hidden');
        });

        document.querySelectorAll('.close-modal[data-modal="teamModal"]').forEach(btn => {
            btn.addEventListener('click', () => teamModal.classList.add('hidden'));
        });

        $('#formAssignTeam').on('submit', function(e) {
            e.preventDefault();
            if (!currentProjectId) return;

            $('#select_team_members option').prop('disabled', false); // Unlock sebelum serialize
            const formData = $(this).serialize();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            fetch(`/projects/kanban/${currentProjectId}/assign-team`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('Tim berhasil diperbarui.');
                    teamModal.classList.add('hidden');
                    loadTeamAndPopulateDropdowns(); // Segarkan dropdown
                } else alert(res.message || 'Gagal memperbarui tim.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Tim';
            });
        });

        // ==========================================
        // 4. PAPAN KANBAN & SORTABLE JS
        // ==========================================
        document.querySelectorAll('.kanban-task-list').forEach(list => {
            new Sortable(list, {
                group: 'kanban',
                animation: 150,
                onEnd: function (evt) {
                    const taskId = evt.item.dataset.id;
                    const newState = evt.to.dataset.state;

                    fetch(`${tasksBaseUrl}/${taskId}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ status: newState })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert('❌ ' + (data.message || 'Gagal memperbarui status.'));
                            evt.from.appendChild(evt.item); // Kembalikan posisi jika gagal
                        }
                    }).catch(err => {
                        console.error('Error update state:', err);
                        evt.from.appendChild(evt.item);
                    });
                }
            });
        });

        function clearAllKanbanColumns() {
            document.querySelectorAll('.kanban-task-list').forEach(list => list.innerHTML = '');
        }

        function loadTasks(projectId) {
            clearAllKanbanColumns();
            fetch(`${fetchTasksUrl}?project_id=${projectId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        res.data.forEach(task => {
                            const taskList = document.getElementById(`list-${task.status}`);
                            if (taskList) taskList.appendChild(createCardElement(task));
                        });
                    }
                })
                .catch(err => console.error("Gagal memuat task:", err));
        }

        // ==========================================
        // 5. QUICK-ADD TASK (TAMBAH TUGAS)
        // ==========================================
        document.querySelectorAll('.add-card-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                const footer = e.currentTarget.closest('.kanban-footer');
                footer.querySelector('.add-card-btn').classList.add('hidden');
                const form = footer.querySelector('.add-card-form');
                form.classList.remove('hidden');
                form.querySelector('input[name="title"]').focus();
            });
        });

        document.querySelectorAll('.cancel-add-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                const form = e.currentTarget.closest('.add-card-form');
                form.classList.add('hidden');
                form.reset();
                form.closest('.kanban-footer').querySelector('.add-card-btn').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.add-card-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const title = this.querySelector('input[name="title"]').value.trim();
                const assignSelect = this.querySelector('select[name="user_id"]');
                const assigneeId = assignSelect ? assignSelect.value : null;
                const state = this.dataset.state;
                
                if (!title || !currentProjectId) return alert('Judul tugas wajib diisi.');

                const taskList = this.closest('.kanban-column').querySelector('.kanban-task-list');

                fetch(storeTaskUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        project_id: currentProjectId, 
                        title: title, 
                        status: state,
                        assignee_id: assigneeId
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        taskList.appendChild(createCardElement(res.data));
                        form.reset();
                        form.classList.add('hidden');
                        form.closest('.kanban-footer').querySelector('.add-card-btn').classList.remove('hidden');
                    } else alert(res.message || 'Gagal menambah task.');
                })
                .catch(err => console.error('Error simpan task:', err));
            });
        });

        function createCardElement(task) {
            const card = document.createElement('div');
            card.className = 'kanban-task bg-white p-3 rounded-lg shadow cursor-grab active:cursor-grabbing';
            
            // Set Atribut Data
            card.dataset.id = task.id;
            card.dataset.title = task.title;
            card.dataset.description = task.description || '';
            card.dataset.dateStart = task.startdate ? task.startdate.split('T')[0] : '';
            card.dataset.dateEnd = task.enddate ? task.enddate.split('T')[0] : '';
            card.dataset.assigneeId = task.assignee_id || '';

            let authorName = 'N/A';
            let authorPhoto = AppConfig.defaultAvatarUrl;

            // Render Assignee (Karyawan yang ditugaskan) jika ada
            if (task.assignee) {
                authorName = task.assignee.nama_lengkap;
                if (task.assignee.foto) authorPhoto = AppConfig.storageBaseUrl + task.assignee.foto;
            }

            let dateHtml = ''; 
            if (task.enddate) {
                dateHtml = `<div class="task-due-date mt-1 text-xs text-gray-500"><i class="far fa-calendar-alt mr-1"></i>Selesai: ${new Date(task.enddate).toLocaleDateString('id-ID')}</div>`;
            }

            card.innerHTML = `
                <div class="flex justify-between items-start space-x-2 pointer-events-none">
                    <div class="flex-grow min-w-0">
                        <h6 class="font-semibold text-gray-800 truncate">${task.title}</h6>
                        ${dateHtml}
                    </div>
                    ${task.assignee_id ? `
                    <div class="flex-shrink-0">
                        <img src="${authorPhoto}" alt="${authorName}" title="${authorName}" class="w-8 h-8 rounded-full object-cover border border-gray-300">
                    </div>` : ''}
                </div>
            `;
            return card;
        }

        // ==========================================
        // 6. MODAL DETAIL TASK & AKTIVITAS HARIAN
        // ==========================================
        document.addEventListener('click', function(e) {
            const card = e.target.closest('.kanban-task');
            if (card) openTaskModal(card);
        });

        function renderDailyActivities(activities, container) {
            container.innerHTML = '';
            if (!activities || activities.length === 0) {
                container.innerHTML = `<p class="text-gray-500 text-sm italic">Belum ada aktivitas harian.</p>`;
                return;
            }

            activities.forEach(act => {
                let authorName = 'N/A';
                let authorPhoto = AppConfig.defaultAvatarUrl;

                // PERBAIKAN: Objek 'act.user' sekarang adalah data dari tabel Karyawan
                if (act.user) {
                    authorName = act.user.nama_lengkap || 'N/A';
                    if (act.user.foto) {
                        authorPhoto = AppConfig.storageBaseUrl + act.user.foto;
                    }
                }

                // Format Tanggal
                let dateOptions = { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                let date = new Date(act.activity_date || act.created_at).toLocaleDateString('id-ID', dateOptions);
                
                // Pewarnaan Badge Status
                let statusColor = 'bg-gray-200 text-gray-800';
                if(act.status === 'Selesai') statusColor = 'bg-green-100 text-green-800';
                else if(act.status === 'On Progres') statusColor = 'bg-blue-100 text-blue-800';
                else if(act.status === 'On Progres Dilanjutkan Besok') statusColor = 'bg-yellow-100 text-yellow-800';
                else if(act.status === 'Gagal') statusColor = 'bg-red-100 text-red-800';

                // Tautan Dokumen
                let docHtml = act.doc ? `<a href="${AppConfig.storageBaseUrl.replace('posts/', '')}${act.doc}" target="_blank" class="text-blue-500 hover:text-blue-700 hover:underline text-xs mt-2 inline-block font-medium"><i class="fas fa-paperclip mr-1"></i>Lihat Dokumen Terlampir</a>` : '';

                // Render Elemen
                let html = `
                    <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                        <div class="flex justify-between items-start mb-1">
                            <div class="flex items-center space-x-2">
                                <img src="${authorPhoto}" class="w-6 h-6 rounded-full object-cover border border-gray-200">
                                <div>
                                    <p class="text-xs font-bold text-gray-800">${authorName}</p>
                                    <p class="text-[10px] text-gray-500">${date}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold ${statusColor}">${act.status}</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-2 leading-relaxed">${act.activity}</p>
                        ${docHtml}
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
        }

        function loadTaskActivities(taskId) {
            const dailyContainer = document.getElementById('dailyActivityContainer');
            dailyContainer.innerHTML = `<p class="text-gray-500 text-sm italic">Memuat aktivitas...</p>`;
            
            fetch(`${tasksBaseUrl}/${taskId}/activities`)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        renderDailyActivities(res.data, dailyContainer);
                    } else {
                        dailyContainer.innerHTML = `<p class="text-gray-500 text-sm italic">Belum ada aktivitas.</p>`;
                    }
                })
                .catch(err => {
                    dailyContainer.innerHTML = `<p class="text-red-500 text-sm">Gagal memuat aktivitas.</p>`;
                });
        }

        function openTaskModal(card) {
            const isPM = (currentUserKaryawanId === currentPmId);
            const isAssignee = (currentUserKaryawanId === card.dataset.assigneeId);
            taskForm.dataset.taskId = card.dataset.id;
            
            // Tampilkan Data PM (Pembuat Task)
            const selOption = projectSelect.options[projectSelect.selectedIndex];
            document.getElementById('detail_pm_name').textContent = selOption.dataset.pmName || 'N/A';
            document.getElementById('detail_pm_photo').src = selOption.dataset.pmPhoto || AppConfig.defaultAvatarUrl;

            // Set Data Form Utama
            taskForm.querySelector('input[name="title"]').value = card.dataset.title;
            taskForm.querySelector('textarea[name="description"]').value = card.dataset.description;
            taskForm.querySelector('input[name="startdate"]').value = card.dataset.dateStart;
            taskForm.querySelector('input[name="enddate"]').value = card.dataset.dateEnd;
            
            const detailUserSelect = document.getElementById('detail_user_id');
            if (detailUserSelect) detailUserSelect.value = card.dataset.assigneeId;

            // Otorisasi Visibilitas Tombol/Form
            const inputs = taskForm.querySelectorAll('input:not(#new_activity_file), textarea:not(#new_activity_text), select:not(#new_activity_status)');
            inputs.forEach(el => {
                el.disabled = !isPM;
            });
            document.getElementById('btnSaveDetail').style.display = isPM ? 'inline-block' : 'none';
            document.getElementById('deleteTaskBtn').style.display = isPM ? 'inline-block' : 'none';
            
            // Sembunyikan Form Tambah Aktivitas jika bukan Assignee
            document.getElementById('addActivitySection').style.display = isAssignee ? 'block' : 'none';

            // Muat Riwayat Aktivitas Harian
            loadTaskActivities(card.dataset.id);

            taskModal.classList.remove('hidden');
        }

        document.getElementById('closeTaskModalBtn').addEventListener('click', () => taskModal.classList.add('hidden'));

        // Simpan Perubahan Detail Task (Hanya PM)
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const taskId = this.dataset.taskId;
            if (!taskId) return;
            
            const btnSave = document.getElementById('btnSaveDetail');
            btnSave.disabled = true;
            btnSave.textContent = 'Menyimpan...';

            fetch(`${tasksBaseUrl}/${taskId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    title: this.querySelector('input[name="title"]').value,
                    description: this.querySelector('textarea[name="description"]').value,
                    startdate: this.querySelector('input[name="startdate"]').value || null,
                    enddate: this.querySelector('input[name="enddate"]').value || null,
                    assignee_id: document.getElementById('detail_user_id')?.value || null
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    loadTasks(currentProjectId); // Muat ulang kartu board
                    taskModal.classList.add('hidden');
                } else alert(res.message || 'Gagal menyimpan perubahan.');
            })
            .finally(() => {
                btnSave.textContent = 'Simpan Perubahan';
                btnSave.disabled = false;
            });
        });

        // Hapus Task (Hanya PM)
        document.getElementById('deleteTaskBtn').addEventListener('click', function() {
            const taskId = taskForm.dataset.taskId;
            if (!taskId || !confirm('Hapus tugas ini secara permanen?')) return;

            fetch(`${tasksBaseUrl}/${taskId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    document.querySelector(`.kanban-task[data-id="${taskId}"]`)?.remove();
                    taskModal.classList.add('hidden');
                } else alert(res.message || 'Gagal menghapus task.');
            });
        });

        // Submit Aktivitas Harian Baru (Menggunakan FormData)
        document.getElementById('btnSubmitActivity')?.addEventListener('click', function() {
            const taskId = taskForm.dataset.taskId;
            if (!taskId) return;

            const text = document.getElementById('new_activity_text').value.trim();
            if (!text) return alert('Deskripsi aktivitas wajib diisi.');

            const formData = new FormData();
            formData.append('activity', text);
            formData.append('status', document.getElementById('new_activity_status').value);
            
            const fileInput = document.getElementById('new_activity_file');
            if (fileInput.files.length > 0) formData.append('doc', fileInput.files[0]);

            const btn = this;
            btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';

            fetch(`${tasksBaseUrl}/${taskId}/activities`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    document.getElementById('new_activity_text').value = '';
                    fileInput.value = '';
                    loadTaskActivities(taskId); // Segarkan list aktivitas secara instan
                } else alert(res.message || 'Gagal menambah aktivitas.');
            })
            .finally(() => {
                btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Simpan Aktivitas Baru';
            });
        });

    });
</script>
@endsection