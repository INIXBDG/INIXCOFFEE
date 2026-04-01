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
    {{-- Header & Project Selector --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
        <h4 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-columns mr-2 text-blue-500"></i>Papan Kanban
        </h4>
        <div class="w-full md:w-1/3">
            <select class="shadow appearance-none border border-blue-400 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" id="filter_project_id" name="filter_project_id">
                <option value="">-- Pilih Proyek Fase Teknis --</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
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

{{-- Modal Detail Task --}}
<div id="taskDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-[60]">
  <div class="relative mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
    <form id="taskDetailForm">
      <div class="flex justify-between items-center border-b pb-3">
        <h5 class="text-lg font-semibold">Detail Task</h5>
        <button type="button" id="closeTaskModalBtn" class="text-gray-400 hover:text-gray-600">&times;</button>
      </div>

      <div class="mt-4 space-y-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Dibuat oleh</label>
          <div class="flex items-center space-x-3">
            <img class="task-author-photo w-10 h-10 rounded-full bg-gray-200 object-cover" src="{{ $defaultAvatarUrl }}" alt="Author photo">
            <p class="task-author-name text-gray-800 font-medium">N/A</p>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
          <input type="text" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="flex space-x-4">
            <div class="w-1/2">
              <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
              <input type="date" name="date_start" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-1/2">
              <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Selesai</label>
              <input type="date" name="date_end" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
          <textarea name="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" placeholder="Tuliskan deskripsi..."></textarea>
        </div>
      </div>

        <div class="mt-6">
        <label class="block text-gray-700 text-sm font-bold mb-2">Aktivitas Harian</label>
        <div id="dailyActivityContainer" class="border rounded-md p-3 bg-gray-50 max-h-60 overflow-y-auto space-y-3">
            <p class="text-gray-500 text-sm italic">Memuat aktivitas...</p>
        </div>
        </div>

      <div class="flex justify-between items-center pt-4 border-t mt-4">
        <button type="button" id="deleteTaskBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150">
          <i class="fas fa-trash-alt mr-1"></i> Hapus
        </button>

        <button type="submit" id="btnSaveDetail" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150">
          Simpan Perubahan
        </button>
      </div>
    </form>
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
        // Konfigurasi Endpoint (Akan dihubungkan ke Controller di langkah selanjutnya)
        const updateStateUrl = "{{ url('/projects/kanban/tasks/update-state') }}";
        const storeTaskUrl = "{{ url('/projects/kanban/tasks') }}";
        const tasksBaseUrl = "{{ url('/projects/kanban/tasks') }}"; 
        const fetchTasksUrl = "{{ url('/projects/kanban/get-tasks') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        let currentProjectId = null;

        // === Penanganan Dropdown Proyek ===
        const projectSelect = document.getElementById('filter_project_id');
        const emptyState = document.getElementById('emptyState');
        const kanbanBoardWrapper = document.getElementById('kanbanBoardWrapper');

        projectSelect.addEventListener('change', function() {
            currentProjectId = this.value;

            if (currentProjectId) {
                emptyState.classList.add('hidden');
                kanbanBoardWrapper.classList.remove('hidden');
                btnKelolaTim.classList.remove('hidden'); // ✅ tampilkan tombol
                loadTasks(currentProjectId);
            } else {
                emptyState.classList.remove('hidden');
                kanbanBoardWrapper.classList.add('hidden');
                btnKelolaTim.classList.add('hidden'); // ✅ sembunyikan tombol
                clearAllKanbanColumns();
            }
        });

        // === Inisialisasi SortableJS ===
        document.querySelectorAll('.kanban-task-list').forEach(list => {
            new Sortable(list, {
                group: 'kanban',
                animation: 150,
                onEnd: function (evt) {
                    const taskEl = evt.item;
                    const targetListEl = evt.to;
                    const taskId = taskEl.dataset.id;
                    const newState = targetListEl.dataset.state;

                    fetch(updateStateUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ id: taskId, state: newState })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert('❌ ' + (data.message || 'Gagal memperbarui status.'));
                        }
                    }).catch(err => {
                        console.error('Error saat update state:', err);
                    });
                }
            });
        });

        // === Fungsi Pengambilan Data Utama (AJAX Load) ===
        function loadTasks(projectId) {
            clearAllKanbanColumns();
            fetch(`${fetchTasksUrl}?project_id=${projectId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        res.data.forEach(task => {
                            const taskList = document.getElementById(`list-${task.status}`);
                            if (taskList) {
                                const cardElement = createCardElement(task);
                                taskList.appendChild(cardElement);
                            }
                        });
                    }
                })
                .catch(err => console.error("Gagal memuat task:", err));
        }

        function clearAllKanbanColumns() {
            document.querySelectorAll('.kanban-task-list').forEach(list => {
                list.innerHTML = '';
            });
        }

        // === Logika Quick-Add (Menyertakan project_id) ===
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
                resetQuickAddForm(form);
            });
        });

        function resetQuickAddForm(form) {
            const footer = form.closest('.kanban-footer');
            form.classList.add('hidden');
            form.reset();
            footer.querySelector('.add-card-btn').classList.remove('hidden');
        }

        document.querySelectorAll('.add-card-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const titleInput = this.querySelector('input[name="title"]');
                const title = titleInput.value.trim();
                const state = this.dataset.state;
                
                if (!title || !currentProjectId) {
                    alert('Judul tugas tidak boleh kosong dan proyek harus dipilih.');
                    return;
                }

                const taskList = this.closest('.kanban-column').querySelector('.kanban-task-list');

                fetch(storeTaskUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        project_id: currentProjectId, 
                        title: title, 
                        description: null, 
                        status: state 
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        const cardElement = createCardElement(res.data);
                        taskList.appendChild(cardElement);
                        resetQuickAddForm(form);
                    } else {
                        alert('Gagal menambah task.');
                    }
                })
                .catch(err => {
                    console.error('Error simpan task:', err);
                });
            });
        });

        // === Pembuatan Elemen Kartu Task (Render HTML Client-side) ===
        function createCardElement(task) {
            const card = document.createElement('div');
            card.className = 'kanban-task bg-white p-3 rounded-lg shadow cursor-grab active:cursor-grabbing';

            let authorName = 'N/A';
            let authorPhoto = AppConfig.defaultAvatarUrl;

            if (task.user) {
                authorName = task.user.name || 'N/A';
                if (task.user.karyawan) {
                    authorName = task.user.karyawan.nama_lengkap || authorName;
                    if (task.user.karyawan.foto) {
                        authorPhoto = AppConfig.storageBaseUrl + task.user.karyawan.foto;
                    }
                }
            }

            // Perbarui data attributes
            card.dataset.id = task.id;
            card.dataset.title = task.title;
            card.dataset.description = task.description || '';
            card.dataset.dateStart = task.date_start ? task.date_start.split('T')[0] : '';
            card.dataset.dateEnd = task.date_end ? task.date_end.split('T')[0] : '';
            card.dataset.authorName = authorName;
            card.dataset.authorPhoto = authorPhoto;

            // Bangun Struktur Card
            const topRowDiv = document.createElement('div');
            topRowDiv.className = 'flex justify-between items-start space-x-2';
            const leftColDiv = document.createElement('div');
            leftColDiv.className = 'flex-grow min-w-0';
            const titleWrapper = document.createElement('div');
            titleWrapper.className = 'task-title-wrapper';
            titleWrapper.innerHTML = `
                <h6 class="task-title font-semibold text-gray-800 cursor-pointer p-1 truncate">${task.title}</h6>
                <input type="text" class="task-title-input hidden w-full border border-blue-500 rounded px-3 py-1 text-sm" value="${task.title}" />
            `;
            leftColDiv.appendChild(titleWrapper);

            let dateHtml = ''; 
            if (task.date_end) {
                const dateObj = new Date(task.date_end);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                dateHtml = `<div class="task-due-date mt-1 text-xs text-gray-500 pointer-events-none flex items-center"><i class="far fa-calendar-alt mr-1"></i><span>Selesai: ${formattedDate}</span></div>`;
            } else if (task.date_start) {
                const dateObj = new Date(task.date_start);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                dateHtml = `<div class="task-due-date mt-1 text-xs text-gray-500 pointer-events-none flex items-center"><i class="far fa-calendar-alt mr-1"></i><span>Mulai: ${formattedDate}</span></div>`;
            }
            if (dateHtml) { leftColDiv.insertAdjacentHTML('beforeend', dateHtml); }
            topRowDiv.appendChild(leftColDiv);
            
            if (task.user) {
                const rightColDiv = document.createElement('div');
                rightColDiv.className = 'flex-shrink-0';
                const authorImg = document.createElement('img');
                authorImg.src = authorPhoto;
                authorImg.alt = authorName;
                authorImg.title = authorName;
                authorImg.className = 'w-8 h-8 rounded-full object-cover border border-gray-300';
                rightColDiv.appendChild(authorImg);
                topRowDiv.appendChild(rightColDiv);
            }
            card.appendChild(topRowDiv);

            return card;
        }

        // === Logika Edit Judul Inline ===
        document.addEventListener('dblclick', function(e) {
            if (e.target.matches('.task-title')) {
                const wrapper = e.target.closest('.task-title-wrapper');
                const titleEl = wrapper.querySelector('.task-title');
                const inputEl = wrapper.querySelector('.task-title-input');

                inputEl.dataset.originalTitle = titleEl.textContent.trim();
                titleEl.classList.add('hidden');
                inputEl.classList.remove('hidden');
                inputEl.focus();
                inputEl.select();
            }
        });

        document.addEventListener('blur', function(e) {
            if (e.target.matches('.task-title-input')) {
                saveTitleChange(e.target);
            }
        }, true);

        document.addEventListener('keydown', function(e) {
            if (e.target.matches('.task-title-input')) {
                if (e.key === 'Enter') {
                    saveTitleChange(e.target);
                } else if (e.key === 'Escape') {
                    cancelTitleChange(e.target);
                }
            }
        });

        function cancelTitleChange(inputEl) {
            const wrapper = inputEl.closest('.task-title-wrapper');
            const titleEl = wrapper.querySelector('.task-title');
            inputEl.value = titleEl.textContent.trim();
            inputEl.classList.add('hidden');
            titleEl.classList.remove('hidden');
        }

        function saveTitleChange(inputEl) {
            const wrapper = inputEl.closest('.task-title-wrapper');
            const titleEl = wrapper.querySelector('.task-title');
            const card = inputEl.closest('.kanban-task');
            const taskId = card.dataset.id;
            const newTitle = inputEl.value.trim();
            const originalTitle = titleEl.textContent.trim();

            if (!newTitle || newTitle === originalTitle) {
                cancelTitleChange(inputEl);
                return;
            }

            titleEl.textContent = newTitle;
            inputEl.classList.add('hidden');
            titleEl.classList.remove('hidden');
            card.dataset.title = newTitle;

            fetch(`${tasksBaseUrl}/${taskId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ title: newTitle })
            })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    alert('Gagal memperbarui judul.');
                    titleEl.textContent = originalTitle;
                    card.dataset.title = originalTitle;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan.');
                titleEl.textContent = originalTitle;
                card.dataset.title = originalTitle;
            });
        }

        // === Logika Modal Detail Task ===
        const taskModal = document.getElementById('taskDetailModal');
        const taskForm = document.getElementById('taskDetailForm');
        const closeTaskModalBtn = document.getElementById('closeTaskModalBtn');

        document.addEventListener('click', function(e) {
            const card = e.target.closest('.kanban-task');
            if (card && !e.target.closest('.task-title-wrapper') && !e.target.matches('.task-title-input')) {
                openTaskModal(card);
            }
        });

        function openTaskModal(card) {
            taskForm.dataset.taskId = card.dataset.id;
            taskForm.querySelector('input[name="title"]').value = card.dataset.title;
            taskForm.querySelector('textarea[name="description"]').value = card.dataset.description;
            taskForm.querySelector('input[name="date_start"]').value = card.dataset.dateStart;
            taskForm.querySelector('input[name="date_end"]').value = card.dataset.dateEnd;
            taskModal.querySelector('.task-author-name').textContent = card.dataset.authorName;
            taskModal.querySelector('.task-author-photo').src = card.dataset.authorPhoto;

            taskModal.classList.remove('hidden');
        }

        function closeTaskModal() {
            taskModal.classList.add('hidden');
            taskForm.reset();
            delete taskForm.dataset.taskId;
        }

        closeTaskModalBtn.addEventListener('click', closeTaskModal);
        taskModal.addEventListener('click', (e) => (e.target === taskModal) && closeTaskModal());

        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const taskId = this.dataset.taskId;
            if (!taskId) return;
            
            const btnSave = document.getElementById('btnSaveDetail');
            const updateData = {
                title: this.querySelector('input[name="title"]').value,
                description: this.querySelector('textarea[name="description"]').value,
                date_start: this.querySelector('input[name="date_start"]').value || null,
                date_end: this.querySelector('input[name="date_end"]').value || null
            };

            btnSave.textContent = 'Menyimpan...';
            btnSave.disabled = true;

            fetch(`${tasksBaseUrl}/${taskId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(updateData)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    loadTasks(currentProjectId); // Refresh ulang task dari server untuk sinkronisasi
                    closeTaskModal();
                } else {
                    alert('Gagal menyimpan perubahan.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan.');
            })
            .finally(() => {
                btnSave.textContent = 'Simpan Perubahan';
                btnSave.disabled = false;
            });
        });

        // === Logika Hapus Task ===
        const deleteBtn = document.getElementById('deleteTaskBtn');
        deleteBtn.addEventListener('click', function() {
            const taskId = taskForm.dataset.taskId;
            if (!taskId) return;

            if (!confirm('Apakah Anda yakin ingin menghapus task ini?')) return;

            fetch(`${tasksBaseUrl}/${taskId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const card = document.querySelector(`.kanban-task[data-id="${taskId}"]`);
                    if (card) card.remove();
                    closeTaskModal();
                } else {
                    alert('Gagal menghapus task.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan saat menghubungi server.');
            });
        });
        // === Logika Manajemen Tim ===
        const btnKelolaTim = document.getElementById('btnKelolaTim');
        const teamModal = document.getElementById('teamModal');
        
        // Inisialisasi Select2
        $('#select_team_members').select2({
            placeholder: "Cari dan pilih karyawan...",
            dropdownParent: $('#teamModal'),
            ajax: {
                url: "{{ route('getUserProject') }}", // Rute global untuk mencari karyawan
                dataType: 'json',
                delay: 250,
                processResults: function(response){
                    return {
                        results: $.map(response.data, function(item){
                            return { 
                                id: item.kode_karyawan, 
                                text: item.nama_lengkap 
                            }
                        })
                    }
                }
            }
        });

        // Event membuka modal tim
        btnKelolaTim.addEventListener('click', function() {
            if (!currentProjectId) return;
            
            // Muat anggota tim saat ini
            fetch(`/projects/kanban/${currentProjectId}/team-members`)
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        $('#select_team_members').empty();
                        res.data.forEach(member => {
                            let option = new Option(member.text, member.id, true, true);
                            $('#select_team_members').append(option);
                        });
                        $('#select_team_members').trigger('change');
                    }
                })
                .catch(err => console.error("Gagal memuat tim:", err));
                
            teamModal.classList.remove('hidden');
        });

        // Menutup modal tim
        document.querySelectorAll('.close-modal[data-modal="teamModal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                teamModal.classList.add('hidden');
            });
        });

        // Submit form tim
        $('#formAssignTeam').on('submit', function(e) {
            e.preventDefault();
            if (!currentProjectId) return;

            const formData = $(this).serialize(); // Serialize menggunakan jQuery karena pakai Select2
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            fetch(`/projects/kanban/${currentProjectId}/assign-team`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('Tim berhasil diperbarui.');
                    teamModal.classList.add('hidden');
                } else {
                    alert(res.message || 'Gagal memperbarui tim.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan sistem.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan Tim';
            });
        });

        
    
    });
</script>
@endsection