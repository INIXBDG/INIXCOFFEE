@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
        $createNotAllowed = ['HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
    @endphp

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Contact Client</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal"
                    onclick="resetForm()" @if (in_array(Auth::user()->jabatan, $createNotAllowed)) disabled @endif>
                    Tambah Client
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    @if(in_array(Auth::user()->jabatan, $allowedUser))
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="filter_sales" class="form-label fw-semibold">Filter Sales</label>
                                <select id="filter_sales" class="form-select">
                                    <option value="">Semua Sales</option>
                                    <option value="HW">Hera</option>
                                    <option value="VN">Savana</option>
                                    <option value="RR">Rara</option>
                                    <option value="NA">Nabila</option>
                                    <option value="AN">Alfasyiani</option>
                                    <option value="RN">Reni</option>
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table id="picTable" class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th style="text-align:center;">No</th>
                                    <th>Nama</th>
                                    <th>Perusahaan</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                    <th>Email</th>
                                    <th>CP (no)</th>
                                    <th>Divisi</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Client -->
            <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="clientModalLabel">Tambah Contact Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form id="clientForm" action="{{ route('store.pic') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="id_perusahaan" class="form-label">Perusahaan</label>
                                    <select name="id_perusahaan" id="id_perusahaan" class="form-select" required>
                                        <option value="" disabled selected>Pilih Perusahaan</option>
                                        @foreach ($perusahaans as $perusahaan)
                                            <option value="{{ $perusahaan->id }}">
                                                {{ $perusahaan->nama_perusahaan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Perusahaan wajib dipilih.</div>
                                </div>

                                @if (in_array(Auth::user()->jabatan, ['Adm Sales', 'SPV Sales']))
                                    <div class="mb-3">
                                        <label for="sales_key" class="form-label">Sales</label>
                                        <select name="sales_key" id="sales_key" class="form-select">
                                            <option value="">-- Pilih Sales --</option>
                                            <option value="HW">Hera</option>
                                            <option value="VN">Savana</option>
                                            <option value="RR">Rara</option>
                                            <option value="NA">Nabila</option>
                                            <option value="AN">Alfasyiani</option>
                                            <option value="RN">Reni</option>
                                        </select>
                                        <div class="form-text">Pilih sales yang bertanggung jawab atas kontak ini.</div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Contact</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="form-text">Isi jika ada.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="cp" class="form-label">Phone CP</label>
                                    <input type="text" class="form-control" id="cp" name="cp">
                                    <div class="form-text">Nomor kontak.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="divisi" class="form-label">Divisi</label>
                                    <input type="text" class="form-control" id="divisi" name="divisi">
                                    <div class="form-text">Misal: Marketing, Finance, HR.</div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Contact</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Client -->
            <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editClientModalLabel">Edit Contact Client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <form id="editClientForm" action="{{ route('pic.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="contact_id" id="edit_contact_id">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit_id_perusahaan" class="form-label">Perusahaan</label>
                                    <select name="id_perusahaan" id="edit_id_perusahaan" class="form-select" required>
                                        <option value="" disabled>Pilih Perusahaan</option>
                                        @foreach ($perusahaans as $perusahaan)
                                            <option value="{{ $perusahaan->id }}">
                                                {{ $perusahaan->nama_perusahaan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Perusahaan wajib dipilih.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_nama" class="form-label">Nama Contact</label>
                                    <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email">
                                    <div class="form-text">Isi jika ada.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_cp" class="form-label">Phone CP</label>
                                    <input type="text" class="form-control" id="edit_cp" name="cp">
                                    <div class="form-text">Nomor kontak.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="edit_divisi" class="form-label">Divisi</label>
                                    <input type="text" class="form-control" id="edit_divisi" name="divisi">
                                    <div class="form-text">Misal: Marketing, Finance, HR.</div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Update Contact</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Delete Confirmation -->
            <div class="modal fade" id="deleteClientModal" tabindex="-1" aria-labelledby="deleteClientModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteClientModalLabel">Konfirmasi Hapus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus contact ini?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const isRestricted = @json(in_array(auth()->user()->jabatan, $allowedUser));

        @if(session('open_modal'))
            $(document).ready(function() {
                $('#clientModal').modal('show');
            });
        @endif

        $(document).ready(function() {
            var table = $('#picTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('index.json.pic') }}",
                    type: 'GET',
                    data: function (d) {
                        d.sales_filter = $('#filter_sales').val(); // 🔹 Kirim filter sales ke backend
                    }
                },
                columns: [
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        className: "text-center",
                        orderable: false,
                        searchable: false
                    },
                    { data: 'nama', name: 'nama' },
                    { data: 'perusahaan', name: 'perusahaan' },
                    { data: 'sales_key', name: 'sales_key' },
                    { data: 'status', name: 'status' },
                    { data: 'email', name: 'email' },
                    { data: 'cp', name: 'cp' },
                    { data: 'divisi', name: 'divisi' },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (row.status === 'Contact' || row.status === 'Contact Baru') {
                                return `
                                    <div class="d-flex flex-column gap-2">
                                        <button
                                            class="btn btn-sm btn-warning edit-btn"
                                            data-id="${row.contact_id}"
                                            data-nama="${row.nama}"
                                            data-perusahaan="${row.perusahaan}"
                                            data-email="${row.email}"
                                            data-cp="${row.cp}"
                                            data-divisi="${row.divisi}"
                                            data-id_perusahaan="${row.id_perusahaan}"
                                            ${isRestricted ? 'disabled' : ''}
                                        >Edit</button>

                                        <button
                                            class="btn btn-sm btn-danger delete-btn"
                                            data-id="${row.contact_id}"
                                            ${isRestricted ? 'disabled' : ''}
                                        >Delete</button>
                                    </div>
                                `;
                            }
                            return '';
                        }
                    }
                ],
                order: [[0, 'asc']]
            });

            // 🔹 Event filter dropdown
            $('#filter_sales').on('change', function() {
                table.ajax.reload();
            });

            // Reset form untuk Tambah Client
            window.resetForm = function() {
                $('#clientForm')[0].reset();
                $('#id_perusahaan').val('').trigger('change'); // reset select2
                $('#clientForm').find('.is-invalid').removeClass('is-invalid');
                $('#clientForm').find('.invalid-feedback').hide();
            };

            // Handle Edit button click
            $('#picTable').on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                var nama = $(this).data('nama');
                var email = $(this).data('email');
                var cp = $(this).data('cp');
                var divisi = $(this).data('divisi');
                var id_perusahaan = $(this).data('id_perusahaan');

                $('#edit_contact_id').val(id);
                $('#edit_nama').val(nama);
                $('#edit_email').val(email);
                $('#edit_cp').val(cp);
                $('#edit_divisi').val(divisi);

                // set select2 di form edit
                $('#edit_id_perusahaan').val(id_perusahaan.toString()).trigger('change');

                $('#editClientModal').modal('show');
            });

            // Handle Delete button click
            $('#picTable').on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                $('#confirmDelete').data('id', id);
                $('#deleteClientModal').modal('show');
            });

            // Handle Confirm Delete
            $('#confirmDelete').on('click', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route('pic.delete', ':id') }}'.replace(':id', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        $('#deleteClientModal').modal('hide');
                        table.ajax.reload();
                        alert('Contact berhasil dihapus.');
                    },
                    error: function(xhr) {
                        alert('Terjadi kesalahan saat menghapus contact.');
                    }
                });
            });

            // Inisialisasi select2
            initPerusahaanSelect2('#id_perusahaan');       // form tambah
            initPerusahaanSelect2('#edit_id_perusahaan');  // form edit
        });

        // Fungsi umum select2
        function initPerusahaanSelect2(selector) {
            var $select = $(selector);

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

    </script>
@endsection
