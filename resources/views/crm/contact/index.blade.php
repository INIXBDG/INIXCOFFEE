@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Manajemen Contact</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                    onclick="resetForm()">
                    Tambah Contact
                </button>
            </div>

            <!-- Tabel Contact -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Daftar Contact</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Perusahaan</th>
                                    <th>Lokasi</th>
                                    <th>PIC</th>
                                    <th>No Telepon</th>
                                    <th>Status</th>
                                    <th>Sales</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $contact)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $contact->nama_perusahaan }}</td>
                                        <td>{{ $contact->lokasi ?? '-' }}</td>
                                        <td>{{ $contact->cp ?? '-' }}</td>
                                        <td>{{ $contact->no_telp ?? '-' }}</td>
                                        <td>{{ $contact->status ?? '-' }}</td>
                                        <td>{{ $contact->sales_key }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editContactModal"
                                                    onclick='editContact(@json($contact))'>
                                                    Edit
                                                </button>
                                                <form action="{{ route('delete.contact', $contact->id) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus?')"
                                                    style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Edit Contact -->
            <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Perusahaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editContactForm" method="POST" enctype="multipart/form-data"
                                action="">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id" id="edit_contact_id">

                                <div class="mb-3">
                                    <label class="form-label" for="edit_nama_perusahaan">Nama Perusahaan</label>
                                    <input type="text" class="form-control" id="edit_nama_perusahaan"
                                        name="nama_perusahaan" required maxlength="255">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_kategori_perusahaan">Kategori Perusahaan</label>
                                    <input type="text" class="form-control" id="edit_kategori_perusahaan"
                                        name="kategori_perusahaan" required maxlength="255">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_cp">PIC</label>
                                    <input type="text" class="form-control" id="edit_cp" name="cp"
                                        maxlength="100">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_email">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required
                                        maxlength="255">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_lokasi">Lokasi</label>
                                    <input type="text" class="form-control" id="edit_lokasi" name="lokasi"
                                        maxlength="255">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_status">Status</label>
                                    <input type="text" class="form-control" id="edit_status" name="status" required
                                        maxlength="50">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_npwp">NPWP</label>
                                    <input type="text" class="form-control" id="edit_npwp" name="npwp"
                                        maxlength="50">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_alamat">Alamat</label>
                                    <textarea class="form-control" id="edit_alamat" name="alamat" maxlength="500"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_no_telp">No Telepon</label>
                                    <input type="text" class="form-control" id="edit_no_telp" name="no_telp"
                                        maxlength="20">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_foto_npwp">Foto NPWP (jpg, jpeg, png, pdf max
                                        2MB)</label>
                                    <input type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-control"
                                        id="edit_foto_npwp" name="foto_npwp">
                                </div>

                                <button type="submit" class="btn btn-primary">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Create Contact -->
            <div class="modal fade" id="opportunityModal" tabindex="-1" aria-labelledby="opportunityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="opportunityModalLabel">Tambah Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="contactForm" action="{{ route('store.contact') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" id="contact_id">

                                <div class="mb-3">
                                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="cp">No Telepon</label>
                                    <input type="text" class="form-control" id="cp" name="cp" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="divisi">Divisi</label>
                                    <input type="text" class="form-control" id="divisi" name="divisi" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="id_perusahaan">Perusahaan</label>
                                    <select class="form-select" id="id_perusahaan" name="id_perusahaan" required>
                                        <option value="" disabled selected>Pilih Perusahaan</option>
                                        @foreach ($perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editContact(contact) {
            // Mengisi input form dengan data yang diterima dari parameter 'contact'
            document.getElementById('edit_nama_perusahaan').value = contact.nama_perusahaan || '';
            document.getElementById('edit_email').value = contact.email || '';
            document.getElementById('edit_cp').value = contact.cp || '';
            document.getElementById('edit_kategori_perusahaan').value = contact.kategori_perusahaan || '';
            document.getElementById('edit_lokasi').value = contact.lokasi || '';
            document.getElementById('edit_status').value = contact.status || '';
            document.getElementById('edit_npwp').value = contact.npwp || '';
            document.getElementById('edit_alamat').value = contact.alamat || '';
            document.getElementById('edit_no_telp').value = contact.no_telp || '';

            // Menyimpan ID perusahaan agar bisa dikirim dalam form
            document.getElementById('edit_contact_id').value = contact.id;

            // Set action URL form update agar sesuai dengan id yang akan diupdate
            document.getElementById('editContactForm').action = '/crm/contact/update/' + contact.id;
        }
    </script>
@endsection
