@extends('layouts_crm.app')

@section('crm_contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Database Client</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                    onclick="resetForm()">
                    Tambah Contact
                </button>
            </div>

            <form id="filterForm" class="mb-4 ">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <input type="text" id="filter_nama_perusahaan" class="form-control" placeholder="Cari Nama Perusahaan...">
                    </div>
                    <div class="col-md-3">
                        <select id="filter_lokasi" class="form-select">
                            <option value="">Cari Lokasi</option>
                            <option value="Aceh">Aceh</option>
                            <option value="Sumatera Utara">Sumatera Utara</option>
                            <option value="Sumatera Barat">Sumatera Barat</option>
                            <option value="Riau">Riau</option>
                            <option value="Kepulauan Riau">Kepulauan Riau</option>
                            <option value="Jambi">Jambi</option>
                            <option value="Bengkulu">Bengkulu</option>
                            <option value="Sumatera Selatan">Sumatera Selatan</option>
                            <option value="Bangka Belitung">Bangka Belitung</option>
                            <option value="Lampung">Lampung</option>
                            <option value="DKI Jakarta">DKI Jakarta</option>
                            <option value="Banten">Banten</option>
                            <option value="Jawa Barat">Jawa Barat</option>
                            <option value="Jawa Tengah">Jawa Tengah</option>
                            <option value="DI Yogyakarta">DI Yogyakarta</option>
                            <option value="Jawa Timur">Jawa Timur</option>
                            <option value="Bali">Bali</option>
                            <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                            <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                            <option value="Kalimantan Barat">Kalimantan Barat</option>
                            <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                            <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                            <option value="Kalimantan Timur">Kalimantan Timur</option>
                            <option value="Kalimantan Utara">Kalimantan Utara</option>
                            <option value="Sulawesi Utara">Sulawesi Utara</option>
                            <option value="Gorontalo">Gorontalo</option>
                            <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                            <option value="Sulawesi Barat">Sulawesi Barat</option>
                            <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                            <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                            <option value="Maluku">Maluku</option>
                            <option value="Maluku Utara">Maluku Utara</option>
                            <option value="Papua">Papua</option>
                            <option value="Papua Barat">Papua Barat</option>
                            <option value="Papua Selatan">Papua Selatan</option>
                            <option value="Papua Tengah">Papua Tengah</option>
                            <option value="Papua Pegunungan">Papua Pegunungan</option>
                            <option value="Papua Barat Daya">Papua Barat Daya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filter_status" class="form-select">
                            <option value="">Cari Status</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                            <option value="Q3">Q3</option>
                            <option value="Q4">Q4</option>
                            <option value="Database Baru">Database Baru</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filter_sales" class="form-select">
                            <option value="">Cari Sales</option>
                            <option value="HW">Hera</option>
                            <option value="VN">Savana</option>
                            <option value="RR">Rara</option>
                            <option value="NA">Nabila</option>
                            <option value="AN">Alfasyiani</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Tabel Contact -->
            <div class="card card-rounded shadow-sm">
                <div class="card-body">
                    <!-- Wrapper untuk scroll horizontal -->
                    <div class="table-responsive" style="overflow-x: auto;">
                        <div class="rounded border" style="display: inline-block;">
                            <table class="table table-bordered table-hover mb-0" id="perusahaanTable">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="text-align: center;">No</th>
                                        <th style="text-align: center;">Perusahaan</th>
                                        <th style="text-align: center;">Lokasi</th>
                                        <th style="text-align: center;">PIC</th>
                                        <th style="text-align: center;">No Telepon</th>
                                        <th style="text-align: center;">Status</th>
                                        <th style="text-align: center;">Sales</th>
                                        <th style="text-align: center;">Kelas Terakhir</th>
                                        <th style="text-align: center;">Aktivitas Terakhir</th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data rows akan diisi di sini -->
                                </tbody>
                            </table>
                        </div>
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
                            <form id="editContactForm" method="POST" enctype="multipart/form-data" action="">
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
                                    <select class="form-select @error('kategori_perusahaan') is-invalid @enderror"
                                        name="kategori_perusahaan" id="edit_kategori_perusahaan"
                                        autocomplete="kategori_perusahaan" required>
                                        <option value="" selected>Pilih Kategori Perusahaan</option>
                                        <option value="Pemerintahan Daerah">Pemerintahan Daerah</option>
                                        <option value="Kementerian">Kementerian</option>
                                        <option value="Lembaga Pemerintahan">Lembaga Pemerintahan</option>
                                        <option value="BUMN">BUMN</option>
                                        <option value="BUMD">BUMD</option>
                                        <option value="Swasta">Swasta</option>
                                        <option value="Akademik">Akademik</option>
                                        <option value="Bank Daerah">Bank Daerah</option>
                                        <option value="Bank Umum">Bank Umum</option>
                                        <option value="Bank BUMN">Bank BUMN</option>
                                        <option value="Rumah Sakit">Rumah Sakit</option>
                                        <option value="Personal">Personal</option>
                                    </select>
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
                                    <select class="form-select" id="edit_lokasi" name="lokasi" required>
                                        <option value="">Pilih Lokasi</option>
                                        <option value="Aceh">Aceh</option>
                                        <option value="Sumatera Utara">Sumatera Utara</option>
                                        <option value="Sumatera Barat">Sumatera Barat</option>
                                        <option value="Riau">Riau</option>
                                        <option value="Kepulauan Riau">Kepulauan Riau</option>
                                        <option value="Jambi">Jambi</option>
                                        <option value="Bengkulu">Bengkulu</option>
                                        <option value="Sumatera Selatan">Sumatera Selatan</option>
                                        <option value="Bangka Belitung">Bangka Belitung</option>
                                        <option value="Lampung">Lampung</option>
                                        <option value="DKI Jakarta">DKI Jakarta</option>
                                        <option value="Banten">Banten</option>
                                        <option value="Jawa Barat">Jawa Barat</option>
                                        <option value="Jawa Tengah">Jawa Tengah</option>
                                        <option value="DI Yogyakarta">DI Yogyakarta</option>
                                        <option value="Jawa Timur">Jawa Timur</option>
                                        <option value="Bali">Bali</option>
                                        <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                                        <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                                        <option value="Kalimantan Barat">Kalimantan Barat</option>
                                        <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                                        <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                                        <option value="Kalimantan Timur">Kalimantan Timur</option>
                                        <option value="Kalimantan Utara">Kalimantan Utara</option>
                                        <option value="Sulawesi Utara">Sulawesi Utara</option>
                                        <option value="Gorontalo">Gorontalo</option>
                                        <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                                        <option value="Sulawesi Barat">Sulawesi Barat</option>
                                        <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                                        <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                                        <option value="Maluku">Maluku</option>
                                        <option value="Maluku Utara">Maluku Utara</option>
                                        <option value="Papua">Papua</option>
                                        <option value="Papua Barat">Papua Barat</option>
                                        <option value="Papua Selatan">Papua Selatan</option>
                                        <option value="Papua Tengah">Papua Tengah</option>
                                        <option value="Papua Pegunungan">Papua Pegunungan</option>
                                        <option value="Papua Barat Daya">Papua Barat Daya</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="edit_status"
                                        name="status" autocomplete="status" required>
                                        <option value="" selected>Pilih Status</option>
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                        <option value="Database Baru">Database Baru</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                            <h5 class="modal-title" id="opportunityModalLabel">Tambah Perusahaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="perusahaanForm" action="{{ route('store.contact') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="nama_perusahaan">Nama Perusahaan</label>
                                    <input type="text" class="form-control" id="nama_perusahaan"
                                        name="nama_perusahaan" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="kategori_perusahaan">Kategori Perusahaan</label>
                                    <select class="form-select @error('kategori_perusahaan') is-invalid @enderror"
                                        name="kategori_perusahaan" id="kategori_perusahaan"
                                        autocomplete="kategori_perusahaan">
                                        <option value="" selected>Pilih Kategori Perusahaan</option>
                                        <option value="Pemerintahan Daerah">Pemerintahan Daerah</option>
                                        <option value="Kementerian">Kementerian</option>
                                        <option value="Lembaga Pemerintahan">Lembaga Pemerintahan</option>
                                        <option value="BUMN">BUMN</option>
                                        <option value="BUMD">BUMD</option>
                                        <option value="Swasta">Swasta</option>
                                        <option value="Akademik">Akademik</option>
                                        <option value="Bank Daerah">Bank Daerah</option>
                                        <option value="Bank Umum">Bank Umum</option>
                                        <option value="Bank BUMN">Bank BUMN</option>
                                        <option value="Rumah Sakit">Rumah Sakit</option>
                                        <option value="Personal">Personal</option>
                                    </select>
                                    @error('kategori_perusahaan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="lokasi">Lokasi</label>
                                    <select class="form-select" id="lokasi" name="lokasi">
                                        <option value="">Pilih Lokasi</option>
                                        <option value="Aceh">Aceh</option>
                                        <option value="Sumatera Utara">Sumatera Utara</option>
                                        <option value="Sumatera Barat">Sumatera Barat</option>
                                        <option value="Riau">Riau</option>
                                        <option value="Kepulauan Riau">Kepulauan Riau</option>
                                        <option value="Jambi">Jambi</option>
                                        <option value="Bengkulu">Bengkulu</option>
                                        <option value="Sumatera Selatan">Sumatera Selatan</option>
                                        <option value="Bangka Belitung">Bangka Belitung</option>
                                        <option value="Lampung">Lampung</option>
                                        <option value="DKI Jakarta">DKI Jakarta</option>
                                        <option value="Banten">Banten</option>
                                        <option value="Jawa Barat">Jawa Barat</option>
                                        <option value="Jawa Tengah">Jawa Tengah</option>
                                        <option value="DI Yogyakarta">DI Yogyakarta</option>
                                        <option value="Jawa Timur">Jawa Timur</option>
                                        <option value="Bali">Bali</option>
                                        <option value="Nusa Tenggara Barat">Nusa Tenggara Barat</option>
                                        <option value="Nusa Tenggara Timur">Nusa Tenggara Timur</option>
                                        <option value="Kalimantan Barat">Kalimantan Barat</option>
                                        <option value="Kalimantan Tengah">Kalimantan Tengah</option>
                                        <option value="Kalimantan Selatan">Kalimantan Selatan</option>
                                        <option value="Kalimantan Timur">Kalimantan Timur</option>
                                        <option value="Kalimantan Utara">Kalimantan Utara</option>
                                        <option value="Sulawesi Utara">Sulawesi Utara</option>
                                        <option value="Gorontalo">Gorontalo</option>
                                        <option value="Sulawesi Tengah">Sulawesi Tengah</option>
                                        <option value="Sulawesi Barat">Sulawesi Barat</option>
                                        <option value="Sulawesi Selatan">Sulawesi Selatan</option>
                                        <option value="Sulawesi Tenggara">Sulawesi Tenggara</option>
                                        <option value="Maluku">Maluku</option>
                                        <option value="Maluku Utara">Maluku Utara</option>
                                        <option value="Papua">Papua</option>
                                        <option value="Papua Barat">Papua Barat</option>
                                        <option value="Papua Selatan">Papua Selatan</option>
                                        <option value="Papua Tengah">Papua Tengah</option>
                                        <option value="Papua Pegunungan">Papua Pegunungan</option>
                                        <option value="Papua Barat Daya">Papua Barat Daya</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status"
                                        name="status" autocomplete="status">
                                        <option value="" selected>Pilih Status</option>
                                        <option value="Q1">Q1</option>
                                        <option value="Q2">Q2</option>
                                        <option value="Q3">Q3</option>
                                        <option value="Q4">Q4</option>
                                        <option value="Database Baru">Database Baru</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="npwp">NPWP</label>
                                    <input type="text" class="form-control" id="npwp" name="npwp">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="cp">Contact Person (CP)</label>
                                    <input type="text" class="form-control" id="cp" name="cp">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="no_telp">No Telepon</label>
                                    <input type="text" class="form-control" id="no_telp" name="no_telp">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="foto_npwp">Foto NPWP</label>
                                    <input class="form-control" type="file" id="foto_npwp" name="foto_npwp"
                                        accept=".jpeg,.jpg,.png,.pdf">
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
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const tbodyElement = document.querySelector('#perusahaanTable tbody');

    async function fetchData(filters = {}) {
        const params = new URLSearchParams();

        if (filters.nama_perusahaan) params.append('nama_perusahaan', filters.nama_perusahaan);
        if (filters.lokasi) params.append('lokasi', filters.lokasi);
        if (filters.status) params.append('status', filters.status);
        if (filters.sales_key) params.append('sales_key', filters.sales_key);

        try {
            const response = await fetch("{{ route('contact.data') }}?" + params.toString());
            const data = await response.json();

            if (!data.length) {
                tbodyElement.innerHTML = `<tr><td colspan="10" class="text-center">Data tidak ditemukan</td></tr>`;
                return;
            }

            let tbody = '';
            data.forEach((contact, i) => {
                const contactData = JSON.stringify(contact).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                tbody += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${contact.nama_perusahaan}</td>
                    <td>${contact.lokasi}</td>
                    <td>${contact.cp}</td>
                    <td>${contact.no_telp}</td>
                    <td>${contact.status}</td>
                    <td>${contact.sales_key}</td>
                    <td>${contact.kelas_terakhir} ${contact.kelas_terakhir_date ? ' | <span style="color:red;">(' + contact.kelas_terakhir_date + ')</span>' : ''}</td>
                    <td>${contact.aktivitas_terakhir_date || ''}</td>
                    <td>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-sm btn-warning"
                                data-contact="${contactData}"
                                data-bs-toggle="modal"
                                data-bs-target="#editContactModal"
                                onclick="editContactFromButton(this)">
                                Edit
                            </button>

                            <form action="/crm/contact/delete/${contact.id}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus?')"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>

                            <a href="/crm/contact/${contact.id}/detail" class="btn btn-sm btn-info">
                                Detail
                            </a>
                        </div>
                    </td>
                </tr>`;
            });

            tbodyElement.innerHTML = tbody;
        } catch (e) {
            console.error(e);
            tbodyElement.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>`;
        }
    }

    // Ambil elemen filter
    const filterNama = document.getElementById('filter_nama_perusahaan');
    const filterLokasi = document.getElementById('filter_lokasi');
    const filterStatus = document.getElementById('filter_status');
    const filterSales = document.getElementById('filter_sales');

    // Fungsi untuk mengumpulkan nilai filter dan panggil fetchData
    function applyFilters() {
        const filters = {
            nama_perusahaan: filterNama.value.trim(),
            lokasi: filterLokasi.value,
            status: filterStatus.value,
            sales_key: filterSales.value,
        };
        fetchData(filters);
    }

    // Event listener realtime:
    filterNama.addEventListener('input', applyFilters);
    filterLokasi.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);
    filterSales.addEventListener('change', applyFilters);

    // Inisialisasi data pertama kali saat halaman load
    fetchData();

    // Nonaktifkan form submit agar tombol filter tidak reload
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    // Fungsi untuk edit contact dari tombol
    window.editContactFromButton = function(button) {
        let contactStr = button.getAttribute('data-contact');
        let contactJson = contactStr.replace(/&quot;/g, '"').replace(/&apos;/g, "'");
        let contact = JSON.parse(contactJson);
        editContact(contact);
    };

    // Fungsi editContact
    window.editContact = function(contact) {
        document.getElementById('edit_nama_perusahaan').value = contact.nama_perusahaan || '';
        document.getElementById('edit_email').value = contact.email || '';
        document.getElementById('edit_cp').value = contact.cp || '';

        const kategoriSelect = document.getElementById('edit_kategori_perusahaan');
        if (kategoriSelect) kategoriSelect.value = contact.kategori_perusahaan || '';

        const lokasiSelect = document.getElementById('edit_lokasi');
        if (lokasiSelect) lokasiSelect.value = contact.lokasi || '';

        const statusSelect = document.getElementById('edit_status');
        if (statusSelect) statusSelect.value = contact.status || '';

        document.getElementById('edit_npwp').value = contact.npwp || '';
        document.getElementById('edit_alamat').value = contact.alamat || '';
        document.getElementById('edit_no_telp').value = contact.no_telp || '';

        document.getElementById('edit_contact_id').value = contact.id || '';
        const editForm = document.getElementById('editContactForm');
        if (editForm) editForm.action = '/crm/contact/update/' + contact.id;
    };
});

</script>
@endsection
