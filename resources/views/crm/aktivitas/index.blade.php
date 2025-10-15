@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
        $sales = Auth::user()->id_sales;
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Activity Management</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal"
                    onclick="resetForm()" @if (in_array(Auth::user()->jabatan, $allowedUser)) disabled @endif>
                    Tambah Aktivitas
                </button>
            </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <!-- Jenis Aktivitas -->
                            <div class="col-md-3">
                                <label for="filter_aktivitas" class="form-label">Jenis Aktivitas</label>
                                <select id="filter_aktivitas" class="form-select">
                                    <option value="">Semua</option>
                                    <option value="Call">Call</option>
                                    <option value="Email">Email</option>
                                    <option value="Visit">Visit</option>
                                    <option value="Meet">Meeting</option>
                                    <option value="Incharge">Incharge Inhouse</option>
                                    <option value="PA">Penawaran Awal</option>
                                    <option value="PI">Penawaran Internal</option>
                                    <option value="Telemarketing">Telemarketing</option>
                                    <option value="Form_Masuk">Form Masuk</option>
                                    <option value="Form_Keluar">Form Keluar</option>
                                    <option value="DB">DB</option>
                                    <option value="Contact">Contact</option>
                                </select>
                            </div>

                            <!-- Rentang Waktu Aktivitas -->
                            <div class="col-md-3">
                                <label for="filter_waktu_start" class="form-label">Waktu Aktivitas (Mulai)</label>
                                <input type="date" id="filter_waktu_start" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_waktu_end" class="form-label">Waktu Aktivitas (Selesai)</label>
                                <input type="date" id="filter_waktu_end" class="form-control">
                            </div>

                        @if(in_array(Auth::user()->jabatan, $allowedUser))
                            <!-- Rentang Created At -->
                            <div class="col-md-3">
                                <label for="filter_created_start" class="form-label">Dibuat Dari</label>
                                <input type="date" id="filter_created_start" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_created_end" class="form-label">Dibuat Sampai</label>
                                <input type="date" id="filter_created_end" class="form-control">
                            </div>
                        @endif
                            <div class="col-md-2">
                                <button id="btnFilter" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btnResetFilter" class="btn btn-outline-secondary w-100">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Tabel Aktivitas -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="aktivitasTable" class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Client</th>
                                    <th>Sales</th>
                                    <th style="text-align: center;">Jenis Aktivitas</th>
                                    <th>Deskripsi</th>
                                    <th style="text-align: center;">Waktu Aktivitas</th>
                                    <th style="text-align: center;">Pax</th>
                                    <th style="text-align: center;">Harga</th>
                                    <th style="text-align: center;">Total</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4" id="salesTargetWrapper">
                <!-- Card sales akan muncul di sini -->
            </div>

            <!-- Modal untuk Create Aktivitas -->
            <div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="activityModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="activityForm" action="{{ route('store.aktivitas.new') }}" method="POST">
                                @csrf
                                <input type="hidden" id="contact_type" name="contact_type" value="contact">

                                {{-- Dropdown perusahaan Klien --}}
                                <div class="mb-3">
                                    <label class="form-label" for="id_perusahaan">Nama Perusahaan</label>
                                    <select class="form-select" id="id_perusahaan" name="id_perusahaan" required>
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach ($perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Nama Kontak</label>
                                    <select class="form-select" id="id_contact" name="id_contact" required>
                                    </select>
                                </div>

                                {{-- Input Manual untuk Kontak Baru --}}
                                <div id="newContactFields"
                                    style="display: none; border: 1px solid #ddd; border-radius: 8px; padding: 15px; background-color: #f8f9fa;">
                                    <h6 class="mb-3">Tambah Kontak Baru</h6>

                                    <div class="mb-3">
                                        <label class="form-label" for="nama_perusahaan">Nama</label>
                                        <input type="text" class="form-control" id="nama_perusahaan"
                                            name="nama_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="email_perusahaan">Email</label>
                                        <input type="email" class="form-control" id="email_perusahaan"
                                            name="email_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="divisi_perusahaan">Divisi</label>
                                        <input type="text" class="form-control" id="divisi_perusahaan"
                                            name="divisi_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="cp_perusahaan">Contact Person (No)</label>
                                        <input type="text" class="form-control" id="cp_perusahaan"
                                            name="cp_perusahaan">
                                    </div>
                                </div>

                                {{-- Jenis Aktivitas --}}
                                <div class="mb-3">
                                    <label class="form-label" for="aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="aktivitas" name="aktivitas" required>
                                        <option value="" disabled selected>Pilih Jenis Aktivitas</option>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                        <option value="Meet">Meeting</option>
                                        <option value="Incharge">Incharge Inhouse</option>
                                        <option value="PA">Penawaran Awal</option>
                                        <option value="PI">Penawaran Internal</option>
                                        <option value="Telemarketing">Telemarketing</option>
                                        <option value="Form_Masuk">Regis Form Masuk</option>
                                        <option value="Form_Keluar">Regis Form Keluar</option>
                                    </select>
                                </div>

                                <div class="hidden-container" id="hidden-container" style="display: none;">
                                    {{-- Pax --}}
                                    <div class="mb-3">
                                        <label for="pax">Jumlah Pax</label>
                                        <input type="number" id="pax" name="pax" class="form-control"
                                            min="1">
                                    </div>

                                    {{-- Harga --}}
                                    <div class="mb-3">
                                        <label for="harga">Harga per Pax</label>
                                        <input type="text" id="harga" name="harga" class="form-control">
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                                </div>

                                {{-- Waktu Aktivitas --}}
                                <div class="mb-3">
                                    <label class="form-label" for="waktu_aktivitas">Waktu Aktivitas</label>
                                    <input type="date" class="form-control" id="waktu_aktivitas"
                                        name="waktu_aktivitas" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal untuk Edit Aktivitas -->
            <div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <form id="editActivityForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editActivityModalLabel">Edit Aktivitas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" for="edit_id_contact_display">Nama Kontak</label>
                                    <input type="text" class="form-control" id="edit_id_contact_display" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="edit_aktivitas" name="aktivitas" required>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                        <option value="Meet">Meeting</option>
                                        <option value="Incharge">Incharge Inhouse</option>
                                        <option value="PA">Penawaran Awal</option>
                                        <option value="PI">Penawaran Internal</option>
                                        <option value="Telemarketing">Telemarketing</option>
                                        <option value="Form_Masuk">Form Masuk</option>
                                        <option value="Form_Keluar">Form Keluar</option>
                                    </select>
                                </div>

                                <div class="hidden-container" id="edit-hidden-container" style="display: none;">
                                    <div class="mb-3">
                                        <label for="edit_pax">Jumlah Pax</label>
                                        <input type="number" id="edit_pax" name="pax" class="form-control"
                                            min="1">
                                    </div>

                                    <div class="mb-3">
                                        <label for="edit_harga">Harga per Pax</label>
                                        <input type="text" id="edit_harga" name="harga" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="edit_deskripsi" name="deskripsi"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_waktu_aktivitas">Waktu Aktivitas</label>
                                    <input type="date" class="form-control" id="edit_waktu_aktivitas"
                                        name="waktu_aktivitas" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Perbarui</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // ===============================
        // 🔹 Fungsi Format Angka (IDR)
        // ===============================
        function formatNumber(value) {
            if (!value) return '';
            const number = parseFloat(value.toString().replace(/[^0-9.-]+/g, '')) || 0;
            return number.toLocaleString('id-ID');
        }

        function unformatNumber(value) {
            if (!value) return '';
            return parseFloat(value.toString().replace(/[^0-9.-]+/g, '')) || '';
        }

        // ===============================
        // 🔹 DataTable & Form Events
        // ===============================
        $(document).ready(function() {
            // === DataTable ===
            $('#aktivitasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('index.aktivitas.json') }}',
                    type: 'GET',
                    data: function(d) {
                        d.filter_aktivitas = $('#filter_aktivitas').val();
                        d.filter_waktu_start = $('#filter_waktu_start').val();
                        d.filter_waktu_end = $('#filter_waktu_end').val();
                        d.filter_created_start = $('#filter_created_start').val();
                        d.filter_created_end = $('#filter_created_end').val();
                    },
                    dataSrc: 'data',
                    error: function(xhr, error, thrown) {
                        console.error('Error:', xhr.responseText);
                        alert('Gagal memuat data aktivitas: ' + thrown);
                    }
                },
                columns: [
                    { data: 'kontak' },
                    { data: 'id_sales' },
                    { data: 'aktivitas' },
                    { data: 'deskripsi' },
                    { data: 'waktu_aktivitas' },
                    { data: 'pax', render: d => d ? d : '-' },
                    { data: 'harga', render: d => d ? formatNumber(d) : '-' },
                    { data: 'total', render: d => d ? formatNumber(d) : '-' },
                    {
                        data: 'id',
                        render: function(id, type, row) {
                            const isDisabled = row.aktivitas === 'DB' || row.aktivitas === 'Contact';
                            return `
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-warning"
                                        ${isDisabled ? 'disabled' : ''}
                                        onclick='editAktivitas(${JSON.stringify(row)})'>Edit</button>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="hapusAktivitas(${id})">Hapus</button>
                                </div>`;
                        }
                    }
                ]
            });

            // === Filter & Reset ===
            $('#btnFilter').on('click', () => $('#aktivitasTable').DataTable().ajax.reload());
            $('#btnResetFilter').on('click', function() {
                $('#filter_aktivitas, #filter_waktu_start, #filter_waktu_end, #filter_created_start, #filter_created_end').val('');
                $('#aktivitasTable').DataTable().ajax.reload();
            });

            // === Select2 Init ===
            initPerusahaanSelect2();
            initContactSelect2();

            // === Harga Input Formatting ===
            $('#harga, #edit_harga').on('input change', function() {
                const val = unformatNumber(this.value);
                this.value = formatNumber(val);
            });

            // === Form Submit (Create) ===
            $('#activityForm').on('submit', function(e) {
                e.preventDefault();
                const originalValue = $('#harga').val();
                $('#harga').val(unformatNumber(originalValue));
                this.submit();
                $('#harga').val(formatNumber(originalValue));
            });

            // === Form Submit (Edit) ===
            $('#editActivityForm').on('submit', async function(e) {
                e.preventDefault();
                const id = $('#edit_id').val();
                const url = `/crm/aktivitas/update/${id}`;
                const data = {
                    id_perusahaan: $('#edit_id_perusahaan').val(),
                    id_contact: $('#edit_id_contact').val(),
                    aktivitas: $('#edit_aktivitas').val(),
                    deskripsi: $('#edit_deskripsi').val(),
                    waktu_aktivitas: $('#edit_waktu_aktivitas').val(),
                    pax: $('#edit_pax').val(),
                    harga: unformatNumber($('#edit_harga').val())
                };

                try {
                    const res = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });
                    if (!res.ok) throw new Error(await res.text());
                    const result = await res.json();
                    alert(result.message || 'Aktivitas berhasil diperbarui.');
                    bootstrap.Modal.getInstance(document.getElementById('editActivityModal')).hide();
                    $('#aktivitasTable').DataTable().ajax.reload();
                } catch (err) {
                    console.error(err);
                    alert('Terjadi kesalahan saat memperbarui aktivitas.');
                }
            });
        });

        // ===============================
        // 🔹 Fungsi Select2
        // ===============================
        function initPerusahaanSelect2() {
            const $select = $('#id_perusahaan');
            if (!$.fn.select2) return console.error('Select2 belum ter-load!');
            const $modal = $select.closest('.modal');
            $select.select2({ width: '100%', theme: 'bootstrap-5', dropdownParent: $modal.length ? $modal : $(document.body) });
        }

        function initContactSelect2() {
            const $select = $('#id_contact');
            if (!$.fn.select2) return console.error('Select2 belum ter-load!');
            const $modal = $select.closest('.modal');
            $select.select2({ width: '100%', theme: 'bootstrap-5', dropdownParent: $modal.length ? $modal : $(document.body) });
        }

        // ===============================
        // 🔹 Fungsi Load Semua Target Aktivitas (Dengan Chart)
        // ===============================
            async function loadSemuaTargetAktivitas() {
                try {
                    console.log("🚀 Memulai loadSemuaTargetAktivitas...");

                    const res = await fetch(`/crm/semua-target-aktivitas`);
                    console.log("📡 Status fetch:", res.status, res.statusText);

                    if (!res.ok) throw new Error("Gagal mengambil data target aktivitas");

                    const response = await res.json();
                    console.log("🧩 Data dari API:", response);

                    const wrapper = document.getElementById("salesTargetWrapper");
                    if (!wrapper) {
                        console.error("❌ Elemen #salesTargetWrapper tidak ditemukan!");
                        return;
                    }

                    wrapper.innerHTML = "";

                    let list = [];

                    // ✅ Gunakan id_sales untuk deteksi
                    if (Array.isArray(response)) {
                        console.log("👔 Mode manajemen (array sales) terdeteksi");
                        list = response;
                    } else if (response.id_sales) {
                        console.log("👤 Mode sales tunggal terdeteksi:", response.id_sales);
                        list = [response];
                    } else {
                        console.warn("⚠️ Data tidak memiliki id_sales, tidak dapat ditampilkan:", response);
                        wrapper.innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-warning text-center mb-0">
                                    Tidak ada data target aktivitas sales.
                                </div>
                            </div>`;
                        return;
                    }

                    console.log("📋 Data final untuk dirender:", list);

                    // 🔹 Layout wrapper
                    if (list.length > 1) {
                        wrapper.classList.add("row", "g-3");
                        wrapper.style.maxHeight = "";
                        wrapper.style.overflow = "";
                        console.log("💡 Layout: grid (manajemen)");
                    } else {
                        wrapper.classList.remove("row");
                        wrapper.style.maxHeight = "";
                        wrapper.style.overflow = "";
                        console.log("💡 Layout: tunggal (sales)");
                    }

                    // 🔹 Render setiap sales
                    list.forEach((sales, index) => {
                        console.log(`🧱 Render sales ke-${index + 1}:`, sales);

                        const items = sales.data || [];
                        if (items.length === 0) {
                            console.warn(`⚠️ Sales ${sales.id_sales} tidak punya data aktivitas.`);
                            return;
                        }

                        const progressBars = items.map(row => {
                            const jenis = row.jenis || "-";
                            const target = row.target ?? 0;
                            const realisasi = row.realisasi ?? 0;
                            const percent = row.percent ?? 0;
                            const deadline = row.deadline || "-";

                            let color = "#e0e0e0";
                            if (percent >= 100) color = "#4caf50";
                            else if (percent >= 50) color = "#2196f3";
                            else if (percent > 0) color = "#ffb300";

                            let deadlineColor = "#dc3545";
                            const today = new Date();
                            const [d, m, y] = deadline.split('/');
                            if (d && m && y) {
                                const deadlineDate = new Date(`${y}-${m}-${d}`);
                                if (deadlineDate >= today) deadlineColor = "#28a745";
                            }

                            return `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span>${jenis}: ${realisasi}/${target}</span>
                                        <span>${percent}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" style="width:${percent}%; background-color:${color};"></div>
                                    </div>
                                    <div class="mt-1 fw-bold" style="font-size: 0.9rem; color:${deadlineColor}">
                                        Deadline: ${deadline}
                                    </div>
                                </div>`;
                        }).join("");

                        const colClass = list.length > 1 ? "col-xl-3 col-lg-4 col-md-6 col-sm-12" : "col-12";
                        const cardScrollStyle = list.length > 1 ? `max-height: 250px; overflow-y: auto;` : "";

                        wrapper.innerHTML += `
                            <div class="${colClass}">
                                <div class="card shadow-sm border-0 rounded-3 h-100">
                                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0 text-primary fw-semibold">
                                            ${sales.sales || sales.id_sales}
                                        </h6>
                                    </div>
                                    <div class="card-body p-3" style="${cardScrollStyle}">
                                        ${progressBars}
                                    </div>
                                </div>
                            </div>`;
                    });

                    console.log("✅ Render selesai.");

                } catch (err) {
                    console.error("💥 ERROR:", err);
                    alert("Terjadi kesalahan saat memuat data target aktivitas.");
                }
            }


        // ===============================
        // 🔹 Fungsi Edit & Hapus Aktivitas (Tetap Sama)
        // ===============================
        function editAktivitas(row) {
            $('#edit_id').val(row.id);
            $('#edit_id_perusahaan').val(row.id_perusahaan);
            $('#edit_id_contact').val(row.id_contact);
            $('#edit_id_perusahaan_display').val(row.nama_perusahaan || '');
            $('#edit_id_contact_display').val(row.kontak || '');

            let aktivitasValue = row.aktivitas;
            const map = { 'Form Masuk': 'Form_Masuk', 'Form Keluar': 'Form_Keluar', 'Incharge Inhouse': 'Incharge' };
            aktivitasValue = map[aktivitasValue] || aktivitasValue;

            $('#edit_aktivitas').val(aktivitasValue);
            $('#edit_deskripsi').val(row.deskripsi);
            $('#edit_pax').val(row.pax || '');
            $('#edit_harga').val(row.harga ? formatNumber(row.harga) : '');

            const parts = (row.waktu_aktivitas || '').split('/');
            $('#edit_waktu_aktivitas').val(parts.length === 3 ? `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}` : '');

            const editHiddenContainer = document.getElementById('edit-hidden-container');
            if (['PA', 'Form_Masuk', 'Form_Keluar'].includes(aktivitasValue)) {
                editHiddenContainer.style.display = 'block';
            } else {
                editHiddenContainer.style.display = 'none';
                $('#edit_pax').val('');
                $('#edit_harga').val('');
            }

            new bootstrap.Modal(document.getElementById('editActivityModal')).show();
        }

        function hapusAktivitas(id) {
            if (!confirm("Yakin ingin menghapus aktivitas ini?")) return;
            fetch(`{{ url('crm/aktivitas/delete') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(data => {
                alert(data.message || 'Aktivitas berhasil dihapus.');
                $('#aktivitasTable').DataTable().ajax.reload();
            })
            .catch(() => alert('Terjadi kesalahan saat menghapus aktivitas.'));
        }

        document.addEventListener("DOMContentLoaded", loadSemuaTargetAktivitas);
    </script>

    <style>
        #salesTargetWrapper {
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }

    </style>
@endsection
