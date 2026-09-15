@extends('layouts_office.app')

@section('office_contents')
<div class="container-fluid">

    <div id="alertBox"></div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Pilih Data yang Di-hide</h5>
        <a href="{{ route('office.rekapRkm') }}" class="btn btn-secondary btn-sm">
          Kembali
        </a>
    </div>

    {{-- CARD FILTER + SEARCH --}}
    <div class="card mb-3">
        <div class="card-header">
            <strong>Filter Rekap RKM</strong>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('office.rekapRkm.select') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tipe Filter</label>
                    <select name="filter_type" id="filter_type" class="form-select">
                        <option value="bulan" {{ $filterType == 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                        <option value="triwulan" {{ $filterType == 'triwulan' ? 'selected' : '' }}>Per Triwulan</option>
                        <option value="tahun" {{ $filterType == 'tahun' ? 'selected' : '' }}>Sepanjang Tahun</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y = now()->year - 5; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2" id="wrapper_bulan" style="{{ $filterType != 'bulan' ? 'display:none;' : '' }}">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $label)
                            <option value="{{ $val }}" {{ (string)$bulan === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2" id="wrapper_triwulan" style="{{ $filterType != 'triwulan' ? 'display:none;' : '' }}">
                    <label class="form-label">Triwulan</label>
                    <select name="triwulan" class="form-select">
                        <option value="1" {{ (string)$triwulan === '1' ? 'selected' : '' }}>Triwulan 1</option>
                        <option value="2" {{ (string)$triwulan === '2' ? 'selected' : '' }}>Triwulan 2</option>
                        <option value="3" {{ (string)$triwulan === '3' ? 'selected' : '' }}>Triwulan 3</option>
                        <option value="4" {{ (string)$triwulan === '4' ? 'selected' : '' }}>Triwulan 4</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama materi / perusahaan" value="{{ $search }}">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD TABEL SELECT HIDE --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Pilih Data yang Di-hide</strong>
            <small class="text-muted">Perubahan tersimpan otomatis saat checkbox diklik</small>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCheckAll">Pilih Semua (halaman ini)</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUncheckAll">Batal Semua (halaman ini)</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="checkAllHeader">
                            </th>
                            <th>#</th>
                            <th>Periode Mulai</th>
                            <th>Periode Selesai</th>
                            <th>Nama Materi</th>
                            <th>Nama Perusahaan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($peluang as $item)
                            <tr data-id="{{ $item->id }}">
                                <td>
                                    <input type="checkbox"
                                           class="chk-hide"
                                           data-id="{{ $item->id }}"
                                           {{ optional($item->rkm)->hide ? 'checked' : '' }}>
                                </td>
                                <td>{{ $peluang->firstItem() + $loop->index }}</td>
                                <td>{{ $item->periode_mulai ?? '-' }}</td>
                                <td>{{ $item->periode_selesai ?? '-' }}</td>
                                <td>{{ optional(optional($item->rkm)->materi)->nama_materi ?? '-' }}</td>
                                <td>{{ optional(optional($item->rkm)->perusahaan)->nama_perusahaan ?? '-' }}</td>
                                <td>
                                    <span class="badge status-badge {{ optional($item->rkm)->hide ? 'bg-secondary' : 'bg-success' }}">
                                        {{ optional($item->rkm)->hide ? 'Hide' : 'Show' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data untuk filter/pencarian ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <small class="text-muted">
                    Menampilkan {{ $peluang->firstItem() ?? 0 }}-{{ $peluang->lastItem() ?? 0 }} dari {{ $peluang->total() }} data
                </small>
                {{ $peluang->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';

    // Toggle field bulan/triwulan sesuai filter_type dipilih
    const filterType = document.getElementById('filter_type');
    const wrapperBulan = document.getElementById('wrapper_bulan');
    const wrapperTriwulan = document.getElementById('wrapper_triwulan');

    filterType.addEventListener('change', function () {
        if (this.value === 'bulan') {
            wrapperBulan.style.display = '';
            wrapperTriwulan.style.display = 'none';
        } else if (this.value === 'triwulan') {
            wrapperBulan.style.display = 'none';
            wrapperTriwulan.style.display = '';
        } else {
            wrapperBulan.style.display = 'none';
            wrapperTriwulan.style.display = 'none';
        }
    });

    function showAlert(message, type = 'success') {
        const alertBox = document.getElementById('alertBox');
        alertBox.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        setTimeout(() => { alertBox.innerHTML = ''; }, 2500);
    }

    function updateRowBadge(id, hide) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;
        const badge = row.querySelector('.status-badge');
        badge.textContent = hide ? 'Hide' : 'Show';
        badge.classList.remove('bg-secondary', 'bg-success');
        badge.classList.add(hide ? 'bg-secondary' : 'bg-success');
    }

    // Toggle satu baris via AJAX, langsung tersimpan
    document.querySelectorAll('.chk-hide').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const id = this.dataset.id;
            const hide = this.checked;

            fetch("{{ route('office.rekapRkm.toggleHide') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ peluang_id: id, hide: hide }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateRowBadge(id, hide);
                    showAlert('Status berhasil diperbarui.');
                } else {
                    this.checked = !hide;
                    showAlert('Gagal memperbarui status.', 'danger');
                }
            })
            .catch(() => {
                this.checked = !hide;
                showAlert('Terjadi kesalahan koneksi.', 'danger');
            });
        });
    });

    // Pilih Semua / Batal Semua — hanya untuk baris di halaman yang sedang tampil
    function bulkToggle(hide) {
        const checkboxes = document.querySelectorAll('.chk-hide');
        const ids = Array.from(checkboxes).map(cb => cb.dataset.id);

        if (!ids.length) return;

        fetch("{{ route('office.rekapRkm.bulkToggleHide') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids: ids, hide: hide }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                checkboxes.forEach(cb => {
                    cb.checked = hide;
                    updateRowBadge(cb.dataset.id, hide);
                });
                showAlert(`${data.count} data berhasil diperbarui.`);
            } else {
                showAlert('Gagal memperbarui data.', 'danger');
            }
        })
        .catch(() => showAlert('Terjadi kesalahan koneksi.', 'danger'));
    }

    document.getElementById('btnCheckAll').addEventListener('click', () => bulkToggle(true));
    document.getElementById('btnUncheckAll').addEventListener('click', () => bulkToggle(false));

    document.getElementById('checkAllHeader').addEventListener('change', function () {
        bulkToggle(this.checked);
    });
});
</script>
@endsection