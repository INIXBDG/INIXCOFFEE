@extends('layouts_crm.app')

@section('crm_contents')
    @php
        $allowedUser = ['Adm Sales', 'SPV Sales', 'HRD', 'Finance & Accounting', 'GM', 'Direktur Utama', 'Direktur'];
    @endphp

    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Prospect Management</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                    onclick="resetForm()" @if (in_array(Auth::user()->jabatan, $allowedUser)) disabled @endif>
                    Tambah Lead
                </button>
            </div>

            <!-- Tabel Peluang -->
            <div class="card ">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="peluangTable" class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th style="text-align:center;">No</th>
                                    <th style="text-align: center;">Materi</th>
                                    <th style="text-align: center;">Client</th>
                                    <th style="text-align: center;">Harga (Rp)</th>
                                    <th style="text-align: center;">Net Sales</th>
                                    <th style="text-align: center;">Pax</th>
                                    <th style="text-align: center;">Periode</th>
                                    <th style="text-align: center;">Tahap</th>
                                    <th style="text-align: center;">Sales</th>
                                    <th style="text-align: center;">Prospek Terbuat</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tambah Lead Modal -->
            <div class="modal fade" id="opportunityModal" tabindex="-1" aria-labelledby="opportunityModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="form-data" action="{{ route('store.peluang') }}" method="POST"
                                class="needs-validation" novalidate>
                                @csrf

                                <!-- Form Kontak -->
                                <div class="mb-3">
                                    <label class="form-label" for="id_perusahaan">Perusahaan</label>
                                    <select class="form-select" id="id_perusahaan" name="id_contact" required>
                                        <option value="" disabled selected>Pilih Perusahaan</option>
                                        @foreach ($Perusahaan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}
                                                ({{ $p->cp ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Pilih Perusahaan.</div>
                                </div>

                                <!-- Form Materi -->
                                <div class="mb-3">
                                    <label class="form-label" for="materi">Materi</label>
                                    <select class="form-select" id="materi" name="materi" required>
                                        <option value="" disabled selected>Pilih Materi</option>
                                        @foreach ($materi as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_materi }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Pilih materi.</div>
                                </div>

                                <!-- Lain-lain -->
                                <div class="mb-3">
                                    <label class="form-label" for="catatan">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="harga">Harga Penawaran (Rp)</label>
                                    <input type="text" class="form-control" id="harga" name="harga" required>
                                    <div class="invalid-feedback">Masukkan harga.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="pax">Jumlah Peserta (Pax)</label>
                                    <input type="number" class="form-control" id="pax" name="pax" min="1"
                                        required>
                                    <div class="invalid-feedback">Masukkan jumlah peserta.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="periode_mulai">Periode Mulai</label>
                                    <input type="date" class="form-control" id="periode_mulai" name="periode_mulai">
                                    <div class="invalid-feedback">Pilih tanggal mulai.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="periode_selesai">Periode Selesai</label>
                                    <input type="date" class="form-control" id="periode_selesai"
                                        name="periode_selesai">
                                    <div class="invalid-feedback">Pilih tanggal selesai.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="metode_kelas">Metode Kelas</label>
                                    <select class="form-select" id="metode_kelas" name="metode_kelas" required>
                                        <option value="" disabled selected>Pilih Metode Kelas</option>
                                        <option value="Inhouse Bandung">Inhouse Bandung</option>
                                        <option value="Inhouse Luar Bandung">Inhouse Luar Bandung</option>
                                        <option value="Offline">Offline</option>
                                        <option value="Virtual">Virtual</option>
                                        <!-- Tambahkan opsi lain jika diperlukan -->
                                    </select>
                                    <div class="invalid-feedback">Pilih metode kelas.</div>
                                </div>

                                <!-- Tambahan: Event (Dropdown) -->
                                <div class="mb-3">
                                    <label class="form-label" for="event">Event</label>
                                    <select class="form-select" id="event" name="event" required>
                                        <option value="" disabled selected>Pilih Event</option>
                                        <option value="Kelas">Kelas</option>
                                        <option value="Workshop">Workshop</option>
                                        <option value="Webinar">Webinar</option>
                                        <option value="Narasumber">Narasumber</option>
                                        <option value="Pinjam Instruktur">Pinjam Instruktur</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih event.</div>
                                </div>

                                <!-- Tambahan: Exam (Button Toggle) -->
                                <div class="mb-3">
                                    <label class="form-label">Exam</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="examToggle" role="switch"
                                            onchange="document.getElementById('exam').value = this.checked ? '1' : '0';">
                                        <label class="form-check-label" for="examToggle">Aktif</label>
                                    </div>
                                    <input type="hidden" id="exam" name="exam" value="0">
                                    <div class="invalid-feedback">Pilih status exam.</div>
                                </div>

                                <!-- Tambahan: Authorize (Toggle Switch) -->
                                <div class="mb-3">
                                    <label class="form-label">Authorize</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="authorizeToggle"
                                            role="switch"
                                            onchange="document.getElementById('authorize').value = this.checked ? '1' : '0';">
                                        <label class="form-check-label" for="authorizeToggle">Aktif</label>
                                    </div>
                                    <input type="hidden" id="authorize" name="authorize" value="0">
                                    <div class="invalid-feedback">Pilih status authorize.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tentatif</label>
                                    <input type="hidden" name="tentatif" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="tentatifSwitch" name="tentatif" value="1"
                                            {{ old('tentatif', $model->tentatif ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tentatifSwitch">Tentatif</label>
                                    </div>
                                </div>

                                <!-- Aktivitas yang bisa dikaitkan -->
                                <div class="mb-3">
                                    <label class="form-label">Pilih Aktivitas (Opsional)</label>
                                    <div id="aktivitasTableWrapper" class="overflow-auto">
                                        <p class="text-muted">Silakan pilih contact client terlebih dahulu.</p>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Global CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $(document).ready(function() {
            let table = $('#peluangTable').DataTable({
                processing: true,
                ajax: {
                    url: '{{ route('index.peluang.json') }}',
                    dataSrc: function(json) {
                        console.log("Data dari server:", json);
                        return json.data;
                    },
                    error: function(xhr, error, thrown) {
                        alert('Gagal memuat data peluang: ' + thrown);
                    }
                },
                columns: [
                    { data: null, className: "text-center", orderable: false, searchable: false }, // nomor urut
                    {
                        data: null,
                        render: function(data, type, row) {
                            return row.materi_relation?.nama_materi || '-';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const namaPerusahaan = row.rkm_data?.perusahaan?.nama_perusahaan || '-';
                            const cp = row.rkm_data?.perusahaan?.cp;
                            return cp ? namaPerusahaan + ' (' + cp + ')' : namaPerusahaan;
                        }
                    },
                    {
                        data: 'harga',
                        render: function(data, type, row) {
                            return data ? 'Rp ' + parseInt(data).toLocaleString('id-ID') : 'Rp 0';
                        }
                    },
                    {
                        data: 'netsales',
                        render: function(data, type, row) {
                            return data ? 'Rp ' + parseInt(data).toLocaleString('id-ID') : 'Rp 0,00';
                        }
                    },
                    { data: 'pax' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            const startDate = data.periode_mulai ? moment(data.periode_mulai).format('DD-MM-YYYY') : '';
                            const endDate = data.periode_selesai ? moment(data.periode_selesai).format('DD-MM-YYYY') : '';
                            return startDate && endDate ? `${startDate} s/d ${endDate}` : 'Tentatif';
                        }
                    },
                    {
                        data: 'tahap',
                        render: function(data, type, row) {
                            return data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
                        }
                    },
                    { data: 'id_sales' },
                    {
                        data: 'created_at',
                        render: function(data, type, row) {
                            return data ? moment(data).format('DD-MM-YYYY') : '-';
                        }
                    },
                    { data: 'id', render: function(id, type, data) {
                        const rkm = data.rkm_formatted;
                        const isLost = data.tahap?.toLowerCase() === 'lost';
                        let rkmButton = '';

                        if (isLost || !rkm) {
                            rkmButton = `<span class="btn btn-sm btn-info disabled w-100" style="pointer-events: none; opacity: 0.5;">RKM</span>`;
                        } else {
                            rkmButton = `<a class="btn btn-sm btn-info w-100" target="_blank" href="/rkm/${rkm.materi_key}ixb${rkm.tanggal_awal_day}ie${rkm.tanggal_awal_year}ie${rkm.tanggal_awal_month}ixb${rkm.metode_kelas}">RKM</a>`;
                        }

                        return `
                            <div class="d-flex flex-column gap-2" style="min-width: 80px;">
                                <a href="/crm/peluang/detail/${id}" class="btn btn-sm btn-warning w-100">Detail</a>
                                ${rkmButton}
                                <button onclick="hapusPeluang(${id})" class="btn btn-sm btn-danger w-100">LOST</button>
                            </div>
                        `;
                    }}
                ],
                order: [[7, 'desc']]
            });

            // 🔹 Callback untuk nomor urut tetap mengikuti tampilan
            table.on('order.dt search.dt draw.dt', function() {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            initPerusahaanSelect2();
            initMateriSelect2();

            // Pindah event listener ke sini untuk consistency dengan jQuery dan Select2
            $('#id_perusahaan').on('change', function() {
                const perusahaanId = $(this).val();

                if (!perusahaanId) {
                    $('#aktivitasTableWrapper').html(
                        `<p class="text-muted">Silakan pilih contact client terlebih dahulu.</p>`);
                    return;
                }

                $.ajax({
                    url: `/crm/ambil/aktivitas/${perusahaanId}`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Asumsi response mirip DataTable: { data: [...] }, jadi ambil data.data
                        const activities = data.data || data; // Fallback jika langsung array

                        if (!Array.isArray(activities) || activities.length === 0) {
                            $('#aktivitasTableWrapper').html(
                                `<p class="text-muted">Tidak ada aktivitas yang tersedia untuk contact ini.</p>`);
                            return;
                        }

                        let table = `
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pilih</th>
                                            <th>Kontak</th>
                                            <th>Jenis Aktivitas</th>
                                            <th>Subjek</th>
                                            <th>Deskripsi</th>
                                            <th>Waktu Aktivitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;

                        activities.forEach(a => {
                            const waktu = new Date(a.waktu).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            });
                            table += `
                                <tr>
                                    <td><input type="checkbox" name="id_aktivitas[]" value="${a.id}"></td>
                                    <td>${a.kontak || '-'}</td>
                                    <td>${a.aktivitas || '-'}</td>
                                    <td>${a.subject || '-'}</td>
                                    <td>${a.deskripsi ?? '-'}</td>
                                    <td>${waktu}</td>
                                </tr>
                            `;
                        });

                        table += `</tbody></table></div>`;
                        $('#aktivitasTableWrapper').html(table);
                    },
                    error: function(err) {
                        console.error('Gagal memuat aktivitas:', err);
                        $('#aktivitasTableWrapper').html(
                            `<p class="text-danger">Terjadi kesalahan saat memuat aktivitas. Periksa console untuk detail.</p>`);
                    }
                });
            });
        });

        function initPerusahaanSelect2() {
            var $select = $('#id_perusahaan');

            // safety: pastikan select2 tersedia
            if (typeof $.fn.select2 !== 'function') {
                console.error('Select2 belum ter-load!');
                return;
            }

            // cari modal parent (jika ada)
            var $closestModal = $select.closest('.modal');

            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                // pastikan dropdown di-append ke modal (atau body jika tidak ada modal)
                dropdownParent: $closestModal.length ? $closestModal : $(document.body)
            });
        }

        function initMateriSelect2() {
            var $select = $('#materi');

            // safety: pastikan select2 tersedia
            if (typeof $.fn.select2 !== 'function') {
                console.error('Select2 belum ter-load!');
                return;
            }

            // cari modal parent (jika ada)
            var $closestModal = $select.closest('.modal');

            $select.select2({
                width: '100%',
                theme: 'bootstrap-5',
                // pastikan dropdown di-append ke modal (atau body jika tidak ada modal)
                dropdownParent: $closestModal.length ? $closestModal : $(document.body)
            });
        }

        function resetForm() {
            const form = document.getElementById('form-data');
            form.reset();
            document.getElementById('aktivitasTableWrapper').innerHTML =
                `<p class="text-muted">Silakan pilih contact client terlebih dahulu.</p>`;
            form.classList.remove('was-validated');
        }

        function hapusPeluang(id) {
            if (!confirm("Yakin ingin menghapus peluang ini?")) return;

            fetch(`/crm/peluang/delete/${id}`, {
                    method: 'put',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json(); // Parse JSON response
                    } else {
                        throw new Error('Gagal mengubah status.');
                    }
                })
                .then(data => {
                    alert(data.message || 'Peluang berhasil diubah statusnya.'); // Show success message
                    $('#peluangTable').DataTable().ajax.reload(); // Refresh DataTable
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert(error.message || 'Terjadi kesalahan saat mengubah status data.');
                });
        }

        function formatRupiah(angka) {
            let numberString = angka.replace(/[^,\d]/g, '').toString();
            let split = numberString.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah ? 'Rp ' + rupiah : '';
        }

        function unformatRupiah(rupiah) {
            return rupiah.replace(/[^0-9]/g, '');
        }

        const hargaInput = document.getElementById('harga');
        const netsalesInput = document.getElementById('netsales');

        [hargaInput, netsalesInput].forEach(input => {
            if (input !== null) {
                input.addEventListener('input', function() {
                    this.value = formatRupiah(this.value);
                });
            }
        });

        const formData = document.getElementById('form-data');

        if (formData !== null) {
            formData.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');

                if (hargaInput !== null) {
                    hargaInput.value = unformatRupiah(hargaInput.value);
                }
                if (netsalesInput !== null) {
                    netsalesInput.value = unformatRupiah(netsalesInput.value);
                }
            });
        }
    </script>
@endsection
