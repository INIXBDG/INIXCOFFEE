@extends('layouts.app')

@section('content')
@php
    $defaultAvatarUrl = "https://placehold.co/40x40/E2E8F0/94A3B8?text=AV";
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

<div class="container mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h4 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-columns mr-2 text-blue-500"></i> Activity
        </h4>
    </div>

    {{-- Papan Kanban (Container untuk Kolom) --}}
    <div class="kanban-board-container flex space-x-4 overflow-x-auto pb-4">

        @foreach ($boards as $board)
            {{-- Kolom --}}
            <div class="kanban-column flex flex-col flex-shrink-0 w-80 flex-grow bg-gray-100 rounded-lg shadow-md h-[80vh]">

                {{-- Header Kolom --}}
                <div class="p-4 border-b-4 border-{{ $board['color'] }}-400 flex-shrink-0">
                    <h5 class="font-semibold text-gray-700">{{ $board['title'] }}</h5>
                </div>

                {{-- List Task --}}
                <div
                    class="kanban-task-list p-4 space-y-3 flex-grow overflow-y-auto"
                    data-state="{{ $board['key'] }}"
                >
                    {{-- Loop Task --}}
                    @foreach ($tasks[$board['key']] ?? [] as $task)
                        @php
                            $authorName = optional(optional($task->user)->karyawan)->nama_lengkap ?? (optional($task->user)->name ?? 'N/A');
                            $authorPhoto = (optional(optional($task->user)->karyawan)->foto)
                                ? asset('storage/posts/' . $task->user->karyawan->foto)
                                : $defaultAvatarUrl;
                            // Anda tidak perlu mendefinisikan $latestActivity di sini lagi
                        @endphp

                        {{-- Kartu Task --}}
                        <div
                            class="kanban-task bg-white p-3 rounded-lg shadow cursor-grab active:cursor-grabbing"
                            data-id="{{ $task->id }}"
                            data-title="{{ $task->title }}"
                            data-description="{{ $task->description ?? '' }}"
                            data-date-start="{{ $task->date_start ? $task->date_start->format('Y-m-d') : '' }}"
                            data-date-end="{{ $task->date_end ? $task->date_end->format('Y-m-d') : '' }}"
                            data-author-name="{{ $authorName }}"
                            data-author-photo="{{ $authorPhoto }}"
                        >
                            {{-- Baris Atas: Judul, Tanggal, Foto --}}
                            <div class="flex justify-between items-start space-x-2">
                                <div class="flex-grow min-w-0">
                                    <div class="task-title-wrapper">
                                        <h6 class="task-title font-semibold text-gray-800 cursor-pointer p-1 truncate">{{ $task->title }}</h6>
                                        <input type="text" class="task-title-input hidden w-full border border-blue-500 rounded px-3 py-1 text-sm" value="{{ $task->title }}" />
                                    </div>
                                    @if($task->date_end || $task->date_start)
                                    <div class="task-due-date mt-1 text-xs text-gray-500 pointer-events-none flex items-center">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        @if($task->date_end) <span>Selesai: {{ $task->date_end->format('d M') }}</span>
                                        @elseif($task->date_start) <span>Mulai: {{ $task->date_start->format('d M') }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                @if($task->user)
                                <div class="flex-shrink-0">
                                    <img src="{{ $authorPhoto }}" alt="{{ $authorName }}" title="{{ $authorName }}" class="w-8 h-8 rounded-full object-cover border border-gray-300">
                                </div>
                                @endif
                            </div>

                            {{-- ✅ TIMELINE AKTIVITAS TERAKHIR (KONDISI DIPERBAIKI) --}}
                            {{-- Periksa langsung relasi pada $task --}}
                            @if($task->latestActivity)
                            <div class="task-timeline mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500 space-y-1">
                                @php
                                    // Definisikan variabel lokal di sini setelah @if
                                    $latestActivity = $task->latestActivity;
                                    $timelineEvents = [];
                                    if ($latestActivity->on_progress_at) $timelineEvents[] = ['label' => 'Mulai', 'time' => \Carbon\Carbon::parse($latestActivity->on_progress_at), 'color' => 'blue'];
                                    if ($latestActivity->on_progress_next_day_at) $timelineEvents[] = ['label' => 'Lanjut Besok', 'time' => \Carbon\Carbon::parse($latestActivity->on_progress_next_day_at), 'color' => 'yellow'];
                                    if ($latestActivity->failed_at) $timelineEvents[] = ['label' => 'Gagal', 'time' => \Carbon\Carbon::parse($latestActivity->failed_at), 'color' => 'red'];
                                    if ($latestActivity->completed_at) $timelineEvents[] = ['label' => 'Selesai', 'time' => \Carbon\Carbon::parse($latestActivity->completed_at), 'color' => 'green'];
                                    usort($timelineEvents, fn($a, $b) => $a['time'] <=> $b['time']);
                                @endphp
                                @foreach($timelineEvents as $event)
                                    <div class="flex items-center">
                                        <span class="w-2 h-2 rounded-full bg-{{ $event['color'] }}-500 mr-2 flex-shrink-0"></span>
                                        <span>{{ $event['label'] }} - {{ $event['time']->isoFormat('D MMM, HH:mm') }}</span>
                                    </div>
                                @endforeach
                                {{-- Fallback ke status saat ini --}}
                                @if(empty($timelineEvents) && $latestActivity->status)
                                    @php
                                        $fallbackColor = 'gray';
                                        if($latestActivity->status == 'On Progres') $fallbackColor = 'blue';
                                        if($latestActivity->status == 'On Progres Dilanjutkan Besok') $fallbackColor = 'yellow';
                                    @endphp
                                    <div class="flex items-center italic">
                                         <span class="w-2 h-2 rounded-full bg-{{ $fallbackColor }}-400 mr-2 flex-shrink-0"></span>
                                         <span>{{ $latestActivity->status }}</span>
                                    </div>
                                @endif
                            </div>
                            @endif
                            {{-- AKHIR TIMELINE --}}

                        </div>
                    @endforeach
                </div>

                {{-- Footer Quick-Add --}}
                <div class="kanban-footer p-3 border-t border-gray-200 flex-shrink-0">
                    <button class="add-card-btn text-gray-500 hover:text-gray-700 hover:bg-gray-200 w-full text-left p-2 rounded-md transition duration-150">
                        <i class="fas fa-plus mr-2"></i> Add a card
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

{{-- Modal Detail Task (Untuk Klik Sekali) --}}
<div id="taskDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden z-50">
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
            {{-- JavaScript akan mengisi 'src' gambar ini --}}
            <img class="task-author-photo w-10 h-10 rounded-full bg-gray-200 object-cover" src="https://placehold.co/40x40/E2E8F0/94A3B8?text=AV" alt="Author photo">
            {{-- JavaScript akan mengisi nama lengkap di sini --}}
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
      <div class="flex justify-end pt-4 border-t mt-4">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>


{{-- Script CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
    const AppConfig = {
        storageBaseUrl: "{{ asset('storage/posts/') }}/",
        defaultAvatarUrl: "{{ $defaultAvatarUrl }}"
    };
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Ambil URL dan token dari Blade
        const updateStateUrl = "{{ route('tasks.update-state') }}";
        const storeTaskUrl = "{{ route('tasks.store') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const tasksBaseUrl = "{{ url('tasks') }}"; // Untuk update /tasks/{id}

        // === Inisialisasi SortableJS ===
        document.querySelectorAll('.kanban-task-list').forEach(list => {
            new Sortable(list, {
                group: 'kanban', // Memungkinkan drag antar list
                animation: 150,
                onEnd: function (evt) {
                    // Fungsi ini dipanggil setelah drag-and-drop selesai
                    const taskEl = evt.item;
                    const targetListEl = evt.to;
                    const taskId = taskEl.dataset.id;
                    const newState = targetListEl.dataset.state;

                    // Kirim perubahan state ke server
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
                    });
                }
            });
        });

        // === FUNGSI HELPER ===
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

            // --- Bangun Struktur Card ---
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
            let dateHtml = ''; // Buat HTML tanggal
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
            if (task.user) { // Buat Kolom Kanan (Foto)
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
            card.appendChild(topRowDiv); // Masukkan baris atas ke card

            // ✅ TAMBAHKAN TIMELINE AKTIVITAS TERAKHIR (jika ada)
            // Laravel defaultnya mengubah camelCase ke snake_case di JSON, jadi akses latest_activity
            const latestActivity = task.latest_activity;
            if (latestActivity) {
                const timelineDiv = document.createElement('div');
                timelineDiv.className = 'task-timeline mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500 space-y-1';

                const timelineEvents = [];
                if (latestActivity.on_progress_at) timelineEvents.push({ label: 'Mulai', time: latestActivity.on_progress_at, color: 'blue' });
                if (latestActivity.on_progress_next_day_at) timelineEvents.push({ label: 'Lanjut Besok', time: latestActivity.on_progress_next_day_at, color: 'yellow' });
                if (latestActivity.failed_at) timelineEvents.push({ label: 'Gagal', time: latestActivity.failed_at, color: 'red' });
                if (latestActivity.completed_at) timelineEvents.push({ label: 'Selesai', time: latestActivity.completed_at, color: 'green' });

                timelineEvents.sort((a, b) => new Date(a.time) - new Date(b.time));

                if (timelineEvents.length > 0) {
                    timelineEvents.forEach(event => {
                        const eventTime = new Date(event.time);
                        // Format: 24 Okt, 10:48
                        const formattedTime = eventTime.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute: '2-digit'});
                        timelineDiv.innerHTML += `
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-${event.color}-500 mr-2 flex-shrink-0"></span>
                                <span>${event.label} - ${formattedTime}</span>
                            </div>
                        `;
                    });
                } else if (latestActivity.status) { // Fallback ke status saat ini
                    let fallbackColor = 'gray';
                    if(latestActivity.status == 'On Progres') fallbackColor = 'blue';
                    if(latestActivity.status == 'On Progres Dilanjutkan Besok') fallbackColor = 'yellow';
                     timelineDiv.innerHTML += `
                        <div class="flex items-center italic">
                             <span class="w-2 h-2 rounded-full bg-${fallbackColor}-400 mr-2 flex-shrink-0"></span>
                             <span>${latestActivity.status}</span>
                        </div>
                    `;
                }
                card.appendChild(timelineDiv); // Tambahkan timeline ke card
            }

            return card;
        }
        /**
         * Mereset dan menyembunyikan form quick-add.
         */
        function resetQuickAddForm(form) {
            const footer = form.closest('.kanban-footer');
            form.classList.add('hidden');
            form.reset();
            footer.querySelector('.add-card-btn').classList.remove('hidden');
        }

        // === Logika Quick-Add ===

        // Tampilkan form
        document.querySelectorAll('.add-card-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                const footer = e.currentTarget.closest('.kanban-footer');
                footer.querySelector('.add-card-btn').classList.add('hidden');
                const form = footer.querySelector('.add-card-form');
                form.classList.remove('hidden');
                form.querySelector('input[name="title"]').focus();
            });
        });

        // Tombol "Cancel" (X)
        document.querySelectorAll('.cancel-add-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                const form = e.currentTarget.closest('.add-card-form');
                resetQuickAddForm(form);
            });
        });

        // Submit form quick-add
        document.querySelectorAll('.add-card-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const titleInput = this.querySelector('input[name="title"]');
                const title = titleInput.value.trim();
                const state = this.dataset.state;
                if (!title) return;
                const taskList = this.closest('.kanban-column').querySelector('.kanban-task-list');

                fetch(storeTaskUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ title: title, description: null, state: state })
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
                });
            });
        });

        // === Logika Edit Judul Inline ===

        // Double-click untuk edit
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

        // Klik di luar (blur) untuk simpan
        document.addEventListener('blur', function(e) {
            if (e.target.matches('.task-title-input')) {
                saveTitleChange(e.target);
            }
        }, true);

        // Tombol Enter/Escape
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

            // Optimistic UI Update
            titleEl.textContent = newTitle;
            inputEl.classList.add('hidden');
            titleEl.classList.remove('hidden');
            card.dataset.title = newTitle; // Update data-attribute juga

            const updateTitleUrl = `${tasksBaseUrl}/${taskId}`;

            fetch(updateTitleUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ title: newTitle })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    card.dataset.title = res.data.title;
                } else {
                    alert('Gagal memperbarui judul.');
                    titleEl.textContent = originalTitle; // Revert
                    card.dataset.title = originalTitle;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan.');
                titleEl.textContent = originalTitle; // Revert
                card.dataset.title = originalTitle;
            });
        }

        // === Logika Modal Detail Task ===

        const taskModal = document.getElementById('taskDetailModal');
        const taskForm = document.getElementById('taskDetailForm');
        const closeTaskModalBtn = document.getElementById('closeTaskModalBtn');

        // Buka Modal (Klik sekali)
        document.addEventListener('click', function(e) {
            const card = e.target.closest('.kanban-task');
            if (card && !e.target.closest('.task-title-wrapper') && !e.target.matches('.task-title-input')) {
                openTaskModal(card);
            }
        });

        // Mengisi data modal
        function openTaskModal(card) {
            taskForm.dataset.taskId = card.dataset.id;

            // Isi form task
            taskForm.querySelector('input[name="title"]').value = card.dataset.title;
            taskForm.querySelector('textarea[name="description"]').value = card.dataset.description;
            taskForm.querySelector('input[name="date_start"]').value = card.dataset.dateStart;
            taskForm.querySelector('input[name="date_end"]').value = card.dataset.dateEnd;
            taskModal.querySelector('.task-author-name').textContent = card.dataset.authorName;
            taskModal.querySelector('.task-author-photo').src = card.dataset.authorPhoto;

            // === Bagian Daily Activity ===
            const dailyContainer = document.getElementById('dailyActivityContainer');
            dailyContainer.innerHTML = `<p class="text-gray-500 text-sm italic">Memuat aktivitas...</p>`;

            const taskId = card.dataset.id;
            fetch(`${tasksBaseUrl}/${taskId}/activities`)
                .then(res => res.json())
                .then(activities => {
                    if (!activities || activities.length === 0) {
                        dailyContainer.innerHTML = `<p class="text-gray-500 text-sm italic">Belum ada aktivitas harian.</p>`;
                        return;
                    }

                    dailyContainer.innerHTML = '';

                    renderDailyActivities(
                        activities,
                        dailyContainer,
                        `<p class="text-gray-500 text-sm italic">Belum ada aktivitas harian.</p>`
                    );
                })
                .catch(err => {
                    console.error(err);
                    dailyContainer.innerHTML = `<p class="text-red-500 text-sm">Gagal memuat aktivitas.</p>`;
                });

            // Tampilkan modal
            taskModal.classList.remove('hidden');
        }

        function renderDailyActivities(activities, container, emptyHTML) {
            container.innerHTML = ''; // Hapus loading/pesan lama

            if (!activities || activities.length === 0) {
                container.innerHTML = emptyHTML; // Tampilkan pesan kosong
                return;
            }

            const storageBase = AppConfig.storageBaseUrl.replace('/posts/', ''); // Hapus /posts/ jika perlu

            activities.forEach(activity => {
                const activityItem = document.createElement('div');
                activityItem.className = 'flex space-x-3 border-b border-gray-200 pb-3 mb-3 last:border-b-0 last:pb-0 last:mb-0';

                // --- Foto Kiri ---
                let activityAuthor = 'N/A';
                let activityAuthorPhoto = AppConfig.defaultAvatarUrl;
                if (activity.user) {
                    activityAuthor = activity.user.name || 'N/A';
                    if (activity.user.karyawan) {
                        activityAuthor = activity.user.karyawan.nama_lengkap || activityAuthor;
                        if (activity.user.karyawan.foto) {
                            activityAuthorPhoto = AppConfig.storageBaseUrl + activity.user.karyawan.foto;
                        }
                    }
                }
                const authorHtml = `
                    <div class="flex-shrink-0">
                        <img src="${activityAuthorPhoto}" alt="${activityAuthor}" title="${activityAuthor}" class="w-10 h-10 rounded-full object-cover border border-gray-300">
                    </div>
                `;

                // --- Konten Kanan ---
                const contentDiv = document.createElement('div');
                contentDiv.className = 'flex-1 min-w-0';

                // Info Tanggal & Status Badge
                const dateObj = new Date(activity.activity_date);
                const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year:'numeric' });
                let statusClass = 'bg-gray-100 text-gray-600';
                switch (activity.status) { /* ... logika switch status ... */ }
                const statusBadge = `<span class="py-0.5 px-2 rounded-full text-xs font-medium ${statusClass}">${activity.status || 'N/A'}</span>`;

                // Link Dokumen
                let docLink = '';
                if (activity.doc) { /* ... logika buat docLink ... */ }

                // --- Bangun Timeline Status ---
                let timelineHtml = '';
                const timelineEvents = [];
                if (activity.on_progress_at) timelineEvents.push({ label: 'Mulai', time: activity.on_progress_at, color: 'blue' });
                if (activity.on_progress_next_day_at) timelineEvents.push({ label: 'Lanjut Besok', time: activity.on_progress_next_day_at, color: 'yellow' });
                if (activity.failed_at) timelineEvents.push({ label: 'Gagal', time: activity.failed_at, color: 'red' });
                if (activity.completed_at) timelineEvents.push({ label: 'Selesai', time: activity.completed_at, color: 'green' });
                timelineEvents.sort((a, b) => new Date(a.time) - new Date(b.time));

                if (timelineEvents.length > 0) {
                    timelineHtml = '<div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500 space-y-1">'; // Div pembungkus timeline
                    timelineEvents.forEach(event => {
                        const eventTime = new Date(event.time);
                        const formattedTime = eventTime.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute: '2-digit'});
                        timelineHtml += `
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-${event.color}-500 mr-2 flex-shrink-0" title="${event.label}"></span>
                                <span>${event.label} - ${formattedTime}</span>
                            </div>
                        `;
                    });
                    timelineHtml += '</div>';
                }
                // --- Akhir Timeline Status ---

                // ✅ Gabungkan HTML Konten Kanan secara Eksplisit
                contentDiv.innerHTML = `
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <p class="text-sm text-gray-800 font-medium">${activityAuthor}</p>
                            <p class="text-xs text-gray-500">${formattedDate}</p>
                        </div>
                        ${statusBadge}
                    </div>
                    <p class="text-sm text-gray-700 mt-1">${activity.activity || '(Tidak ada aktivitas)'}</p>
                    ${activity.description ? `<p class="text-xs text-gray-500 mt-1 italic">${activity.description}</p>` : ''}
                    ${docLink}
                    ${timelineHtml} {{-- Pastikan variabel ini dimasukkan --}}
                `;

                // Gabungkan Foto (Kiri) dan Konten (Kanan)
                activityItem.innerHTML = authorHtml;
                activityItem.appendChild(contentDiv);

                container.appendChild(activityItem);
            });
        }

        // Menutup modal
        function closeTaskModal() {
            taskModal.classList.add('hidden');
            taskForm.reset();
            delete taskForm.dataset.taskId;
        }

        closeTaskModalBtn.addEventListener('click', closeTaskModal);
        taskModal.addEventListener('click', (e) => (e.target === taskModal) && closeTaskModal());

        // Submit form modal (simpan perubahan)
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const taskId = this.dataset.taskId;
            if (!taskId) return;

            const updateData = {
                title: this.querySelector('input[name="title"]').value,
                description: this.querySelector('textarea[name="description"]').value,
                date_start: this.querySelector('input[name="date_start"]').value || null,
                date_end: this.querySelector('input[name="date_end"]').value || null
            };

            const updateTaskUrl = `${tasksBaseUrl}/${taskId}`;

            fetch(updateTaskUrl, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(updateData)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const card = document.querySelector(`.kanban-task[data-id="${taskId}"]`);
                    if (card) {
                        const newCard = createCardElement(res.data);
                        card.innerHTML = newCard.innerHTML;

                        // Update data attributes di card utamanya
                        let authorName = 'N/A';
                        let authorPhoto = AppConfig.defaultAvatarUrl;
                        if (res.data.user) {
                            authorName = res.data.user.name || 'N/A';
                            if (res.data.user.karyawan) {
                                authorName = res.data.user.karyawan.nama_lengkap || authorName;
                                if (res.data.user.karyawan.foto) {
                                    authorPhoto = AppConfig.storageBaseUrl + res.data.user.karyawan.foto;
                                }
                            }
                        }

                        card.dataset.title = res.data.title;
                        card.dataset.description = res.data.description || '';
                        card.dataset.dateStart = res.data.date_start ? res.data.date_start.split('T')[0] : '';
                        card.dataset.dateEnd = res.data.date_end ? res.data.date_end.split('T')[0] : '';
                        card.dataset.authorName = authorName;
                        card.dataset.authorPhoto = authorPhoto;
                    }
                    closeTaskModal();
                } else {
                    alert('Gagal menyimpan perubahan. Cek konsol.');
                    console.error('Error:', res.errors);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Terjadi kesalahan.');
            });
        });

    });
</script>
@endsection
