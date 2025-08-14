@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Activity Management</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#activityModal"
                    onclick="resetForm()">
                    Tambah Aktivitas
                </button>
            </div>

            <!-- Tabel Aktivitas -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="aktivitasTable" class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Client</th>
                                    <th style="text-align: center;">Jenis Aktivitas</th>
                                    <th style="text-align: center;">Subjek</th>
                                    <th>Deskripsi</th>
                                    <th style="text-align: center;">Waktu Aktivitas</th>
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
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="activityModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="activityForm" action="{{ route('store.aktivitas.new') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Kontak Klien</label>
                                    <select class="form-select" id="id_contact" name="id_contact" required>
                                        <option value="" disabled selected>Pilih Kontak</option>
                                        @forelse ($contact as $contact)
                                            <option value="{{ $contact->id }}">{{ $contact->nama_perusahaan }}
                                                ({{ $contact->cp ?? '-' }})
                                            </option>
                                        @empty
                                            <option disabled>Tidak ada kontak tersedia</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="aktivitas" name="aktivitas" required>
                                        <option value="" disabled selected>Pilih Jenis Aktivitas</option>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                        <option value="Meet">Meeting</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="subject">Subjek</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="waktu_aktivitas">Waktu Aktivitas</label>
                                    <input type="date" class="form-control" id="waktu_aktivitas" name="waktu_aktivitas"
                                        required>
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
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editActivityModalLabel">Edit Aktivitas</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <input type="hidden" id="edit_id">

                                <div class="mb-3">
                                    <label class="form-label" for="edit_aktivitas">Jenis Aktivitas</label>
                                    <select class="form-select" id="edit_aktivitas" name="aktivitas" required>
                                        <option value="Call">Call</option>
                                        <option value="Email">Email</option>
                                        <option value="Visit">Visit</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_subject">Subjek</label>
                                    <input type="text" class="form-control" id="edit_subject" name="subject"
                                        required>
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

    <script>
        $(document).ready(function() {
            $('#aktivitasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('index.aktivitas.json') }}',
                    type: 'GET',
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
                        data: 'aktivitas'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'deskripsi'
                    },
                    {
                        data: 'waktu_aktivitas'
                    },
                    {
                        data: 'id',
                        render: function(id, type, row) {
                            return `
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" onclick='editAktivitas(${JSON.stringify(row)})'>Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="hapusAktivitas(${id})">Hapus</button>
                            </div>
                        `;
                        }
                    }
                ]
            });
        });

        function editAktivitas(row) {
            $('#edit_id').val(row.id);
            $('#edit_aktivitas').val(row.aktivitas);
            $('#edit_subject').val(row.subject);
            $('#edit_deskripsi').val(row.deskripsi);

            if (row.waktu_aktivitas) {
                const tanggal = new Date(row.waktu_aktivitas).toISOString().split('T')[0];
                $('#edit_waktu_aktivitas').val(tanggal);
            }

            const modal = new bootstrap.Modal(document.getElementById('editActivityModal'));
            modal.show();
        }

        $('#editActivityForm').submit(function(e) {
            e.preventDefault();

            const id = $('#edit_id').val();
            const url = `/crm/aktivitas/update/${id}`;
            const data = {
                aktivitas: $('#edit_aktivitas').val(),
                subject: $('#edit_subject').val(),
                deskripsi: $('#edit_deskripsi').val(),
                waktu_aktivitas: $('#edit_waktu_aktivitas').val()
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
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editActivityModal'));
                    modal.hide();
                    $('#aktivitasTable').DataTable().ajax.reload();
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat memperbarui aktivitas.');
                });
        });

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
        }
    </script>
@endsection
