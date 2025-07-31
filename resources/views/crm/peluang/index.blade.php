@extends('layouts_crm.app')

@section('crm_contents')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Manajemen Lead</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opportunityModal"
                onclick="resetForm()">
                Tambah Lead
            </button>
        </div>

        <!-- Tabel Peluang -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Daftar Lead</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Materi</th>
                                <th>Harga (Rp)</th>
                                <th>Net Sales</th>
                                <th>Pax</th>
                                <th>Periode</th>
                                <th>Tahap</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $peluang)
                                <tr>
                                    <td>{{ $peluang['materi'] }}</td>
                                    <td>{{ number_format($peluang['harga'], 2, ',', '.') }}</td>
                                    <td>{{ number_format($peluang['netsales'], 2, ',', '.') }}</td>
                                    <td>{{ $peluang['pax'] }}</td>
                                    <td>{{ $peluang['periode_mulai'] }} s/d {{ $peluang['periode_selesai'] }}</td>
                                    <td>{{ ucfirst($peluang['tahap']) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('detail.peluang', ['id' => $peluang->id]) }}"
                                                class="btn btn-sm btn-warning">Detail</a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="deletepeluang({{ $peluang['id'] }})">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal untuk Create Peluang -->
        <div class="modal fade" id="opportunityModal" tabindex="-1" aria-labelledby="opportunityModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Lead</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="opportunityForm" action="{{ route('store.peluang') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="id_contact">Contact Client</label>
                                <select class="form-select" id="id_contact" name="id_contact" required>
                                    <option value="" disabled selected>Pilih Contact</option>
                                    @foreach ($contact as $c)
                                        <option value="{{ $c->id }}">{{ $c->nama_perusahaan }} ({{ $c->cp ?? '-'}})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="materi">Materi</label>
                                <select class="form-select" id="materi" name="materi" required>
                                    <option value="" disabled selected>Pilih Materi</option>
                                    @foreach ($materi as $item)
                                        <option value="{{ $item->nama_materi }}">{{ $item->nama_materi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="catatan">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="harga">Harga (Rp)</label>
                                <input type="text" class="form-control" id="harga" name="harga" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="netsales">Net Sales (Rp)</label>
                                <input type="text" class="form-control" id="netsales" name="netsales" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="pax">Jumlah Peserta (Pax)</label>
                                <input type="number" class="form-control" id="pax" name="pax" min="1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="periode_mulai">Periode Mulai</label>
                                <input type="date" class="form-control" id="periode_mulai" name="periode_mulai" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="periode_selesai">Periode Selesai</label>
                                <input type="date" class="form-control" id="periode_selesai" name="periode_selesai" required>
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
    input.addEventListener('input', function() {
        this.value = formatRupiah(this.value);
    });
});

document.getElementById('form-data').addEventListener('submit', function(e) {
    hargaInput.value = unformatRupiah(hargaInput.value);
    netsalesInput.value = unformatRupiah(netsalesInput.value);
});
</script>

@endsection
