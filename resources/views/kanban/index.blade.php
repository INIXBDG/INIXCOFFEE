@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-kanban-fill me-2 text-primary"></i> Kanban Task Board
        </h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Task
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="kanban-board"></div>
        </div>
    </div>
</div>

{{-- ✅ Modal Bootstrap Tambah Task --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addTaskForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTaskLabel">Tambah Task Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Nama Task</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan deskripsi singkat..."></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Status Awal</label>
            <select name="state" class="form-select" required>
                <option value="todo">To Do</option>
                <option value="inprogress">In Progress</option>
                <option value="done">Done</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- Bootstrap & jKanban --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/jkanban@1.3.1/dist/jkanban.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jkanban@1.3.1/dist/jkanban.min.js"></script>

{!! $kanban->scripts() !!}

<script>
document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const title = this.title.value.trim();
    const description = this.description.value.trim();
    const state = this.state.value;

    if (!title) return alert('Nama task wajib diisi.');

    fetch('/tasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title, description, state })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert('Gagal menambah task.');
        }
    });
});
</script>
@endsection
