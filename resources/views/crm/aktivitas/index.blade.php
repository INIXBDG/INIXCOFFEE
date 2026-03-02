@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
        $createNotAllowed = ['HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
        $sales = Auth::user()->id_sales;
        $isAllowedUser = in_array(Auth::user()->jabatan, $allowedUser);
    @endphp
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Activity Management</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal"
                    onclick="resetForm()" @if (in_array(Auth::user()->jabatan, $createNotAllowed)) disabled @endif>
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

                        @if (in_array(Auth::user()->jabatan, $allowedUser))
                            <div class="col-md-3">
                                <label for="filter_sales" class="form-label">Filter Sales</label>
                                <select id="filter_sales" class="form-select">
                                    <option value="">-- Semua Sales --</option>
                                    <option value="HW">Hera</option>
                                    <option value="VN">Savana</option>
                                    <option value="RR">Rara</option>
                                    <option value="NA">Nabila</option>
                                    <option value="AN">Alfasyiani</option>
                                    <option value="RN">Reni</option>
                                </select>
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
                                    <th style="text-align:center;">No</th>
                                    <th>Client</th>
                                    <th>Sales</th>
                                    <th style="text-align: center;">Jenis Aktivitas</th>
                                    <th>Deskripsi</th>
                                    <th style="text-align: center;">Waktu Aktivitas</th>
                                    <th style="text-align: center;">Pax</th>
                                    <th style="text-align: center;">Harga</th>
                                    <th style="text-align: center;">Total</th>
                                    <th style="text-align: center;">Foto</th>
                                    <th style="text-align: center;">Lokasi</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="totalContainer" class="mt-3 fw-bold"></div>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="activityForm" action="{{ route('store.aktivitas.new') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="contact_type" name="contact_type" value="contact">

                                @if (auth()->user()->jabatan === 'Adm Sales' || auth()->user()->jabatan === 'SPV Sales')
                                    <div class="mb-3">
                                        <label class="form-label" for="id_sales">Pilih Sales</label>
                                        <select class="form-select" id="id_sales" name="id_sales" required>
                                            <option value="">-- Pilih Sales --</option>
                                            <option value="HW">Hera</option>
                                            <option value="VN">Savana</option>
                                            <option value="RR">Rara</option>
                                            <option value="NA">Nabila</option>
                                            <option value="AN">Alfasyiani</option>
                                            <option value="RN">Reni</option>
                                        </select>
                                    </div>
                                @endif

                                {{-- Dropdown perusahaan Klien --}}
                                <div class="mb-3">
                                    <label class="form-label" for="id_perusahaan">Nama Perusahaan</label>
                                    <select class="form-select" id="id_perusahaan" name="id_perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach ($perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Nama Kontak</label>
                                    <select class="form-select" id="id_contact" name="id_contact">
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
                                    <select class="form-select" id="aktivitas" name="aktivitas">
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

                                <div id="camera_wrapper" style="display: none; margin-bottom: 15px;">
                                    <label class="form-label">Dokumentasi Visit</label>

                                    <div class="mb-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="upload_method"
                                                id="method_camera" value="camera" checked>
                                            <label class="form-check-label" for="method_camera">Ambil Foto
                                                (Kamera)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="upload_method"
                                                id="method_upload" value="upload">
                                            <label class="form-check-label" for="method_upload">Upload File</label>
                                        </div>
                                    </div>

                                    <div id="live_camera_container">
                                        <div id="camera"
                                            style="width: 320px; height: 240px; border: 2px solid #ddd; border-radius: 5px; background: #eee;">
                                        </div>
                                        <small class="text-muted d-block mt-1">Pastikan izin kamera dan GPS aktif.</small>
                                    </div>

                                    <div id="file_upload_container" style="display: none;">
                                        <input type="file" class="form-control" id="file_foto" name="file_foto"
                                            accept="image/*">
                                        <small class="text-muted">Pilih foto dari galeri (Koordinat tidak akan
                                            direkam).</small>
                                    </div>
                                </div>

                                <input type="hidden" name="foto_lokasi" id="foto_lokasi">
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">

                                <button type="submit" class="btn btn-primary" id="btnSubmitAktivitas">Simpan</button>
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
                        <input type="hidden" id="edit_contact_type"> <!-- Simpan tipe contact -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editActivityModalLabel">Edit Aktivitas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Select2 untuk tipe Contact -->
                                <div class="mb-3" id="edit_contact_select_wrapper">
                                    <label class="form-label" for="edit_id_contact">Nama Kontak</label>
                                    <select class="form-select" id="edit_id_contact" name="id_contact">
                                        <option value="">Pilih Kontak</option>
                                        @foreach ($contact as $c)
                                            <option value="{{ $c->id }}">
                                                {{ $c->nama }} ({{ $c->divisi ?? 'Tidak ada divisi' }}) -
                                                {{ $c->perusahaan->nama_perusahaan ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Input readonly untuk selain Contact (DB, Peserta, dll) -->
                                <div class="mb-3" id="edit_contact_readonly_wrapper" style="display: none;">
                                    <label class="form-label" for="edit_contact_display">Nama Kontak</label>
                                    <input type="text" class="form-control" id="edit_contact_display" readonly>
                                    <input type="hidden" id="edit_id_contact_hidden" name="id_contact">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="edit_aktivitas" name="aktivitas" required>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                        <option value="Meet">Meeting</option>
                                        <option value="Contact">Contact Baru</option>
                                        <option value="Incharge">Incharge Inhouse</option>
                                        <option value="PA">Penawaran Awal</option>
                                        <option value="PI">Penawaran Internal</option>
                                        <option value="Telemarketing">Telemarketing</option>
                                        <option value="Form_Masuk">Form Masuk</option>
                                        <option value="Form_Keluar">Form Keluar</option>
                                        <option value="DB">DB</option>
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
    <script src="{{ asset('js/webcam.js') }}"></script>
    <script>
        // ===============================
        // 🔹 Fungsi Format Angka (IDR)
        // ===============================
        function unformatNumber(value) {
            if (!value) return '';
            return parseFloat(value.toString().replace(/\./g, '').replace(/,/g, '')) || '';
        }

        function formatNumber(value) {
            if (!value) return '';
            return parseInt(value, 10).toLocaleString('id-ID');
        }

        // Function untuk reverse koordinat
        async function updateAddressText(lat, lng, elementId) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18`);
                const data = await response.json();

                const targetElement = document.getElementById(elementId);
                if (targetElement) {
                    const address = data.display_name || "Alamat tidak ditemukan";
                    const shortAddress = [
                        data.address.village || '',
                        data.address.subdistrict || '',
                        data.address.state || ''
                    ].filter(Boolean).join(', ');

                    targetElement.innerHTML = `
                        <span title="${address}"><strong>${shortAddress}</strong></span><br>
                            <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="ms-1 text-primary">
                            <small class="text-muted">${data.address.city || data.address.town || data.address.village || ''}</small>
                        </a>
                    `;
                }
            } catch (error) {
                console.error("Geocode Error:", error);
                const targetElement = document.getElementById(elementId);
                if (targetElement) targetElement.innerHTML = "Gagal memuat alamat";
            }
        }

        // ===============================
        // 🔹 DataTable & Form Events
        // ===============================
        $(document).ready(function() {
            $('#aktivitasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('index.aktivitas.json') }}',
                    type: 'GET',
                    data: function(d) {
                        d.filter_sales = $('#filter_sales').val();
                        d.filter_aktivitas = $('#filter_aktivitas').val();
                        d.filter_waktu_start = $('#filter_waktu_start').val();
                        d.filter_waktu_end = $('#filter_waktu_end').val();
                        d.filter_created_start = $('#filter_created_start').val();
                        d.filter_created_end = $('#filter_created_end').val();
                    },
                    dataSrc: function(json) {
                        if (json.total !== undefined) {
                            $('#totalContainer').html(
                                `Total Keseluruhan: <span class="text-success">Rp ${formatNumber(json.total)}</span>`
                            );
                        } else {
                            $('#totalContainer').empty();
                        }
                        return json.data; // ini tetap untuk isi tabel
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Error:', xhr.responseText);
                        alert('Gagal memuat data aktivitas: ' + thrown);
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    {
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
                        render: d => d ? d : '-'
                    },
                    {
                        data: 'harga',
                        render: d => d ? formatNumber(d) : '-'
                    },
                    {
                        data: 'total',
                        render: d => d ? formatNumber(d) : '-'
                    },
                    {
                        data: 'foto_lokasi',
                        render: function(data) {
                            if (data) {
                                return `<img src="/${data}" alt="Foto" style="width: 80px; height: auto; border-radius: 5px; cursor: pointer;" 
                    onclick="window.open(this.src)" class="img-thumbnail">`;
                            }
                            return '<span class="text-muted">-</span>';
                        },
                        className: "text-center"
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const lat = row.latitude;
                            const lng = row.longitude;

                            if (!lat || !lng)
                                return '<span class="text-muted">Koordinat tidak ada</span>';

                            const cellId = `loc-${row.id}`;

                            updateAddressText(lat, lng, cellId);

                            return `<div id="${cellId}" class="small"><i class="fa fa-spinner fa-spin"></i> Mencari alamat...</div>`;
                        },
                        className: "text-start"
                    },
                    {
                        data: 'id',
                        render: function(id, type, row) {
                            const isDisabled = row.aktivitas === 'DB';
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
                $('#filter_sales, #filter_aktivitas, #filter_waktu_start, #filter_waktu_end, #filter_created_start, #filter_created_end')
                    .val('');
                $('#aktivitasTable').DataTable().ajax.reload();
            });

            // === Select2 Init ===
            initPerusahaanSelect2();
            initContactSelect2();
            initEditContactSelect2();

            $('#harga, #edit_harga').on('input', function(e) {
                const input = e.target;
                const raw = input.value.replace(/[^\d]/g, '');
                if (!raw) {
                    input.value = '';
                    return;
                }
                const formatted = parseInt(raw, 10).toLocaleString('id-ID');
                input.value = formatted;
                input.setSelectionRange(formatted.length, formatted.length);
            });

            // === Form Submit (Edit) ===
            $('#editActivityForm').on('submit', async function(e) {
                e.preventDefault();
                const id = $('#edit_id').val();
                const url = `/crm/aktivitas/update/${id}`;
                const hargaUnformatted = unformatNumber($('#edit_harga').val());

                // Ambil id_contact dari select2 atau hidden input tergantung tipe
                const contactType = $('#edit_contact_type').val();
                let idContact;

                if (contactType === 'contact') {
                    idContact = $('#edit_id_contact').val();
                } else {
                    idContact = $('#edit_id_contact_hidden').val();
                }

                const data = {
                    id_contact: idContact,
                    aktivitas: $('#edit_aktivitas').val(),
                    deskripsi: $('#edit_deskripsi').val(),
                    waktu_aktivitas: $('#edit_waktu_aktivitas').val(),
                    pax: $('#edit_pax').val(),
                    harga: isNaN(hargaUnformatted) ? null : hargaUnformatted
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
            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        }

        function initContactSelect2() {
            const $select = $('#id_contact');
            if (!$.fn.select2) return console.error('Select2 belum ter-load!');
            const $modal = $select.closest('.modal');
            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        }

        function initEditContactSelect2() {
            const $select = $('#edit_id_contact');
            if (!$.fn.select2) return console.error('Select2 belum ter-load!');
            const $modal = $select.closest('.modal');

            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2('destroy');
            }

            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $modal.length ? $modal : $(document.body),
                placeholder: 'Pilih Kontak'
            });
        }

        window.isAllowedUser = {{ $isAllowedUser ? 'true' : 'false' }};

        // ===============================
        // 🔹 Fungsi Load Semua Target Aktivitas
        // ===============================
        async function loadSemuaTargetAktivitas(isAllowedUser = false) {
            try {
                console.log("🚀 Memulai loadSemuaTargetAktivitas...");
                const res = await fetch(`/crm/semua-target-aktivitas`);
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

                if (response.id_sales && Array.isArray(response.data)) {
                    console.log("👤 Mode Sales Tunggal");
                    list = [response];
                } else if (Array.isArray(response.data)) {
                    console.log("👥 Mode Multi Sales");
                    list = response.data;
                } else {
                    console.warn("⚠️ Format data tidak dikenali:", response);
                    return;
                }

                list.forEach((sales) => {
                    const idSales = sales.id_sales || "(tanpa ID)";
                    const items = sales.data || [];

                    if (!items.length) {
                        console.warn(`⚠️ Sales ${idSales} tidak punya data aktivitas.`);
                        return;
                    }

                    const progressBars = items.map(row => {
                        const jenis = row.jenis || "-";
                        const target = row.target ?? 0;
                        const realisasi = row.realisasi ?? 0;
                        const percent = row.percent ?? 0;

                        let color = "#e0e0e0";
                        if (percent >= 100) color = "#4caf50";
                        else if (percent >= 50) color = "#2196f3";
                        else if (percent > 0) color = "#ffb300";

                        return `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>${jenis}: ${realisasi}/${target}</span>
                                <span>${percent}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="width:${percent}%; background-color:${color};"></div>
                            </div>
                        </div>`;
                    }).join("");

                    const colClass = isAllowedUser ? "col-xl-3 col-lg-4 col-md-6 col-sm-12" : "col-12";

                    wrapper.innerHTML += `
                    <div class="${colClass}">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0 text-primary fw-semibold">
                                    Sales: ${idSales}
                                </h6>
                            </div>
                            <div class="card-body p-3" style="max-height: ${isAllowedUser ? '300px' : 'none'}; overflow-y: ${isAllowedUser ? 'auto' : 'visible'};">
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
        // 🔹 Fungsi Edit Aktivitas
        // ===============================
        function editAktivitas(row) {
            console.log('🔍 Data row:', row);

            $('#edit_id').val(row.id);

            // Simpan tipe contact (dari backend harus mengirim field 'contact_type')
            const contactType = row.contact_type || 'contact'; // default 'contact'
            $('#edit_contact_type').val(contactType);

            // Toggle tampilan berdasarkan tipe
            if (contactType === 'contact') {
                // Tampilkan Select2, sembunyikan readonly
                $('#edit_contact_select_wrapper').show();
                $('#edit_contact_readonly_wrapper').hide();

                // Set value select2 dan trigger change untuk memilih option
                $('#edit_id_contact').val(row.id_contact).trigger('change');
            } else {
                // Tampilkan readonly, sembunyikan Select2
                $('#edit_contact_select_wrapper').hide();
                $('#edit_contact_readonly_wrapper').show();

                // Set value untuk readonly dan hidden input
                $('#edit_contact_display').val(row.kontak || '-');
                $('#edit_id_contact_hidden').val(row.id_contact);
            }

            // Set aktivitas
            let aktivitasValue = row.aktivitas;
            const map = {
                'Form Masuk': 'Form_Masuk',
                'Form Keluar': 'Form_Keluar',
                'Incharge Inhouse': 'Incharge'
            };
            aktivitasValue = map[aktivitasValue] || aktivitasValue;
            $('#edit_aktivitas').val(aktivitasValue);

            // Set fields lainnya
            $('#edit_deskripsi').val(row.deskripsi);
            $('#edit_pax').val(row.pax || '');
            $('#edit_harga').val(row.harga ? formatNumber(row.harga) : '');

            const parts = (row.waktu_aktivitas || '').split('/');
            $('#edit_waktu_aktivitas').val(parts.length === 3 ?
                `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}` : '');

            // Toggle hidden container untuk pax & harga
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

        // ===============================
        // 🔹 Fungsi Hapus Aktivitas
        // ===============================
        function hapusAktivitas(id) {
            if (!confirm("Yakin ingin menghapus aktivitas ini?")) return;
            fetch(`{{ url('crm/aktivitas/delete') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.ok ? res.json() : Promise.reject(res))
                .then(data => {
                    alert(data.message || 'Aktivitas berhasil dihapus.');
                    $('#aktivitasTable').DataTable().ajax.reload();
                })
                .catch(() => alert('Terjadi kesalahan saat menghapus aktivitas.'));
        }

        document.addEventListener("DOMContentLoaded", function() {
            const contactSelect = document.getElementById("id_contact");
            const contactTypeInput = document.getElementById("contact_type");
            const newContactFields = document.getElementById("newContactFields");
            const aktivitasOption = document.getElementById("aktivitas");
            const cameraWrapper = document.getElementById("camera_wrapper");
            const hiddenContainer = document.getElementById("hidden-container");
            const paxInput = document.getElementById("pax");
            const hargaInput = document.getElementById("harga");
            const editAktivitasOption = document.getElementById("edit_aktivitas");
            const editHiddenContainer = document.getElementById("edit-hidden-container");
            const isAllowedUser = window.isAllowedUser || false;
            loadSemuaTargetAktivitas(isAllowedUser);

            // Ambil kontak saat perusahaan dipilih
            $('#id_perusahaan').on('change', function() {
                const perusahaanId = $(this).val();
                contactSelect.innerHTML = `
                <option value="" disabled selected>Pilih Kontak</option>
                <option value="new" data-type="contact">+ Tambahkan Kontak Baru</option>`;

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

            aktivitasOption.addEventListener('change', function() {
                if (this.value === 'Visit') {
                    $(cameraWrapper).show();
                    Webcam.set({
                        width: 320,
                        height: 240,
                        image_format: 'jpeg',
                        jpeg_quality: 90
                    });
                    Webcam.attach('#camera');

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            document.getElementById('latitude').value = position.coords.latitude;
                            document.getElementById('longitude').value = position.coords.longitude;
                        }, function(error) {
                            alert("Gagal mengambil lokasi. Pastikan GPS aktif dan izin diberikan.");
                        });
                    }
                } else {
                    $(cameraWrapper).hide();
                    Webcam.reset();
                }

                // Toggle hidden-container untuk PA, Form_Masuk, Form_Keluar
                if (["PA", "Form_Masuk", "Form_Keluar"].includes(this.value)) {
                    hiddenContainer.style.display = "block";
                } else {
                    hiddenContainer.style.display = "none";
                    if (paxInput) paxInput.value = "";
                    if (hargaInput) hargaInput.value = "";
                }
            });

            $('input[name="upload_method"]').on('change', function() {
                if (this.value === 'camera') {
                    $('#live_camera_container').show();
                    $('#file_upload_container').hide();
                    $('#file_foto').val('');

                    Webcam.attach('#camera');

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            document.getElementById('latitude').value = position.coords.latitude;
                            document.getElementById('longitude').value = position.coords.longitude;
                        });
                    }
                } else {
                    $('#live_camera_container').hide();
                    $('#file_upload_container').show();

                    Webcam.reset();

                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                    document.getElementById('foto_lokasi').value = '';
                }
            });

            $('#activityForm').on('submit', function(e) {
                const activityType = $('#aktivitas').val();
                const uploadMethod = $('input[name="upload_method"]:checked').val();

                if (activityType === 'Visit') {
                    if (uploadMethod === 'camera') {
                        e.preventDefault();
                        const form = this;

                        Webcam.snap(function(data_uri) {
                            document.getElementById('foto_lokasi').value = data_uri;

                            if (!document.getElementById('latitude').value) {
                                alert(
                                    "Lokasi belum terdeteksi. Gunakan metode Upload jika GPS bermasalah.");
                                return;
                            }
                            form.submit();
                        });
                    }
                }
            });

            $('#activityForm').on('submit', function(e) {
                // 1. Format Harga
                const hargaEl = document.getElementById('harga');
                if (hargaEl) {
                    const raw = unformatNumber(hargaEl.value);
                    hargaEl.value = isNaN(raw) ? '' : raw;
                }

                const activityType = $('#aktivitas').val();
                const uploadMethod = $('input[name="upload_method"]:checked').val();

                if (activityType === 'Visit') {
                    if (uploadMethod === 'camera') {
                        // Jika pilih kamera, pastikan snap diambil
                        e.preventDefault();
                        const form = this;
                        Webcam.snap(function(data_uri) {
                            document.getElementById('foto_lokasi').value = data_uri;

                            if (!data_uri || data_uri.length < 100) {
                                alert("Gagal mengambil foto dari kamera.");
                                return;
                            }

                            if (!document.getElementById('latitude').value) {
                                alert(
                                    "Lokasi belum terdeteksi. Silakan tunggu atau gunakan metode Upload.");
                                return;
                            }
                            form.off('submit').submit(); // Gunakan off agar tidak looping event
                        });
                    } else {
                        // Jika pilih upload, pastikan file dipilih
                        const fileInput = document.getElementById('file_foto');
                        if (fileInput.files.length === 0) {
                            e.preventDefault();
                            alert("Silakan pilih file foto terlebih dahulu.");
                        }
                        // Biarkan submit normal untuk upload file
                    }
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
    <style>
        #salesTargetWrapper {
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }
    </style>
@endsection
