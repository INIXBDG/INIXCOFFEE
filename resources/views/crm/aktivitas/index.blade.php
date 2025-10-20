@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
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

            <!-- Filter Section -->
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

                        <!-- Rentang Created At -->
                        <div class="col-md-3">
                            <label for="filter_created_start" class="form-label">Dibuat Dari</label>
                            <input type="date" id="filter_created_start" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_created_end" class="form-label">Dibuat Sampai</label>
                            <input type="date" id="filter_created_end" class="form-control">
                        </div>

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
            <div class="card">
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
    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Fungsi untuk format angka dengan pemisah ribuan (IDR style)
        function formatNumber(value) {
            if (!value) return '';
            const number = parseFloat(value.replace(/[^0-9.-]+/g, '')) || 0;
            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        // Fungsi untuk menghapus format angka
        function unformatNumber(value) {
            if (!value) return '';
            return parseFloat(value.replace(/[^0-9.-]+/g, '')) || '';
        }

        $(document).ready(function() {
            // Initialize DataTable
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
                columns: [{
                        data: 'kontak'
                    },
                    {
                        data: 'id_sales'
                    },
                    {
                        data: 'aktivitas'
                    },
                    {
                        data: 'deskripsi'
                    },
                    {
                        data: 'waktu_aktivitas'
                    },
                    {
                        data: 'pax',
                        render: data => data ? data : '-'
                    },
                    {
                        data: 'harga',
                        render: data => data ? formatNumber(data) : '-'
                    },
                    {
                        data: 'total',
                        render: data => data ? formatNumber(data) : '-'
                    },
                    {
                        data: 'id',
                        render: function(id, type, row) {
                            const isDisabled = row.aktivitas === 'DB' || row.aktivitas ===
                            'Contact';

                            return `
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-warning"
                        ${isDisabled ? 'disabled' : ''}
                        onclick='editAktivitas(${JSON.stringify(row)})'>
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger"
                        onclick="hapusAktivitas(${id})">
                        Hapus
                    </button>
                </div>`;
                        }
                    }
                ]
            });

            // 🔹 Event filter
            $('#btnFilter').on('click', function() {
                $('#aktivitasTable').DataTable().ajax.reload();
            });

            // 🔹 Event reset filter
            $('#btnResetFilter').on('click', function() {
                $('#filter_aktivitas').val('');
                $('#filter_waktu_start').val('');
                $('#filter_waktu_end').val('');
                $('#filter_created_start').val('');
                $('#filter_created_end').val('');
                $('#aktivitasTable').DataTable().ajax.reload();
            });


            // Initialize Select2
            initPerusahaanSelect2();
            initContactSelect2();

            // Format harga pada input create
            const hargaInput = $('#harga');
            hargaInput.on('input change', function() {
                const value = unformatNumber(this.value);
                this.value = formatNumber(value);
            });

            // Format harga pada input edit
            const editHargaInput = $('#edit_harga');
            editHargaInput.on('input change', function() {
                const value = unformatNumber(this.value);
                this.value = formatNumber(value);
            });

            // Handle form submission untuk create (unformat harga)
            $('#activityForm').on('submit', function(e) {
                e.preventDefault();
                const originalValue = hargaInput.val();
                hargaInput.val(unformatNumber(originalValue)); // Remove formatting
                this.submit(); // Proceed with form submission
                hargaInput.val(formatNumber(originalValue)); // Restore formatting after submit
            });

            // Handle form submission untuk edit (unformat harga)
            $('#editActivityForm').on('submit', function(e) {
                e.preventDefault();
                const originalValue = editHargaInput.val();
                editHargaInput.val(unformatNumber(originalValue)); // Remove formatting

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

                fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(async (res) => {
                        if (!res.ok) {
                            const text = await res.text();
                            throw new Error('Gagal update: ' + text);
                        }
                        return res.json();
                    })
                    .then(res => {
                        alert(res.message || 'Aktivitas berhasil diperbarui.');
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'editActivityModal'));
                        modal.hide();
                        $('#aktivitasTable').DataTable().ajax.reload();
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan saat memperbarui aktivitas.');
                    })
                    .finally(() => {
                        editHargaInput.val(formatNumber(originalValue)); // Restore formatting
                    });
            });
        });

        function initPerusahaanSelect2() {
            var $select = $('#id_perusahaan');
            if (typeof $.fn.select2 !== 'function') {
                console.error('Select2 belum ter-load!');
                return;
            }
            var $closestModal = $select.closest('.modal');
            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $closestModal.length ? $closestModal : $(document.body)
            });
        }

        function initContactSelect2() {
            var $select = $('#id_contact');
            if (typeof $.fn.select2 !== 'function') {
                console.error('Select2 belum ter-load!');
                return;
            }
            var $closestModal = $select.closest('.modal');
            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $closestModal.length ? $closestModal : $(document.body)
            });
        }

        function editAktivitas(row) {
            $('#edit_id').val(row.id);
            $('#edit_id_perusahaan').val(row.id_perusahaan);
            $('#edit_id_contact').val(row.id_contact);
            $('#edit_id_perusahaan_display').val(row.nama_perusahaan || '');
            $('#edit_id_contact_display').val(row.kontak || '');

            // 🔹 Map tampilan ke value asli
            let aktivitasValue = row.aktivitas;
            switch (aktivitasValue) {
                case 'Form Masuk':
                    aktivitasValue = 'Form_Masuk';
                    break;
                case 'Form Keluar':
                    aktivitasValue = 'Form_Keluar';
                    break;
                case 'Incharge Inhouse':
                    aktivitasValue = 'Incharge';
                    break;
            }

            // 🔹 Set value ke select
            $('#edit_aktivitas').val(aktivitasValue);

            $('#edit_deskripsi').val(row.deskripsi);
            $('#edit_pax').val(row.pax || '');
            $('#edit_harga').val(row.harga ? formatNumber(row.harga) : '');

            // 🔹 Format tanggal
            if (row.waktu_aktivitas) {
                const parts = row.waktu_aktivitas.split('/');
                if (parts.length === 3) {
                    const formattedDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                    $('#edit_waktu_aktivitas').val(formattedDate);
                } else {
                    $('#edit_waktu_aktivitas').val('');
                }
            } else {
                $('#edit_waktu_aktivitas').val('');
            }

            // 🔹 Gunakan aktivitasValue (BUKAN row.aktivitas)
            const editHiddenContainer = document.getElementById('edit-hidden-container');
            if (['PA', 'Form_Masuk', 'Form_Keluar'].includes(aktivitasValue)) {
                editHiddenContainer.style.display = 'block';
            } else {
                editHiddenContainer.style.display = 'none';
                $('#edit_pax').val('');
                $('#edit_harga').val('');
            }

            const modal = new bootstrap.Modal(document.getElementById('editActivityModal'));
            modal.show();
        }

        function hapusAktivitas(id) {
            if (!confirm("Yakin ingin menghapus aktivitas ini?")) return;

            fetch(`{{ url('crm/aktivitas/delete') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) return response.json();
                    throw new Error('Gagal menghapus aktivitas.');
                })
                .then(data => {
                    alert(data.message || 'Aktivitas berhasil dihapus.');
                    $('#aktivitasTable').DataTable().ajax.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Terjadi kesalahan saat menghapus aktivitas.');
                });
        }

        function resetForm() {
            document.getElementById('activityForm').reset();
            document.getElementById('hidden-container').style.display = 'none';
            $('#harga').val('');
        }

        document.addEventListener("DOMContentLoaded", function() {
            const contactSelect = document.getElementById("id_contact");
            const contactTypeInput = document.getElementById("contact_type");
            const newContactFields = document.getElementById("newContactFields");
            const aktivitasOption = document.getElementById("aktivitas");
            const hiddenContainer = document.getElementById("hidden-container");
            const paxInput = document.getElementById("pax");
            const hargaInput = document.getElementById("harga");
            const editAktivitasOption = document.getElementById("edit_aktivitas");
            const editHiddenContainer = document.getElementById("edit-hidden-container");

            // Ambil kontak saat perusahaan dipilih
            $('#id_perusahaan').on('change', function() {
                const perusahaanId = $(this).val();
                contactSelect.innerHTML = `
                <option value="" disabled selected>Pilih Kontak</option>
                <option value="new" data-type="contact">+ Tambahkan Kontak Baru</option>
            `;

                if (!perusahaanId) return;

                fetch(`/crm/get-contacts-peserta/${perusahaanId}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Gagal mengambil data kontak dan peserta");
                        return response.json();
                    })
                    .then(data => {
                        if (data.length === 0) {
                            const option = document.createElement("option");
                            option.value = "";
                            option.textContent = "Tidak ada kontak atau peserta tersedia";
                            option.disabled = true;
                            contactSelect.appendChild(option);
                        } else {
                            data.forEach(item => {
                                const option = document.createElement("option");
                                option.value = item.id;
                                option.dataset.type = item.type;
                                const nama = item.nama || "Tidak ada nama";
                                const email = item.email || "Tidak ada email";
                                const divisi = item.type === 'peserta' ?
                                    'C-Peserta' :
                                    (item.divisi || 'tidak ada divisi');
                                option.textContent = `${nama} (${divisi}) - ${email}`;
                                contactSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Gagal mengambil data kontak dan peserta:", error);
                        const option = document.createElement("option");
                        option.value = "";
                        option.textContent = "Terjadi kesalahan saat mengambil data";
                        option.disabled = true;
                        contactSelect.appendChild(option);
                    });
            });

            // Tampilkan form kontak baru jika pilih "new"
            $('#id_contact').on('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const type = selectedOption ? (selectedOption.dataset.type || "contact") : "contact";
                contactTypeInput.value = type;
                if (this.value === "new") {
                    newContactFields.style.display = "block";
                } else {
                    newContactFields.style.display = "none";
                }
            });

            // Show/hide hidden container berdasarkan jenis aktivitas (create)
            aktivitasOption.addEventListener('change', function() {
                const selected = this.value;
                if (['PA', 'Form_Masuk', 'Form_Keluar'].includes(selected)) {
                    hiddenContainer.style.display = 'block';
                } else {
                    hiddenContainer.style.display = 'none';
                    if (paxInput) paxInput.value = '';
                    if (hargaInput) hargaInput.value = '';
                }
            });

            // Show/hide hidden container berdasarkan jenis aktivitas (edit)
            editAktivitasOption.addEventListener('change', function() {
                let selected = this.value;

                const aktivitasMap = {
                    'Form Masuk': 'Form_Masuk',
                    'Form Keluar': 'Form_Keluar',
                    'Incharge Inhouse': 'Incharge'
                };
                selected = aktivitasMap[selected] || selected;

                const editHiddenContainer = document.getElementById('edit-hidden-container');
                const editPaxInput = document.getElementById('edit_pax');
                const editHargaInput = document.getElementById('edit_harga');

                if (['PA', 'Form_Masuk', 'Form_Keluar'].includes(selected)) {
                    editHiddenContainer.style.display = 'block';
                } else {
                    editHiddenContainer.style.display = 'none';
                    if (editPaxInput) editPaxInput.value = '';
                    if (editHargaInput) editHargaInput.value = '';
                }
            });

        });
    </script>
@endsection
