@extends('layouts_crm.app')

@section('crm_contents')
    <style>
        h1, h2, h3, h4, h5, h6, p, span, div, td, th { font-display: swap !important; }
        .skeleton-cell {
            background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
            background-size: 200% 100%;
            animation: pulse 1.5s infinite;
            color: transparent;
            user-select: none;
        }
        @keyframes pulse { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    </style>

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Database Client</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick="exportPdf()">Export PDF</button>
                    <!-- Menggunakan Gate Laravel Standar -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                        onclick="resetForm()" @can('Store Contact CRM') @else disabled @endcan>
                        Tambah Perusahaan
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Menggunakan Gate Laravel Standar -->
                    @can('akses-filter-sales')
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="filterSales" class="form-label">Filter Sales</label>
                                <select id="filterSales" class="form-select">
                                    <option value="">Cari Sales</option>
                                    @foreach ($sales as $item)
                                        <option value="{{$item->kode_karyawan}}">{{$item->nama_lengkap}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endcan

                    <div id="dt-space-reserver" style="height: 50px; width: 100%;"></div>

                    <div class="table-responsive" style="min-height: 500px;">
                        <table class="table table-bordered table-hover w-100" id="perusahaanTable">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Perusahaan</th>
                                    <th class="text-center">Lokasi</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Sales</th>
                                    <th class="text-center">Kelas Terakhir</th>
                                    <th class="text-center">Aktivitas Terakhir</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < 10; $i++)
                                    <!-- Menyesuaikan tinggi dengan tumpukan tombol di kolom aksi -->
                                    <tr style="height: 95px;">
                                        @for ($j = 0; $j < 8; $j++)
                                            <td class="skeleton-cell border-bottom">&nbsp;</td>
                                        @endfor
                                    </tr>
                                @endfor
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
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
                                    <label class="form-label" for="edit_email">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email"
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
            <div class="modal fade" id="opportunityModal" tabindex="-1" aria-labelledby="opportunityModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="opportunityModalLabel">Tambah Perusahaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="perusahaanForm" action="{{ route('store.contact') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="nama_perusahaan">Nama Perusahaan</label>
                                    <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="kategori_perusahaan">Kategori Perusahaan</label>
                                    <select class="form-select @error('kategori_perusahaan') is-invalid @enderror" name="kategori_perusahaan" id="kategori_perusahaan" autocomplete="kategori_perusahaan">
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
                                        @foreach ($lokasi as $item)
                                            <option value="{{ $item->lokasi }}">{{ $item->lokasi }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" autocomplete="status" required>
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
                                    <input class="form-control" type="file" id="foto_npwp" name="foto_npwp" accept=".jpeg,.jpg,.png,.pdf">
                                </div>

                                <!-- Penambahan ID btn-submit-perusahaan -->
                                <button type="submit" id="btn-submit-perusahaan" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        $(document).ready(function() {
            let table;

            setTimeout(() => {
                table = $('#perusahaanTable').DataTable({
                    processing: false,
                    serverSide: true,
                    deferRender: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('contact.data') }}",
                        type: "GET",
                        data: function(d) {
                            const filterSalesElement = document.getElementById('filterSales');
                            if (filterSalesElement) d.sales_key = filterSalesElement.value;
                        },
                        error: function(xhr, error, thrown) {
                            alert('Gagal memuat data perusahaan: ' + thrown);
                        }
                    },
                    initComplete: function(settings, json) {
                        const reserver = document.getElementById('dt-space-reserver');
                        if (reserver) reserver.style.display = 'none';
                    },
                    columns: [
                        { data: null, className: "text-center", orderable: false, searchable: false },
                        { data: 'nama_perusahaan', name: 'nama_perusahaan' },
                        { data: 'lokasi', name: 'lokasi' },
                        { data: 'status', name: 'status' },
                        { data: 'sales_key', name: 'sales_key' },
                        {
                            data: 'kelas_terakhir',
                            name: 'kelas_terakhir',
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                if (data === 'Belum ada kelas') return data;
                                return `${data} ${row.kelas_terakhir_date ? '| <span style="color:red;">(' + row.kelas_terakhir_date + ')</span>' : ''}`;
                            }
                        },
                        { data: 'aktivitas_terakhir_date', name: 'aktivitas_terakhir_date', orderable: false, searchable: false },
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            render: function (id, type, row) {
                                const contactData = JSON.stringify(row).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                                return `
                                    <div class="d-flex flex-column gap-2">
                                        <a href="/crm/contact/${id}/detail" class="btn btn-sm btn-info w-100">Detail</a>
                                        <button class="btn btn-sm btn-warning w-100"
                                            data-contact="${contactData}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editContactModal"
                                            onclick="editContactFromButton(this)">Edit</button>
                                        <form action="/crm/contact/delete/${id}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')" style="display:inline;">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-danger w-100">Hapus</button>
                                        </form>
                                    </div>`;
                            }
                        }
                    ]
                });

                table.on('draw.dt', function() {
                    let info = table.page.info();
                    table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                        cell.innerHTML = info.start + i + 1;
                    });
                });

                $('#filterSales').on('change', function() { table.ajax.reload(); });

            }, 10);

            window.exportPdf = function() {
                const salesFilter = document.getElementById('filterSales') ? document.getElementById('filterSales').value : '';
                const searchFilter = $('#perusahaanTable').DataTable().search() || '';
                const url = "{{ route('contact.export_pdf') }}?sales_key=" + encodeURIComponent(salesFilter) + "&search=" + encodeURIComponent(searchFilter);
                window.open(url, '_blank');
            };

            window.editContactFromButton = function(button) {
                const contactJson = button.getAttribute('data-contact').replace(/&quot;/g, '"').replace(/&apos;/g, "'");
                editContact(JSON.parse(contactJson));
            };

            window.editContact = function(contact) {
                document.getElementById('edit_nama_perusahaan').value = contact.nama_perusahaan || '';
                document.getElementById('edit_email').value = contact.email || '';

                ['edit_kategori_perusahaan', 'edit_lokasi', 'edit_status'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = contact[id.replace('edit_', '')] || '';
                });

                document.getElementById('edit_npwp').value = contact.npwp || '';
                document.getElementById('edit_alamat').value = contact.alamat || '';
                document.getElementById('edit_contact_id').value = contact.id || '';

                const editForm = document.getElementById('editContactForm');
                if (editForm) editForm.action = '/crm/contact/update/' + contact.id;
            };
        });
    </script>
@endsection
