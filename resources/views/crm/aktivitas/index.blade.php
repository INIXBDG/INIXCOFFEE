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
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="activityModalLabel">Tambah Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="activityForm" action="{{ route('store.aktivitas.new') }}" method="POST">
                                @csrf

                                {{-- Dropdown perusahaan Klien --}}
                                <div class="mb-3">
                                    <label class="form-label" for="id_perusahaan">Nama Perusahaan</label>
                                    <select class="form-select" id="id_perusahaan" name="id_perusahaan" required>
                                        <option value="" disabled selected>Pilih Perusahaan</option>
                                        @forelse ($perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @empty
                                            <option disabled>Tidak ada kontak tersedia</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="id_contact">Nama Kontak</label>
                                    <select class="form-select" id="id_contact" name="id_contact" required>
                                    </select>
                                </div>

                                {{-- Input Manual untuk Kontak Baru --}}
                                <div id="newContactFields" style="display: none; border: 1px solid #ddd; border-radius: 8px; padding: 15px; background-color: #f8f9fa;">
                                    <h6 class="mb-3">Tambah Kontak Baru</h6>

                                    <div class="mb-3">
                                        <label class="form-label" for="nama_perusahaan">Nama</label>
                                        <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="email_perusahaan">Email</label>
                                        <input type="email" class="form-control" id="email_perusahaan" name="email_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="divisi_perusahaan">Divisi</label>
                                        <input type="text" class="form-control" id="divisi_perusahaan" name="divisi_perusahaan">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="cp_perusahaan">Contact Person (No)</label>
                                        <input type="text" class="form-control" id="cp_perusahaan" name="cp_perusahaan">
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
                                    </select>
                                </div>

                                {{-- Subjek --}}
                                <div class="mb-3">
                                    <label class="form-label" for="subject">Subjek</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <label class="form-label" for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi"></textarea>
                                </div>

                                {{-- Waktu Aktivitas --}}
                                <div class="mb-3">
                                    <label class="form-label" for="waktu_aktivitas">Waktu Aktivitas</label>
                                    <input type="date" class="form-control" id="waktu_aktivitas" name="waktu_aktivitas" required>
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
                const dateObj = new Date(row.waktu_aktivitas);
                if (!isNaN(dateObj)) {
                    const tanggal = dateObj.toISOString().split('T')[0];
                    $('#edit_waktu_aktivitas').val(tanggal);
                } else {
                    console.error("Nilai waktu_aktivitas tidak valid:", row.waktu_aktivitas);
                    $('#edit_waktu_aktivitas').val('');
                }
            } else {
                $('#edit_waktu_aktivitas').val('');
            }
            console.log('buka');

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

        document.addEventListener("DOMContentLoaded", function() {
            const perusahaanSelect = document.getElementById("id_perusahaan");
            const contactSelect = document.getElementById("id_contact");
            const newContactFields = document.getElementById("newContactFields");

            // Cek apakah elemen kontak ada
            if (!perusahaanSelect || !contactSelect) {
                console.error("Dropdown perusahaan atau kontak tidak ditemukan!");
                return;
            }

            // Saat pilih perusahaan → load kontak via AJAX
            perusahaanSelect.addEventListener("change", function() {
                const perusahaanId = this.value;

                // Reset dropdown kontak
                contactSelect.innerHTML = `
                    <option value="" disabled selected>Pilih Kontak</option>
                    <option value="new">+ Tambahkan Kontak Baru</option>
                `;

                // Jika tidak ada perusahaan yang dipilih, hentikan proses
                if (!perusahaanId) return;

                // Ambil kontak via AJAX
                fetch(`/crm/get-contacts/${perusahaanId}`)
                    .then(response => {
                        if (!response.ok) throw new Error("Gagal fetch data kontak");
                        return response.json();
                    })
                    .then(data => {
                        if (data.length === 0) {
                            const option = document.createElement("option");
                            option.value = "";
                            option.textContent = "Tidak ada kontak tersedia";
                            option.disabled = true;
                            contactSelect.appendChild(option);
                        } else {
                            data.forEach(contact => {
                                const option = document.createElement("option");
                                option.value = contact.id;
                                option.textContent = `${contact.nama} (${contact.divisi || "Tidak ada divisi"}) - ${contact.email || "Tidak ada email"}`;
                                contactSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error("Gagal mengambil data kontak:", error));
            });

            // Tampilkan form tambah kontak baru jika pilih "+ Tambahkan Kontak Baru"
            contactSelect.addEventListener("change", function() {
                if (this.value === "new") {
                    newContactFields.style.display = "block";
                    // newContactFields.querySelectorAll("input").forEach(input => input.required = true);
                } else {
                    newContactFields.style.display = "none";
                    // newContactFields.querySelectorAll("input").forEach(input => input.required = false);
                }
            });
        });

    </script>
@endsection
