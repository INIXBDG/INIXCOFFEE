@extends('databasekpi.berandaKPI')

@section('contentKPI')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .task-card {
        transition: transform 0.2s;
        border-left: 4px solid #0d6efd;
    }

    .task-card.status-done {
        border-left-color: #198754;
    }

    .task-card.status-cancelled {
        border-left-color: #dc3545;
    }

    .task-card.status-in-progress {
        border-left-color: #ffc107;
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
    }

    .freq-badge {
        font-size: 0.75rem;
    }

    .status-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .radio-custom {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .radio-custom label {
        position: relative;
        padding-left: 30px;
        cursor: pointer;
        font-size: 0.9rem;
        user-select: none;
    }

    .radio-custom input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .radio-custom .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: #f1f1f1;
        border-radius: 50%;
        border: 2px solid #0d6efd;
    }

    .radio-custom input:checked~.checkmark {
        background-color: #0d6efd;
    }

    .radio-custom .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .radio-custom input:checked~.checkmark:after {
        display: block;
    }

    .radio-custom .checkmark:after {
        top: 50%;
        left: 50%;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: white;
        transform: translate(-50%, -50%);
    }

    .task-scroll-container {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
        padding-top: 15px;
    }
</style>

<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-file-document"></i>
            </span> Project
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span> Control Tugas
                    <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Buat, progreskan, dan selesaikan tugas disini agar memenuhi kebutuhan KPI.">
                    </i>
                </li>
            </ul>
        </nav>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2></h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal">
            <i class="fas fa-plus me-1"></i> Tambah Tugas
        </button>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h5 class="mb-0" id="totalTasks">0</h5>
                    <small>Total Tugas</small>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-0" id="completedTasks">0</h5>
                    <small>Selesai</small>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-0" id="progressPercent">0%</h5>
                    <small>Persentase</small>
                </div>
                <div class="col-md-3">
                    <h5 class="mb-0" id="activeTasks">0</h5>
                    <small>Aktif</small>
                </div>
            </div>
            <div class="progress mt-3 mb-3">
                <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="radio-custom">
                <label>
                    <input type="radio" name="timeFilter" value="all" checked> Semua
                    <span class="checkmark"></span>
                </label>
                <label>
                    <input type="radio" name="timeFilter" value="today"> Hari Ini
                    <span class="checkmark"></span>
                </label>
                <label>
                    <input type="radio" name="timeFilter" value="week"> Minggu Ini
                    <span class="checkmark"></span>
                </label>
                <label>
                    <input type="radio" name="timeFilter" value="month"> Bulan Ini
                    <span class="checkmark"></span>
                </label>
                <label>
                    <input type="radio" name="timeFilter" value="quarter"> Kuartal Ini
                    <span class="checkmark"></span>
                </label>
                <label>
                    <input type="radio" name="timeFilter" value="year"> Tahun Ini
                    <span class="checkmark"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="task-scroll-container">
        <div id="taskList" class="row g-3">
        </div>
    </div>

    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Buat Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="taskForm">
                        <input type="hidden" id="taskId">
                        <div class="mb-3">
                            <label class="form-label">Judul Tugas</label>
                            <input type="text" class="form-control" id="taskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="taskDesc" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="startDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="endDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Frekuensi</label>
                            <select class="form-select" id="frequency" required>
                                <option value="harian">Harian</option>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="tahunan">Tahunan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Pencapaian</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="targetType" value="tahunan" id="targetTahunan" checked>
                                <label class="form-check-label" for="targetTahunan">Target Tahunan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="targetType" value="rinci" id="targetRinci">
                                <label class="form-check-label" for="targetRinci">Target Per Minggu/Bulan/Tahun</label>
                            </div>
                        </div>
                        <div id="detailedTargetFields" class="d-none">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="number" class="form-control" id="targetWeekly" placeholder="Target/Minggu (%)">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" id="targetMonthly" placeholder="Target/Bulan (%)">
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control" id="targetYearly" placeholder="Target/Tahun (%)">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveTaskBtn">Simpan Tugas</button>
                </div>
            </div>
        </div>
    </div>
</div>
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-info me-3',
        },
    });
</script>
@endif

@if (session('error'))

<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif

@if ($errors->any())

<script>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        customClass: {
            confirmButton: 'btn btn-gradient-danger me-3',
        },
    });
</script>
@endif
@endsection
@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment-with-locales.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let tasks = JSON.parse(localStorage.getItem('tasks')) || [];

    const taskList = document.getElementById('taskList');
    const taskForm = document.getElementById('taskForm');
    const saveTaskBtn = document.getElementById('saveTaskBtn');
    const modalTitle = document.getElementById('modalTitle');
    const detailedTargetFields = document.getElementById('detailedTargetFields');
    const targetRinci = document.getElementById('targetRinci');

    targetRinci.addEventListener('change', () => {
        detailedTargetFields.classList.toggle('d-none');
    });

    document.querySelectorAll('input[name="timeFilter"]').forEach(radio => {
        radio.addEventListener('change', renderTasks);
    });

    function renderTasks() {
        const filter = document.querySelector('input[name="timeFilter"]:checked').value;
        let filteredTasks = [...tasks];

        const now = new Date();
        const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const startOfWeek = new Date(startOfDay);
        startOfWeek.setDate(startOfDay.getDate() - startOfDay.getDay());
        const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        const currentQuarter = Math.floor(now.getMonth() / 3);
        const startOfQuarter = new Date(now.getFullYear(), currentQuarter * 3, 1);
        const startOfYear = new Date(now.getFullYear(), 0, 1);

        if (filter !== 'all') {
            filteredTasks = tasks.filter(task => {
                const taskDate = new Date(task.startDate);
                if (filter === 'today') {
                    return taskDate >= startOfDay;
                } else if (filter === 'week') {
                    return taskDate >= startOfWeek;
                } else if (filter === 'month') {
                    return taskDate >= startOfMonth;
                } else if (filter === 'quarter') {
                    return taskDate >= startOfQuarter;
                } else if (filter === 'year') {
                    return taskDate >= startOfYear;
                }
                return true;
            });
        }

        filteredTasks.sort((a, b) => {
            if (a.status === 'done' && b.status !== 'done') return 1;
            if (b.status === 'done' && a.status !== 'done') return -1;
            return 0;
        });

        taskList.innerHTML = '';
        if (filteredTasks.length === 0) {
            taskList.innerHTML = `<div class="col-12"><div class="alert alert-info text-center">Belum ada tugas. Tambahkan tugas pertama Anda!</div></div>`;
            updateSummary(filteredTasks);
            return;
        }

        filteredTasks.forEach(task => {
            const card = document.createElement('div');
            card.className = `col-md-6 col-lg-4 task-card status-${task.status}`;
            card.innerHTML = `
            <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="card-title mb-1">${task.title}</h6>
            <span class="badge ${getFreqBadgeClass(task.frequency)} freq-badge">${task.frequency}</span>
            </div>
            <p class="text-muted small mb-2">${task.description || ''}</p>
            <div class="mt-auto">
            <div class="d-flex justify-content-between text-muted small mb-1">
            <span>${formatDate(task.startDate)} – ${formatDate(task.endDate)}</span>
            </div>
            <div class="d-flex align-items-center mb-2">
            <span class="status-dot" style="background-color: ${getStatusColor(task.status)}"></span>
            <small>${getStatusText(task.status)}</small>
            </div>
            <div class="progress progress-sm mb-2">
            <div class="progress-bar ${getStatusProgressBarClass(task.status)}" role="progressbar" style="width: ${getProgressWidth(task.status)}%"></div>
            </div>
            <div class="d-flex justify-content-between mt-2">
            <button class="btn btn-sm btn-outline-success btn-done" data-id="${task.id}" ${task.status === 'done' ? 'disabled' : ''}>
            <i class="fas fa-check"></i> Selesai
            </button>
            <button class="btn btn-sm btn-outline-danger btn-cancel" data-id="${task.id}" ${task.status === 'cancelled' ? 'disabled' : ''}>
            <i class="fas fa-times"></i> Batalkan
            </button>
            </div>
            </div>
            </div>
            </div>
            `;
            taskList.appendChild(card);
        });

        document.querySelectorAll('.btn-done').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.btn-done').dataset.id;
                updateTaskStatus(id, 'done');
            });
        });

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.btn-cancel').dataset.id;
                updateTaskStatus(id, 'cancelled');
            });
        });

        updateSummary(filteredTasks);
    }

    function getFreqBadgeClass(freq) {
        const map = {
            harian: 'bg-primary',
            mingguan: 'bg-success',
            bulanan: 'bg-info',
            tahunan: 'bg-warning text-dark'
        };
        return map[freq] || 'bg-secondary';
    }

    function getStatusColor(status) {
        const map = {
            pending: '#6c757d',
            'in-progress': '#ffc107',
            done: '#198754',
            cancelled: '#dc3545'
        };
        return map[status] || '#6c757d';
    }

    function getStatusText(status) {
        const map = {
            pending: 'Belum Dimulai',
            'in-progress': 'Sedang Dikerjakan',
            done: 'Selesai',
            cancelled: 'Dibatalkan'
        };
        return map[status] || 'Tidak Diketahui';
    }

    function getStatusProgressBarClass(status) {
        if (status === 'done') return 'bg-success';
        if (status === 'cancelled') return 'bg-danger';
        return 'bg-warning';
    }

    function getProgressWidth(status) {
        if (status === 'done') return 100;
        if (status === 'cancelled') return 0;
        return 50;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID');
    }

    function updateTaskStatus(id, status) {
        const task = tasks.find(t => t.id == id);
        if (task) {
            task.status = status;
            task.updatedAt = new Date().toISOString();
            saveTasks();
            renderTasks();
        }
    }

    function saveTasks() {
        localStorage.setItem('tasks', JSON.stringify(tasks));
    }

    function updateSummary(filteredTasks) {
        const total = filteredTasks.length;
        const completed = filteredTasks.filter(t => t.status === 'done').length;
        const active = filteredTasks.filter(t => !['done', 'cancelled'].includes(t.status)).length;
        const percent = total ? Math.round((completed / total) * 100) : 0;

        document.getElementById('totalTasks').textContent = total;
        document.getElementById('completedTasks').textContent = completed;
        document.getElementById('activeTasks').textContent = active;
        document.getElementById('progressPercent').textContent = `${percent}%`;
        document.getElementById('progressBar').style.width = `${percent}%`;
    }

    saveTaskBtn.addEventListener('click', () => {
        const id = document.getElementById('taskId').value || Date.now().toString();
        const task = {
            id,
            title: document.getElementById('taskTitle').value,
            description: document.getElementById('taskDesc').value,
            startDate: document.getElementById('startDate').value,
            endDate: document.getElementById('endDate').value,
            frequency: document.getElementById('frequency').value,
            targetType: document.querySelector('input[name="targetType"]:checked').value,
            targetWeekly: document.getElementById('targetWeekly')?.value || '',
            targetMonthly: document.getElementById('targetMonthly')?.value || '',
            targetYearly: document.getElementById('targetYearly')?.value || '',
            status: 'pending',
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString()
        };

        if (document.getElementById('taskId').value) {
            const index = tasks.findIndex(t => t.id === id);
            if (index !== -1) tasks[index] = task;
        } else {
            tasks.push(task);
        }

        saveTasks();
        renderTasks();
        const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
        modal.hide();
        taskForm.reset();
        document.getElementById('taskId').value = '';
        detailedTargetFields.classList.add('d-none');
    });

    document.getElementById('startDate').valueAsDate = new Date();
    document.getElementById('endDate').valueAsDate = new Date(new Date().setDate(new Date().getDate() + 7));

    renderTasks();
</script>
@endsection