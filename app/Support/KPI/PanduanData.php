<?php

namespace App\Support\KPI;

class PanduanData
{
    private static ?array $data = null;

    public static function all(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $panduan = [
            // =====================================================
            // 1. PELAKSANAAN KEGIATAN KARYAWAN
            // =====================================================
            'pelaksanaan_kegiatan_karyawan' => [
                'judul' => 'Pelaksanaan Kegiatan Karyawan',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>berapa persen kegiatan karyawan yang dihadiri dengan baik</strong>
                    (minimal 80% peserta hadir).
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li><strong>Tabel Kegiatan</strong> → daftar kegiatan yang sudah dibuat di tahun tersebut</li>
                    <li><strong>Tabel Absensi Karyawan</strong> → data kehadiran peserta di hari kegiatan</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                <ol>
                    <li>Ambil semua kegiatan di tahun yang dipilih (contoh: 2025)</li>
                    <li>Untuk setiap kegiatan, hitung:
                        <br><code>Persentase Hadir = (Jumlah yang absen "Masuk" ÷ Total peserta) × 100</code>
                    </li>
                    <li>Jika persentase ≥ 80% → kegiatan dianggap <strong>berhasil</strong></li>
                    <li>Progress akhir = (Jumlah kegiatan berhasil ÷ Total kegiatan) × 100</li>
                </ol>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br>
                    Ada 4 kegiatan di tahun 2025:
                    <ul class="mb-2 mt-2">
                        <li>Kegiatan A → 9 dari 10 peserta hadir = <strong>90%</strong> ✅ (berhasil)</li>
                        <li>Kegiatan B → 7 dari 10 peserta hadir = <strong>70%</strong> ❌ (gagal)</li>
                        <li>Kegiatan C → 8 dari 10 peserta hadir = <strong>80%</strong> ✅ (berhasil)</li>
                        <li>Kegiatan D → 5 dari 10 peserta hadir = <strong>50%</strong> ❌ (gagal)</li>
                    </ul>
                    Kegiatan yang berhasil: 2 dari 4<br>
                    <strong>Progress = (2 ÷ 4) × 100 = 50%</strong>
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena salah satu alasan berikut:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / salah</td>
                                <td>Pastikan saat buat target, kolom tahun sudah diisi (contoh: 2025)</td>
                            </tr>
                            <tr>
                                <td>Belum ada kegiatan di tahun tersebut</td>
                                <td>Buat data kegiatan terlebih dahulu di menu Kegiatan</td>
                            </tr>
                            <tr>
                                <td>Peserta kegiatan kosong</td>
                                <td>Pastikan setiap kegiatan sudah diisi daftar pesertanya</td>
                            </tr>
                            <tr>
                                <td>Peserta tidak absen "Masuk"</td>
                                <td>Pastikan karyawan yang ikut kegiatan sudah melakukan absensi dengan status <strong>Masuk</strong> di hari yang sama</td>
                            </tr>
                            <tr>
                                <td>Tanggal absensi tidak sama dengan hari kegiatan</td>
                                <td>Absensi harus dilakukan di tanggal yang sama dengan waktu kegiatan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong> Coba cek 1 kegiatan yang sudah ada. Lihat apakah daftar pesertanya terisi dan apakah mereka sudah absen "Masuk" di hari kegiatan tersebut.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Buat Target</strong><br>
                        HRD membuat target KPI dengan memilih Assistant Route "Pelaksanaan Kegiatan Karyawan" dan mengisi tahun.
                    </li>
                    <li class="mb-2"><strong>Buat Kegiatan</strong><br>
                        Setiap ada kegiatan (training, gathering, dll), data kegiatan dibuat dan diisi daftar pesertanya.
                    </li>
                    <li class="mb-2"><strong>Peserta Absen</strong><br>
                        Pada hari kegiatan, peserta melakukan absensi dengan status "Masuk".
                    </li>
                    <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                        Sistem otomatis menghitung berapa persen kegiatan yang dihadiri ≥ 80% peserta.
                    </li>
                    <li class="mb-0"><strong>Lihat Hasil</strong><br>
                        Progress muncul di dashboard KPI. Bisa dilihat detail per bulan dan per hari.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 2. PENGELUARAN BIAYA KARYAWAN
            // =====================================================
            'pengeluaran_biaya_karyawan' => [
                'judul' => 'Pengeluaran Biaya Karyawan',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>seberapa baik pengeluaran biaya karyawan dikelola</strong>.
                    Progress dihitung dari <strong>4 komponen</strong>, masing-masing maksimal <strong>25 poin</strong>
                    (total maksimal 100).
                </p>

                <h6 class="fw-semibold">4 Komponen yang Dinilai</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:18%">Komponen</th>
                                <th style="width:32%">Sumber Data</th>
                                <th>Cara Dapat Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1. Gaji</strong></td>
                                <td>Tabel <code>log_gaji</code><br>
                                    (gaji + tunjangan jabatan)</td>
                                <td>
                                    Ada data gaji di tahun tersebut → <strong>25 poin</strong><br>
                                    Tidak ada data sama sekali → <strong>0 poin</strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>2. BPJS</strong></td>
                                <td>Tabel <code>tunjangan_karyawan</code><br>
                                    (jenis: BPJS Tenaga Kerja & BPJS Kesehatan)</td>
                                <td>
                                    Jika ada data budget:<br>
                                    • Realisasi ≤ budget → <strong>25 poin</strong><br>
                                    • Realisasi > budget → poin dikurangi sesuai kelebihan
                                </td>
                            </tr>
                            <tr>
                                <td><strong>3. Rekrutmen</strong></td>
                                <td>Tabel <code>kegiatan</code><br>
                                    (tipe = rekrutment) → kolom realisasi</td>
                                <td>
                                    Jika ada data budget:<br>
                                    • Realisasi ≤ budget → <strong>25 poin</strong><br>
                                    • Realisasi > budget → poin dikurangi sesuai kelebihan
                                </td>
                            </tr>
                            <tr>
                                <td><strong>4. Kegiatan</strong></td>
                                <td>Tabel <code>kegiatan</code> (tipe = kegiatan)<br>
                                    + relasi <code>pengajuan_barang.detail</code></td>
                                <td>
                                    Budget dihitung dari qty × harga di pengajuan barang.<br>
                                    • Realisasi ≤ budget → <strong>25 poin</strong><br>
                                    • Realisasi > budget → poin dikurangi sesuai kelebihan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-semibold mt-4">Cara Menghitung Jika Melebihi Budget</h6>
                <p>Jika realisasi lebih besar dari budget, sistem menghitung seperti ini:</p>
                <ol>
                    <li>Hitung persentase kelebihan:<br>
                        <code>Persentase Kelebihan = ((Realisasi − Budget) ÷ Budget) × 100</code>
                    </li>
                    <li>Kurangi dari 100%, lalu kalikan 25:<br>
                        <code>Skor = (100 − Persentase Kelebihan) ÷ 100 × 25</code>
                    </li>
                    <li>Jika hasilnya negatif, skor menjadi <strong>0</strong></li>
                </ol>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br><br>
                    <strong>Gaji:</strong> Ada data gaji → <strong>25 poin</strong><br><br>

                    <strong>BPJS:</strong><br>
                    Budget = Rp 10.000.000<br>
                    Realisasi = Rp 10.000.000 → tidak melebihi → <strong>25 poin</strong><br><br>

                    <strong>Rekrutmen:</strong><br>
                    Budget = Rp 5.000.000<br>
                    Realisasi = Rp 6.000.000 → kelebihan 20%<br>
                    Skor = (100 − 20) ÷ 100 × 25 = <strong>20 poin</strong><br><br>

                    <strong>Kegiatan:</strong><br>
                    Budget (dari pengajuan barang) = Rp 8.000.000<br>
                    Realisasi = Rp 12.000.000 → kelebihan 50%<br>
                    Skor = (100 − 50) ÷ 100 × 25 = <strong>12,5 poin</strong><br><br>

                    <strong>Progress total = 25 + 25 + 20 + 12,5 = 82,5%</strong>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Catatan penting:</strong><br>
                    Untuk komponen <strong>Kegiatan</strong>, budget dihitung dari detail pengajuan barang
                    (qty × harga). Sedangkan realisasi diambil dari kolom <code>realisasi</code> di tabel kegiatan.
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau sangat rendah, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / tidak valid</td>
                                <td>Pastikan saat membuat target, tahun sudah diisi dengan benar (contoh: 2025)</td>
                            </tr>
                            <tr>
                                <td>Belum ada data gaji sama sekali</td>
                                <td>Pastikan data gaji sudah masuk ke sistem (tabel Log Gaji)</td>
                            </tr>
                            <tr>
                                <td>Jenis tunjangan BPJS tidak ditemukan</td>
                                <td>Pastikan di master data ada nama <strong>"BPJS Tenaga Kerja"</strong> dan <strong>"BPJS Kesehatan"</strong></td>
                            </tr>
                            <tr>
                                <td>Belum ada kegiatan tipe "rekrutment"</td>
                                <td>Buat data kegiatan dengan tipe <strong>rekrutment</strong> dan isi kolom realisasi</td>
                            </tr>
                            <tr>
                                <td>Belum ada kegiatan tipe "kegiatan"</td>
                                <td>Buat data kegiatan dengan tipe <strong>kegiatan</strong></td>
                            </tr>
                            <tr>
                                <td>Pengajuan barang belum terhubung ke kegiatan</td>
                                <td>Pastikan pengajuan barang sudah dihubungkan ke kegiatan, dan detail qty × harga terisi lengkap</td>
                            </tr>
                            <tr>
                                <td>Realisasi jauh melebihi budget</td>
                                <td>Semakin besar kelebihannya, semakin kecil poin yang didapat. Usahakan realisasi tidak melebihi anggaran</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    <strong>Tips:</strong> Komponen yang tidak punya data sama sekali akan mendapat 0 poin.
                    Komponen yang melebihi budget masih bisa dapat poin, tapi berkurang sesuai besar kelebihannya.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Buat Target</strong><br>
                        HRD membuat target KPI dengan memilih Assistant Route
                        <em>"Pengeluaran Biaya Karyawan"</em> dan mengisi tahun.
                    </li>
                    <li class="mb-2">
                        <strong>Data Masuk ke Sistem</strong><br>
                        Data gaji, BPJS, kegiatan rekrutmen, dan kegiatan rutin masuk melalui modul masing-masing.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Mengambil Data</strong><br>
                        Sistem mengambil data 4 komponen di tahun yang dipilih.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Menghitung Skor</strong><br>
                        Setiap komponen dinilai maksimal 25 poin:
                        <ul class="mt-1 mb-0">
                            <li>Gaji → ada data = 25, tidak ada = 0</li>
                            <li>BPJS, Rekrutmen, Kegiatan → penuh 25 jika tidak melebihi budget,
                                dikurangi jika melebihi</li>
                        </ul>
                    </li>
                    <li class="mb-2">
                        <strong>Total Progress</strong><br>
                        Keempat skor dijumlahkan (maksimal 100).
                    </li>
                    <li class="mb-0">
                        <strong>Lihat Hasil</strong><br>
                        Progress dan skor per komponen bisa dilihat di halaman detail KPI.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 3. ADMINISTRASI KARYAWAN
            // =====================================================
            'administrasi_karyawan' => [
                'judul' => 'Administrasi Karyawan',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>seberapa tepat waktu penyelesaian administrasi karyawan</strong>.
                    Semakin cepat diselesaikan (tidak telat), semakin tinggi skornya.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Semua data diambil dari tabel <strong>Administrasi Karyawan</strong></li>
                    <li>Yang dilihat: status, tanggal deadline (dateline), dan tanggal selesai</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung Skor per Data</h6>
                <ul>
                    <li>Jika status <strong>bukan "selesai"</strong> → skor = <strong>0</strong></li>
                    <li>Jika status <strong>"selesai"</strong>:
                        <ul>
                            <li>Tidak telat → skor = <strong>1</strong> (sempurna)</li>
                            <li>Telat 1 hari → skor = 0,9</li>
                            <li>Telat 2 hari → skor = 0,8</li>
                            <li>... dan seterusnya (setiap hari telat dikurangi 0,1)</li>
                            <li>Telat 7 hari atau lebih → skor = <strong>0</strong></li>
                        </ul>
                    </li>
                </ul>

                <p class="mt-3">
                    <strong>Progress akhir</strong> = (Total skor semua data ÷ Jumlah data) × 100
                </p>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br>
                    Ada 4 data administrasi di tahun 2025:
                    <ul class="mb-2 mt-2">
                        <li>Data 1 → selesai tepat waktu → skor <strong>1</strong></li>
                        <li>Data 2 → telat 2 hari → skor <strong>0,8</strong></li>
                        <li>Data 3 → belum selesai → skor <strong>0</strong></li>
                        <li>Data 4 → telat 8 hari → skor <strong>0</strong></li>
                    </ul>
                    Total skor = 1 + 0,8 + 0 + 0 = 1,8<br>
                    <strong>Progress = (1,8 ÷ 4) × 100 = 45%</strong>
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau rendah, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi</td>
                                <td>Pastikan tahun sudah diisi saat membuat target</td>
                            </tr>
                            <tr>
                                <td>Belum ada data administrasi</td>
                                <td>Input data administrasi karyawan terlebih dahulu</td>
                            </tr>
                            <tr>
                                <td>Banyak data belum berstatus "selesai"</td>
                                <td>Update status menjadi "selesai" setelah pekerjaan selesai</td>
                            </tr>
                            <tr>
                                <td>Kolom deadline atau tanggal selesai kosong</td>
                                <td>Isi lengkap tanggal deadline dan tanggal selesai</td>
                            </tr>
                            <tr>
                                <td>Banyak data terlambat ≥ 7 hari</td>
                                <td>Usahakan menyelesaikan administrasi maksimal 6 hari setelah deadline agar masih mendapat skor</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong> Semakin tepat waktu, semakin tinggi skor.
                    Bahkan telat 1–6 hari masih dapat poin (meski berkurang).
                    Telat 7 hari ke atas = 0 poin.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Buat Target</strong><br>
                        Buat target KPI dengan Assistant Route "Administrasi Karyawan" dan pilih tahun.
                    </li>
                    <li class="mb-2"><strong>Input Data Administrasi</strong><br>
                        Setiap ada administrasi karyawan (surat, dokumen, dll), data diinput lengkap dengan deadline.
                    </li>
                    <li class="mb-2"><strong>Selesaikan & Update Status</strong><br>
                        Setelah selesai, update status menjadi "selesai" dan isi tanggal selesai.
                    </li>
                    <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                        Sistem otomatis menilai ketepatan waktu setiap data, lalu menghitung rata-rata skor.
                    </li>
                    <li class="mb-0"><strong>Lihat Hasil</strong><br>
                        Progress muncul di dashboard. Bisa dilihat juga breakdown per bulan.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 4. MENGUKUR KUALITAS APLIKASI AGAR MINIM BUG
            // =====================================================
            'mengukur_kualitas_aplikasi_agar_minim_bug' => [
                'judul' => 'Mengukur Kualitas Aplikasi Agar Minim Bug',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini menilai kualitas output programmer berdasarkan
                    <strong>rasio Request vs Error</strong> dan
                    <strong>kecepatan penyelesaian bug</strong>.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Fitur Ticketing</li>
                    <li>Hanya ticket milik PIC yang terdaftar di target (umumnya mapping nama depan karyawan)</li>
                    <li>Filter tahun berdasarkan tahun target</li>
                    <li>Error yang dihitung: <code>kategori = "Error (Aplikasi)"</code> + <code>keperluan = "Programming"</code> + sudah <code>diselesaikan</code></li>
                </ul>

                <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                <pre class="bg-light p-3 rounded border small">skorRasio     = (jumlahRequest / totalTicket) × 100
                rataSkorError = rata-rata skor setiap ticket error
                skorKualitas  = (skorRasio × 0.5) + (rataSkorError × 0.5)
                progress      = min(100, (skorKualitas / nilaiTarget) × 100)</pre>

                <h6 class="fw-semibold mt-4">Penilaian per Ticket Error</h6>
                <p>Menggunakan <strong>jam kerja</strong> (<code>hitungJamKerja</code>), bukan jam kalender.</p>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tingkat Kesulitan</th>
                                <th>Durasi (jam kerja)</th>
                                <th>Skor Dasar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="4"><strong>Major</strong></td>
                                <td>≤ 40</td>
                                <td>100</td>
                            </tr>
                            <tr>
                                <td>≤ 56</td>
                                <td>80</td>
                            </tr>
                            <tr>
                                <td>≤ 72</td>
                                <td>60</td>
                            </tr>
                            <tr>
                                <td>> 72</td>
                                <td>30</td>
                            </tr>
                            <tr>
                                <td rowspan="4"><strong>Selain Major</strong></td>
                                <td>≤ 4</td>
                                <td>100</td>
                            </tr>
                            <tr>
                                <td>≤ 8</td>
                                <td>80</td>
                            </tr>
                            <tr>
                                <td>≤ 24</td>
                                <td>60</td>
                            </tr>
                            <tr>
                                <td>> 24</td>
                                <td>30</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mb-2">Bobot setelah skor dasar:</p>
                <ul>
                    <li><code>Major</code> → × 1.5</li>
                    <li><code>Moderate</code> → × 1.2</li>
                    <li>Lainnya → × 1.0</li>
                </ul>
                <p>Hasil akhir per ticket dibatasi maksimal 100: <code>min(100, skorDurasi × bobot)</code></p>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh:</strong><br>
                    Request = 30 | Error = 10 → totalTicket = 40<br>
                    skorRasio = (30/40) × 100 = <strong>75</strong><br><br>
                    Dari 10 error:
                    <ul class="mb-1">
                        <li>4 Major ≤ 40 jam → 100 × 1.5 → dibatasi 100</li>
                        <li>3 non-Major ≤ 4 jam → 100</li>
                        <li>3 non-Major > 24 jam → 30</li>
                    </ul>
                    rataSkorError = 79<br>
                    skorKualitas = (75 × 0.5) + (79 × 0.5) = <strong>77</strong><br>
                    Jika nilaiTarget = 80 → progress = (77/80) × 100 = <strong>96.3%</strong>
                </div>
            ',

                'error_kosong' => '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>detail_jangka</code> kosong / tahun tidak valid</td>
                                <td>Pastikan tahun terisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada karyawan di data KPI</td>
                                <td>Assign programmer ke target</td>
                            </tr>
                            <tr>
                                <td>Nama PIC di ticket tidak match</td>
                                <td>Sistem memakai nama depan karyawan. Pastikan kolom <code>pic</code> di ticket sesuai (contoh: "Budi Santoso" → "Budi")</td>
                            </tr>
                            <tr>
                                <td>Tidak ada ticket Request / Error</td>
                                <td>Pastikan ada data ticket di tahun tersebut</td>
                            </tr>
                            <tr>
                                <td>Ticket Error belum punya <code>tanggal_selesai</code></td>
                                <td>Error hanya dihitung jika sudah selesai</td>
                            </tr>
                            <tr>
                                <td>Ticket Error <code>keperluan</code> ≠ Programming</td>
                                <td>Hanya ticket dengan keperluan Programming yang masuk perhitungan error</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Setup Target</strong> — pilih Assistant Route + tahun + assign programmer</li>
                    <li class="mb-2"><strong>Ticket masuk</strong> — Request & Error (Aplikasi) dibuat di sistem</li>
                    <li class="mb-2"><strong>Pengerjaan</strong> — ticket dikerjakan, tanggal selesai diisi</li>
                    <li class="mb-2"><strong>Perhitungan</strong> — sistem hitung rasio + skor durasi error (dengan bobot)</li>
                    <li class="mb-0"><strong>Hasil</strong> — progress = skorKualitas terhadap nilaiTarget (max 100%)</li>
                </ol>
            ',
            ],

            // =====================================================
            // 5. KETEPATAN WAKTU PENYELESAIAN FITUR
            // =====================================================
            'ketepatan_waktu_penyelesaian_fitur' => [
                'judul' => 'Ketepatan Waktu Penyelesaian Fitur',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>persentase ticket yang memenuhi SLA</strong>
                    berdasarkan prioritas (ditentukan dari kombinasi kategori + tingkat kesulitan).
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Fitur Ticketing</li>
                    <li>Filter PIC sesuai programmer yang di-assign</li>
                    <li>Hanya ticket yang sudah diselesaikan</li>
                    <li>Keperluan akan disesuaikan dengan jabatan (Programming)</li>
                </ul>

                <h6 class="fw-semibold mt-4">Penentuan Prioritas & SLA</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kondisi</th>
                                <th>Prioritas</th>
                                <th>SLA (jam kerja)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>kategori = Error (Aplikasi)</code> + <code>tingkat_kesulitan = Major</code></td>
                                <td><span class="badge bg-danger">Error_Major</span></td>
                                <td>≤ 40</td>
                            </tr>
                            <tr>
                                <td><code>tingkat_kesulitan = Major / Moderate</code></td>
                                <td><span class="badge bg-warning text-dark">High</span></td>
                                <td>≤ 24</td>
                            </tr>
                            <tr>
                                <td><code>kategori = Error (Aplikasi)</code> (selain Major)</td>
                                <td><span class="badge bg-info">Medium</span></td>
                                <td>≤ 40</td>
                            </tr>
                            <tr>
                                <td>Selain di atas</td>
                                <td><span class="badge bg-secondary">Low</span></td>
                                <td>Selalu dianggap memenuhi SLA</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-semibold">Rumus Progress</h6>
                <pre class="bg-light p-3 rounded border small">realisasi = (jumlah ticket memenuhi SLA / total ticket) × 100
progress  = min(100, (realisasi / nilaiTarget) × 100)</pre>

                <p class="mt-2">Durasi dihitung menggunakan <code>hitungJamKerja</code> (jam kerja efektif).</p>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh:</strong><br>
                    Total ticket = 20<br>
                    • 5 Error_Major → 4 memenuhi SLA<br>
                    • 8 High → 6 memenuhi SLA<br>
                    • 4 Medium → 3 memenuhi SLA<br>
                    • 3 Low → 3 (otomatis memenuhi)<br><br>
                    metCount = 16<br>
                    realisasi = (16/20) × 100 = <strong>80%</strong><br>
                    Jika nilaiTarget = 90 → progress = (80/90) × 100 = <strong>88.9%</strong>
                </div>
            ',

                'error_kosong' => '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Belum ada programmer di target</td>
                                <td>Assign karyawan ke target</td>
                            </tr>
                            <tr>
                                <td>PIC ticket tidak match</td>
                                <td>Data PIC harus sesuai</td>
                            </tr>
                            <tr>
                                <td>Ticket belum diselesaikan</td>
                                <td>Hanya ticket selesai yang dihitung</td>
                            </tr>
                            <tr>
                                <td>Keperluan ticket tidak sesuai dengan tujuan ticket</td>
                                <td>Harus match mapping jabatan → Programming / Technical Support / Tim Digital</td>
                            </tr>
                            <tr>
                                <td>Banyak ticket melewati SLA</td>
                                <td>Perhatikan batas waktu sesuai prioritas di tabel di atas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Setup Target</strong> — pilih route + tahun + assign programmer</li>
                    <li class="mb-2"><strong>Ticket dibuat</strong> — isi kategori + tingkat kesulitan</li>
                    <li class="mb-2"><strong>Pengerjaan</strong> — selesaikan ticket, isi tanggal & jam selesai</li>
                    <li class="mb-2"><strong>Evaluasi SLA</strong> — sistem tentukan prioritas lalu bandingkan durasi vs batas waktu</li>
                    <li class="mb-0"><strong>Hasil</strong> — progress berdasarkan % ticket yang memenuhi SLA terhadap nilaiTarget</li>
                </ol>
            ',
            ],

            // =====================================================
            // 6. TINGKAT KEBERHASILAN SUPPORT MEMENUHI SLA
            // =====================================================
            'tingkat_keberhasilan_support_memenuhi_sla' => [
                'judul' => 'Tingkat Keberhasilan Support Memenuhi SLA',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>persentase ticket support yang diselesaikan dalam batas SLA 8 jam kerja</strong>.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Tabel <code>tickets</code></li>
                    <li>Filter PIC sesuai nama depan karyawan yang di-assign ke target</li>
                    <li>Filter <code>keperluan</code> berdasarkan jabatan:
                        <ul>
                            <li>Technical Support → <code>Technical Support</code></li>
                        </ul>
                    </li>
                    <li>Hanya ticket yang sudah punya <code>tanggal_selesai</code></li>
                    <li>Filter tahun dari <code>detail_jangka</code></li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Menghitung Durasi</h6>
                <p>Titik awal perhitungan:</p>
                <ul>
                    <li>Jika <code>tanggal_response</code> + <code>jam_response</code> ada → pakai waktu response</li>
                    <li>Jika tidak ada → pakai <code>created_at</code></li>
                </ul>
                <p>Titik akhir: <code>tanggal_selesai</code> + <code>jam_selesai</code></p>
                <p>Durasi dihitung dengan <code>hitungJamKerja</code> (jam kerja efektif).</p>

                <h6 class="fw-semibold mt-4">Kriteria SLA</h6>
                <pre class="bg-light p-3 rounded border small">SLA terpenuhi  = durasi ≤ 8 jam kerja
progress       = (jumlah ticket memenuhi SLA / total ticket) × 100</pre>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh:</strong><br>
                    Total ticket selesai = 40<br>
                    Ticket selesai ≤ 8 jam kerja = 32<br>
                    Progress = (32 / 40) × 100 = <strong>80%</strong>
                </div>
            ',

                'error_kosong' => '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>detail_jangka</code> kosong / tahun tidak valid</td>
                                <td>Pastikan tahun terisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada karyawan di target</td>
                                <td>Assign karyawan Technical Support / terkait ke target</td>
                            </tr>
                            <tr>
                                <td>PIC ticket tidak match</td>
                                <td>Kolom <code>pic</code> harus sesuai nama depan karyawan</td>
                            </tr>
                            <tr>
                                <td><code>keperluan</code> tidak sesuai jabatan</td>
                                <td>Harus <code>Technical Support</code> atau <code>Programming</code> sesuai mapping jabatan</td>
                            </tr>
                            <tr>
                                <td>Ticket belum punya <code>tanggal_selesai</code></td>
                                <td>Hanya ticket yang sudah selesai yang dihitung</td>
                            </tr>
                            <tr>
                                <td>Banyak ticket melebihi 8 jam kerja</td>
                                <td>Usahakan resolve dalam ≤ 8 jam kerja sejak response (atau created_at jika response kosong)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Setup Target</strong> — pilih route + tahun + assign staff support</li>
                    <li class="mb-2"><strong>Ticket masuk</strong> — ticket dibuat dengan keperluan sesuai jabatan</li>
                    <li class="mb-2"><strong>Response & Resolve</strong> — isi waktu response (opsional) dan tanggal/jam selesai</li>
                    <li class="mb-2"><strong>Evaluasi SLA</strong> — sistem hitung jam kerja dari response/created sampai selesai, cek ≤ 8 jam</li>
                    <li class="mb-0"><strong>Hasil</strong> — progress = % ticket yang memenuhi SLA</li>
                </ol>
            ',
            ],

            // =====================================================
            // 7. KUALITAS LAYANAN EXAM
            // =====================================================
            'kualitas_layanan_exam' => [
                'judul' => 'Kualitas Layanan Exam',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>persentase sesi exam yang mendapat penilaian rata-rata ≥ 3.5</strong>
                    dari peserta.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Tabel <code>penilaian_exam</code></li>
                    <li>Group by <code>id_rkm</code> (satu sesi exam = satu id_rkm)</li>
                    <li>Nilai yang dipakai: rata-rata <code>nilai_emote</code> per id_rkm</li>
                    <li>Filter tahun dari <code>detail_jangka</code></li>
                    <li>Hanya data yang punya <code>id_rkm</code></li>
                </ul>

                <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                <pre class="bg-light p-3 rounded border small">Per id_rkm:
nilai_rata = AVG(nilai_emote)

Sesi dianggap qualified jika nilai_rata ≥ 3.5

progress = (jumlah sesi qualified / total sesi) × 100</pre>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh:</strong><br>
                    Ada 20 sesi exam (20 id_rkm unik) di tahun 2025:<br>
                    • 16 sesi punya rata-rata nilai_emote ≥ 3.5<br>
                    • 4 sesi di bawah 3.5<br><br>
                    Progress = (16 / 20) × 100 = <strong>80%</strong>
                </div>
            ',

                'error_kosong' => '
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>detail_jangka</code> kosong / tahun tidak valid</td>
                                <td>Pastikan tahun terisi</td>
                            </tr>
                            <tr>
                                <td>Belum ada data di <code>penilaian_exam</code></td>
                                <td>Pastikan peserta sudah mengisi penilaian exam</td>
                            </tr>
                            <tr>
                                <td><code>id_rkm</code> kosong</td>
                                <td>Hanya penilaian yang terhubung ke RKM yang dihitung</td>
                            </tr>
                            <tr>
                                <td>Banyak sesi nilai rata-rata &lt; 3.5</td>
                                <td>Tingkatkan kualitas layanan exam agar penilaian peserta ≥ 3.5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2"><strong>Setup Target</strong> — pilih Assistant Route + tahun</li>
                    <li class="mb-2"><strong>Exam berjalan</strong> — sesi exam terhubung ke id_rkm</li>
                    <li class="mb-2"><strong>Peserta menilai</strong> — isi nilai_emote di penilaian exam</li>
                    <li class="mb-2"><strong>Agregasi</strong> — sistem hitung rata-rata nilai per id_rkm</li>
                    <li class="mb-0"><strong>Hasil</strong> — progress = % sesi yang rata-ratanya ≥ 3.5</li>
                </ol>
            ',
            ],

            // =====================================================
            // 8. MENINGKATKAN KEPUASAN DAN LOYALITAS KARYAWAN
            // =====================================================
            'meningkatkan_kepuasan_dan_loyalitas_peserta' => [
                'judul' => 'Meningkatkan Kepuasan dan Loyalitas Peserta',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>berapa persen karyawan yang merasa puas</strong>
                    berdasarkan hasil survey kepuasan.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Data diambil dari <strong>Survey Kepuasan</strong></li>
                    <li>Yang dipakai: jawaban pertanyaan 1, 2, dan 4</li>
                    <li>Pertanyaan 1 = "Bagaimana Anda menilai kecepatan respon tim ITSM dalam menangani insiden Anda?" dengan jawaban berupa pilihan rentang 1 sampai 4</li>
                    <li>Pertanyaan 2 = "Apakah insiden Anda teratasi dengan efektif?" dengan jawaban berupa pilihan "Ya" atau "Tidak"</li>
                    <li>Pertanyaan 4 = "Bagaimana Anda menilai kualitas layanan yang diberikan oleh tim ITSM dalam memenuhi request Anda?" dengan jawaban berupa pilihan rentang 1 sampai 4</li>
                    <li>Data yang diambil hanya data yang tahunnya sama dengan tahun target</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung Skor per Responden</h6>
                <p>Setiap jawaban diubah menjadi angka:</p>
                <ul>
                    <li><strong>Pertanyaan 1 dan Pertanyaan 4</strong> (skala 1–4):
                        <ul>
                            <li>pilihan jawaban 1 bintang → 10 poin</li>
                            <li>pilihan jawaban 2 bintang → 20 poin</li>
                            <li>pilihan jawaban 3 bintang → 30 poin</li>
                            <li>pilihan jawaban 4 bintang → 40 poin</li>
                        </ul>
                    </li>
                    <li><strong>Pertanyaan 2</strong>:
                        <ul>
                            <li>pilihan jawaban "Ya" → 20 poin</li>
                            <li>pilihan jawaban "Tidak" → 10 poin</li>
                        </ul>
                    </li>
                </ul>

                <p class="mt-2">Lalu dihitung:</p>
                <ol>
                    <li>Jumlahkan poin dari pertanyaan 1 + 2 + 4 (maksimal 100)</li>
                    <li>Ubah menjadi skor akhir: <code>1 + (total poin × 3) ÷ 100</code></li>
                    <li>Jika skor akhir ≥ 3,0 → responden dianggap <strong>puas</strong></li>
                </ol>

                <p>
                    Progress = (Jumlah responden puas ÷ Total responden) × 100
                </p>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br>
                    Seorang peserta menjawab:
                    <ul class="mb-2 mt-2">
                        <li>pertanyaan 1 = 4 → 40 poin</li>
                        <li>pertanyaan 2 = Ya → 20 poin</li>
                        <li>pertanyaan 4 = 3 → 30 poin</li>
                    </ul>
                    Total poin = 90<br>
                    Skor akhir = 1 + (90 × 3) ÷ 100 = 1 + 2,7 = <strong>3,7</strong> ✅ (puas)<br><br>
                    Jika dari 50 responden, 40 orang skornya ≥ 3,0:<br>
                    <strong>Progress = (40 ÷ 50) × 100 = 80%</strong>
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / salah</td>
                                <td>Pastikan tahun sudah diisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada data survey di tahun tersebut</td>
                                <td>Pastikan peserta sudah mengisi survey kepuasan</td>
                            </tr>
                            <tr>
                                <td>Jawaban dari pertanyaan 1 / 2 / 4 kosong atau tidak valid</td>
                                <td>Pastikan form survey terisi lengkap</td>
                            </tr>
                            <tr>
                                <td>Banyak responden skornya di bawah 3,0</td>
                                <td>Tingkatkan kualitas layanan agar penilaian peserta lebih tinggi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong> Semakin banyak peserta yang memberi penilaian bagus (skor ≥ 3,0),
                    semakin tinggi progress target ini.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Buat Target</strong><br>
                        Buat target KPI dengan memilih Assistant Route
                        <em>"Meningkatkan Kepuasan dan Loyalitas Peserta"</em> dan isi tahun.
                    </li>
                    <li class="mb-2">
                        <strong>karyawan Mengisi Survey</strong><br>
                        karyawan mengisi survey kepuasan setelah ticketing telah diselesaikan.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Menghitung</strong><br>
                        Setiap jawaban diubah jadi skor. Jika skor ≥ 3,0 dianggap puas.
                    </li>
                    <li class="mb-0">
                        <strong>Lihat Hasil</strong><br>
                        Progress muncul di halaman KPI personal. Bisa dilihat juga detail per bulan dibagian detail saat klik kartu target.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 9. AVAILABILITY SISTEM INTERNAL KRITIS
            // =====================================================
            'availability_sistem_internal_kritis' => [
                'judul' => 'Availability Sistem Internal Kritis',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>seberapa sering sistem internal yang penting dalam kondisi normal (up)</strong>.
                    Semakin jarang sistem down, semakin tinggi progress-nya.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Data diambil dari tabel <strong>Activity Log</strong> (hasil pengecekan sistem)</li>
                    <li>Hanya data dengan status antara 100–599</li>
                    <li>Hanya data di tahun yang dipilih saat membuat target</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                <ol>
                    <li>Ambil semua hasil pengecekan sistem di tahun tersebut</li>
                    <li>Progress = (Jumlah pengecekan normal ÷ Total pengecekan) × 100</li>
                </ol>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br>
                    Di tahun 2025 sistem dicek sebanyak 1.000 kali:
                    <ul class="mb-2 mt-2">
                        <li>950 kali status normal (up) ✅</li>
                        <li>50 kali status bermasalah (down) ❌</li>
                    </ul>
                    <strong>Progress = (950 ÷ 1.000) × 100 = 95%</strong>
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / salah</td>
                                <td>Pastikan tahun sudah diisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada data pengecekan di Activity Log</td>
                                <td>Pastikan monitoring sistem berjalan dan mencatat status</td>
                            </tr>
                            <tr>
                                <td>Status di luar rentang 100–599</td>
                                <td>Hanya status dalam rentang tersebut yang dihitung</td>
                            </tr>
                            <tr>
                                <td>Banyak pengecekan berstatus down</td>
                                <td>Perbaiki stabilitas sistem agar lebih jarang down</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong> Progress tinggi berarti sistem hampir selalu dalam kondisi normal.
                    Fokus pada menjaga ketersediaan sistem kritis.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Buat Target</strong><br>
                        Buat target KPI dengan memilih Assistant Route
                        <em>"Availability Sistem Internal Kritis"</em> dan isi tahun.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Dimonitor</strong><br>
                        Sistem monitoring secara berkala mengecek kondisi sistem internal.
                    </li>
                    <li class="mb-2">
                        <strong>Hasil Tersimpan</strong><br>
                        Setiap pengecekan dicatat di Activity Log (status up/down).
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Menghitung</strong><br>
                        Sistem menghitung berapa persen pengecekan yang berstatus normal.
                    </li>
                    <li class="mb-0">
                        <strong>Lihat Hasil</strong><br>
                        Progress muncul di dashboard. Bisa dilihat juga detail per bulan / per hari.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 10. PERSENTASE GAP KOMPETENSI
            // =====================================================
            'persentase_gap_kompetensi' => [
                'judul' => 'Persentase Gap Kompetensi Tim terhadap Standar Skill',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>seberapa jauh kemampuan tim tertinggal dari standar skill yang dibutuhkan</strong>.
                    Semakin kecil gap-nya, semakin bagus. Target tercapai jika gap <strong>kurang dari batas yang ditetapkan</strong>
                    (misalnya &lt; 10%).
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Data diambil dari detail karyawan di target KPI</li>
                    <li>Yang dipakai:
                        <ul>
                            <li>kemampuan aktual karyawan</li>
                            <li>standar skill yang diharapkan sesuai peran</li>
                        </ul>
                    </li>
                    <li>Kemampuan dan standar harus memakai <strong>skala yang sama</strong> (misalnya sama-sama 1&ndash;10, atau sama-sama 0&ndash;100)</li>
                    <li>Hanya data yang punya standar > 0 yang dihitung</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                <ol>
                    <li>Untuk setiap karyawan, hitung kekurangan = standar &minus; kemampuan.
                        Jika kemampuan sudah sama atau melebihi standar, kekurangan = 0</li>
                    <li>Jumlahkan semua kekurangan</li>
                    <li>Jumlahkan semua standar skill</li>
                    <li>Gap = (Total kekurangan ÷ Total standar) × 100</li>
                    <li>Hasil dibatasi antara 0% dan 100%</li>
                </ol>

                <div class="alert alert-warning mt-3">
                    <strong>Penting:</strong> Kelebihan skill satu karyawan <strong>tidak</strong> menutupi kekurangan karyawan lain.
                    Karyawan yang nilainya di atas standar dihitung kekurangannya 0, bukan nilai negatif.
                </div>

                <h6 class="fw-semibold mt-4">Tampilan Tim vs Personal</h6>
                <ul>
                    <li><strong>Tim:</strong> semua karyawan di target ikut dihitung</li>
                    <li><strong>Personal:</strong> hanya data karyawan yang dipilih yang dihitung, dengan rumus yang sama.
                        Misalnya standar 10 dan target &lt; 10%, maka karyawan tersebut hanya boleh kurang kurang dari 1 poin dari standarnya</li>
                </ul>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata (skala 1&ndash;10):</strong><br>
                    Ada 3 karyawan di target:
                    <ul class="mb-2 mt-2">
                        <li>Karyawan A &rarr; kemampuan 5, standar 10 &rarr; kekurangan 5</li>
                        <li>Karyawan B &rarr; kemampuan 10, standar 7 &rarr; kekurangan 0 (kelebihan tidak dihitung)</li>
                        <li>Karyawan C &rarr; kemampuan 9, standar 9 &rarr; kekurangan 0</li>
                    </ul>
                    Total kekurangan = 5 + 0 + 0 = 5<br>
                    Total standar = 10 + 7 + 9 = 26<br>
                    <strong>Gap = (5 ÷ 26) × 100 = 19,2%</strong><br>
                    Karena 19,2% belum di bawah target &lt; 10%, target <strong>belum tercapai</strong>.
                    Jika Karyawan A naik menjadi kemampuan 9, kekurangan menjadi 1 dan gap = (1 ÷ 26) × 100 = 3,8% &rarr; <strong>tercapai</strong>.
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika gap muncul <strong>kosong / strip (-)</strong> atau nilainya tidak sesuai harapan, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / salah</td>
                                <td>Pastikan tahun sudah diisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada karyawan yang ditugaskan di target</td>
                                <td>Pastikan target sudah diisi daftar karyawan</td>
                            </tr>
                            <tr>
                                <td>Nilai kemampuan / standar masih kosong</td>
                                <td>Isi nilai kemampuan dan standar skill untuk setiap karyawan</td>
                            </tr>
                            <tr>
                                <td>Semua standar masih 0</td>
                                <td>Pastikan standar sudah diisi (harus > 0), karena data dengan standar 0 tidak dihitung</td>
                            </tr>
                            <tr>
                                <td>Skala kemampuan dan standar berbeda (misalnya kemampuan 1&ndash;10, standar 0&ndash;100)</td>
                                <td>Samakan skala keduanya, kalau tidak gap akan terlihat sangat besar atau sangat kecil</td>
                            </tr>
                            <tr>
                                <td>Gap masih di atas target</td>
                                <td>Tingkatkan skill karyawan yang masih di bawah standar</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong> Gap 0% berarti semua karyawan sudah memenuhi atau melampaui standar.
                    Semakin kecil gap, semakin baik. Data ini biasanya diisi / diupdate oleh Koordinator ITSM.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Buat Target</strong><br>
                        Buat target KPI dengan memilih Assistant Route
                        <em>"Persentase Gap Kompetensi Tim terhadap Standar Skill"</em>,
                        isi tahun, isi batas maksimal gap (misalnya 10 untuk target &lt; 10%), dan pilih karyawan.
                    </li>
                    <li class="mb-2">
                        <strong>Isi Data Kompetensi</strong><br>
                        Koordinator mengisi nilai kemampuan dan standar skill per karyawan, dengan skala yang sama.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Menghitung</strong><br>
                        Sistem menghitung kekurangan kemampuan tiap karyawan terhadap standarnya, lalu membandingkan total kekurangan dengan total standar.
                    </li>
                    <li class="mb-0">
                        <strong>Lihat Hasil</strong><br>
                        Gap muncul di dashboard dan dibandingkan dengan target (semakin kecil semakin bagus).
                        Bisa dilihat juga berapa karyawan yang sudah memenuhi / belum memenuhi standar, baik untuk tim maupun per karyawan.
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // 11. KONSISTENSI CAMPAIGN DIGITAL
            // =====================================================
            'konsistensi_campaign_digital' => [
                'judul' => 'Konsistensi Campaign Digital',

                'dari_mana_data' => '
                <p class="mb-3">
                    Target ini mengukur <strong>seberapa konsisten dan produktif Tim Digital dalam mengunggah konten</strong>
                    setiap minggu. Target ideal: minimal <strong>3 konten per minggu</strong>.
                </p>

                <h6 class="fw-semibold">Sumber Data</h6>
                <ul>
                    <li>Data diambil dari tabel <strong>Content Schedule</strong></li>
                    <li>Yang dipakai: tanggal upload konten</li>
                    <li>Hanya data di tahun yang dipilih saat membuat target</li>
                </ul>

                <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                <p>Progress dihitung dari <strong>2 skor</strong> yang digabung:</p>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Skor</th>
                                <th>Penjelasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1. Skor Konsistensi (CS)</strong><br><small class="text-muted">Bobot 60%</small></td>
                                <td>
                                    Mengukur berapa persen minggu yang berhasil mengunggah minimal 3 konten.<br>
                                    Hanya minggu yang ada upload-nya yang dihitung.
                                </td>
                            </tr>
                            <tr>
                                <td><strong>2. Skor Produktivitas (PS)</strong><br><small class="text-muted">Bobot 40%</small></td>
                                <td>
                                    Membandingkan total konten sepanjang tahun dengan target ideal
                                    (3 konten × jumlah minggu dalam setahun).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="mb-2"><strong>Rumus akhir:</strong></p>
                <p>
                    Progress = (Skor Konsistensi × 60%) + (Skor Produktivitas × 40%)
                </p>

                <div class="alert alert-light border mt-3">
                    <strong>Contoh nyata:</strong><br><br>
                    Di tahun 2025:
                    <ul class="mb-2 mt-2">
                        <li>Ada 40 minggu yang sempat ada upload konten</li>
                        <li>Dari 40 minggu itu, 30 minggu berhasil upload ≥ 3 konten</li>
                        <li>Total konten sepanjang tahun = 120</li>
                        <li>Jumlah minggu dalam setahun ≈ 52</li>
                    </ul>
                    <strong>Skor Konsistensi (CS)</strong> = 30 ÷ 40 = 0,75 (75%)<br>
                    <strong>Skor Produktivitas (PS)</strong> = 120 ÷ (3 × 52) = 120 ÷ 156 ≈ 0,77 (77%)<br><br>
                    Progress = (0,75 × 0,6) + (0,77 × 0,4) = 0,45 + 0,308 = <strong>0,758 → 75,8%</strong>
                </div>
            ',

                'error_kosong' => '
                <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena:</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kemungkinan Penyebab</th>
                                <th>Solusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tahun target belum diisi / salah</td>
                                <td>Pastikan tahun sudah diisi dengan benar</td>
                            </tr>
                            <tr>
                                <td>Belum ada data konten di Content Schedule</td>
                                <td>Pastikan jadwal upload konten sudah diinput dan punya tanggal upload</td>
                            </tr>
                            <tr>
                                <td>Tanggal upload kosong</td>
                                <td>Pastikan setiap konten sudah diisi <code>upload_date</code></td>
                            </tr>
                            <tr>
                                <td>Banyak minggu upload kurang dari 3 konten</td>
                                <td>Usahakan konsisten minimal 3 konten setiap minggu</td>
                            </tr>
                            <tr>
                                <td>Total konten sepanjang tahun masih sedikit</td>
                                <td>Tingkatkan frekuensi upload agar skor produktivitas naik</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <strong>Tips:</strong>
                    Konsistensi (upload rutin tiap minggu) lebih diutamakan (bobot 60%)
                    dibanding sekadar banyak konten di waktu tertentu.
                    Idealnya: stabil 3 konten per minggu sepanjang tahun.
                </div>
            ',

                'alur_bisnis' => '
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Buat Target</strong><br>
                        Buat target KPI dengan memilih Assistant Route
                        <em>"Konsistensi Campaign Digital"</em> dan isi tahun.
                    </li>
                    <li class="mb-2">
                        <strong>Jadwalkan & Upload Konten</strong><br>
                        Tim Digital membuat jadwal konten dan mengunggah sesuai rencana.
                        Setiap upload dicatat tanggalnya.
                    </li>
                    <li class="mb-2">
                        <strong>Sistem Menghitung per Minggu</strong><br>
                        Sistem mengelompokkan konten per minggu (Senin–Minggu),
                        lalu melihat minggu mana yang mencapai minimal 3 konten.
                    </li>
                    <li class="mb-2">
                        <strong>Hitung 2 Skor</strong><br>
                        • Konsistensi: % minggu yang berhasil ≥ 3 konten<br>
                        • Produktivitas: total konten dibanding target tahunan
                    </li>
                    <li class="mb-0">
                        <strong>Lihat Hasil</strong><br>
                        Progress muncul di dashboard (gabungan 60% konsistensi + 40% produktivitas).
                    </li>
                </ol>
            ',
            ],

            // =====================================================
            // ADMIN HOLDING 1. KETEPATAN WAKTU PO
            // =====================================================
            'ketepatan_waktu_po' => [
                'judul' => 'Ketepatan Waktu PO',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>seberapa tepat waktu dokumen PO diunggah</strong>.
                        Setiap PO diberi nilai <strong>0 sampai 100</strong>, lalu progress adalah
                        <strong>rata-rata nilai semua PO</strong> di tahun yang dipilih.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>Data PO (Nomor Modul)</strong> → PO yang dibuat di tahun target. Tahun dilihat dari <em>tanggal PO dibuat</em>, bukan tanggal diunggah.</li>
                        <li><strong>Modul yang terhubung ke PO</strong> → diambil <em>tanggal awal training</em>. Jika satu PO punya beberapa modul, dipakai tanggal yang <strong>paling awal</strong>.</li>
                        <li><strong>Tabel Hari Libur</strong> → dipakai untuk menggeser batas waktu jika jatuh di hari libur.</li>
                        <li>Kolom yang dilihat: <code>uploaded</code> (tanggal upload PO), <code>type</code> (jenis PO), <code>created_at</code> (tanggal PO dibuat), dan <code>tanggal_subscode_masuk</code> (tanggal subscode diterima).</li>
                    </ul>

                    <div class="alert alert-warning">
                        <strong>PO yang tidak dihitung:</strong> PO yang <strong>belum diunggah</strong>, dan PO yang
                        <strong>belum terhubung ke modul bertanggal awal training</strong>. PO seperti ini dilewati,
                        bukan diberi nilai 0.
                    </div>

                    <h6 class="fw-semibold mt-4">Batas Waktu Tergantung Jenis PO</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:22%">Jenis PO</th>
                                    <th>Batas Waktu</th>
                                    <th>Yang Dinilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Bukan Authorize</strong></td>
                                    <td>
                                        <strong>Hari Senin di minggu berikutnya</strong> setelah PO dibuat.<br>
                                        Jika Senin itu hari libur (sesuai tabel Hari Libur), batas digeser ke hari berikutnya sampai bukan hari libur.
                                    </td>
                                    <td>Tanggal upload PO</td>
                                </tr>
                                <tr>
                                    <td><strong>Authorize</strong></td>
                                    <td><strong>Tanggal awal training</strong> (yang paling awal dari modul terkait)</td>
                                    <td>
                                        Dua hal: tanggal upload PO <strong>dan</strong> tanggal subscode masuk.<br>
                                        Nilai akhir = <strong>rata-rata keduanya</strong>.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-semibold">Cara Memberi Nilai (Berdasarkan Keterlambatan)</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kondisi</th>
                                    <th style="width:20%">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Tepat waktu atau lebih awal dari batas</td><td><strong>100</strong></td></tr>
                                <tr><td>Terlambat 1 sampai 3 hari</td><td><strong>80</strong></td></tr>
                                <tr><td>Terlambat 4 sampai 7 hari</td><td><strong>60</strong></td></tr>
                                <tr><td>Terlambat lebih dari 7 hari</td><td><strong>0</strong></td></tr>
                                <tr><td>Tanggal yang dinilai kosong (misalnya subscode belum diisi)</td><td><strong>0</strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted">Keterlambatan dihitung dalam <strong>hari kalender</strong> (Sabtu dan Minggu ikut dihitung).</p>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah nilai semua PO yang dihitung / jumlah PO yang dihitung
gap      = progress - nilai target</pre>
                    <p class="small text-muted">Gap negatif berarti progress masih di bawah target.</p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (4 PO di tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li><strong>PO A (bukan Authorize)</strong> dibuat hari Rabu, 5 Maret. Batas = Senin, 10 Maret. Diunggah Rabu, 12 Maret → telat 2 hari → nilai <strong>80</strong></li>
                            <li><strong>PO B (Authorize)</strong>, training mulai 20 Maret. PO diunggah 18 Maret → <strong>100</strong>. Subscode masuk 24 Maret → telat 4 hari → <strong>60</strong>. Nilai = (100 + 60) ÷ 2 = <strong>80</strong></li>
                            <li><strong>PO C (Authorize)</strong>, PO diunggah tepat waktu (100) tetapi subscode belum diisi (0). Nilai = (100 + 0) ÷ 2 = <strong>50</strong></li>
                            <li><strong>PO D (bukan Authorize)</strong> diunggah telat 10 hari → <strong>0</strong></li>
                        </ul>
                        Progress = (80 + 80 + 50 + 0) ÷ 4 = <strong>52,5</strong><br>
                        Jika nilai target = 90, gap = 52,5 − 90 = <strong>−37,5</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah PO yang nilainya <strong>di atas atau sama dengan target</strong> dibanding yang di bawah target.</li>
                        <li><strong>Grafik bulanan:</strong> rata-rata nilai PO, dikelompokkan menurut <strong>bulan PO diunggah</strong>.</li>
                        <li><strong>Grafik harian:</strong> rata-rata nilai PO menurut <strong>tanggal PO diunggah</strong>.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Angka target ini bersifat data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada PO yang dibuat di tahun tersebut</td>
                                    <td>Pastikan PO sudah dibuat di menu Modul pada tahun target</td>
                                </tr>
                                <tr>
                                    <td>PO belum diunggah (kolom upload kosong)</td>
                                    <td>Unggah dokumen PO. PO yang belum diunggah tidak ikut dihitung</td>
                                </tr>
                                <tr>
                                    <td>PO belum terhubung ke modul, atau modul belum punya tanggal awal training</td>
                                    <td>Hubungkan PO ke modul dan isi tanggal awal training. Tanpa itu PO dilewati</td>
                                </tr>
                                <tr>
                                    <td>PO jenis Authorize, tanggal subscode masuk belum diisi</td>
                                    <td>Isi tanggal subscode masuk. Jika kosong, nilai PO tersebut maksimal hanya <strong>50</strong></td>
                                </tr>
                                <tr>
                                    <td>Banyak PO diunggah terlambat</td>
                                    <td>Unggah PO sebelum batas waktu. Telat lebih dari 7 hari = nilai 0</td>
                                </tr>
                                <tr>
                                    <td>Hari libur belum diinput di tabel Hari Libur</td>
                                    <td>Batas waktu hanya bergeser untuk hari libur yang sudah terdaftar. Lengkapi data hari libur</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                                <tr>
                                    <td>PO dibuat akhir tahun, diunggah awal tahun berikutnya</td>
                                    <td>PO tetap dihitung di tahun PO dibuat, tetapi pada grafik bulanan muncul di bulan upload</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Untuk PO Authorize, usahakan PO <strong>dan</strong> subscode sama-sama
                        masuk sebelum training dimulai agar nilainya penuh 100.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Ketepatan Waktu PO"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>PO Dibuat</strong><br>
                            PO dibuat di menu Modul dan dihubungkan ke modul pelatihan yang punya tanggal awal training.
                        </li>
                        <li class="mb-2"><strong>Unggah PO</strong><br>
                            Admin mengunggah dokumen PO. Tanggal upload tercatat otomatis oleh sistem.
                        </li>
                        <li class="mb-2"><strong>Isi Subscode (khusus Authorize)</strong><br>
                            Saat subscode diterima, isi tanggal subscode masuk pada PO tersebut.
                        </li>
                        <li class="mb-2"><strong>Sistem Menilai</strong><br>
                            Sistem menentukan batas waktu, membandingkan dengan tanggal upload, lalu memberi nilai 0 sampai 100 per PO.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress (rata-rata nilai) muncul di dashboard. Detail per bulan dan per hari ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // ADMIN HOLDING 2. KUALITAS DOKUMENTASI SUPPORT DAN PROCTOR
            // =====================================================
            'kualitas_dokumentasi_support_dan_proctor' => [
                'judul' => 'Kualitas Dokumentasi Support dan Proctor',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen exam yang sudah didokumentasikan</strong>
                        oleh tim support dan proctor. Semakin lengkap dokumentasinya, semakin tinggi progress-nya.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>Registrasi Exam</strong> (<code>registexam</code>) → jumlah exam yang terdaftar di tahun target. Ini menjadi <strong>pembagi</strong>.</li>
                        <li><strong>Dokumentasi Exam</strong> (<code>DokumentasiExam</code>) → jumlah dokumentasi yang sudah diisi di tahun target. Ini menjadi <strong>yang dibagi</strong>.</li>
                        <li>Tahun kedua data dilihat dari <strong>tanggal data tersebut dibuat</strong> di sistem.</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Kapan Dokumentasi Dianggap Terisi?</h6>
                    <p>Satu data dokumentasi dihitung jika <strong>salah satu</strong> dari ini terisi:</p>
                    <ul>
                        <li>kolom <code>skor</code> (nilai/skor exam), <strong>atau</strong></li>
                        <li>kolom <code>dokumentasi</code> (bukti/dokumen exam)</li>
                    </ul>
                    <p>Jika keduanya kosong, data tersebut <strong>tidak dihitung</strong>.</p>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah dokumentasi terisi / jumlah registrasi exam) x 100
gap      = 0 jika progress sudah di atas target
                    (jika belum: progress - nilai target, hasilnya negatif)</pre>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Ada <strong>200</strong> registrasi exam</li>
                            <li>Dokumentasi yang terisi (skor atau dokumen ada) = <strong>170</strong></li>
                            <li>Ada 10 data dokumentasi yang skor dan dokumennya kosong → <strong>tidak dihitung</strong></li>
                        </ul>
                        Progress = (170 ÷ 200) × 100 = <strong>85%</strong><br>
                        Jika nilai target = 90%, gap = 85 − 90 = <strong>−5</strong>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Perlu diketahui:</strong> sistem membandingkan <strong>jumlah</strong>, bukan mencocokkan satu per satu.
                        Artinya sistem tidak mengecek apakah setiap registrasi punya dokumentasinya sendiri.
                        Jika ada exam yang dokumentasinya diisi lebih dari satu kali, progress bisa
                        <strong>melebihi 100%</strong>.
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah exam yang sudah terdokumentasi dibanding sisanya.</li>
                        <li><strong>Grafik bulanan:</strong> <strong>persentase</strong> dokumentasi terisi dibanding registrasi di bulan itu. Bulan yang belum punya dokumentasi sama sekali tidak muncul.</li>
                        <li><strong>Grafik harian:</strong> <strong>jumlah</strong> dokumentasi yang dibuat pada tanggal tersebut (bukan persentase).</li>
                    </ul>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada registrasi exam di tahun tersebut</td>
                                    <td>Jika tidak ada registrasi, progress otomatis 0. Pastikan peserta exam sudah terdaftar</td>
                                </tr>
                                <tr>
                                    <td>Dokumentasi exam belum diisi</td>
                                    <td>Minta tim support / proctor melengkapi dokumentasi di menu Daftar Peserta Exam</td>
                                </tr>
                                <tr>
                                    <td>Dokumentasi dibuat tetapi skor dan dokumen sama-sama kosong</td>
                                    <td>Isi minimal salah satu: skor atau dokumen pendukung</td>
                                </tr>
                                <tr>
                                    <td>Dokumentasi diisi di tahun berbeda dari registrasinya</td>
                                    <td>Tahun dilihat dari tanggal data dibuat. Dokumentasi yang dibuat di tahun berikutnya dihitung di tahun berikutnya</td>
                                </tr>
                                <tr>
                                    <td>Progress lebih dari 100%</td>
                                    <td>Kemungkinan ada dokumentasi ganda untuk exam yang sama. Periksa dan rapikan datanya</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Biasakan mengisi dokumentasi (skor atau dokumen) segera setelah exam selesai,
                        supaya tidak menumpuk dan progress bulanan tetap tinggi.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Kualitas Dokumentasi Support dan Proctor"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Peserta Terdaftar Exam</strong><br>
                            Setiap peserta atau sesi exam yang didaftarkan tercatat sebagai registrasi exam.
                        </li>
                        <li class="mb-2"><strong>Exam Berjalan</strong><br>
                            Support dan proctor mengawasi exam.
                        </li>
                        <li class="mb-2"><strong>Isi Dokumentasi</strong><br>
                            Setelah exam, tim mengisi skor dan/atau mengunggah dokumentasi exam.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem membagi jumlah dokumentasi terisi dengan jumlah registrasi exam di tahun target.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress muncul di dashboard. Detail per bulan dan per hari ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // ADM SALES 1. LAPORAN MOM
            // =====================================================
            'laporan_mom' => [
                'judul' => 'Laporan MoM',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>kelengkapan administrasi penjualan dalam satu tahun</strong>.
                        Nilainya dihitung dari <strong>4 komponen</strong>, masing-masing maksimal <strong>25 poin</strong>
                        (total maksimal 100).
                    </p>

                    <h6 class="fw-semibold">4 Komponen yang Dinilai</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:20%">Komponen</th>
                                    <th style="width:35%">Sumber Data</th>
                                    <th>Cara Dapat Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1. Laporan MoM</strong></td>
                                    <td>Data <strong>Laporan Harian Sales</strong> (tahun dilihat dari tanggal laporan dibuat)</td>
                                    <td>Ada <strong>minimal 1</strong> laporan di tahun itu → <strong>25 poin</strong><br>Tidak ada sama sekali → 0 poin</td>
                                </tr>
                                <tr>
                                    <td><strong>2. PA</strong></td>
                                    <td><strong>Checklist RKM</strong>, kolom PA yang sudah dicentang (tahun dilihat dari tanggal checklist dibuat)</td>
                                    <td>Ada <strong>minimal 1</strong> checklist yang PA-nya dicentang → <strong>25 poin</strong><br>Tidak ada → 0 poin</td>
                                </tr>
                                <tr>
                                    <td><strong>3. Surat Kontrak</strong></td>
                                    <td><strong>Checklist RKM</strong>, kolom Surat Kontrak yang sudah dicentang</td>
                                    <td>Ada <strong>minimal 1</strong> checklist yang Surat Kontraknya dicentang → <strong>25 poin</strong><br>Tidak ada → 0 poin</td>
                                </tr>
                                <tr>
                                    <td><strong>4. E-Regist</strong></td>
                                    <td>Data <strong>RKM</strong> (tahun dilihat dari <em>tanggal awal kelas</em>), kolom form registrasi</td>
                                    <td>
                                        Dihitung <strong>proporsional</strong>:<br>
                                        (RKM yang form registrasinya terisi ÷ total RKM) × 25
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Penting dipahami:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Komponen 1, 2, dan 3 hanya mengecek <strong>ada atau tidaknya</strong> data, bukan jumlahnya. Punya 1 laporan MoM atau 500 laporan MoM sama-sama mendapat 25 poin.</li>
                            <li>Hanya komponen 4 (E-Regist) yang nilainya naik-turun sesuai persentase.</li>
                            <li>Jika <strong>tidak ada satu pun RKM</strong> di tahun tersebut, keempat komponen otomatis bernilai 0 sehingga progress = 0.</li>
                        </ul>
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">poin E-Regist = (RKM dengan form registrasi terisi / total RKM) x 25
progress      = poin MoM + poin PA + poin Surat Kontrak + poin E-Regist
gap           = 0 jika progress sudah melewati target
                            (jika belum: progress - nilai target, hasilnya negatif)</pre>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Ada 120 laporan MoM → <strong>25 poin</strong></li>
                            <li>Ada checklist RKM yang PA-nya dicentang → <strong>25 poin</strong></li>
                            <li>Ada checklist RKM yang Surat Kontraknya dicentang → <strong>25 poin</strong></li>
                            <li>Ada 200 RKM, dan 160 di antaranya form registrasinya terisi → (160 ÷ 200) × 25 = <strong>20 poin</strong></li>
                        </ul>
                        Progress = 25 + 25 + 25 + 20 = <strong>95</strong><br>
                        Jika nilai target = 100, gap = 95 − 100 = <strong>−5</strong>
                    </div>

                    <div class="alert alert-secondary">
                        <strong>Kondisi khusus:</strong> jika di tahun tersebut <strong>belum ada satu pun laporan MoM</strong>,
                        poin MoM = 0 dan bobot E-Regist berubah dari 25 menjadi <strong>100</strong>.
                        Akibatnya total progress bisa <strong>melebihi 100</strong>.<br>
                        <em>Contoh:</em> tanpa laporan MoM, PA dan Surat Kontrak ada, form registrasi terisi 75% →
                        0 + 25 + 25 + (75% × 100 = 75) = <strong>125</strong>.
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah RKM yang form registrasinya sudah terisi (Above) dibanding yang belum (Below).</li>
                        <li><strong>Grafik bulanan:</strong> rata-rata jumlah laporan MoM per hari dalam bulan tersebut (hanya hari yang ada laporannya).</li>
                        <li><strong>Grafik harian:</strong> jumlah laporan MoM yang dibuat pada tanggal tersebut.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Grafik bulanan dan harian menunjukkan <strong>banyaknya laporan MoM</strong>, bukan skor target.
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada RKM di tahun tersebut</td>
                                    <td>Semua komponen bernilai 0 jika tidak ada RKM. Pastikan RKM sudah dibuat dengan tanggal awal di tahun target</td>
                                </tr>
                                <tr>
                                    <td>Belum ada laporan MoM di tahun tersebut</td>
                                    <td>Minta sales membuat laporan di menu Laporan Harian. Perhatikan juga kondisi khusus pada penjelasan di atas</td>
                                </tr>
                                <tr>
                                    <td>Belum ada checklist PA yang dicentang</td>
                                    <td>Centang PA pada checklist RKM. Cukup 1 data agar mendapat 25 poin</td>
                                </tr>
                                <tr>
                                    <td>Belum ada checklist Surat Kontrak yang dicentang</td>
                                    <td>Centang Surat Kontrak pada checklist RKM. Cukup 1 data agar mendapat 25 poin</td>
                                </tr>
                                <tr>
                                    <td>Banyak RKM yang form registrasinya belum terisi</td>
                                    <td>Lengkapi form registrasi (E-Regist) pada setiap RKM. Inilah satu-satunya komponen yang naik bertahap</td>
                                </tr>
                                <tr>
                                    <td>Progress lebih dari 100</td>
                                    <td>Terjadi jika belum ada laporan MoM sama sekali (lihat kondisi khusus). Buat laporan MoM agar perhitungan kembali normal</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Poin MoM, PA, dan Surat Kontrak cukup didapat dengan 1 data per komponen.
                        Poin yang paling perlu dijaga sepanjang tahun adalah kelengkapan form registrasi pada setiap RKM.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Laporan MoM"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Buat Laporan MoM</strong><br>
                            Sales membuat laporan harian di menu Laporan Harian. Tanggal laporan tercatat otomatis.
                        </li>
                        <li class="mb-2"><strong>Lengkapi Checklist RKM</strong><br>
                            Pada checklist RKM, centang PA dan Surat Kontrak jika dokumennya sudah ada.
                        </li>
                        <li class="mb-2"><strong>Lengkapi Form Registrasi</strong><br>
                            Pastikan setiap RKM sudah terisi form registrasinya (E-Regist).
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem menjumlahkan 4 komponen (masing-masing maksimal 25 poin).
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress muncul di dashboard. Rincian grafik ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // ADM SALES 2. AKURASI KELENGKAPAN DATA PENJUALAN
            // =====================================================
            'akurasi_kelengkapan_data_penjualan' => [
                'judul' => 'Akurasi Kelengkapan Data Penjualan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen RKM yang data penjualannya akurat</strong>.
                        Sebuah RKM disebut akurat jika <strong>total biaya pada Perhitungan Net Sales</strong>
                        sama persis dengan <strong>angka net sales pada Outstanding</strong>.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>RKM</strong> → daftar kelas yang diperiksa</li>
                        <li><strong>Perhitungan Net Sales</strong> (milik RKM) → rincian biaya</li>
                        <li><strong>Outstanding</strong> (milik RKM) → angka net sales yang tercatat</li>
                        <li><strong>Peluang</strong> (milik RKM) → untuk mengecek apakah masih tentatif</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">RKM yang Diperiksa</h6>
                    <p>RKM hanya ikut diperiksa jika <strong>semua</strong> syarat ini terpenuhi:</p>
                    <ul>
                        <li>Tanggal awal kelas ada di tahun target. Jika tahun target adalah <strong>tahun berjalan</strong>, hanya yang tanggal awalnya sampai <strong>hari ini</strong></li>
                        <li>Status RKM = <code>0</code></li>
                        <li>RKM belum dihapus</li>
                        <li>Peluangnya <strong>tidak berstatus tentatif</strong></li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menilai Setiap RKM</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Kondisi RKM</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Belum punya data Perhitungan Net Sales</td>
                                    <td><strong>Dilewati</strong>, tidak dihitung sama sekali (tidak menaikkan maupun menurunkan progress)</td>
                                </tr>
                                <tr>
                                    <td>Punya Perhitungan Net Sales, tetapi belum punya data Outstanding</td>
                                    <td>Dihitung, dan dianggap <strong>tidak akurat</strong></td>
                                </tr>
                                <tr>
                                    <td>Punya keduanya, total biaya <strong>sama persis</strong> dengan net sales Outstanding</td>
                                    <td>Dihitung sebagai <strong>akurat</strong></td>
                                </tr>
                                <tr>
                                    <td>Punya keduanya, tetapi angkanya berbeda</td>
                                    <td>Dihitung, dan dianggap <strong>tidak akurat</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-semibold">Yang Dijumlahkan dari Perhitungan Net Sales</h6>
                    <p>Sistem menjumlahkan 8 komponen biaya berikut. Komponen yang kosong dianggap 0:</p>
                    <ul>
                        <li>Transportasi</li>
                        <li>Akomodasi Peserta</li>
                        <li>Akomodasi Tim</li>
                        <li>Fresh Money</li>
                        <li>Entertain</li>
                        <li>Souvenir</li>
                        <li>Cashback</li>
                        <li>Sewa Laptop</li>
                    </ul>
                    <p>
                        Jika satu RKM punya beberapa baris Perhitungan Net Sales atau beberapa baris Outstanding,
                        semuanya <strong>dijumlahkan</strong> lebih dulu sebelum dibandingkan.
                    </p>

                    <div class="alert alert-warning">
                        <strong>Pembandingan sangat ketat:</strong> selisih Rp 1 saja sudah dianggap <strong>tidak akurat</strong>.
                        Angka di belakang koma diabaikan (dibuang) sebelum dibandingkan.
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah RKM akurat / jumlah RKM yang dihitung) x 100
gap      = progress - nilai target</pre>
                    <p class="small text-muted">Gap negatif berarti progress masih di bawah target.</p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Ada 50 RKM yang memenuhi syarat</li>
                            <li>5 RKM belum punya Perhitungan Net Sales → <strong>dilewati</strong>, sisa 45 RKM yang dihitung</li>
                            <li>3 RKM punya perhitungan tetapi belum ada Outstanding → tidak akurat</li>
                            <li>Dari 42 RKM yang punya keduanya: 36 angkanya sama persis (akurat), 6 berbeda (tidak akurat)</li>
                        </ul>
                        Progress = (36 ÷ 45) × 100 = <strong>80%</strong><br>
                        Jika nilai target = 90, gap = 80 − 90 = <strong>−10</strong><br><br>
                        <strong>Contoh satu RKM:</strong> Transportasi Rp 500.000 + Akomodasi Peserta Rp 1.000.000 + Souvenir Rp 250.000
                        = Rp 1.750.000. Jika net sales di Outstanding juga Rp 1.750.000 → <strong>akurat</strong>.
                        Jika tercatat Rp 1.800.000 → <strong>tidak akurat</strong>.
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah RKM akurat (Above) dibanding yang tidak akurat (Below), dari RKM yang dihitung.</li>
                        <li><strong>Grafik bulanan:</strong> <strong>jumlah</strong> RKM akurat per bulan, menurut bulan tanggal awal kelas.</li>
                        <li><strong>Grafik harian:</strong> <strong>jumlah</strong> RKM akurat pada tanggal awal kelas tersebut.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Bulan yang tidak punya RKM akurat sama sekali tidak muncul di grafik, walaupun ada RKM di bulan itu.
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Tidak ada RKM yang memenuhi syarat</td>
                                    <td>Periksa: tanggal awal kelas di tahun target, status RKM 0, RKM belum dihapus, dan peluangnya sudah tidak tentatif</td>
                                </tr>
                                <tr>
                                    <td>Peluang RKM masih berstatus tentatif</td>
                                    <td>RKM dengan peluang tentatif tidak diperiksa. Ubah status peluang jika sudah pasti</td>
                                </tr>
                                <tr>
                                    <td>RKM belum punya Perhitungan Net Sales</td>
                                    <td>RKM ini dilewati. Isi Perhitungan Net Sales di menu Laporan Penjualan agar ikut dinilai</td>
                                </tr>
                                <tr>
                                    <td>RKM sudah punya Perhitungan Net Sales tetapi belum punya Outstanding</td>
                                    <td>RKM ini pasti dianggap tidak akurat. Lengkapi data di menu Outstanding</td>
                                </tr>
                                <tr>
                                    <td>Total biaya berbeda dengan net sales Outstanding</td>
                                    <td>Cocokkan kedua angka sampai sama persis. Periksa 8 komponen biaya, terutama yang kosong atau salah ketik</td>
                                </tr>
                                <tr>
                                    <td>Progress turun setelah ada RKM baru</td>
                                    <td>RKM baru yang sudah punya perhitungan tetapi belum punya Outstanding langsung dihitung tidak akurat</td>
                                </tr>
                                <tr>
                                    <td>RKM bulan-bulan mendatang tidak ikut dihitung</td>
                                    <td>Untuk tahun berjalan, hanya RKM dengan tanggal awal sampai hari ini yang dihitung</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Cek dulu RKM yang sudah punya Perhitungan Net Sales tetapi belum punya Outstanding,
                        karena pasti dihitung tidak akurat. Setelah itu baru cocokkan angka yang selisih.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Akurasi Kelengkapan Data Penjualan"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>RKM dan Peluang Siap</strong><br>
                            RKM dibuat dan peluangnya sudah tidak tentatif.
                        </li>
                        <li class="mb-2"><strong>Isi Perhitungan Net Sales</strong><br>
                            Isi rincian biaya (transportasi, akomodasi, fresh money, entertain, souvenir, cashback, sewa laptop) untuk RKM.
                        </li>
                        <li class="mb-2"><strong>Isi Outstanding</strong><br>
                            Catat net sales pada data Outstanding RKM yang sama.
                        </li>
                        <li class="mb-2"><strong>Sistem Membandingkan</strong><br>
                            Sistem menjumlahkan biaya, lalu membandingkannya dengan net sales Outstanding. Jika sama persis, RKM dianggap akurat.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress (persentase RKM akurat) muncul di dashboard. Rincian ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // ADM SALES 3. TODO ADMINISTRASI
            // =====================================================
            'todo_administrasi' => [
                'judul' => 'Todo Administrasi',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen todo administrasi yang sudah benar-benar selesai</strong>
                        di tahun yang dipilih.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>Todo Administrasi</strong></li>
                        <li>Tahun dilihat dari <strong>tanggal todo dibuat</strong></li>
                        <li>Semua todo di tahun itu dihitung, tidak dibedakan per orang</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Kapan Todo Dianggap Selesai?</h6>
                    <p>Todo dihitung selesai jika <strong>kedua syarat</strong> ini terpenuhi:</p>
                    <ol>
                        <li>Status todo = <strong>selesai</strong></li>
                        <li>Kolom <strong>Solusi sudah terisi</strong></li>
                    </ol>
                    <p>Jika salah satunya tidak terpenuhi, todo dianggap <strong>belum selesai</strong>.</p>

                    <div class="alert alert-warning">
                        <strong>Ketepatan waktu tidak dinilai.</strong> Todo yang diselesaikan terlambat tetap dihitung selesai
                        asalkan statusnya selesai dan solusinya terisi.
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah todo selesai / total todo di tahun itu) x 100
gap      = progress - nilai target</pre>
                    <p class="small text-muted">Jika belum ada todo sama sekali di tahun itu, progress = 0. Gap negatif berarti progress masih di bawah target.</p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Total todo = <strong>80</strong></li>
                            <li>60 todo berstatus selesai <strong>dan</strong> solusinya terisi → dihitung selesai</li>
                            <li>10 todo berstatus selesai <strong>tetapi solusinya kosong</strong> → belum dihitung</li>
                            <li>10 todo belum berstatus selesai → belum dihitung</li>
                        </ul>
                        Progress = (60 ÷ 80) × 100 = <strong>75%</strong><br>
                        Jika nilai target = 90, gap = 75 − 90 = <strong>−15</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah todo selesai (Above) dibanding yang belum selesai (Below).</li>
                        <li><strong>Grafik bulanan:</strong> gambaran kasar jumlah todo yang dibuat per hari dalam bulan tersebut.</li>
                        <li><strong>Grafik harian:</strong> jumlah todo yang <strong>dibuat</strong> pada tanggal tersebut.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Grafik bulanan dan harian menunjukkan <strong>banyaknya todo yang masuk</strong>, bukan persentase yang selesai.
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada todo di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Pastikan todo sudah dibuat di menu Todo Administrasi</td>
                                </tr>
                                <tr>
                                    <td>Status todo belum diubah menjadi selesai</td>
                                    <td>Ubah status menjadi selesai setelah pekerjaan rampung</td>
                                </tr>
                                <tr>
                                    <td>Status sudah selesai tetapi kolom Solusi kosong</td>
                                    <td>Isi kolom Solusi. Tanpa solusi, todo belum dihitung selesai</td>
                                </tr>
                                <tr>
                                    <td>Todo dibuat di tahun lalu, diselesaikan di tahun ini</td>
                                    <td>Todo tetap dihitung di tahun saat todo dibuat, bukan tahun selesai</td>
                                </tr>
                                <tr>
                                    <td>Banyak todo menumpuk dan belum selesai</td>
                                    <td>Selesaikan todo yang tertunda dan lengkapi solusinya</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Biasakan mengisi solusi pada saat mengubah status menjadi selesai,
                        agar todo langsung dihitung dan progress tidak tertahan.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Todo Administrasi"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Todo Dibuat</strong><br>
                            Setiap pekerjaan administrasi dicatat sebagai todo di menu Todo Administrasi. Tanggal dibuat tercatat otomatis.
                        </li>
                        <li class="mb-2"><strong>Kerjakan dan Isi Solusi</strong><br>
                            Setelah dikerjakan, isi kolom Solusi lalu ubah status menjadi selesai.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem menghitung berapa persen todo yang berstatus selesai dan solusinya terisi.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress muncul di dashboard. Rincian ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // CUSTOMER CARE 1. PESERTA PUAS DENGAN PELAYANAN DAN FASILITAS TRAINING
            // =====================================================
            'peserta_puas_dengan_pelayanan_dan_fasilitas_training' => [
                'judul' => 'Peserta Puas dengan Pelayanan dan Fasilitas Training',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen peserta yang puas</strong> terhadap pelayanan dan fasilitas training,
                        berdasarkan <strong>form feedback</strong> yang diisi peserta.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Feedback Peserta</strong> (tabel <code>Nilaifeedback</code>)</li>
                        <li>Tahun dilihat dari <strong>tanggal feedback dibuat</strong></li>
                        <li>Semua feedback di tahun itu dihitung, tidak dibedakan per orang atau per kelas</li>
                        <li>Setiap feedback memiliki <strong>12 pertanyaan penilaian</strong>: F1 sampai F5 dan P1 sampai P7, dengan skala <strong>1 sampai 4</strong></li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menilai Setiap Feedback</h6>
                    <ol>
                        <li>Semua jawaban (12 pertanyaan) dijumlahkan</li>
                        <li>Dibagi 12 untuk mendapat <strong>rata-rata</strong> (hasilnya dibatasi antara 1 sampai 4)</li>
                        <li>Jika rata-rata <strong>≥ 3,5</strong> → peserta dianggap <strong>puas</strong></li>
                        <li>Jika di bawah 3,5 → <strong>belum puas</strong></li>
                    </ol>
                    <p class="small text-muted">
                        Agar rata-rata mencapai 3,5, jumlah seluruh jawaban minimal <strong>42 dari 48</strong>.
                    </p>

                    <div class="alert alert-warning">
                        <strong>Pertanyaan yang kosong dihitung 0.</strong> Jika ada pertanyaan yang tidak terisi, nilainya dianggap 0,
                        sehingga rata-rata peserta bisa turun dan dianggap belum puas walaupun jawaban lainnya bagus.
                        Pembaginya tetap 12.
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah peserta puas / total feedback) x 100
gap      = 0 jika progress sudah melewati target
                    (jika belum: progress - nilai target, hasilnya negatif)</pre>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong><br><br>
                        <strong>Satu feedback:</strong> F1 sampai F5 semuanya 4 (jumlah 20), P1 sampai P7 berisi 4, 4, 4, 3, 3, 3, 3 (jumlah 24).
                        Total = 44, rata-rata = 44 ÷ 12 = <strong>3,67</strong> → puas ✅<br><br>
                        <strong>Feedback lain:</strong> semua jawaban 3. Total = 36, rata-rata = <strong>3,0</strong> → belum puas ❌<br><br>
                        Jika ada 200 feedback dan 170 di antaranya rata-ratanya ≥ 3,5:<br>
                        Progress = (170 ÷ 200) × 100 = <strong>85%</strong><br>
                        Jika nilai target = 90, gap = 85 − 90 = <strong>−5</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah peserta puas (Above) dibanding yang belum puas (Below).</li>
                        <li><strong>Grafik bulanan:</strong> <strong>rata-rata skor</strong> feedback (skala 1 sampai 4) pada bulan tersebut.</li>
                        <li><strong>Grafik harian:</strong> skor feedback pada tanggal tersebut. Jika ada beberapa feedback di hari yang sama, hanya <strong>feedback terakhir</strong> yang ditampilkan.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada feedback di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Pastikan peserta sudah mengisi feedback</td>
                                </tr>
                                <tr>
                                    <td>Banyak pertanyaan feedback tidak terisi</td>
                                    <td>Pertanyaan kosong dihitung 0 sehingga rata-rata turun. Pastikan form feedback diisi lengkap</td>
                                </tr>
                                <tr>
                                    <td>Banyak feedback dengan rata-rata di bawah 3,5</td>
                                    <td>Tingkatkan kualitas pelayanan dan fasilitas training. Rata-rata 3,4 pun belum dihitung puas</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Ajak peserta mengisi semua pertanyaan feedback. Satu atau dua pertanyaan
                        yang terlewat bisa membuat peserta yang sebenarnya puas tidak terhitung puas.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Peserta Puas dengan Pelayanan dan Fasilitas Training"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Training Berjalan</strong><br>
                            Peserta mengikuti training.
                        </li>
                        <li class="mb-2"><strong>Peserta Mengisi Feedback</strong><br>
                            Peserta mengisi form feedback (12 pertanyaan, skala 1 sampai 4). Tanggal pengisian tercatat otomatis.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem merata-ratakan 12 jawaban tiap feedback. Rata-rata ≥ 3,5 dianggap puas.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress (persentase peserta puas) muncul di dashboard. Rincian ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // CUSTOMER CARE 2. DORONG INOVASI PELAYANAN
            // =====================================================
            'dorong_inovasi_pelayanan' => [
                'judul' => 'Dorong Inovasi Pelayanan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini <strong>tidak dihitung otomatis</strong>. Nilai pencapaian
                        <strong>diisi manual</strong> oleh pengguna, lalu langsung menjadi progress dan dibandingkan dengan nilai target.
                    </p>

                    <h6 class="fw-semibold">Cara Mengisi</h6>
                    <ol>
                        <li>Pada tabel target, klik tombol <strong>Aksi</strong> → <strong>Isi Data</strong></li>
                        <li>Masukkan angka pada kolom <strong>Masukan Nilai</strong></li>
                        <li>(Opsional) Unggah <strong>Dokumen Pendukung</strong> berupa PDF, JPG, atau PNG (maksimal 5 MB)</li>
                        <li>Klik <strong>Simpan</strong></li>
                    </ol>

                    <div class="alert alert-warning">
                        <strong>Perlu diketahui:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Angka yang disimpan adalah <strong>angka bulat saja</strong>. Pilihan <em>Format Nilai</em> (Angka, Persen, Rupiah) hanya mengatur tampilan di kolom input, bukan nilai yang tersimpan.</li>
                            <li>Nilai <strong>0 atau kosong</strong> dihitung sebagai progress 0.</li>
                            <li>Isian baru <strong>menimpa</strong> isian lama. Dokumen baru juga menggantikan dokumen lama.</li>
                        </ul>
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = nilai yang diisi manual (dibulatkan)
gap      = 0 jika progress sudah melewati target
                    (jika belum: progress - nilai target, hasilnya negatif)</pre>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Nilai target = 10 inovasi. Anda mengisi <strong>8</strong> dan melampirkan dokumen bukti.<br>
                        Progress = <strong>8</strong>, gap = 8 − 10 = <strong>−2</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Dokumen Pendukung</h6>
                    <p class="mb-0">
                        Dokumen tampil di bagian <strong>Dokumen Manual</strong> pada halaman detail dan bisa diunduh.
                        Hanya <strong>gambar (JPG, JPEG, PNG) dan PDF</strong> yang bisa ditampilkan.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Nilai belum diisi</td>
                                    <td>Klik <strong>Aksi → Isi Data</strong>, masukkan nilai, lalu simpan</td>
                                </tr>
                                <tr>
                                    <td>Nilai yang diisi 0</td>
                                    <td>Nilai 0 dihitung progress 0. Isi dengan angka lebih dari 0</td>
                                </tr>
                                <tr>
                                    <td>Tahun (detail jangka) target kosong</td>
                                    <td>Target tanpa detail jangka dihitung progress 0 walaupun nilai sudah diisi. Periksa saat membuat target, atau buat ulang target</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0, serta tahun valid</td>
                                </tr>
                                <tr>
                                    <td>Gagal menyimpan</td>
                                    <td>Pastikan dokumen berformat PDF, JPG, atau PNG dan ukuran maksimal 5 MB</td>
                                </tr>
                                <tr>
                                    <td>Dokumen tidak tampil di detail</td>
                                    <td>Hanya gambar dan PDF yang bisa ditampilkan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Dorong Inovasi Pelayanan"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Jalankan Inovasi</strong><br>
                            Tim menjalankan inovasi pelayanan sepanjang tahun.
                        </li>
                        <li class="mb-2"><strong>Isi Data</strong><br>
                            Lewat <strong>Aksi → Isi Data</strong>, masukkan jumlah pencapaian dan lampirkan bukti.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress dan dokumen muncul di dashboard serta halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // CUSTOMER CARE 3. PENANGANAN KOMPLAIN PESERTA
            // =====================================================
            'penanganan_komplain_peserta' => [
                'judul' => 'Penanganan Komplain Peserta',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen komplain peserta yang diselesaikan di hari yang sama</strong>
                        dengan hari komplain masuk.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Komplain Peserta</strong></li>
                        <li>Tahun dilihat dari <strong>tanggal komplain dibuat</strong></li>
                        <li>Kolom yang dilihat: tanggal komplain dibuat dan <code>tanggal_selesai</code></li>
                        <li>Semua komplain di tahun itu dihitung, tidak dibedakan per orang</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Kapan Komplain Dianggap Tepat Waktu?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60%">Kondisi</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tanggal selesai <strong>sama</strong> dengan tanggal komplain dibuat</td>
                                    <td><span class="badge bg-success">Tepat waktu</span></td>
                                </tr>
                                <tr>
                                    <td>Tanggal selesai <strong>berbeda hari</strong> (sehari setelahnya pun)</td>
                                    <td><span class="badge bg-danger">Tidak tepat waktu</span></td>
                                </tr>
                                <tr>
                                    <td>Tanggal selesai <strong>belum diisi</strong></td>
                                    <td><span class="badge bg-danger">Tidak tepat waktu</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted">Yang dibandingkan hanya <strong>tanggalnya</strong>, bukan jam. Komplain masuk pukul 16.00 dan selesai pukul 17.00 di hari yang sama tetap tepat waktu.</p>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah komplain selesai di hari yang sama / total komplain) x 100
gap      = 0 jika progress sudah melewati target
                    (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika belum ada komplain sama sekali di tahun itu, progress = 0.</p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Total komplain = <strong>20</strong></li>
                            <li>15 komplain selesai di hari yang sama → tepat waktu</li>
                            <li>3 komplain selesai keesokan harinya → tidak tepat waktu</li>
                            <li>2 komplain belum diisi tanggal selesainya → tidak tepat waktu</li>
                        </ul>
                        Progress = (15 ÷ 20) × 100 = <strong>75%</strong><br>
                        Jika nilai target = 90, gap = 75 − 90 = <strong>−15</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah komplain tepat waktu (Above) dibanding yang tidak tepat waktu (Below).</li>
                        <li><strong>Grafik harian:</strong> persentase komplain yang diselesaikan di hari yang sama, pada tanggal komplain tersebut.</li>
                        <li><strong>Grafik bulanan:</strong> rata-rata dari persentase harian. Setiap <strong>hari</strong> dihitung sama bobotnya, jadi hari dengan 1 komplain berpengaruh sama dengan hari dengan 10 komplain.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada komplain di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Progress 0 di sini bukan berarti kinerja buruk, hanya belum ada data</td>
                                </tr>
                                <tr>
                                    <td>Tanggal selesai belum diisi</td>
                                    <td>Isi tanggal selesai segera setelah komplain ditangani. Tanpa itu komplain dianggap tidak tepat waktu</td>
                                </tr>
                                <tr>
                                    <td>Komplain selesai di hari berikutnya</td>
                                    <td>Hanya penyelesaian di hari yang sama yang dianggap tepat waktu</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Usahakan setiap komplain selesai dan tanggal selesainya diisi di hari yang sama.
                        Komplain yang masuk sore hari paling berisiko terlewat.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Penanganan Komplain Peserta"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>Komplain Masuk</strong><br>
                            Komplain peserta dicatat di menu Komplain Peserta. Tanggal masuk tercatat otomatis.
                        </li>
                        <li class="mb-2"><strong>Tangani dan Isi Tanggal Selesai</strong><br>
                            Setelah ditangani, isi tanggal selesai pada komplain tersebut.
                        </li>
                        <li class="mb-2"><strong>Sistem Membandingkan</strong><br>
                            Sistem mengecek apakah tanggal selesai sama dengan tanggal komplain masuk.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress (persentase komplain tepat waktu) muncul di dashboard. Rincian ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // CUSTOMER CARE 4. REPORT PERSIAPAN KELAS
            // =====================================================
            'report_persiapan_kelas' => [
                'judul' => 'Report Persiapan Kelas',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen kelas (RKM) yang persiapannya lengkap</strong>.
                        Persiapan dianggap lengkap jika <strong>seluruh item checklist</strong> sudah dicentang.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>RKM</strong> → jumlah kelas di tahun target (pembagi). Hanya RKM berstatus <code>0</code>, dengan tahun dilihat dari <strong>tanggal awal kelas</strong>.</li>
                        <li><strong>Checklist Keperluan</strong> → checklist persiapan milik RKM (yang dihitung / pembilang)</li>
                        <li><strong>Sub Checklist Keperluan</strong> → rincian checklist yang lebih detail</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Kapan Checklist Dianggap Tuntas?</h6>
                    <p>Satu checklist tuntas jika <strong>semua</strong> syarat ini terpenuhi:</p>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%">Bagian</th>
                                    <th>Yang Harus Dicentang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Checklist Utama</strong> (5 item)</td>
                                    <td>Tanggal keperluan <strong>terisi</strong>, serta Materi, Kelas, CB, Maksi, dan Keperluan Kelas <strong>semuanya dicentang</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Checklist: Materi</strong></td>
                                    <td>Materi Module, Materi E-Learning</td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Checklist: CB</strong></td>
                                    <td>CB Instruktur, CB Peserta</td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Checklist: Maksi</strong></td>
                                    <td>Maksi Instruktur, Maksi Peserta</td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Checklist: Kelas</strong> (9 item)</td>
                                    <td>AC, Jam, Buku, Pulpen, Permen, Camilan, Minuman, Lampu, Kondisi Kebersihan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Semua atau tidak sama sekali.</strong> Total ada <strong>5 item utama + 15 item sub checklist</strong>.
                        Jika <strong>satu saja</strong> belum dicentang, checklist tersebut tidak dihitung tuntas
                        (tidak ada nilai setengah).
                    </div>

                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah checklist tuntas / jumlah RKM) x 100
gap      = 0 jika progress sudah melewati target
                    (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika tidak ada RKM di tahun itu, progress = 0.</p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Ada <strong>40</strong> RKM</li>
                            <li>30 checklist sudah lengkap semua item → tuntas</li>
                            <li>10 checklist masih ada yang belum dicentang (misalnya Lampu atau Camilan) → tidak dihitung</li>
                        </ul>
                        Progress = (30 ÷ 40) × 100 = <strong>75%</strong><br>
                        Jika nilai target = 90, gap = 75 − 90 = <strong>−15</strong>
                    </div>

                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah checklist tuntas (Above) dibanding sisanya (Below, dari total RKM).</li>
                        <li><strong>Grafik bulanan:</strong> <strong>jumlah</strong> checklist tuntas pada bulan tersebut, menurut <strong>tanggal keperluan</strong>.</li>
                        <li><strong>Grafik harian:</strong> <strong>jumlah</strong> checklist tuntas pada tanggal keperluan tersebut.</li>
                        <li>Progress pada halaman detail <strong>dibatasi maksimal 100</strong>.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.
                    </p>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada RKM di tahun tersebut</td>
                                    <td>Progress otomatis 0. Pastikan RKM sudah dibuat dengan tanggal awal di tahun target dan berstatus 0</td>
                                </tr>
                                <tr>
                                    <td>Checklist belum dibuat untuk RKM</td>
                                    <td>Buat checklist persiapan untuk setiap RKM</td>
                                </tr>
                                <tr>
                                    <td>Tanggal keperluan belum diisi</td>
                                    <td>Isi tanggal keperluan pada checklist. Tanpa tanggal, checklist tidak dihitung tuntas</td>
                                </tr>
                                <tr>
                                    <td>Ada satu atau lebih item utama yang belum dicentang</td>
                                    <td>Centang Materi, Kelas, CB, Maksi, dan Keperluan Kelas</td>
                                </tr>
                                <tr>
                                    <td>Sub checklist belum dibuat atau belum lengkap</td>
                                    <td>Pastikan sub checklist ada dan ke-15 itemnya dicentang. Tanpa sub checklist, checklist tidak dihitung tuntas</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Periksa item yang paling sering terlewat, seperti Camilan, Lampu, atau
                        Kondisi Kebersihan. Satu item kosong membuat seluruh checklist tidak dihitung.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Report Persiapan Kelas"</em> dan isi tahun.
                        </li>
                        <li class="mb-2"><strong>RKM Dibuat</strong><br>
                            Setiap kelas dijadwalkan sebagai RKM dengan tanggal awal kelas.
                        </li>
                        <li class="mb-2"><strong>Isi Checklist Persiapan</strong><br>
                            Customer Care mengisi tanggal keperluan dan mencentang checklist utama beserta sub checklist.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem menghitung checklist yang seluruh itemnya sudah dicentang, lalu membaginya dengan jumlah RKM.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress muncul di dashboard. Rincian ada di halaman detail target.
                        </li>
                    </ol>
                '
            ],

            // =====================================================
            // KEPUASAN CLIENT ITSM
            // =====================================================
            'kepuasan_client_itsm' => [
                'judul' => 'Kepuasan Client ITSM',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen client yang merasa puas</strong>
                        terhadap layanan ITSM (layanan teknologi & sistem internal perusahaan).
                    </p>

                    <p class="mb-3">
                        Singkatnya: setelah client menerima layanan, mereka mengisi penilaian.
                        Sistem lalu menghitung berapa banyak yang memberi nilai bagus.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>Nilai Feedback</strong> (form penilaian client)</li>
                        <li>Setiap client mengisi 5 pertanyaan dari feedback peserta</li>
                        <li>Hanya data di <strong>tahun yang dipilih</strong> saat membuat target yang dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Step by Step)</h6>
                    <ol>
                        <li>
                            <strong>Ambil semua feedback</strong> di tahun target
                            <br><small class="text-muted">Contoh: semua feedback tahun 2025</small>
                        </li>
                        <li>
                            <strong>Untuk setiap client / responden</strong>, hitung rata-rata 5 nilai dari pertanyaan feedback
                        </li>
                        <li>
                            <strong>Batasi nilai</strong> agar tetap di antara 1–4
                            <br><small class="text-muted">(kalau hasil aneh, sistem tetap memotong ke rentang ini)</small>
                        </li>
                        <li>
                            <strong>Tentukan status puas / tidak puas</strong>
                            <ul>
                                <li>Rata-rata <strong>≥ 3,0</strong> → dianggap <strong>puas</strong> ✅</li>
                                <li>Rata-rata <strong>&lt; 3,0</strong> → dianggap <strong>tidak puas</strong> ❌</li>
                            </ul>
                        </li>
                        <li>
                            <strong>Hitung progress akhir</strong>
                            <br><code>Progress = (Jumlah yang puas ÷ Total responden) × 100</code>
                        </li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (mudah dibayangkan):</strong><br><br>
                        Di tahun 2025 ada <strong>5 client</strong> yang mengisi feedback:
                        <div class="table-responsive mt-2 mb-2">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Client</th>
                                        <th>F1</th>
                                        <th>F2</th>
                                        <th>F3</th>
                                        <th>F4</th>
                                        <th>F5</th>
                                        <th>Rata-rata</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>A</td>
                                        <td>4</td><td>4</td><td>3</td><td>4</td><td>3</td>
                                        <td><strong>3,6</strong></td>
                                        <td>Puas ✅</td>
                                    </tr>
                                    <tr>
                                        <td>B</td>
                                        <td>2</td><td>3</td><td>2</td><td>3</td><td>3</td>
                                        <td><strong>2,6</strong></td>
                                        <td>Tidak puas ❌</td>
                                    </tr>
                                    <tr>
                                        <td>C</td>
                                        <td>3</td><td>3</td><td>3</td><td>4</td><td>3</td>
                                        <td><strong>3,2</strong></td>
                                        <td>Puas ✅</td>
                                    </tr>
                                    <tr>
                                        <td>D</td>
                                        <td>3</td><td>3</td><td>3</td><td>3</td><td>3</td>
                                        <td><strong>3,0</strong></td>
                                        <td>Puas ✅</td>
                                    </tr>
                                    <tr>
                                        <td>E</td>
                                        <td>2</td><td>2</td><td>3</td><td>2</td><td>2</td>
                                        <td><strong>2,2</strong></td>
                                        <td>Tidak puas ❌</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        Yang puas: <strong>3 orang</strong> (A, C, D)<br>
                        Total responden: <strong>5 orang</strong><br>
                        <strong>Progress = (3 ÷ 5) × 100 = 60%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Intinya:</strong> semakin banyak client yang rata-ratanya minimal 3,0,
                        semakin tinggi angka progress di dashboard.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">
                        Kalau progress muncul <strong>0</strong>, kosong, atau terasa aneh,
                        cek kemungkinan berikut satu per satu:
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi / Yang Perlu Dicek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau salah</td>
                                    <td>Saat buat target, pastikan kolom tahun terisi (contoh: 2025). Tahun yang salah = data tidak ketemu.</td>
                                </tr>
                                <tr>
                                    <td>Belum ada client yang mengisi feedback</td>
                                    <td>Pastikan form feedback sudah diisi client di tahun yang sama dengan target.</td>
                                </tr>
                                <tr>
                                    <td>Nilai F1–F5 kosong / tidak lengkap</td>
                                    <td>Feedback harus terisi kelima nilai. Kalau banyak yang kosong, rata-rata bisa rendah atau tidak valid.</td>
                                </tr>
                                <tr>
                                    <td>Banyak client memberi nilai di bawah 3</td>
                                    <td>Ini bukan error sistem. Artinya kualitas layanan masih perlu ditingkatkan agar penilaian naik.</td>
                                </tr>
                                <tr>
                                    <td>Feedback dibuat di tahun berbeda</td>
                                    <td>Sistem hanya menghitung data sesuai tahun target. Cek tanggal pengisian feedback.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips praktis:</strong><br>
                        1. Buka data feedback tahun berjalan<br>
                        2. Hitung manual 2–3 data: rata-ratanya ≥ 3 atau tidak<br>
                        3. Bandingkan dengan progress di dashboard<br>
                        Kalau manual sudah benar tapi dashboard 0, kemungkinan filter tahun / data belum masuk.
                    </div>
                ',

                'alur_bisnis' => '
                    <p class="mb-3">Alur dari awal sampai hasil muncul di dashboard:</p>
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target KPI</strong><br>
                            Pilih Assistant Route <em>"Kepuasan Client ITSM"</em>, isi tahun, lalu simpan.
                        </li>
                        <li class="mb-2">
                            <strong>Layanan ITSM berjalan</strong><br>
                            Tim ITSM melayani client (support, sistem, dll).
                        </li>
                        <li class="mb-2">
                            <strong>Client mengisi feedback</strong><br>
                            Client menilai layanan lewat form (F1 sampai F5).
                        </li>
                        <li class="mb-2">
                            <strong>Sistem menghitung otomatis</strong><br>
                            Setiap feedback dihitung rata-ratanya. Yang ≥ 3,0 dihitung sebagai “puas”.
                        </li>
                        <li class="mb-0">
                            <strong>Lihat hasil di dashboard</strong><br>
                            Progress = persen client yang puas. Bisa dilihat juga ringkasan per bulan.
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // INOVATION ADAPTION RATE
            // =====================================================
            'inovation_adaption_rate' => [
                'judul' => 'Inovation Adaption Rate',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>apakah divisi sudah mulai mengadopsi inovasi</strong>
                        dengan cara sederhana: <strong>apakah ada ide inovasi yang tercatat di sistem</strong>
                        pada tahun target.
                    </p>

                    <p class="mb-3">
                        Bukan menghitung seberapa bagus ide-nya, dan bukan menghitung berapa banyak ide.
                        Cukup menjawab pertanyaan: <em>“Di tahun ini, sudah ada ide inovasi?”</em>
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data Diambil Dari Fitur Ide Inovasi</li>
                        <li>Yang dilihat: jumlah ide yang dibuat di tahun target</li>
                        <li>Filter berdasarkan tahun di <code>detail_jangka</code> (tahun yang dipilih saat buat target)</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Sistem menghitung berapa ide inovasi yang dibuat di tahun tersebut</li>
                        <li>
                            <strong>Jika jumlah ide ≥ 1</strong> (ada minimal satu ide)
                            <br>→ Progress = <strong>100%</strong>
                        </li>
                        <li>
                            <strong>Jika jumlah ide = 0</strong> (belum ada sama sekali)
                            <br>→ Progress = <strong>0%</strong>
                        </li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br><br>

                        <strong>Kasus 1 — Sudah ada ide</strong><br>
                        Tahun target: 2025<br>
                        Ide yang diinput di 2025:
                        <ul class="mb-2 mt-1">
                            <li>Ide A: “Otomasi laporan harian”</li>
                            <li>Ide B: “Chatbot FAQ internal”</li>
                            <li>Ide C: “Dashboard monitoring server”</li>
                        </ul>
                        Jumlah ide = 3 (≥ 1)<br>
                        <strong>Progress = 100%</strong><br><br>

                        <strong>Kasus 2 — Belum ada ide</strong><br>
                        Tahun target: 2025<br>
                        Belum ada data ide inovasi sepanjang tahun 2025<br>
                        Jumlah ide = 0<br>
                        <strong>Progress = 0%</strong><br><br>

                        <strong>Kasus 3 — Ide ada, tapi tahun beda</strong><br>
                        Ada 5 ide, semuanya dibuat tahun 2024<br>
                        Target yang dicek: tahun 2025<br>
                        Jumlah ide di 2025 = 0<br>
                        <strong>Progress = 0%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Penting untuk dipahami:</strong><br>
                        • 1 ide saja sudah cukup untuk progress 100%<br>
                        • 10 ide juga tetap 100% (tidak ada tambahan poin)<br>
                        • Yang dihitung hanya ide di <strong>tahun target</strong>, bukan total sepanjang masa
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">
                        Kalau progress masih <strong>0</strong>, hampir selalu karena belum ada ide
                        yang tercatat di tahun yang benar. Cek daftar ini:
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi / Yang Perlu Dicek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan saat membuat target, tahun sudah diisi dengan benar (contoh: 2025).</td>
                                </tr>
                                <tr>
                                    <td>Belum ada ide inovasi sama sekali di tahun itu</td>
                                    <td>Input minimal 1 ide di menu Ide Inovasi. Setelah tersimpan, progress bisa jadi 100%.</td>
                                </tr>
                                <tr>
                                    <td>Ide sudah ada, tapi tanggalnya di tahun lain</td>
                                    <td>Cek tanggal pembuatan ide. Harus jatuh di tahun yang sama dengan target.</td>
                                </tr>
                                <tr>
                                    <td>Ide baru diinput, progress belum berubah</td>
                                    <td>Refresh halaman dashboard / buka ulang detail target. Data dihitung ulang saat dibuka.</td>
                                </tr>
                                <tr>
                                    <td>Ide hanya disimpan sebagai draf / belum final</td>
                                    <td>Pastikan ide benar-benar tersimpan di database (status tersimpan, bukan hanya draft di form).</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips cepat:</strong><br>
                        Buka menu Ide Inovasi → filter tahun target → jika daftarnya kosong, progress memang 0.
                        Cukup tambah 1 ide valid di tahun itu, target ini langsung terpenuhi (100%).
                    </div>
                ',

                'alur_bisnis' => '
                    <p class="mb-3">Alur dari setup sampai hasil muncul:</p>
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target KPI</strong><br>
                            Pilih Assistant Route <em>"Inovation Adaption Rate"</em>, isi tahun, lalu simpan.
                        </li>
                        <li class="mb-2">
                            <strong>Tim mengajukan / mencatat ide inovasi</strong><br>
                            Ide dimasukkan lewat menu Ide Inovasi (bisa dari ITSM atau tim terkait).
                        </li>
                        <li class="mb-2">
                            <strong>Sistem mengecek data</strong><br>
                            Sistem menghitung: “Ada berapa ide di tahun ini?”
                        </li>
                        <li class="mb-2">
                            <strong>Hasil dihitung otomatis</strong><br>
                            Ada minimal 1 ide → 100%. Belum ada → 0%.
                        </li>
                        <li class="mb-0">
                            <strong>Lihat di dashboard</strong><br>
                            Progress langsung terlihat. Kalau sudah 100%, artinya target adopsi inovasi untuk tahun itu sudah tercapai.
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // PERBAIKAN KENDARAAN
            // =====================================================
            'perbaikan_kendaraan' => [
                'judul' => 'Perbaikan Kendaraan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen perbaikan kendaraan yang sudah selesai</strong>
                        dibanding semua data perbaikan di tahun tersebut.
                    </p>

                    <p class="mb-3">
                        Semakin banyak perbaikan yang berstatus <strong>Selesai</strong>, semakin tinggi progress-nya.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel / menu <strong>Perbaikan Kendaraan</strong></li>
                        <li>Yang dilihat: status perbaikan (terutama status <code>Selesai</code>)</li>
                        <li>Hanya data di tahun yang dipilih saat membuat target</li>
                        <li>Jika target untuk satu driver tertentu, hanya data driver itu yang dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua data perbaikan di tahun target</li>
                        <li>Hitung berapa yang statusnya <strong>Selesai</strong></li>
                        <li>Progress = (Jumlah selesai ÷ Total perbaikan) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Di tahun 2025 ada 10 data perbaikan:
                        <ul class="mb-2 mt-2">
                            <li>7 sudah status <strong>Selesai</strong> ✅</li>
                            <li>3 masih proses / belum selesai ❌</li>
                        </ul>
                        <strong>Progress = (7 ÷ 10) × 100 = 70%</strong>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data perbaikan di tahun tersebut</td>
                                    <td>Input data perbaikan kendaraan terlebih dahulu</td>
                                </tr>
                                <tr>
                                    <td>Banyak data belum berstatus "Selesai"</td>
                                    <td>Update status menjadi <strong>Selesai</strong> setelah perbaikan selesai</td>
                                </tr>
                                <tr>
                                    <td>Filter driver tidak sesuai</td>
                                    <td>Pastikan data perbaikan terhubung ke driver yang benar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Progress naik hanya jika status diubah menjadi "Selesai".
                        Data yang masih "Proses" / "Menunggu" tidak dihitung sebagai berhasil.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Perbaikan Kendaraan" + tahun</li>
                        <li class="mb-2"><strong>Input perbaikan</strong> — setiap ada perbaikan kendaraan, data diinput</li>
                        <li class="mb-2"><strong>Selesaikan & update status</strong> — setelah selesai, ubah status menjadi "Selesai"</li>
                        <li class="mb-2"><strong>Sistem menghitung</strong> — % data yang sudah selesai</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress muncul di dashboard</li>
                    </ol>
                ',
            ],

            // =====================================================
            // KONTROL PENGELUARAN TRANSPORTASI
            // =====================================================
            'kontrol_pengeluaran_transportasi' => [
                'judul' => 'Kontrol Pengeluaran Transportasi',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen minggu yang pengeluarannya masih aman</strong>
                        (tidak melebihi budget transportasi).
                    </p>

                    <p class="mb-3">
                        Sistem melihat pengeluaran per minggu. Jika di minggu itu masih ada sisa budget (≥ 0),
                        minggu itu dianggap <strong>aman</strong>.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari <strong>Biaya Transportasi Driver</strong></li>
                        <li>Dihubungkan dengan data pickup driver dan SPJ</li>
                        <li>Budget dihitung lewat kalkulator operasional (budget awal + tambahan − terpakai)</li>
                        <li>Hanya data di tahun target yang dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua biaya transportasi di tahun tersebut</li>
                        <li>Kelompokkan per minggu (mulai Senin)</li>
                        <li>Untuk setiap minggu, hitung sisa budget:
                            <br><code>Sisa = Budget − Pengeluaran</code>
                        </li>
                        <li>Jika sisa ≥ 0 → minggu itu <strong>aman</strong></li>
                        <li>Progress = (Jumlah minggu aman ÷ Total minggu yang ada data) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Ada 4 minggu yang punya data pengeluaran:
                        <ul class="mb-2 mt-2">
                            <li>Minggu 1 → sisa budget Rp 200.000 ✅ aman</li>
                            <li>Minggu 2 → sisa budget Rp 0 ✅ aman</li>
                            <li>Minggu 3 → sisa budget −Rp 150.000 ❌ overbudget</li>
                            <li>Minggu 4 → sisa budget Rp 50.000 ✅ aman</li>
                        </ul>
                        Minggu aman: 3 dari 4<br>
                        <strong>Progress = (3 ÷ 4) × 100 = 75%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Catatan:</strong> Jika belum ada data pengeluaran sama sekali di tahun itu,
                        sistem menganggap progress <strong>100%</strong> (belum ada pelanggaran budget).
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress rendah atau aneh, cek kemungkinan berikut:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data biaya transportasi</td>
                                    <td>Input biaya lewat modul terkait. Jika kosong, progress default 100%</td>
                                </tr>
                                <tr>
                                    <td>Banyak minggu overbudget</td>
                                    <td>Kontrol pengeluaran agar tidak melebihi budget mingguan</td>
                                </tr>
                                <tr>
                                    <td>Budget / SPJ tidak terhubung dengan benar</td>
                                    <td>Pastikan data pickup, SPJ, dan biaya saling terhubung</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Fokus jaga pengeluaran per minggu agar sisa budget tidak negatif.
                        Semakin banyak minggu yang aman, semakin tinggi progress.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Kontrol Pengeluaran Transportasi" + tahun</li>
                        <li class="mb-2"><strong>Data biaya masuk</strong> — setiap pengeluaran transportasi dicatat (driver / SPJ / luar)</li>
                        <li class="mb-2"><strong>Sistem hitung per minggu</strong> — bandingkan budget vs realisasi</li>
                        <li class="mb-2"><strong>Tandai minggu aman / over</strong> — sisa ≥ 0 = aman</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % minggu yang aman</li>
                    </ol>
                ',
            ],

            // =====================================================
            // REPORT KONDISI KENDARAAN
            // =====================================================
            'report_kondisi_kendaraan' => [
                'judul' => 'Report Kondisi Kendaraan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>seberapa rutin driver membuat laporan kondisi kendaraan setiap minggu</strong>.
                    </p>

                    <p class="mb-3">
                        Idealnya: setiap minggu ada minimal 1 laporan pemeriksaan kendaraan
                        (hari libur tidak dihitung sebagai kewajiban).
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>Kondisi Kendaraan</strong></li>
                        <li>Yang dipakai: tanggal pemeriksaan (<code>tanggal_pemeriksaan</code>)</li>
                        <li>Hari libur (dari data Hari Libur) tidak dihitung</li>
                        <li>Perhitungan dimulai dari laporan pertama yang valid di tahun itu</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Cari laporan pertama di tahun target (bukan hari libur)</li>
                        <li>Dari minggu laporan pertama itu, hitung berapa minggu sampai periode cek terakhir</li>
                        <li>Untuk setiap minggu: apakah ada minimal 1 laporan?</li>
                        <li>Progress = (Jumlah minggu ada laporan ÷ Total minggu yang dicek) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Dari minggu pertama ada laporan sampai sekarang ada 8 minggu yang dicek:
                        <ul class="mb-2 mt-2">
                            <li>6 minggu ada laporan kondisi ✅</li>
                            <li>2 minggu tidak ada laporan ❌</li>
                        </ul>
                        <strong>Progress = (6 ÷ 8) × 100 = 75%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Catatan:</strong> Minggu berjalan yang belum selesai biasanya belum dihitung penuh
                        (tergantung hari). Yang diutamakan adalah konsistensi laporan tiap minggu.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada laporan kondisi sama sekali</td>
                                    <td>Input laporan kondisi kendaraan (isi tanggal pemeriksaan)</td>
                                </tr>
                                <tr>
                                    <td>Tanggal pemeriksaan kosong</td>
                                    <td>Pastikan kolom tanggal pemeriksaan terisi</td>
                                </tr>
                                <tr>
                                    <td>Banyak minggu tanpa laporan</td>
                                    <td>Biasakan input laporan minimal 1× setiap minggu</td>
                                </tr>
                                <tr>
                                    <td>Laporan hanya di hari libur</td>
                                    <td>Hari libur tidak dihitung. Buat laporan di hari kerja</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Jadwalkan cek kondisi kendaraan rutin tiap minggu
                        (misalnya setiap Senin atau Jumat) agar progress stabil tinggi.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Report Kondisi Kendaraan" + tahun</li>
                        <li class="mb-2"><strong>Periksa kendaraan</strong> — cek kondisi mobil / motor secara rutin</li>
                        <li class="mb-2"><strong>Input laporan</strong> — isi form kondisi kendaraan + tanggal pemeriksaan</li>
                        <li class="mb-2"><strong>Sistem cek per minggu</strong> — minggu mana yang sudah / belum ada laporan</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % minggu yang ada laporan</li>
                    </ol>
                ',
            ],

            // =====================================================
            // FEEDBACK KENYAMANAN BERKENDARA
            // =====================================================
            'feedback_kenyamanan_berkendara' => [
                'judul' => 'Feedback Kenyamanan Berkendara',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen penumpang / client yang merasa nyaman</strong>
                        saat dibawa berkendara, berdasarkan nilai feedback khusus kenyamanan.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>Nilai Feedback</strong></li>
                        <li>Yang dipakai: kolom <code>P8</code> (pertanyaan tentang kenyamanan berkendara)</li>
                        <li>Nilai biasanya skala 1–4</li>
                        <li>Hanya data yang <code>P8</code>-nya terisi (tidak kosong) yang dihitung</li>
                        <li>Filter tahun sesuai target</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua feedback yang punya nilai P8 di tahun target</li>
                        <li>Setiap nilai dibatasi antara 1–4</li>
                        <li>
                            Jika nilai P8 <strong>≥ 3,5</strong> → dianggap <strong>puas / nyaman</strong> ✅
                        </li>
                        <li>
                            Jika nilai P8 <strong>&lt; 3,5</strong> → dianggap belum nyaman ❌
                        </li>
                        <li>Progress = (Jumlah yang puas ÷ Total responden P8) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Ada 10 orang mengisi P8:
                        <ul class="mb-2 mt-2">
                            <li>7 orang nilai ≥ 3,5 ✅</li>
                            <li>3 orang nilai &lt; 3,5 ❌</li>
                        </ul>
                        <strong>Progress = (7 ÷ 10) × 100 = 70%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Intinya:</strong> semakin banyak penumpang yang memberi nilai kenyamanan minimal 3,5,
                        semakin tinggi progress driver di target ini.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau kosong, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada feedback dengan nilai P8</td>
                                    <td>Pastikan form feedback mengisi pertanyaan kenyamanan (P8)</td>
                                </tr>
                                <tr>
                                    <td>P8 kosong atau bukan angka</td>
                                    <td>Hanya nilai numerik yang dihitung. Pastikan form terisi angka 1–4</td>
                                </tr>
                                <tr>
                                    <td>Banyak nilai P8 di bawah 3,5</td>
                                    <td>Tingkatkan kenyamanan berkendara (cara mengemudi, kondisi kendaraan, dll)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Nilai ambang puas di sini lebih tinggi (3,5),
                        jadi butuh penilaian yang cukup baik, bukan sekadar “cukup”.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Feedback Kenyamanan Berkendara" + tahun</li>
                        <li class="mb-2"><strong>Perjalanan berlangsung</strong> — driver mengantar penumpang / client</li>
                        <li class="mb-2"><strong>Penumpang mengisi feedback</strong> — khususnya pertanyaan kenyamanan (P8)</li>
                        <li class="mb-2"><strong>Sistem menghitung</strong> — nilai ≥ 3,5 dihitung sebagai puas</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % responden yang merasa nyaman</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 1. PENGEMBANGAN KURIKULUM PELATIHAN
            // =====================================================
            'pengembangan_kurikulum_pelatihan' => [
                'judul' => 'Pengembangan Kurikulum Pelatihan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa bulan dalam setahun sudah ada materi kurikulum baru</strong>
                        yang diinput ke sistem.
                    </p>

                    <p class="mb-3">
                        Bukan menghitung jumlah materi, tapi <strong>berapa bulan yang “terisi”</strong>
                        (minimal ada 1 materi di bulan itu).
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel / menu <strong>Materi</strong></li>
                        <li>Yang dilihat: tanggal pembuatan materi (<code>created_at</code>)</li>
                        <li>Hanya data di tahun target yang dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua materi di tahun target</li>
                        <li>Lihat bulan apa saja yang punya minimal 1 materi</li>
                        <li>Progress = <strong>jumlah bulan yang terisi</strong> (maksimal 12)</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Di tahun 2025, materi hanya dibuat di bulan:
                        <ul class="mb-2 mt-2">
                            <li>Januari ✅</li>
                            <li>Maret ✅</li>
                            <li>Juni ✅</li>
                            <li>September ✅</li>
                            <li>November ✅</li>
                        </ul>
                        Bulan terisi: <strong>5</strong><br>
                        <strong>Progress = 5</strong>
                        <br><small class="text-muted">(jika target nilai = 12, berarti masih kurang 7 bulan)</small>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Intinya:</strong> idealnya setiap bulan ada minimal 1 materi baru.
                        Semakin banyak bulan yang terisi, semakin tinggi progress.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada materi di tahun tersebut</td>
                                    <td>Input materi kurikulum di menu Materi</td>
                                </tr>
                                <tr>
                                    <td>Materi hanya di 1–2 bulan</td>
                                    <td>Sebarkan pembuatan materi ke lebih banyak bulan</td>
                                </tr>
                                <tr>
                                    <td>Materi dibuat di tahun berbeda</td>
                                    <td>Pastikan tanggal pembuatan sesuai tahun target</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Jadwalkan minimal 1 materi baru tiap bulan agar progress stabil naik.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Pengembangan Kurikulum Pelatihan" + tahun</li>
                        <li class="mb-2"><strong>Buat / update materi</strong> — input materi kurikulum di sistem</li>
                        <li class="mb-2"><strong>Sistem hitung bulan terisi</strong> — bulan mana saja yang ada materi</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = jumlah bulan yang punya materi</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 2. PENINGKATAN KNOWLEDGE SHARING
            // =====================================================
            'peningkatan_knowledge_sharing' => [
                'judul' => 'Peningkatan Knowledge Sharing',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa minggu sudah ada kegiatan Sharing Knowledge</strong>
                        yang tercatat di aktivitas instruktur.
                    </p>

                    <p class="mb-3">
                        Yang dihitung adalah <strong>jumlah minggu unik</strong> yang punya minimal 1 sesi sharing,
                        bukan jumlah sesi keseluruhan.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari <strong>Activity Instruktur</strong></li>
                        <li>Hanya aktivitas dengan tipe <code>Sharing Knowledge</code></li>
                        <li>Yang dilihat: tanggal aktivitas (<code>activity_date</code>)</li>
                        <li>Filter tahun sesuai target</li>
                        <li>Jika di-assign ke orang tertentu, hanya aktivitas orang itu yang dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua aktivitas tipe "Sharing Knowledge" di tahun target</li>
                        <li>Kelompokkan berdasarkan minggu</li>
                        <li>Progress = <strong>jumlah minggu yang punya minimal 1 sesi sharing</strong></li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Di tahun 2025 ada sesi sharing di:
                        <ul class="mb-2 mt-2">
                            <li>Minggu ke-3 Januari</li>
                            <li>Minggu ke-1 Februari (2 sesi di minggu yang sama)</li>
                            <li>Minggu ke-2 Maret</li>
                        </ul>
                        Minggu unik terisi: <strong>3</strong><br>
                        <strong>Progress = 3</strong>
                        <br><small class="text-muted">2 sesi di minggu yang sama tetap dihitung 1 minggu</small>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada aktivitas Sharing Knowledge</td>
                                    <td>Catat kegiatan sharing di Activity Instruktur dengan tipe yang benar</td>
                                </tr>
                                <tr>
                                    <td>Tipe aktivitas salah / beda ejaan</td>
                                    <td>Harus tepat <code>Sharing Knowledge</code></td>
                                </tr>
                                <tr>
                                    <td>Banyak sesi di minggu yang sama</td>
                                    <td>Sebarkan ke minggu berbeda agar jumlah minggu terisi naik</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Rutin 1 sesi sharing per minggu lebih baik daripada banyak sesi di minggu yang sama.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Peningkatan Knowledge Sharing" + tahun</li>
                        <li class="mb-2"><strong>Lakukan sharing</strong> — instruktur / EM mengadakan knowledge sharing</li>
                        <li class="mb-2"><strong>Catat aktivitas</strong> — input di Activity Instruktur, tipe = Sharing Knowledge</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = jumlah minggu yang sudah ada sharing</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 3. PENINGKATAN KONTRIBUSI PELATIHAN
            // =====================================================
            'peningkatan_kontribusi_pelatihan' => [
                'judul' => 'Peningkatan Kontribusi Pelatihan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>seberapa besar kontribusi kelas internal</strong>
                        dibanding target jumlah kelas dalam setahun.
                    </p>

                    <p class="mb-3">
                        Yang dihitung: kelas RKM yang diampu instruktur internal
                        (bukan freelance / OL).
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>RKM</strong> (kelas / batch pelatihan)</li>
                        <li>Yang dihitung sebagai kelas internal:
                            <ul>
                                <li><code>instruktur_key</code> terisi dan bukan <code>-</code> / <code>OL</code></li>
                                <li><code>instruktur_key2</code> bukan <code>OL</code> (atau kosong)</li>
                                <li><code>asisten_key</code> bukan <code>OL</code> (atau kosong)</li>
                            </ul>
                        </li>
                        <li>Filter kelas yang periodenya overlap dengan tahun target</li>
                        <li>Target jumlah kelas internal dalam rumus: <strong>357</strong></li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua RKM yang masuk periode tahun target</li>
                        <li>Saring hanya kelas internal (bukan OL / freelance)</li>
                        <li>Progress = (Jumlah kelas internal ÷ 357) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Kelas internal di tahun 2025 = <strong>286</strong><br>
                        Target acuan = 357<br>
                        <strong>Progress = (286 ÷ 357) × 100 ≈ 80,1%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Catatan:</strong> Kelas yang melibatkan instruktur/asisten OL
                        tidak dihitung sebagai kontribusi internal.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data RKM di tahun tersebut</td>
                                    <td>Pastikan kelas sudah tercatat di RKM</td>
                                </tr>
                                <tr>
                                    <td>Banyak kelas pakai instruktur OL</td>
                                    <td>Kelas OL tidak dihitung. Perbanyak kelas dengan instruktur internal</td>
                                </tr>
                                <tr>
                                    <td>instruktur_key kosong / "-"</td>
                                    <td>Isi instruktur internal dengan benar di data RKM</td>
                                </tr>
                                <tr>
                                    <td>Jumlah kelas internal masih jauh dari 357</td>
                                    <td>Tingkatkan jumlah kelas internal sepanjang tahun</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Peningkatan Kontribusi Pelatihan" + tahun</li>
                        <li class="mb-2"><strong>Kelas berjalan</strong> — RKM diisi dengan instruktur internal</li>
                        <li class="mb-2"><strong>Sistem hitung kelas internal</strong> — filter yang bukan OL</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % terhadap target 357 kelas</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 4. EVALUASI KINERJA INSTRUKTUR
            // =====================================================
            'evaluasi_kinerja_instruktur' => [
                'judul' => 'Evaluasi Kinerja Instruktur',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>tingkat keaktifan instruktur di hari kerja</strong>,
                        berdasarkan aktivitas yang tercatat di sistem.
                    </p>

                    <p class="mb-3">
                        Semakin sering instruktur punya aktivitas di hari kerja (bukan weekend / libur nasional),
                        semakin tinggi progress-nya.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Daftar instruktur: karyawan aktif jabatan <strong>Instruktur</strong> atau <strong>Education Manager</strong>
                            (bukan OL, punya NIP, bukan Direksi)</li>
                        <li>Aktivitas: tabel <strong>Activity Instruktur</strong></li>
                        <li>Hari libur: tabel <strong>Hari Libur</strong> (tidak dihitung sebagai hari kerja)</li>
                        <li>Weekend (Sabtu–Minggu) tidak dihitung</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Hitung jumlah hari kerja dalam periode (senin–jumat, exclude libur nasional)</li>
                        <li>Hitung total “slot” = hari kerja × jumlah instruktur</li>
                        <li>Hitung berapa kali instruktur tercatat aktif per hari kerja</li>
                        <li>Progress = (Total keaktifan ÷ Total slot) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (disederhanakan):</strong><br>
                        Ada 2 instruktur, 10 hari kerja:
                        <ul class="mb-2 mt-2">
                            <li>Total slot = 2 × 10 = 20</li>
                            <li>Yang tercatat aktif = 16</li>
                        </ul>
                        <strong>Progress = (16 ÷ 20) × 100 = 80%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Intinya:</strong> instruktur diharapkan punya aktivitas tercatat di hari kerja.
                        Semakin konsisten, semakin tinggi progress.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data Activity Instruktur</td>
                                    <td>Pastikan aktivitas harian instruktur dicatat di sistem</td>
                                </tr>
                                <tr>
                                    <td>Data karyawan instruktur tidak lengkap</td>
                                    <td>Pastikan jabatan, status aktif, NIP, dan kode karyawan benar</td>
                                </tr>
                                <tr>
                                    <td>Aktivitas hanya di weekend / hari libur</td>
                                    <td>Yang dihitung hanya hari kerja (Senin–Jumat non-libur)</td>
                                </tr>
                                <tr>
                                    <td>Banyak hari kerja tanpa aktivitas</td>
                                    <td>Biasakan input aktivitas setiap hari kerja</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Evaluasi Kinerja Instruktur" + tahun</li>
                        <li class="mb-2"><strong>Instruktur bekerja</strong> — mengajar / aktivitas di hari kerja</li>
                        <li class="mb-2"><strong>Aktivitas dicatat</strong> — input di Activity Instruktur</li>
                        <li class="mb-2"><strong>Sistem hitung keaktifan</strong> — bandingkan dengan total hari kerja × jumlah instruktur</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % keaktifan di hari kerja</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 5. PEMBUATAN ARTIKEL
            // =====================================================
            'pembuatan_artikel' => [
                'judul' => 'Pembuatan Artikel',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa banyak artikel yang sudah dibuat tim Education</strong>
                        dibanding target tahunan.
                    </p>

                    <p class="mb-3">
                        Target acuan jumlah artikel dalam setahun: <strong>24 artikel</strong>.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data artikel diambil dari <strong>API artikel</strong></li>
                        <li>Hanya artikel yang pembuatnya terdaftar sebagai user divisi <strong>Education</strong></li>
                        <li>Filter tanggal artikel sesuai tahun target</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil daftar artikel dari API</li>
                        <li>Saring hanya yang dibuat oleh user divisi Education</li>
                        <li>Saring lagi hanya yang tanggalnya di tahun target</li>
                        <li>Progress = (Jumlah artikel ÷ 24) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Artikel Education di tahun 2025 = <strong>18</strong><br>
                        Target = 24<br>
                        <strong>Progress = (18 ÷ 24) × 100 = 75%</strong>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Catatan:</strong> Artikel dari divisi lain tidak dihitung.
                        Nama pembuat di API harus match dengan nama karyawan Education di sistem.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar</td>
                                </tr>
                                <tr>
                                    <td>API artikel gagal / kosong</td>
                                    <td>Pastikan koneksi API artikel berjalan</td>
                                </tr>
                                <tr>
                                    <td>Pembuat artikel tidak match nama karyawan Education</td>
                                    <td>Nama di artikel harus sesuai nama lengkap karyawan divisi Education</td>
                                </tr>
                                <tr>
                                    <td>Artikel dibuat di tahun berbeda</td>
                                    <td>Cek tanggal artikel sesuai tahun target</td>
                                </tr>
                                <tr>
                                    <td>Jumlah artikel masih di bawah 24</td>
                                    <td>Tingkatkan produksi artikel sepanjang tahun</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — pilih route "Pembuatan Artikel" + tahun</li>
                        <li class="mb-2"><strong>Tim Education menulis artikel</strong> — dipublikasikan lewat sistem artikel</li>
                        <li class="mb-2"><strong>Sistem ambil data API</strong> — filter pembuat divisi Education + tahun</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % terhadap target 24 artikel</li>
                    </ol>
                ',
            ],
            
            // =====================================================
            // 1. OUTSTANDING
            // =====================================================
            'outstanding' => [
                'judul' => 'Outstanding',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen tagihan outstanding yang dibayar tepat waktu</strong>.
                        Artinya: uang masuk tidak melewati tanggal jatuh tempo (due date).
                    </p>

                    <p class="mb-3">
                        Semakin banyak tagihan yang dibayar sebelum atau tepat di due date,
                        semakin tinggi progress-nya.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel <strong>Outstanding</strong></li>
                        <li>Yang dipakai:
                            <ul>
                                <li><code>status_pembayaran</code> (1 = sudah dibayar)</li>
                                <li><code>tanggal_bayar</code></li>
                                <li><code>due_date</code> (tanggal jatuh tempo)</li>
                            </ul>
                        </li>
                        <li>Hanya data di <strong>tahun yang dipilih</strong> saat membuat target</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Mengelompokkan Status</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Artinya</th>
                                    <th>Dihitung di progress?</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tepat</strong></td>
                                    <td>Sudah dibayar dan tanggal bayar ≤ due date</td>
                                    <td>Ya ✅ (nilai bagus)</td>
                                </tr>
                                <tr>
                                    <td><strong>Terlambat</strong></td>
                                    <td>Sudah dibayar tapi lewat due date, atau belum dibayar dan sudah lewat batas</td>
                                    <td>Ya ❌ (menurunkan progress)</td>
                                </tr>
                                <tr>
                                    <td><strong>On track</strong></td>
                                    <td>Belum dibayar, tapi masih dalam batas waktu (belum lewat due date + 6 bulan)</td>
                                    <td>Tidak dihitung</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-semibold">Rumus Progress</h6>
                    <p><code>Progress = (Jumlah tepat ÷ (Tepat + Terlambat)) × 100</code></p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Di tahun 2025 ada data outstanding:
                        <ul class="mb-2 mt-2">
                            <li>40 tagihan dibayar tepat waktu ✅</li>
                            <li>10 tagihan terlambat ❌</li>
                            <li>5 tagihan masih on track (belum jatuh tempo) → diabaikan</li>
                        </ul>
                        Yang dihitung: 40 + 10 = 50<br>
                        <strong>Progress = (40 ÷ 50) × 100 = 80%</strong>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, cek kemungkinan berikut:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah diisi dengan benar saat membuat target</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data outstanding di tahun itu</td>
                                    <td>Pastikan data outstanding sudah masuk sistem</td>
                                </tr>
                                <tr>
                                    <td>Banyak pembayaran lewat due date</td>
                                    <td>Percepat penagihan agar client bayar sebelum jatuh tempo</td>
                                </tr>
                                <tr>
                                    <td>Tanggal bayar atau due date kosong</td>
                                    <td>Lengkapi data tanggal agar sistem bisa menentukan status tepat/terlambat</td>
                                </tr>
                                <tr>
                                    <td>Status pembayaran belum diupdate</td>
                                    <td>Setelah ada pembayaran, update status_pembayaran = 1 dan isi tanggal_bayar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Fokus ke tagihan yang mendekati due date.
                        Yang masih “on track” belum mempengaruhi progress, jadi prioritaskan yang rawan terlambat.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target</strong><br>
                            Pilih Assistant Route <em>"Outstanding"</em>, isi tahun, simpan.
                        </li>
                        <li class="mb-2">
                            <strong>Data outstanding masuk</strong><br>
                            Tagihan / piutang tercatat di sistem beserta due date.
                        </li>
                        <li class="mb-2">
                            <strong>Pembayaran terjadi</strong><br>
                            Saat client bayar, isi tanggal bayar dan update status pembayaran.
                        </li>
                        <li class="mb-2">
                            <strong>Sistem membandingkan</strong><br>
                            Tanggal bayar vs due date → digolongkan tepat / terlambat / on track.
                        </li>
                        <li class="mb-0">
                            <strong>Lihat hasil</strong><br>
                            Progress = persen yang dibayar tepat waktu (dari yang sudah jatuh tempo / sudah dibayar).
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // 2. INISIATIF EFISIENSI KEUANGAN
            // =====================================================
            'inisiatif_efisiensi_keuangan' => [
                'judul' => 'Inisiatif Efisiensi Keuangan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini <strong>tidak dihitung otomatis</strong> dari transaksi.
                        Nilainya diisi manual oleh user sesuai capaian inisiatif efisiensi yang sudah dilakukan.
                    </p>

                    <p class="mb-3">
                        Contoh inisiatif: penghematan biaya, otomasi proses keuangan,
                        negosiasi vendor, pengurangan biaya operasional, dll.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Nilai diambil dari kolom <code>manual_value</code> pada detail target KPI</li>
                        <li>Bisa dilengkapi dokumen pendukung (<code>manual_document</code>) —
                            misalnya laporan penghematan, bukti inisiatif, dll.</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Sistem membaca nilai yang Anda isi di form “Isi Data”</li>
                        <li>Jika nilai > 0 → progress = nilai tersebut</li>
                        <li>Jika belum diisi / 0 → progress = 0</li>
                    </ol>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Penting:</strong> Progress hanya berubah setelah Anda mengisi nilai manual
                        lewat tombol <em>Isi Data</em> di dashboard KPI, lalu menyimpan.
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0, hampir selalu karena nilai manual belum diisi:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Belum mengisi nilai manual</td>
                                    <td>Klik tombol <strong>Isi Data</strong> pada target ini, isi angka capaian, lalu simpan</td>
                                </tr>
                                <tr>
                                    <td>Nilai terisi 0</td>
                                    <td>Isi angka realisasi yang valid (lebih dari 0)</td>
                                </tr>
                                <tr>
                                    <td>Tahun / nilai target tidak valid</td>
                                    <td>Pastikan tahun dan nilai target sudah terisi benar saat membuat target</td>
                                </tr>
                                <tr>
                                    <td>Dokumen pendukung belum diupload (opsional)</td>
                                    <td>Boleh dilampirkan untuk audit, tapi tidak wajib untuk progress muncul</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target</strong><br>
                            Pilih Assistant Route <em>"Inisiatif Efisiensi Keuangan"</em>, isi tahun & nilai target.
                        </li>
                        <li class="mb-2">
                            <strong>Jalankan inisiatif</strong><br>
                            Lakukan project efisiensi / penghematan di area keuangan.
                        </li>
                        <li class="mb-2">
                            <strong>Ukur hasilnya</strong><br>
                            Tentukan angka capaian (misalnya % penghematan, jumlah proses yang diotomasi, dll).
                        </li>
                        <li class="mb-2">
                            <strong>Isi nilai manual</strong><br>
                            Di dashboard KPI, klik Isi Data → input nilai → simpan (bisa + upload dokumen).
                        </li>
                        <li class="mb-0">
                            <strong>Lihat hasil</strong><br>
                            Progress langsung mengikuti nilai yang Anda isi.
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // 3. MENGURANGI MANUAL WORK DAN ERROR
            // =====================================================
            'mengurangi_manual_work_dan_error' => [
                'judul' => 'Mengurangi Manual Work dan Error',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini juga memakai <strong>nilai manual</strong>.
                        Digunakan untuk mencatat capaian pengurangan kerja manual dan/atau pengurangan error
                        di proses keuangan / accounting.
                    </p>

                    <p class="mb-3">
                        Contoh: otomasi rekap, pengurangan input manual, penurunan error posting,
                        standardisasi proses, dll.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Kolom <code>manual_value</code> di detail target</li>
                        <li>Opsional: dokumen pendukung (<code>manual_document</code>)</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <p>Progress = nilai yang diisi di form manual. Jika kosong atau 0 → progress = 0.</p>

                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Sama seperti target inisiatif efisiensi:</strong>
                        progress tidak muncul otomatis. Harus diisi lewat tombol Isi Data.
                    </div>
                ',

                'error_kosong' => '
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Nilai manual belum diisi</td>
                                    <td>Klik <strong>Isi Data</strong>, isi angka capaian, simpan</td>
                                </tr>
                                <tr>
                                    <td>Nilai 0</td>
                                    <td>Isi angka realisasi yang valid</td>
                                </tr>
                                <tr>
                                    <td>Tahun / target tidak valid</td>
                                    <td>Periksa setup target (tahun & nilai target)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — route "Mengurangi Manual Work dan Error" + tahun</li>
                        <li class="mb-2"><strong>Perbaiki proses</strong> — otomasi, kurangi langkah manual, turunkan error</li>
                        <li class="mb-2"><strong>Ukur capaian</strong> — tentukan angka yang mewakili hasil perbaikan</li>
                        <li class="mb-2"><strong>Isi nilai manual</strong> — lewat form Isi Data di dashboard</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = nilai yang diisi</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 4. LAPORAN ANALISIS KEUANGAN
            // =====================================================
            'laporan_analisis_keuangan' => [
                'judul' => 'Laporan Analisis Keuangan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa banyak laporan analisis keuangan</strong>
                        yang sudah dibuat untuk tahun tersebut.
                    </p>

                    <p class="mb-3">
                        Bukan persen, tapi <strong>jumlah laporan</strong>.
                        Semakin banyak laporan yang tercatat di tahun target, semakin tinggi progress.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari tabel / modul <strong>Analysis Report</strong></li>
                        <li>Yang difilter: kolom <code>year</code> = tahun target</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua Analysis Report dengan year = tahun target</li>
                        <li>Hitung jumlahnya</li>
                        <li>Progress = jumlah laporan tersebut</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Di tahun 2025 ada 4 laporan analisis keuangan yang tercatat.<br>
                        <strong>Progress = 4</strong>
                        <br><small class="text-muted">Jika nilai target = 6, berarti masih kurang 2 laporan.</small>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada Analysis Report di tahun itu</td>
                                    <td>Buat laporan analisis di modul terkait dan set year-nya sesuai</td>
                                </tr>
                                <tr>
                                    <td>Field year di laporan tidak sesuai</td>
                                    <td>Pastikan kolom year laporan = tahun target (bukan tahun lain)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Setelah membuat laporan, pastikan field tahun terisi.
                        Tanpa itu, laporan tidak masuk perhitungan KPI.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — route "Laporan Analisis Keuangan" + tahun</li>
                        <li class="mb-2"><strong>Buat laporan analisis</strong> — input di modul Analysis Report</li>
                        <li class="mb-2"><strong>Set tahun laporan</strong> — pastikan year = tahun target</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = jumlah laporan di tahun itu</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 5. PENCAIRAN BIAYA OPERASIONAL
            // =====================================================
            'pencairan_biaya_operasional' => [
                'judul' => 'Pencairan Biaya Operasional',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>seberapa baik proses pencairan pengajuan barang / biaya operasional</strong>.
                        Setiap pengajuan diberi skor 0–1, lalu dirata-rata menjadi persen.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Tabel <strong>Pengajuan Barang</strong> beserta data tracking-nya</li>
                        <li>Filter berdasarkan <code>created_at</code> di tahun target</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Pengajuan yang Diabaikan</h6>
                    <p>Belum dihitung jika masih di tahap approval awal, misalnya:</p>
                    <ul>
                        <li>Sedang ditinjau Education Manager / Koordinator ITSM / SPV Sales / GM</li>
                        <li>Menunggu approve Direksi / membuat permintaan ke Direktur Utama</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Memberi Skor per Pengajuan</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kondisi</th>
                                    <th>Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Status selesai, dan dari terima finance → selesai ≤ 7 hari</td>
                                    <td><strong>1</strong> (bagus)</td>
                                </tr>
                                <tr>
                                    <td>Status selesai, tapi proses > 7 hari</td>
                                    <td><strong>0</strong></td>
                                </tr>
                                <tr>
                                    <td>Belum selesai, umur pengajuan ≤ 2 hari</td>
                                    <td><strong>1</strong></td>
                                </tr>
                                <tr>
                                    <td>Belum selesai, umur 3–21 hari</td>
                                    <td>Menurun bertahap (semakin lama semakin kecil)</td>
                                </tr>
                                <tr>
                                    <td>Belum selesai, umur > 21 hari</td>
                                    <td><strong>0</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p><code>Progress = (Total skor ÷ Jumlah pengajuan yang dihitung) × 100</code></p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        10 pengajuan yang dihitung, total skor = 7,5<br>
                        <strong>Progress = (7,5 ÷ 10) × 100 = 75%</strong>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data pengajuan barang</td>
                                    <td>Pastikan pengajuan sudah masuk sistem</td>
                                </tr>
                                <tr>
                                    <td>Banyak pengajuan masih di approval awal</td>
                                    <td>Status itu diabaikan. Dorong agar masuk ke tahap finance</td>
                                </tr>
                                <tr>
                                    <td>Pencairan sering lebih dari 7 hari</td>
                                    <td>Percepat proses setelah diterima finance</td>
                                </tr>
                                <tr>
                                    <td>Pengajuan menumpuk lama tanpa selesai</td>
                                    <td>Selesaikan atau update status tracking agar tidak skor 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Dua hal yang paling mempengaruhi progress:
                        (1) cepat selesaikan setelah diterima finance, (2) jangan biarkan pengajuan menggantung > 21 hari.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target</strong><br>
                            Pilih route <em>"Pencairan Biaya Operasional"</em>, isi tahun.
                        </li>
                        <li class="mb-2">
                            <strong>Pengajuan dibuat</strong><br>
                            User mengajukan barang / biaya lewat modul Pengajuan Barang.
                        </li>
                        <li class="mb-2">
                            <strong>Proses approval</strong><br>
                            Lewat atasan terkait, lalu masuk ke finance untuk pencairan.
                        </li>
                        <li class="mb-2">
                            <strong>Pencairan & update tracking</strong><br>
                            Setelah cair / selesai, update status (Selesai / Pencairan Sudah Selesai).
                        </li>
                        <li class="mb-0">
                            <strong>Sistem beri skor & hitung progress</strong><br>
                            Setiap pengajuan dinilai, lalu dirata-rata menjadi persen.
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // 6. PENYELESAIAN TAGIHAN PERUSAHAAN
            // =====================================================
            'penyelesaian_tagihan_perusahaan' => [
                'judul' => 'Penyelesaian Tagihan Perusahaan',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen tagihan perusahaan yang sudah diselesaikan</strong>.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data diambil dari <strong>Tracking Tagihan Perusahaan</strong></li>
                        <li>Filter: <code>tanggal_perkiraan_mulai</code> berada di tahun target</li>
                        <li>Untuk setiap tagihan, diambil <strong>tracking terbaru</strong>
                            (berdasarkan id terbesar per <code>id_tagihan_perusahaan</code>)</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil semua tracking di tahun target</li>
                        <li>Ambil status terbaru tiap tagihan</li>
                        <li>Yang dihitung selesai: status (dan tracking) = <strong>selesai</strong></li>
                        <li>Progress = (Jumlah selesai ÷ Total tagihan) × 100</li>
                    </ol>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Total tagihan di 2025 = 20<br>
                        Yang statusnya selesai = 16<br>
                        <strong>Progress = (16 ÷ 20) × 100 = 80%</strong>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data tagihan di tahun itu</td>
                                    <td>Input tracking tagihan perusahaan</td>
                                </tr>
                                <tr>
                                    <td>Status belum diubah menjadi "selesai"</td>
                                    <td>Setelah tagihan tuntas, update status & tracking menjadi selesai</td>
                                </tr>
                                <tr>
                                    <td>Tanggal perkiraan mulai di luar tahun target</td>
                                    <td>Pastikan tanggal masuk ke tahun yang sedang diukur</td>
                                </tr>
                                <tr>
                                    <td>Ada banyak versi tracking, yang terbaru belum selesai</td>
                                    <td>Sistem pakai tracking terbaru. Pastikan update terakhir statusnya selesai</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong> — route "Penyelesaian Tagihan Perusahaan" + tahun</li>
                        <li class="mb-2"><strong>Tagihan dicatat</strong> — tracking tagihan masuk sistem</li>
                        <li class="mb-2"><strong>Proses penagihan / penyelesaian</strong> — dikerjakan sampai tuntas</li>
                        <li class="mb-2"><strong>Update status</strong> — ubah status & tracking menjadi selesai</li>
                        <li class="mb-0"><strong>Lihat hasil</strong> — progress = % tagihan yang selesai</li>
                    </ol>
                ',
            ],

            // =====================================================
            // 7. AKURASI PENCATATAN MASUK
            // =====================================================
            'akurasi_pencatatan_masuk' => [
                'judul' => 'Akurasi Pencatatan Masuk',

                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen pencatatan pendapatan yang akurat</strong>
                        di data Approval Pendapatan.
                    </p>

                    <p class="mb-3">
                        Tujuannya memastikan angka pembayaran, pajak, dan total pemasukan kotor
                        tercatat dengan benar dan konsisten.
                    </p>

                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Tabel <strong>Approval Pendapatan</strong></li>
                        <li>Filter <code>tanggal_mulai</code> di tahun target
                            <ul>
                                <li>Jika tahun berjalan → sampai akhir bulan ini</li>
                                <li>Jika tahun lalu → sampai 31 Desember</li>
                            </ul>
                        </li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Cara Menilai “Akurat”</h6>
                    <ul>
                        <li>
                            <strong>Jika jumlah pembayaran = 0</strong><br>
                            Dicek apakah (pembayaran + PPN + PPH) mendekati total pemasukan kotor,
                            atau pembayaran ≈ kotor (selisih sangat kecil).
                        </li>
                        <li>
                            <strong>Jika jumlah pembayaran ≠ 0</strong><br>
                            Dianggap akurat jika field total pemasukan kotor terisi.
                        </li>
                    </ul>

                    <p class="mt-2"><code>Progress = (Jumlah data akurat ÷ Total data) × 100</code></p>

                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong><br>
                        Total data Approval Pendapatan = 50<br>
                        Yang akurat = 45<br>
                        <strong>Progress = (45 ÷ 50) × 100 = 90%</strong>
                    </div>
                ',

                'error_kosong' => '
                    <p class="mb-3">Jika progress 0 atau rendah, biasanya karena:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi / salah</td>
                                    <td>Pastikan tahun sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data Approval Pendapatan</td>
                                    <td>Pastikan data pendapatan sudah diinput</td>
                                </tr>
                                <tr>
                                    <td>Total pemasukan kotor kosong</td>
                                    <td>Lengkapi field kotor, PPN, PPH</td>
                                </tr>
                                <tr>
                                    <td>Angka pembayaran + pajak tidak match dengan kotor</td>
                                    <td>Perbaiki agar selisihnya sangat kecil / konsisten</td>
                                </tr>
                                <tr>
                                    <td>Tanggal mulai di luar periode yang dihitung</td>
                                    <td>Pastikan tanggal_mulai masuk tahun target</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Biasakan isi lengkap pembayaran, PPN, PPH, dan total kotor
                        di setiap Approval Pendapatan. Data tidak lengkap = rawan dianggap tidak akurat.
                    </div>
                ',

                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Buat Target</strong><br>
                            Pilih route <em>"Akurasi Pencatatan Masuk"</em>, isi tahun.
                        </li>
                        <li class="mb-2">
                            <strong>Input pendapatan</strong><br>
                            Data masuk lewat modul Approval Pendapatan.
                        </li>
                        <li class="mb-2">
                            <strong>Lengkapi angka</strong><br>
                            Isi jumlah pembayaran, PPN, PPH, dan total pemasukan kotor.
                        </li>
                        <li class="mb-2">
                            <strong>Sistem cek konsistensi</strong><br>
                            Membandingkan angka-angka tersebut untuk menentukan akurat / tidak.
                        </li>
                        <li class="mb-0">
                            <strong>Lihat hasil</strong><br>
                            Progress = persen data yang pencatatannya akurat.
                        </li>
                    </ol>
                ',
            ],

            // =====================================================
            // GM 1. PEMASUKAN KOTOR
            // =====================================================
            'pemasukan_kotor' => [
                'judul' => 'Pemasukan Kotor',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>total uang penjualan (pemasukan kotor) perusahaan dalam satu tahun</strong>,
                        lalu membandingkannya dengan target rupiah yang ditetapkan.
                        Kata "kotor" artinya angka penjualan <strong>sebelum dikurangi biaya apa pun</strong>.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>Approval Pendapatan</strong> → data pendapatan setiap kelas / layanan yang sudah dicatat. Yang dibaca ada 3 isian:
                            <ul>
                                <li><code>tanggal_mulai</code> → tanggal mulai kelas / layanan (dipakai untuk menentukan masuk tahun yang mana)</li>
                                <li><code>harga_net</code> → harga bersih per peserta</li>
                                <li><code>pax</code> → jumlah peserta</li>
                            </ul>
                        </li>
                        <li><strong>RKM</strong> → dipakai untuk mengetahui sales mana yang memegang penjualan tersebut (hanya untuk Tabel Performa Sales di halaman detail)</li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li>Ambil semua data Approval Pendapatan yang <strong>tanggal mulainya berada di tahun target</strong> (contoh: 1 Januari sampai 31 Desember 2025).</li>
                        <li>Untuk setiap data, kalikan: <code>harga_net × pax</code>. Hasilnya adalah pendapatan satu kelas.</li>
                        <li>Jumlahkan hasil semua kelas. Angka inilah yang menjadi <strong>Progress</strong> (dalam rupiah).</li>
                        <li>Hitung selisihnya dengan target: <code>Gap = Progress − Nilai Target</code>.
                            Gap negatif artinya masih kurang dari target, gap positif artinya sudah melewati target.</li>
                    </ol>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Kelas A → harga net Rp 5.000.000 × 10 peserta = <strong>Rp 50.000.000</strong></li>
                            <li>Kelas B → harga net Rp 3.000.000 × 8 peserta = <strong>Rp 24.000.000</strong></li>
                            <li>Kelas C → harga net Rp 1.500.000 × 20 peserta = <strong>Rp 30.000.000</strong></li>
                        </ul>
                        Progress = 50.000.000 + 24.000.000 + 30.000.000 = <strong>Rp 104.000.000</strong><br>
                        Jika nilai target = Rp 500.000.000, maka Gap = 104.000.000 − 500.000.000 = <strong>−Rp 396.000.000</strong>
                        (masih kurang). Dalam persen, pencapaian = 104 ÷ 500 = <strong>20,8%</strong>.
                    </div>
        
                    <h6 class="fw-semibold mt-4">Aturan Penting</h6>
                    <ul>
                        <li>Target ini bersifat <strong>data seluruh perusahaan</strong>, bukan data per orang. Siapa pun yang ditugaskan di target ini melihat angka yang sama.</li>
                        <li>Yang menentukan tahun adalah <strong>tanggal mulai kelas</strong>, bukan tanggal data diinput.</li>
                        <li>Angka dibaca sebagai <strong>bilangan bulat</strong>. Bagian desimal / koma pada harga net dan jumlah peserta dibuang.</li>
                        <li>Jika <strong>nilai target kosong atau 0</strong>, atau tahun target tidak valid, progress otomatis 0.</li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Kotak paling atas:</strong> Target, Progress (total rupiah), dan Gap (selisih). Lencana status berbunyi
                            <em>Belum Dimulai</em>, <em>Sedang Berjalan</em>, <em>Mencapai Target</em>, atau <em>Target Gagal</em>
                            (gagal jika tenggat sudah lewat tetapi target belum tercapai).</li>
                        <li><strong>Ringkasan performa:</strong> <em>Bulan Terakhir</em> = bulan terakhir yang punya data beserta totalnya;
                            <em>Top Hari Tertinggi</em> = 3 tanggal dengan pemasukan terbesar.</li>
                        <li><strong>Kartu Triwulan:</strong> pemasukan per 3 bulan. Triwulan 1 = Januari–Maret, Triwulan 2 = April–Juni,
                            Triwulan 3 = Juli–September, Triwulan 4 = Oktober–Desember.</li>
                        <li><strong>Grafik Statistik:</strong> pilih <em>Per Tahun</em> untuk melihat total tiap bulan, atau <em>Per Bulan</em>
                            lalu pilih bulan untuk melihat total tiap tanggal.</li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Tabel Performa Sales</h6>
                    <p>
                        Di bawah kotak atas ada tabel yang menunjukkan capaian <strong>tiap sales</strong>. Yang tampil adalah karyawan
                        aktif dengan jabatan <strong>Sales</strong>.
                    </p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:22%">Kolom</th>
                                    <th>Artinya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Revenue</strong></td>
                                    <td>Total penjualan atas nama sales tersebut di tahun target. Diambil dari kolom total penjualan sales pada data Approval Pendapatan, dikelompokkan berdasarkan kode sales di RKM.</td>
                                </tr>
                                <tr>
                                    <td><strong>Target (Editable)</strong></td>
                                    <td>Target pribadi sales. Bisa diketik langsung. Setelah Anda berhenti mengetik sekitar 1 detik, angka tersimpan otomatis dan muncul tanda centang hijau.</td>
                                </tr>
                                <tr>
                                    <td><strong>Persentase</strong></td>
                                    <td><code>Revenue ÷ Target × 100</code>. Jika target 0, persentase tampil 0%.</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td><span class="badge bg-success">ACHIEVED</span> jika revenue ≥ target, selain itu <span class="badge bg-warning text-dark">PENDING</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Perlu diketahui:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Target pribadi sales diambil dari target KPI <strong>"Target Penjualan Tahunan"</strong> milik Sales. Jika target itu belum dibuat, sistem memakai target ini sebagai gantinya.</li>
                            <li>Jumlah revenue seluruh sales <strong>belum tentu sama persis</strong> dengan Progress di kotak atas, karena Progress dihitung dari harga net × peserta, sedangkan revenue sales dari kolom total penjualan sales.</li>
                            <li>Sales yang <strong>belum didaftarkan</strong> pada target Penjualan Tahunan tampil dengan kolom target berwarna merah dan tidak bisa diedit.</li>
                        </ul>
                    </div>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau tidak sesuai harapan, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Saat membuat target, isi tahun dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Nilai target kosong atau 0</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0. Jika tidak, progress langsung 0 dan halaman detail kosong</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data di Approval Pendapatan pada tahun tersebut</td>
                                    <td>Pastikan data pendapatan kelas sudah diinput</td>
                                </tr>
                                <tr>
                                    <td>Tanggal mulai kelas berada di tahun lain</td>
                                    <td>Tahun dilihat dari tanggal mulai kelas. Kelas yang mulai Desember tahun lalu dihitung di tahun lalu</td>
                                </tr>
                                <tr>
                                    <td>Harga net atau jumlah peserta (pax) kosong</td>
                                    <td>Data tersebut dihitung Rp 0. Lengkapi kedua isian</td>
                                </tr>
                                <tr>
                                    <td>Kolom Target sales berwarna merah / tidak bisa diedit</td>
                                    <td>Sales belum terdaftar pada target KPI Target Penjualan Tahunan. Tambahkan sales ke target tersebut</td>
                                </tr>
                                <tr>
                                    <td>Persentase sales 0% tetapi status ACHIEVED</td>
                                    <td>Target pribadi sales belum diisi (0). Karena revenue ≥ 0, statusnya otomatis ACHIEVED. Isi target pribadi</td>
                                </tr>
                                <tr>
                                    <td>Tabel Performa Sales kosong</td>
                                    <td>Tidak ada karyawan aktif berjabatan Sales dengan kode karyawan terisi. Periksa data karyawan</td>
                                </tr>
                                <tr>
                                    <td>Revenue sales tidak sama dengan Progress</td>
                                    <td>Wajar. Keduanya dihitung dari kolom yang berbeda. Gunakan Progress sebagai angka utama</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Pastikan setiap kelas yang sudah berjalan punya data Approval Pendapatan
                        dengan harga net dan jumlah peserta yang lengkap. Itulah satu-satunya sumber angka target ini.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Pemasukan Kotor"</em>, isi tahun dan nilai target rupiah.
                        </li>
                        <li class="mb-2"><strong>Kelas Dijadwalkan</strong><br>
                            Setiap kelas dibuat di RKM lengkap dengan sales yang memegangnya.
                        </li>
                        <li class="mb-2"><strong>Pendapatan Dicatat</strong><br>
                            Data Approval Pendapatan diisi: tanggal mulai, harga net, dan jumlah peserta.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem mengalikan harga net × peserta tiap kelas, lalu menjumlahkan semuanya untuk tahun target.
                        </li>
                        <li class="mb-2"><strong>Atur Target Sales (opsional)</strong><br>
                            Di halaman detail, isi target pribadi tiap sales pada kolom Target (Editable).
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress, Gap, ringkasan, kartu triwulan, dan grafik muncul di dashboard dan halaman detail.
                        </li>
                    </ol>
                ',
            ],
        
            // =====================================================
            // GM 2. PEMASUKAN BERSIH
            // =====================================================
            'pemasukan_bersih' => [
                'judul' => 'Pemasukan Bersih',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen pemasukan bersih dibanding pemasukan kotor</strong> dalam satu tahun.
                        Singkatnya: dari setiap Rp 100 uang penjualan, berapa rupiah yang benar-benar tersisa sebagai pemasukan bersih.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>Pemasukan Kotor</strong> → dihitung persis seperti target Pemasukan Kotor, yaitu jumlah <code>harga_net × pax</code> dari data <strong>Approval Pendapatan</strong> di tahun target</li>
                        <li><strong>Analysis Report</strong> (laporan analisis keuangan bulanan) → kolom <code>nilai</code> adalah angka pemasukan bersih yang diisi tiap bulan, dan kolom <code>year</code> menentukan tahunnya</li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li>Hitung <strong>pemasukan kotor</strong> tahun target.</li>
                        <li>Jumlahkan semua <code>nilai</code> di Analysis Report pada tahun target. Hasilnya adalah <strong>pemasukan bersih</strong>.</li>
                        <li>Hitung persentasenya:<br>
                            <code>Progress = (Pemasukan Bersih ÷ Pemasukan Kotor) × 100</code>, dibulatkan 2 angka di belakang koma.</li>
                        <li>Gap = <code>Progress − Nilai Target</code>. Jika progress sudah mencapai atau melewati target, Gap ditampilkan 0.</li>
                    </ol>
        
                    <div class="alert alert-warning">
                        <strong>Progress otomatis 0 jika:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pemasukan kotor tahun itu 0 (belum ada data Approval Pendapatan)</li>
                            <li>Belum ada laporan di Analysis Report pada tahun itu</li>
                            <li>Pemasukan bersih <strong>lebih besar</strong> dari pemasukan kotor. Secara logika ini tidak mungkin, jadi sistem menganggapnya data salah dan mengembalikan 0</li>
                        </ul>
                    </div>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong><br>
                        Pemasukan kotor setahun = <strong>Rp 5.000.000.000</strong><br>
                        Isi Analysis Report: Januari Rp 400.000.000, Februari Rp 350.000.000, dan seterusnya sampai total setahun <strong>Rp 1.000.000.000</strong><br><br>
                        Progress = (1.000.000.000 ÷ 5.000.000.000) × 100 = <strong>20%</strong><br>
                        Jika nilai target = 25, Gap = 20 − 25 = <strong>−5</strong> (masih kurang 5 poin persentase).
                    </div>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Kotak paling atas:</strong> Target, Progress (persen), dan Gap.</li>
                        <li><strong>Kartu laporan bulanan:</strong> satu kartu untuk tiap bulan yang punya laporan. Berisi nama bulan,
                            nilai pemasukan (Rp), keterangan laporan, dan tombol <em>Download</em> untuk mengunduh dokumen laporan.
                            Keterangan yang panjang bisa dibuka dengan tombol <em>Lihat Selengkapnya</em>.</li>
                        <li><strong>Grafik Statistik:</strong> nilai pemasukan bersih tiap bulan (Rp). Bulan yang belum punya laporan tidak muncul.</li>
                        <li><strong>Grafik lingkaran:</strong> hanya tampilan pendukung dan <strong>tidak dipakai untuk menghitung progress</strong>.
                            Patokan utama adalah angka Progress dan Gap.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Catatan: pada bulan Januari sampai Maret, kartu laporan bulanan menampilkan laporan <strong>satu tahun sebelum tahun target</strong>.
                        Mulai April, kartu menampilkan laporan tahun target. Ini hanya mempengaruhi kartu, bukan angka Progress.
                        Target ini juga data perusahaan, jadi semua orang yang ditugaskan melihat angka yang sama.
                    </p>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong> atau halaman detail kosong, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Isi tahun dengan benar saat membuat target</td>
                                </tr>
                                <tr>
                                    <td>Pemasukan kotor tahun itu masih 0</td>
                                    <td>Target ini bergantung pada target Pemasukan Kotor. Pastikan data Approval Pendapatan sudah terisi</td>
                                </tr>
                                <tr>
                                    <td>Belum ada laporan di Analysis Report tahun itu</td>
                                    <td>Isi laporan analisis keuangan bulanan di menu Analysis, dan pastikan tahunnya sesuai</td>
                                </tr>
                                <tr>
                                    <td>Total laporan lebih besar dari pemasukan kotor</td>
                                    <td>Periksa angka. Pemasukan bersih tidak boleh melebihi pemasukan kotor. Bisa jadi ada salah ketik atau data Approval Pendapatan belum lengkap</td>
                                </tr>
                                <tr>
                                    <td>Nilai target kosong atau tidak valid</td>
                                    <td>Pastikan nilai target terisi dan lebih dari 0</td>
                                </tr>
                                <tr>
                                    <td>Kolom nilai di Analysis Report kosong</td>
                                    <td>Laporan tanpa nilai tidak ikut menambah pemasukan bersih. Isi nilai tiap bulan</td>
                                </tr>
                                <tr>
                                    <td>Tombol Download tidak menghasilkan file</td>
                                    <td>Dokumen laporan belum diunggah pada Analysis Report bulan tersebut</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Isi laporan Analysis Report setiap bulan dan lampirkan dokumennya.
                        Semakin lengkap laporan setahun, semakin akurat persentase pemasukan bersih.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Pemasukan Bersih"</em>, isi tahun dan nilai target (persen).
                        </li>
                        <li class="mb-2"><strong>Pastikan Pemasukan Kotor Terisi</strong><br>
                            Data Approval Pendapatan harus ada karena menjadi pembagi.
                        </li>
                        <li class="mb-2"><strong>Isi Laporan Bulanan</strong><br>
                            Tim keuangan mengisi Analysis Report tiap bulan: tahun, bulan, nilai, keterangan, dan dokumen.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem membagi total pemasukan bersih dengan pemasukan kotor lalu dikali 100.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress, Gap, kartu laporan bulanan, dan grafik muncul di dashboard dan halaman detail.
                        </li>
                    </ol>
                ',
            ],
        
            // =====================================================
            // GM 3. TARGET PENJUALAN PROJECT TAHUNAN
            // =====================================================
            'target_penjualan_project_tahunan' => [
                'judul' => 'Target Penjualan Project Tahunan',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>total nilai project yang berhasil dimenangkan (closing)</strong> dalam satu tahun,
                        lalu membandingkannya dengan target rupiah.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Lead Project</strong> (menu Leads Project). Yang dibaca:
                            <ul>
                                <li><code>status</code> → hanya yang berstatus <strong>won</strong> (menang / closing) yang dihitung</li>
                                <li><code>tahun_periode</code> → tahun project, harus sama dengan tahun target</li>
                                <li><code>estimasi_nilai</code> → nilai project dalam rupiah</li>
                                <li><code>sales_id</code> → kode karyawan sales pemegang project</li>
                            </ul>
                        </li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li>Ambil semua Lead Project dengan status <strong>won</strong> dan tahun periode sama dengan tahun target.</li>
                        <li>Jika Anda membuka target ini <strong>untuk satu karyawan tertentu</strong>, hanya project milik sales tersebut yang diambil.
                            Jika dibuka dari daftar umum, semua project perusahaan dihitung.</li>
                        <li>Jumlahkan <code>estimasi_nilai</code> semua project tersebut. Hasilnya (dibulatkan) adalah <strong>Progress</strong> dalam rupiah.</li>
                        <li>Gap = <code>Progress − Nilai Target</code>.</li>
                    </ol>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong>
                        <ul class="mb-2 mt-2">
                            <li>Project A, status won, Rp 150.000.000 ✅ dihitung</li>
                            <li>Project B, status won, Rp 300.000.000 ✅ dihitung</li>
                            <li>Project C, status won, Rp 50.000.000 ✅ dihitung</li>
                            <li>Project D, status masih proses, Rp 400.000.000 ❌ tidak dihitung</li>
                            <li>Project E, status kalah, Rp 100.000.000 ❌ tidak dihitung</li>
                        </ul>
                        Progress = 150.000.000 + 300.000.000 + 50.000.000 = <strong>Rp 500.000.000</strong><br>
                        Jika nilai target = Rp 2.000.000.000, pencapaian = 25% dan Gap = <strong>−Rp 1.500.000.000</strong>
                    </div>
        
                    <div class="alert alert-warning">
                        <strong>Perlu diketahui:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Hanya status <strong>won</strong> yang dihitung. Project yang masih berjalan, ditunda, atau kalah tidak menambah progress.</li>
                            <li>Yang dipakai adalah <strong>estimasi nilai</strong>, jadi angka ini mengikuti nilai yang tercatat di Lead Project, bukan nilai invoice.</li>
                            <li>Data project hanya mencatat <strong>tahun periode</strong>, bukan tanggal. Karena itu grafik bulanan, harian, dan ringkasan "Bulan Terakhir / Top Hari" di halaman detail
                                <strong>hanya pelengkap tampilan</strong> dan tidak bisa dipakai membaca kapan project closing.
                                Gunakan angka <strong>Target, Progress, dan Gap</strong> sebagai patokan.</li>
                        </ul>
                    </div>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Kotak paling atas:</strong> Target, Progress (rupiah), Gap, dan lencana status
                            (<em>Belum Dimulai</em>, <em>Sedang Berjalan</em>, <em>Mencapai Target</em>, <em>Target Gagal</em>).</li>
                        <li><strong>Ringkasan performa:</strong> bulan terakhir, 3 hari tertinggi, dan karyawan terkait (lihat catatan di atas).</li>
                        <li><strong>Grafik Statistik:</strong> nilai project won per bulan atau per tanggal (lihat catatan di atas).</li>
                    </ul>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong> atau kosong, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Isi tahun dengan benar saat membuat target</td>
                                </tr>
                                <tr>
                                    <td>Belum ada project berstatus won di tahun itu</td>
                                    <td>Ubah status project yang sudah closing menjadi won di menu Leads Project</td>
                                </tr>
                                <tr>
                                    <td>Tahun periode project berbeda dengan tahun target</td>
                                    <td>Periksa kolom tahun periode pada Lead Project</td>
                                </tr>
                                <tr>
                                    <td>Estimasi nilai project kosong atau 0</td>
                                    <td>Isi estimasi nilai project</td>
                                </tr>
                                <tr>
                                    <td>Target dibuka untuk satu karyawan tetapi hasilnya 0</td>
                                    <td>Hanya project dengan kode sales karyawan tersebut yang dihitung. Pastikan kode karyawan di sales id project sudah benar</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Segera ubah status project menjadi won begitu closing,
                        agar nilainya langsung masuk ke progress.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Target Penjualan Project Tahunan"</em>, isi tahun dan nilai target rupiah.
                        </li>
                        <li class="mb-2"><strong>Catat Lead Project</strong><br>
                            Sales memasukkan calon project beserta estimasi nilai dan tahun periodenya.
                        </li>
                        <li class="mb-2"><strong>Project Closing</strong><br>
                            Setelah project dimenangkan, status diubah menjadi won.
                        </li>
                        <li class="mb-2"><strong>Sistem Menjumlahkan</strong><br>
                            Sistem menjumlahkan estimasi nilai semua project won di tahun target.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress dan Gap muncul di dashboard dan halaman detail.
                        </li>
                    </ol>
                ',
            ],
        
            // =====================================================
            // GM 4. KEPUASAN PELANGGAN
            // =====================================================
            'kepuasan_pelanggan' => [
                'judul' => 'Kepuasan Pelanggan',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>berapa persen kelas / sesi pelatihan yang mendapat penilaian peserta minimal 3,5</strong>
                        (dari skala 1 sampai 4).
                    </p>
                    <p class="mb-3">
                        <strong>Penting:</strong> yang dihitung adalah <strong>kelas</strong>, bukan jumlah peserta.
                        Semua feedback dari satu kelas digabung dulu menjadi satu nilai, baru kelas itu dinilai puas atau belum.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Tabel <strong>Nilai Feedback</strong> → formulir feedback yang diisi peserta setelah kelas</li>
                        <li><strong>RKM</strong> dan <strong>Materi</strong> → untuk mengetahui feedback itu milik kelas yang mana (nama materi + tanggal awal kelas)</li>
                        <li>Tahun dilihat dari <strong>tanggal feedback dibuat</strong></li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Isi Formulir Feedback (Skala 1 sampai 4)</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:22%">Kategori</th>
                                    <th style="width:30%">Pertanyaan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Materi</strong></td><td>M1 sampai M4 (4 pertanyaan)</td><td>Selalu dihitung</td></tr>
                                <tr><td><strong>Pelayanan</strong></td><td>P1 sampai P7 (7 pertanyaan)</td><td>Selalu dihitung</td></tr>
                                <tr><td><strong>Fasilitas</strong></td><td>F1 sampai F5 (5 pertanyaan)</td><td>Selalu dihitung</td></tr>
                                <tr><td><strong>Instruktur</strong></td><td>I1 sampai I8 (8 pertanyaan)</td><td>Selalu dihitung</td></tr>
                                <tr><td><strong>Instruktur 2</strong></td><td>I1b sampai I8b (8 pertanyaan)</td><td>Hanya dihitung jika kelas punya instruktur kedua (nilainya lebih dari 0)</td></tr>
                                <tr><td><strong>Asisten Instruktur</strong></td><td>I1as sampai I8as (8 pertanyaan)</td><td>Hanya dihitung jika kelas punya asisten (nilainya lebih dari 0)</td></tr>
                            </tbody>
                        </table>
                    </div>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li><strong>Ambil semua feedback</strong> di tahun target.</li>
                        <li><strong>Kelompokkan per kelas.</strong> Satu kelompok = satu kelas, ditandai oleh <em>nama materi + tanggal awal kelas</em>.
                            Semua peserta kelas yang sama masuk satu kelompok.</li>
                        <li><strong>Hitung rata-rata tiap kategori</strong> untuk kelompok itu:<br>
                            <code>Rata-rata = Jumlah semua nilai ÷ (Jumlah peserta × Jumlah pertanyaan)</code>, dibulatkan 1 angka di belakang koma.<br>
                            <small class="text-muted">Pertanyaan yang tidak diisi dihitung 0, tetapi tetap ikut sebagai pembagi.</small></li>
                        <li><strong>Hitung nilai kelas:</strong> rata-rata dari kategori Materi, Pelayanan, Fasilitas, dan Instruktur.
                            Jika ada Instruktur 2 atau Asisten, kategori itu ikut dirata-ratakan.</li>
                        <li><strong>Tentukan puas atau belum:</strong> nilai kelas <strong>≥ 3,5</strong> = puas ✅. Di bawah 3,5 = belum puas ❌ (nilai tepat 3,5 dihitung puas).</li>
                        <li><strong>Progress</strong> = <code>(Jumlah kelas puas ÷ Total kelas) × 100</code>, dibulatkan 1 angka di belakang koma.</li>
                        <li><strong>Gap</strong> = <code>Progress − Nilai Target</code>.</li>
                    </ol>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025, ada 4 kelas):</strong><br><br>
                        <strong>Kelas A</strong> (10 peserta): Materi 3,8 | Pelayanan 3,6 | Fasilitas 3,4 | Instruktur 3,9<br>
                        Nilai kelas = (3,8 + 3,6 + 3,4 + 3,9) ÷ 4 = 3,675 → <strong>3,7</strong> ✅ puas<br><br>
                        <strong>Kelas B</strong> (ada instruktur kedua): Materi 3,5 | Pelayanan 3,4 | Fasilitas 3,3 | Instruktur 3,6 | Instruktur 2 3,2<br>
                        Nilai kelas = (3,5 + 3,4 + 3,3 + 3,6 + 3,2) ÷ 5 = <strong>3,4</strong> ❌ belum puas<br><br>
                        <strong>Kelas C</strong> → nilai <strong>3,6</strong> ✅ puas<br>
                        <strong>Kelas D</strong> → nilai <strong>3,5</strong> ✅ puas (tepat di batas)<br><br>
                        Kelas puas = 3 dari 4 → <strong>Progress = (3 ÷ 4) × 100 = 75%</strong><br>
                        Jika nilai target = 80, Gap = 75 − 80 = <strong>−5</strong>
                    </div>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%">Bagian</th>
                                    <th>Artinya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Total Feedback</strong></td>
                                    <td>Jumlah formulir feedback yang masuk di tahun itu</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Sesi</strong></td>
                                    <td>Jumlah kelas (kelompok) yang punya feedback</td>
                                </tr>
                                <tr>
                                    <td><strong>Prediksi</strong></td>
                                    <td>Rata-rata nilai kelas dari 3 bulan terakhir yang punya data (skala 1 sampai 4). Hanya gambaran arah, bukan angka resmi</td>
                                </tr>
                                <tr>
                                    <td><strong>Top Performer</strong></td>
                                    <td>Kelas dengan nilai tertinggi (tertulis nama materi / tanggal awal kelas) beserta nilainya</td>
                                </tr>
                                <tr>
                                    <td><strong>Lowest Performer</strong></td>
                                    <td>Kelas dengan nilai terendah beserta nilainya</td>
                                </tr>
                                <tr>
                                    <td><strong>Trend</strong></td>
                                    <td>Selisih rata-rata bulan terakhir dibanding bulan sebelumnya yang punya data. <em>up</em> = naik, <em>down</em> = turun, <em>stable</em> = tetap atau data kurang dari 2 bulan</td>
                                </tr>
                                <tr>
                                    <td><strong>Konsistensi</strong></td>
                                    <td><em>stable</em> jika nilai antar kelas tidak terlalu berbeda (simpangan kurang dari 0,3). <em>fluctuating</em> jika naik turunnya besar</td>
                                </tr>
                                <tr>
                                    <td><strong>Target</strong></td>
                                    <td><em>on_track</em> = progress sudah mencapai target. <em>at_risk</em> = kurang dari target tetapi selisihnya tidak lebih dari 5 poin. <em>behind</em> = tertinggal lebih dari 5 poin</td>
                                </tr>
                                <tr>
                                    <td><strong>Kategori Penilaian</strong></td>
                                    <td>Rata-rata nilai tiap kategori (Materi, Pelayanan, Fasilitas, Instruktur, Instruktur 2, Asisten Instruktur) dari seluruh kelas. Kategori tanpa data tampil 0</td>
                                </tr>
                                <tr>
                                    <td><strong>Insight</strong></td>
                                    <td>Kalimat ringkasan otomatis tentang tren dan konsistensi</td>
                                </tr>
                                <tr>
                                    <td><strong>Grafik lingkaran</strong></td>
                                    <td>Jumlah kelas puas (Above, nilai ≥ 3,5) dibanding yang belum puas (Below)</td>
                                </tr>
                                <tr>
                                    <td><strong>Grafik Statistik</strong></td>
                                    <td>Rata-rata nilai kelas tiap bulan (skala 1 sampai 4). Pilih <em>Per Bulan</em> untuk melihat per tanggal</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-warning">
                        <strong>Perlu diketahui:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Target ini berbeda dengan <em>Peserta Puas dengan Pelayanan dan Fasilitas Training</em>. Di sini yang dinilai adalah <strong>kelas</strong> (semua kategori digabung), di sana yang dinilai adalah <strong>peserta</strong> (hanya pelayanan dan fasilitas).</li>
                            <li>Dua kelas dengan <strong>materi dan tanggal awal yang sama</strong> digabung menjadi satu kelas.</li>
                            <li>Feedback yang <strong>tidak terhubung ke kelas (RKM)</strong> digabung menjadi satu kelompok tersendiri sehingga bisa menggeser hasil.</li>
                            <li>Rentang tahun dibaca dari 1 Januari sampai 31 Desember. Feedback yang masuk pada <strong>tanggal 31 Desember</strong> (setelah pukul 00.00) tidak ikut terhitung.</li>
                            <li>Pada grafik Statistik, tiap kelas ditempatkan pada tanggal feedback pertamanya. Jika dua kelas jatuh di tanggal yang sama, grafik harian hanya menampilkan satu. Ini tidak mengubah Progress.</li>
                            <li>Data ini milik seluruh perusahaan, jadi semua orang yang ditugaskan melihat angka yang sama.</li>
                        </ul>
                    </div>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Isi tahun dengan benar saat membuat target (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada feedback di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Pastikan peserta sudah mengisi feedback</td>
                                </tr>
                                <tr>
                                    <td>Banyak pertanyaan feedback tidak diisi</td>
                                    <td>Pertanyaan kosong dihitung 0 sehingga rata-rata kelas turun. Ajak peserta mengisi lengkap</td>
                                </tr>
                                <tr>
                                    <td>Nilai kelas di bawah 3,5</td>
                                    <td>Ini bukan error sistem. Nilai 3,4 pun belum dihitung puas. Tingkatkan kualitas kelas</td>
                                </tr>
                                <tr>
                                    <td>Feedback tidak terhubung ke RKM / kelas</td>
                                    <td>Pastikan setiap feedback tersambung ke kelas yang benar</td>
                                </tr>
                                <tr>
                                    <td>Kelas hanya punya 1 atau 2 feedback</td>
                                    <td>Nilai kelas mudah bergeser karena sedikit responden. Dorong lebih banyak peserta mengisi</td>
                                </tr>
                                <tr>
                                    <td>Dua kelas berbeda tampil sebagai satu</td>
                                    <td>Kelas dengan materi dan tanggal awal sama digabung. Ini sudah sesuai aturan sistem</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan feedback tahun itu tersedia</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Lihat <em>Lowest Performer</em> di halaman detail untuk mengetahui kelas mana yang paling menarik turun nilai,
                        dan <em>Kategori Penilaian</em> untuk tahu bagian mana (materi, pelayanan, fasilitas, atau instruktur) yang paling lemah.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Kepuasan Pelanggan"</em>, isi tahun dan nilai target (persen).
                        </li>
                        <li class="mb-2"><strong>Kelas Berjalan</strong><br>
                            Kelas dibuat di RKM dengan materi dan tanggal awal.
                        </li>
                        <li class="mb-2"><strong>Peserta Mengisi Feedback</strong><br>
                            Setelah kelas, peserta mengisi penilaian skala 1 sampai 4. Tanggal pengisian tercatat otomatis.
                        </li>
                        <li class="mb-2"><strong>Sistem Menggabung per Kelas</strong><br>
                            Feedback dikelompokkan per kelas, dihitung rata-ratanya per kategori, lalu digabung menjadi satu nilai kelas.
                        </li>
                        <li class="mb-2"><strong>Sistem Menilai</strong><br>
                            Nilai kelas ≥ 3,5 dianggap puas. Progress = persen kelas yang puas.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress, kategori penilaian, tren, konsistensi, dan insight muncul di halaman detail.
                        </li>
                    </ol>
                ',
            ],
        
            // =====================================================
            // GM 5. RASIO BIAYA OPERASIONAL TERHADAP REVENUE
            // =====================================================
            'rasio_biaya_operasional_terhadap_revenue' => [
                'judul' => 'Rasio Biaya Operasional terhadap Revenue',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini mengukur <strong>seberapa hemat perusahaan mengeluarkan biaya operasional dibanding pemasukannya</strong>.
                        Semakin kecil biaya dibanding pemasukan, semakin tinggi progress-nya.
                    </p>
                    <p class="mb-3">
                        <strong>Nilai target di sini adalah batas rasio yang diinginkan.</strong>
                        Contoh: nilai target 60 artinya perusahaan ingin biaya operasional tidak lebih dari 60% dari pemasukan kotor.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li><strong>Pemasukan Kotor</strong> → dihitung seperti target Pemasukan Kotor (jumlah <code>harga_net × pax</code> dari <strong>Approval Pendapatan</strong> di tahun target)</li>
                        <li><strong>Income Transaction</strong> → catatan transaksi keuangan. Yang dijumlahkan adalah kolom <code>amount</code> untuk tahun target, <strong>hanya dari 23 pos biaya operasional</strong> berikut:
                            <pre class="bg-light p-3 rounded border small mb-0 mt-2">fc_24, fc_25, fc_26, fc_27, fc_28, fc_29, fc_30, fc_31,
fc_32, fc_33, fc_34, fc_35, fc_36, fc_37, fc_38, fc_39,
fc_40, fc_44, fc_49, fc_50, fc_51, fc_84, fc_87</pre>
                            Pos di luar daftar ini tidak dihitung sebagai biaya operasional.
                        </li>
                        <li><strong>Isi Data (manual)</strong> → tombol <em>Aksi → Isi Data</em> pada target ini. Lihat bagian "Wajib Isi Data" di bawah.</li>
                    </ul>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li>Hitung <strong>pemasukan kotor</strong> tahun target.</li>
                        <li>Jumlahkan <strong>biaya operasional</strong> (23 pos di atas) untuk tahun target.</li>
                        <li>Hitung rasio: <code>Rasio = (Biaya Operasional ÷ Pemasukan Kotor) × 100</code></li>
                        <li>Hitung progress: <code>Progress = (Nilai Target ÷ Rasio) × 100</code>, hasilnya dibatasi antara 0 sampai 100 dan dibulatkan 1 angka di belakang koma.</li>
                        <li>Gap = <code>Progress − Nilai Target</code>.</li>
                    </ol>
        
                    <div class="table-responsive mt-3 mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Jika target 60 dan rasio aktual</th>
                                    <th>Progress</th>
                                    <th>Artinya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>40%</td><td>100 (dibatasi, aslinya 150)</td><td>Sangat hemat, melampaui target</td></tr>
                                <tr><td>60%</td><td>100</td><td>Tepat di batas target</td></tr>
                                <tr><td>75%</td><td>80</td><td>Biaya lebih besar dari batas</td></tr>
                                <tr><td>90%</td><td>66,7</td><td>Boros</td></tr>
                                <tr><td>120%</td><td>50</td><td>Biaya melebihi pemasukan</td></tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata (tahun 2025):</strong><br>
                        Pemasukan kotor = <strong>Rp 10.000.000.000</strong><br>
                        Biaya operasional (23 pos) = <strong>Rp 6.500.000.000</strong><br>
                        Rasio = (6.500.000.000 ÷ 10.000.000.000) × 100 = <strong>65%</strong><br>
                        Nilai target = 60<br>
                        Progress = (60 ÷ 65) × 100 = <strong>92,3%</strong>
                    </div>
        
                    <div class="alert alert-warning">
                        <strong>Wajib Isi Data:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Progress <strong>baru dihitung jika kolom Isi Data sudah pernah diisi dan disimpan</strong>. Jika masih kosong, progress otomatis 0 walaupun data keuangan sudah lengkap.</li>
                            <li>Cara mengisi: klik <strong>Aksi → Isi Data</strong>, masukkan angka pada <em>Masukan Nilai</em>, (opsional) unggah dokumen pendukung berupa PDF, JPG, atau PNG maksimal 5 MB, lalu klik <strong>Simpan</strong>.</li>
                            <li>Pilihan <em>Format Nilai</em> (Angka, Persen, Rupiah) hanya mengatur tampilan di kolom input. Yang tersimpan hanya angkanya.</li>
                            <li>Angka yang diisi <strong>tidak masuk rumus</strong>. Rumus memakai data keuangan di atas. Isian ini berfungsi sebagai penanda bahwa data sudah dikonfirmasi, dan dokumen pendukungnya ditampilkan di halaman detail.</li>
                        </ul>
                    </div>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Kotak paling atas:</strong> Target, Progress, dan Gap.</li>
                        <li><strong>Dokumen Manual:</strong> dokumen pendukung yang diunggah lewat Isi Data. Gambar dan PDF bisa ditampilkan dan diunduh.</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Karena Progress adalah skor 0 sampai 100 sedangkan Nilai Target adalah batas rasio, <strong>Gap di sini hanya selisih angka</strong>
                        dan bukan jumlah biaya yang harus dikurangi. Patokan utama adalah Progress.
                        Target ini juga data perusahaan, jadi semua orang yang ditugaskan melihat angka yang sama.
                    </p>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau tidak sesuai harapan, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Isi Data belum diisi / disimpan</strong> (penyebab paling umum)</td>
                                    <td>Klik <strong>Aksi → Isi Data</strong>, isi nilai, lalu simpan. Tanpa ini progress 0</td>
                                </tr>
                                <tr>
                                    <td>Pemasukan kotor tahun itu masih 0</td>
                                    <td>Pastikan data Approval Pendapatan sudah terisi. Rasio tidak bisa dihitung tanpa pemasukan</td>
                                </tr>
                                <tr>
                                    <td>Belum ada biaya di Income Transaction</td>
                                    <td>Pastikan transaksi biaya sudah diinput untuk tahun target. Jika total biaya 0, progress juga 0</td>
                                </tr>
                                <tr>
                                    <td>Biaya dicatat pada kode di luar 23 pos yang dihitung</td>
                                    <td>Hanya kode fc_24 sampai fc_40, fc_44, fc_49, fc_50, fc_51, fc_84, dan fc_87 yang dihitung. Periksa kode pos biaya</td>
                                </tr>
                                <tr>
                                    <td>Nilai target kosong atau 0</td>
                                    <td>Pastikan nilai target terisi dan lebih dari 0</td>
                                </tr>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Isi tahun dengan benar saat membuat target</td>
                                </tr>
                                <tr>
                                    <td>Progress selalu 100</td>
                                    <td>Wajar jika rasio aktual sama dengan atau lebih rendah dari target. Progress dibatasi maksimal 100</td>
                                </tr>
                                <tr>
                                    <td>Gagal menyimpan Isi Data</td>
                                    <td>Pastikan dokumen berformat PDF, JPG, atau PNG dan maksimal 5 MB</td>
                                </tr>
                                <tr>
                                    <td>Dokumen tidak tampil di halaman detail</td>
                                    <td>Hanya gambar (JPG, JPEG, PNG) dan PDF yang bisa ditampilkan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Urutan cek tercepat: (1) apakah Isi Data sudah disimpan, (2) apakah pemasukan kotor tahun itu sudah ada,
                        (3) apakah biaya operasional tahun itu sudah diinput.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Rasio Biaya Operasional terhadap Revenue"</em>, isi tahun dan nilai target (batas rasio, dalam persen).
                        </li>
                        <li class="mb-2"><strong>Data Pemasukan Masuk</strong><br>
                            Data Approval Pendapatan diinput sehingga pemasukan kotor bisa dihitung.
                        </li>
                        <li class="mb-2"><strong>Data Biaya Masuk</strong><br>
                            Biaya operasional dicatat di Income Transaction pada pos-pos yang sudah ditentukan.
                        </li>
                        <li class="mb-2"><strong>Isi Data</strong><br>
                            Di dashboard, klik Aksi → Isi Data, masukkan nilai, lampirkan dokumen pendukung, lalu simpan.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>
                            Sistem menghitung rasio biaya terhadap pemasukan, lalu membandingkannya dengan target.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress dan Gap muncul di dashboard. Dokumen pendukung tampil di halaman detail.
                        </li>
                    </ol>
                ',
            ],
        
            // =====================================================
            // GM 6. PERFORMA KPI DEPARTEMEN
            // =====================================================
            'performa_kpi_departemen' => [
                'judul' => 'Performa KPI Departemen',
        
                'dari_mana_data' => '
                    <p class="mb-3">
                        Target ini adalah <strong>rapor gabungan seluruh departemen</strong>. Sistem mengambil progress dari beberapa target KPI
                        milik tiap divisi, merata-ratakannya per divisi, lalu merata-ratakan semua divisi menjadi satu angka.
                    </p>
        
                    <h6 class="fw-semibold">Sumber Data</h6>
                    <ul>
                        <li>Seluruh <strong>target KPI yang dibuat pada tahun berjalan</strong> (dilihat dari tanggal target dibuat, bukan tahun detail jangka)</li>
                        <li>Divisi dibaca dari detail tiap target</li>
                    </ul>
        
                    <div class="alert alert-warning">
                        <strong>Hanya 4 jenis target yang ikut dihitung:</strong>
                        <ol class="mb-2 mt-2">
                            <li>Pemasukan Kotor</li>
                            <li>Target Penjualan Project Tahunan</li>
                            <li>Meningkatkan Revenue Perusahaan</li>
                            <li>Pendapatan Penjualan Project</li>
                        </ol>
                        Target jenis lain (misalnya kepuasan peserta, ketepatan waktu, atau Performa KPI Departemen itu sendiri)
                        <strong>tidak ikut dihitung</strong>. Divisi yang tidak punya satu pun dari 4 jenis target ini tidak muncul di perhitungan.
                    </div>
        
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung (Langkah demi Langkah)</h6>
                    <ol>
                        <li>Ambil semua target KPI yang dibuat tahun ini, lalu kelompokkan berdasarkan <strong>divisi</strong>.</li>
                        <li>Untuk setiap target dari 4 jenis di atas, hitung progress-nya (dengan aturan masing-masing target).</li>
                        <li><strong>Ubah menjadi persen</strong>:
                            <ul>
                                <li>Target bertipe <strong>rupiah</strong> atau <strong>angka</strong> → <code>(Progress ÷ Nilai Target) × 100</code></li>
                                <li>Target bertipe <strong>persen</strong> → dipakai apa adanya</li>
                                <li>Hasil dibatasi antara 0 sampai 100. Pencapaian di atas target tetap dihitung 100.</li>
                            </ul>
                        </li>
                        <li><strong>Rata-rata per divisi</strong> = jumlah persen target divisi ÷ jumlah target divisi.</li>
                        <li><strong>Progress akhir</strong> = rata-rata dari semua divisi. Setiap divisi bobotnya sama, besar atau kecil.</li>
                        <li>Gap = <code>Progress − Nilai Target</code>.</li>
                    </ol>
        
                    <div class="alert alert-light border mt-3">
                        <strong>Contoh nyata:</strong>
                        <ul class="mb-2 mt-2">
                            <li><strong>Divisi A</strong> punya 2 target: Pemasukan Kotor Rp 4,5 M dari target Rp 5 M = 90%, dan Pendapatan Penjualan Project 70% → rata-rata <strong>80%</strong></li>
                            <li><strong>Divisi B</strong> punya 1 target: 50% → rata-rata <strong>50%</strong></li>
                            <li><strong>Divisi C</strong> punya 1 target: 65% → rata-rata <strong>65%</strong></li>
                        </ul>
                        Progress = (80 + 50 + 65) ÷ 3 = <strong>65%</strong><br>
                        Jika nilai target = 80, Gap = 65 − 80 = <strong>−15</strong> → status <em>behind</em><br>
                        Divisi dengan rata-rata di bawah 70 (Divisi B dan C) masuk daftar <strong>Divisi Berisiko</strong>.
                    </div>
        
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%">Bagian</th>
                                    <th>Artinya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Total KPI</strong></td>
                                    <td>Jumlah <strong>semua</strong> target KPI yang dibuat tahun ini (termasuk jenis yang tidak ikut dihitung)</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Divisi</strong></td>
                                    <td>Jumlah divisi yang benar-benar dihitung. Bisa lebih sedikit dari jumlah divisi sebenarnya</td>
                                </tr>
                                <tr>
                                    <td><strong>Progress</strong></td>
                                    <td>Rata-rata seluruh divisi (persen)</td>
                                </tr>
                                <tr>
                                    <td><strong>Top Division / Lowest Division</strong></td>
                                    <td>Divisi dengan rata-rata tertinggi dan terendah</td>
                                </tr>
                                <tr>
                                    <td><strong>Trend</strong></td>
                                    <td>Saat ini selalu tampil <em>stable (0)</em>. Perubahan antar bulan belum dihitung</td>
                                </tr>
                                <tr>
                                    <td><strong>Konsistensi</strong></td>
                                    <td><em>stable</em> jika angka antar target tidak terlalu jauh berbeda (simpangan kurang dari 10 poin), selain itu <em>fluctuating</em></td>
                                </tr>
                                <tr>
                                    <td><strong>Target</strong></td>
                                    <td><em>on_track</em> = progress mencapai target. <em>at_risk</em> = kurang dari target tetapi selisih tidak lebih dari 5 poin. <em>behind</em> = tertinggal lebih dari 5 poin</td>
                                </tr>
                                <tr>
                                    <td><strong>Breakdown Divisi</strong></td>
                                    <td>Rata-rata persen setiap divisi</td>
                                </tr>
                                <tr>
                                    <td><strong>Divisi Berisiko</strong></td>
                                    <td>Divisi dengan rata-rata <strong>di bawah 70%</strong>. Bagian ini hanya muncul jika ada divisi berisiko</td>
                                </tr>
                                <tr>
                                    <td><strong>Insight</strong></td>
                                    <td>Kalimat ringkasan otomatis berisi rata-rata, divisi terbaik, divisi terendah, status target, dan konsistensi</td>
                                </tr>
                                <tr>
                                    <td><strong>Grafik lingkaran</strong></td>
                                    <td>Bagian tercapai (Above = progress) dibanding sisa menuju 100% (Below)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mb-0">
                        Karena data diambil dari target-target lain, angka di sini ikut berubah ketika progress target-target tersebut berubah.
                    </p>
                ',
        
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau tidak sesuai harapan, biasanya karena:</p>
        
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Belum ada target dari 4 jenis yang dihitung (Pemasukan Kotor, Target Penjualan Project Tahunan, Meningkatkan Revenue Perusahaan, Pendapatan Penjualan Project)</td>
                                    <td>Buat target jenis tersebut untuk divisi terkait. Tanpa itu progress 0 dan halaman detail kosong</td>
                                </tr>
                                <tr>
                                    <td>Target dibuat di tahun sebelumnya</td>
                                    <td>Yang dihitung hanya target yang <strong>dibuat pada tahun berjalan</strong>, walaupun detail jangkanya tahun ini</td>
                                </tr>
                                <tr>
                                    <td>Divisi pada detail target kosong</td>
                                    <td>Target yang tidak punya divisi tidak masuk perhitungan. Pastikan jabatan yang dipilih punya divisi</td>
                                </tr>
                                <tr>
                                    <td>Divisi tertentu tidak muncul di Breakdown</td>
                                    <td>Divisi itu belum punya satu pun dari 4 jenis target. Ini wajar</td>
                                </tr>
                                <tr>
                                    <td>Total KPI banyak tetapi Total Divisi sedikit</td>
                                    <td>Total KPI menghitung semua target, sedangkan Total Divisi hanya divisi yang punya target dari 4 jenis yang dihitung</td>
                                </tr>
                                <tr>
                                    <td>Progress rendah padahal satu divisi bagus</td>
                                    <td>Progress adalah rata-rata semua divisi dengan bobot sama. Satu divisi rendah menarik turun hasil akhir</td>
                                </tr>
                                <tr>
                                    <td>Progress satu target di dalamnya 0</td>
                                    <td>Target itu ikut dihitung 0 dan menurunkan rata-rata. Periksa data pada target tersebut, misalnya nilai target atau tahunnya</td>
                                </tr>
                                <tr>
                                    <td>Trend selalu stable</td>
                                    <td>Wajar. Tren per bulan memang belum dihitung pada target ini</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
        
                    <div class="alert alert-info mt-3 mb-0">
                        <strong>Tips:</strong> Mulai dari bagian <em>Divisi Berisiko</em> dan <em>Lowest Division</em>,
                        lalu buka target milik divisi tersebut untuk melihat target mana yang paling rendah.
                    </div>
                ',
        
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target Departemen</strong><br>
                            Buat target KPI dengan Assistant Route <em>"Performa KPI Departemen"</em> dan isi nilai target (persen).
                        </li>
                        <li class="mb-2"><strong>Tiap Divisi Memiliki Target</strong><br>
                            Divisi membuat target dari 4 jenis yang dihitung pada tahun berjalan.
                        </li>
                        <li class="mb-2"><strong>Sistem Menghitung Tiap Target</strong><br>
                            Progress tiap target diubah menjadi persen (maksimal 100).
                        </li>
                        <li class="mb-2"><strong>Rata-rata per Divisi</strong><br>
                            Persen target dalam satu divisi dirata-ratakan.
                        </li>
                        <li class="mb-2"><strong>Rata-rata Seluruh Divisi</strong><br>
                            Semua divisi dirata-ratakan menjadi satu Progress akhir.
                        </li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>
                            Progress, breakdown divisi, divisi berisiko, status, dan insight muncul di halaman detail.
                        </li>
                    </ol>
                ',
            ],

                        'feedback_kebersihan_dan_kenyamanan' => [
                'judul'          => 'Feedback Kebersihan dan Kenyamanan',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen peserta yang puas terhadap kebersihan dan kenyamanan</strong>, berdasarkan <strong>form feedback</strong> yang diisi peserta. Dari form feedback, hanya <strong>5 pertanyaan</strong> (F1 sampai F5) yang dipakai.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Feedback Peserta</strong> (tabel <code>Nilaifeedback</code>)</li>
                        <li>Tahun dilihat dari <strong>tanggal feedback dibuat</strong></li>
                        <li>Semua feedback di tahun itu dihitung, tidak dibedakan per kelas atau per orang</li>
                        <li>Setiap jawaban berskala <strong>1 sampai 4</strong></li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menilai Setiap Feedback</h6>
                    <ol>
                        <li>Kelima jawaban (F1 sampai F5) dijumlahkan</li>
                        <li>Dibagi 5 untuk mendapat <strong>rata-rata</strong> (hasilnya dibatasi antara 1 sampai 4)</li>
                        <li>Jika rata-rata <strong>3,5 atau lebih</strong> → peserta dianggap <strong>puas</strong></li>
                        <li>Jika di bawah 3,5 → <strong>belum puas</strong></li>
                    </ol>
                    <p class="small text-muted">Agar rata-rata mencapai 3,5, jumlah kelima jawaban minimal <strong>18</strong> (misalnya 4, 4, 3, 3, 4).</p>
                    <div class="alert alert-warning mt-3"><strong>Pertanyaan yang kosong dihitung 0.</strong> Pembaginya tetap 5. Contoh: jawaban 4, 4, 4, 4 dan satu pertanyaan kosong = 16 ÷ 5 = 3,2, sehingga dianggap belum puas walaupun empat jawaban lainnya sempurna.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah peserta puas / total feedback) x 100
gap      = 0 jika progress sudah melewati target
           (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika belum ada feedback sama sekali di tahun itu, progress = 0.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><br><br><strong>Satu feedback:</strong> F1 sampai F5 = 4, 4, 3, 4, 4. Jumlah 19, rata-rata = 19 ÷ 5 = <strong>3,8</strong> → puas<br><strong>Feedback lain:</strong> semua jawaban 3. Rata-rata = <strong>3,0</strong> → belum puas<br><br>Jika ada 150 feedback dan 120 di antaranya rata-ratanya 3,5 atau lebih:<br>Progress = (120 ÷ 150) × 100 = <strong>80%</strong><br>Jika nilai target = 90, gap = 80 − 90 = <strong>−10</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah peserta puas (Above) dibanding yang belum puas (Below).</li>
                        <li><strong>Grafik bulanan:</strong> <strong>rata-rata skor</strong> feedback (skala 1 sampai 4) pada bulan tersebut.</li>
                        <li><strong>Grafik harian:</strong> skor feedback pada tanggal tersebut. Jika ada beberapa feedback di hari yang sama, hanya <strong>feedback terakhir</strong> yang ditampilkan.</li>
                    </ul>
                    <p class="small text-muted mb-0">Angka ini dihitung dari <strong>seluruh feedback peserta</strong>, bukan hanya feedback untuk satu orang, jadi semua orang yang ditugaskan di target melihat angka yang sama.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada feedback di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Pastikan peserta sudah mengisi form feedback</td>
                                </tr>
                                <tr>
                                    <td>Pertanyaan F1 sampai F5 tidak terisi lengkap</td>
                                    <td>Pertanyaan kosong dihitung 0 sehingga rata-rata turun. Pastikan form diisi lengkap</td>
                                </tr>
                                <tr>
                                    <td>Banyak feedback dengan rata-rata di bawah 3,5</td>
                                    <td>Tingkatkan kebersihan dan kenyamanan. Rata-rata 3,4 pun belum dihitung puas</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Ajak peserta mengisi semua pertanyaan kebersihan dan kenyamanan. Satu pertanyaan yang terlewat bisa membuat peserta yang sebenarnya puas tidak terhitung puas.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Feedback Kebersihan dan Kenyamanan"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Peserta Mengikuti Training</strong><br>Peserta menggunakan fasilitas dan ruang kelas.</li>
                        <li class="mb-2"><strong>Peserta Mengisi Feedback</strong><br>Peserta mengisi form feedback. Tanggal pengisian tercatat otomatis.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem merata-ratakan jawaban F1 sampai F5 setiap feedback. Rata-rata 3,5 atau lebih dianggap puas.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (persentase peserta puas) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'penyelesaian_tugas_harian' => [
                'judul'          => 'Penyelesaian Tugas Harian',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen tugas harian yang sudah berstatus selesai</strong> di tahun yang dipilih.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Kontrol Tugas</strong> (menu Daftar Tugas)</li>
                        <li>Tahun dilihat dari <strong>tanggal tugas dibuat</strong></li>
                        <li>Tugas dianggap selesai jika <strong>status = 1 (selesai)</strong></li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Tugas Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Cara melihat</th>
                                    <th>Tugas yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu karyawan</strong> (misalnya dari halaman overview karyawan)</td>
                                    <td>Hanya tugas milik karyawan tersebut</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih karyawan</strong></td>
                                    <td>Seluruh tugas semua karyawan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Ketepatan waktu tidak dinilai.</strong> Tugas yang diselesaikan terlambat tetap dihitung selesai asalkan statusnya selesai.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah tugas selesai / total tugas) x 100
gap      = 0 jika progress sudah melewati target
           (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika belum ada tugas sama sekali di tahun itu, progress = 0.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Total tugas = <strong>120</strong></li><li>96 tugas berstatus selesai</li><li>24 tugas belum selesai</li></ul>Progress = (96 ÷ 120) × 100 = <strong>80%</strong><br>Jika nilai target = 90, gap = 80 − 90 = <strong>−10</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah tugas selesai (Above) dibanding yang belum selesai (Below).</li>
                        <li><strong>Grafik bulanan:</strong> persentase tugas selesai dari tugas yang dibuat pada bulan tersebut.</li>
                        <li><strong>Grafik harian:</strong> persentase tugas selesai dari tugas yang dibuat pada tanggal tersebut.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada tugas di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Pastikan tugas sudah dibuat di menu Daftar Tugas</td>
                                </tr>
                                <tr>
                                    <td>Status tugas belum diubah menjadi selesai</td>
                                    <td>Ubah status menjadi selesai setelah pekerjaan rampung</td>
                                </tr>
                                <tr>
                                    <td>Angka berbeda dengan yang dilihat orang lain</td>
                                    <td>Kemungkinan satu orang melihat per karyawan, yang lain melihat seluruh karyawan. Samakan cara melihatnya</td>
                                </tr>
                                <tr>
                                    <td>Tugas dibuat di tahun lalu, diselesaikan di tahun ini</td>
                                    <td>Tugas tetap dihitung di tahun saat tugas dibuat</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Ubah status tugas menjadi selesai di hari yang sama saat pekerjaan rampung agar progress selalu terbaru.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Penyelesaian Tugas Harian"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Tugas Dibuat</strong><br>Tugas harian dicatat di menu Daftar Tugas. Tanggal dibuat tercatat otomatis.</li>
                        <li class="mb-2"><strong>Kerjakan dan Ubah Status</strong><br>Setelah tugas dikerjakan, ubah status menjadi selesai.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menghitung berapa persen tugas yang berstatus selesai.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian per bulan dan per hari ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'efektifitas_digital_marketing' => [
                'judul'          => 'Efektifitas Digital Marketing',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa kuartal (triwulan) dalam setahun yang sudah memiliki kegiatan kolaborasi digital marketing</strong>. Nilai maksimalnya <strong>4</strong> (empat kuartal).</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Colaborator</strong> (menu Colaborator)</li>
                        <li>Tahun dilihat dari <strong>tanggal data dibuat</strong></li>
                        <li>Semua data di tahun itu dihitung, tidak dibedakan per orang</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Pembagian Kuartal</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%">Kuartal</th>
                                    <th>Bulan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Kuartal 1</td>
                                    <td>Januari sampai Maret</td>
                                </tr>
                                <tr>
                                    <td>Kuartal 2</td>
                                    <td>April sampai Juni</td>
                                </tr>
                                <tr>
                                    <td>Kuartal 3</td>
                                    <td>Juli sampai September</td>
                                </tr>
                                <tr>
                                    <td>Kuartal 4</td>
                                    <td>Oktober sampai Desember</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Hanya ada atau tidaknya data yang dinilai.</strong> Satu data di sebuah kuartal sudah membuat kuartal itu dihitung terisi. Punya 1 data atau 50 data di kuartal yang sama hasilnya sama.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah kuartal yang punya minimal 1 data (0 sampai 4)
gap      = progress - nilai target
           (negatif berarti masih di bawah target)</pre>
                    <p class="small text-muted">Progress ditampilkan sebagai <strong>angka 0 sampai 4</strong>, bukan persen. Gap bisa positif jika nilai target diisi kurang dari 4.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Data dibuat di bulan Februari → Kuartal 1 terisi</li><li>Dua data di bulan Mei → Kuartal 2 terisi</li><li>Data di bulan Agustus → Kuartal 3 terisi</li><li>Tidak ada data Oktober sampai Desember → Kuartal 4 kosong</li></ul>Progress = <strong>3</strong><br>Jika nilai target = 4, gap = 3 − 4 = <strong>−1</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah kuartal yang terisi (Above) dibanding yang masih kosong (Below, dari total 4).</li>
                        <li><strong>Grafik bulanan dan harian:</strong> hanya menandai bulan dan tanggal yang ada datanya (bernilai 1), bukan menunjukkan banyaknya data.</li>
                    </ul>
                    <p class="small text-muted mb-0">Jika belum ada data sama sekali di tahun itu, pada halaman detail progress = 0 dan seluruh 4 kuartal tampil sebagai kosong.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data Colaborator di tahun tersebut</td>
                                    <td>Input data di menu Colaborator. Progress otomatis 0 jika kosong</td>
                                </tr>
                                <tr>
                                    <td>Data hanya ada di beberapa kuartal</td>
                                    <td>Pastikan ada minimal satu kegiatan yang dicatat di setiap kuartal</td>
                                </tr>
                                <tr>
                                    <td>Data dibuat di tahun yang berbeda</td>
                                    <td>Tahun dilihat dari tanggal data dibuat, bukan tanggal kegiatan</td>
                                </tr>
                                <tr>
                                    <td>Progress tampil angka kecil, bukan persen</td>
                                    <td>Itu normal. Progress adalah jumlah kuartal terisi (maksimal 4)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Catat minimal satu kegiatan digital marketing di setiap kuartal agar progress mencapai 4.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Efektifitas Digital Marketing"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Kegiatan Dijalankan</strong><br>Tim menjalankan kegiatan kolaborasi digital marketing.</li>
                        <li class="mb-2"><strong>Catat di Colaborator</strong><br>Setiap kegiatan dicatat di menu Colaborator. Tanggal dibuat tercatat otomatis.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem mengelompokkan data per kuartal, lalu menghitung kuartal yang punya data.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (jumlah kuartal terisi) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'timeline_webinar' => [
                'judul'          => 'Timeline Webinar',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>jumlah webinar (event) yang terlaksana</strong> di tahun yang dipilih.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Event Timeline</strong> (menu Timeline) yang sudah <strong>dipetakan ke bulan dan tahun</strong></li>
                        <li>Tahun dilihat dari <strong>tahun pemetaan</strong>, bukan dari tanggal event dibuat</li>
                        <li>Event yang <strong>belum dipetakan</strong> tidak dihitung</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah event yang dipetakan di tahun target
gap      = progress - nilai target
           (negatif berarti masih di bawah target)</pre>
                    <p class="small text-muted">Progress ditampilkan sebagai <strong>angka</strong> (jumlah event), bukan persen.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><br>Ada 9 event yang dipetakan di tahun 2025 → progress = <strong>9</strong><br>Jika nilai target = 12, gap = 9 − 12 = <strong>−3</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> menunjukkan <strong>jumlah kuartal</strong> yang punya event (Above) dibanding kuartal yang kosong (Below, dari total 4). Ini bukan jumlah event.</li>
                        <li><strong>Grafik bulanan:</strong> jumlah event menurut <strong>bulan pemetaan</strong>.</li>
                        <li><strong>Grafik harian:</strong> jumlah event menurut tanggal event dibuat, dikelompokkan di bawah bulan pemetaannya. Karena itu tanggal bisa tampak berbeda dari bulannya.</li>
                    </ul>
                    <p class="small text-muted mb-0">Jika belum ada event sama sekali, pada halaman detail progress = 0 dan seluruh 4 kuartal tampil sebagai kosong. Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada event di tahun tersebut</td>
                                    <td>Buat event di menu Timeline dan pastikan dipetakan ke bulan dan tahun target</td>
                                </tr>
                                <tr>
                                    <td>Event sudah dibuat tetapi belum dipetakan</td>
                                    <td>Event tanpa pemetaan tidak ikut dihitung. Petakan event ke bulan dan tahun yang benar</td>
                                </tr>
                                <tr>
                                    <td>Event dipetakan ke tahun yang berbeda</td>
                                    <td>Tahun dilihat dari pemetaan. Periksa tahun pemetaannya</td>
                                </tr>
                                <tr>
                                    <td>Pada detail, lingkaran tidak sama dengan progress</td>
                                    <td>Lingkaran menghitung kuartal, sedangkan progress menghitung jumlah event</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Petakan setiap event segera setelah dibuat agar langsung dihitung.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Timeline Webinar"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Rencanakan Webinar</strong><br>Event webinar dibuat di menu Timeline.</li>
                        <li class="mb-2"><strong>Petakan ke Bulan dan Tahun</strong><br>Setiap event dihubungkan ke bulan dan tahun pelaksanaannya.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menghitung jumlah event yang dipetakan di tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (jumlah event) muncul di dashboard. Rincian per bulan dan kuartal ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'presentase_kinerja_instruktur' => [
                'judul'          => 'Presentase Kinerja Instruktur',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>seberapa penuh hari kerja instruktur terisi kegiatan</strong>, baik mengajar kelas maupun aktivitas lain. Satu hari aktif dihitung <strong>9 jam</strong>.</p>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li><strong>Periode:</strong> 1 Januari sampai 31 Desember. Jika tahun target adalah tahun berjalan, periode berakhir di <strong>hari ini</strong>, sehingga targetnya bertambah setiap hari kerja.</li>
                        <li><strong>Hari kerja:</strong> Senin sampai Jumat, dikurangi hari libur yang terdaftar di tabel Hari Libur.</li>
                        <li><strong>Target jam per orang</strong> = jumlah hari kerja × 9 jam.</li>
                        <li><strong>Hari aktif instruktur</strong> = gabungan (tanpa dihitung dobel) dari dua sumber di bawah.</li>
                        <li>Hari aktif <strong>dikurangi hari cuti</strong>.</li>
                        <li><strong>Jam aktif</strong> = hari aktif × 9.</li>
                    </ol>
                    <h6 class="fw-semibold mt-4">Dua Sumber Hari Aktif</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">Sumber</th>
                                    <th>Yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Aktivitas di luar kelas</strong> (menu Activity Instruktur)</td>
                                    <td>Setiap tanggal aktivitas yang <strong>tidak terhubung ke kelas</strong> dan tercatat atas nama instruktur</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas (RKM)</strong></td>
                                    <td>Setiap tanggal dari <strong>tanggal awal sampai tanggal akhir</strong> kelas, jika instruktur tercatat sebagai instruktur 1, instruktur 2, atau asisten</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Perlu diketahui:</strong><ul class="mb-0 mt-2"><li>Kelas yang berlangsung di <strong>Sabtu, Minggu, atau hari libur</strong> tetap dihitung sebagai hari aktif, tetapi hari tersebut tidak menambah target. Akibatnya progress bisa melebihi 100% pada halaman daftar.</li><li><strong>Status persetujuan cuti tidak diperiksa.</strong> Semua pengajuan cuti yang tercatat mengurangi hari aktif.</li><li>Hari cuti hanya mengurangi hari aktif. Cuti tidak menurunkan target jam.</li></ul></div>
                    <h6 class="fw-semibold mt-4">Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu instruktur</strong></td>
                                    <td>Jam aktif instruktur tersebut dibanding target jam</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td><strong>Rata-rata</strong> seluruh karyawan aktif berjabatan Instruktur</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jam aktif / target jam) x 100
gap      = progress - 100 (pada halaman detail)</pre>
                    <p class="small text-muted">Pada halaman detail progress <strong>dibatasi maksimal 100</strong> dan gap selalu dibandingkan dengan 100, bukan dengan nilai target.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025 penuh):</strong><ul class="mb-2 mt-2"><li>Hari kerja = 245 → target jam = 245 × 9 = <strong>2.205 jam</strong></li><li>Instruktur A: 130 hari kelas + 20 hari aktivitas lain, setelah dikurangi cuti = <strong>150 hari aktif</strong> → 150 × 9 = 1.350 jam</li></ul>Progress = (1.350 ÷ 2.205) × 100 = <strong>61,22%</strong><br>Pada halaman detail: progress 61,2 dan gap = 61,2 − 100 = <strong>−38,8</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Kartu instruktur:</strong> persentase, jam aktif dibanding target jam, jumlah Hari Aktif, Hari Cuti, dan Hari Libur.</li>
                        <li><strong>Kalender bulanan:</strong> hijau = hari aktif, kuning = cuti, merah = libur nasional, abu-abu = akhir pekan, kosong = tidak ada aktivitas.</li>
                        <li><strong>Grafik lingkaran:</strong> jam aktif (Above) dibanding sisa jam target (Below). Untuk satu instruktur, Below = 0.</li>
                        <li><strong>Grafik bulanan dan harian:</strong> jam aktif per bulan dan per tanggal (dirata-rata jika semua instruktur).</li>
                        <li>Jika dilihat tanpa memilih orang, hanya tampil satu baris <strong>Rata-rata Seluruh Instruktur</strong> tanpa kalender.</li>
                    </ul>
                    <p class="small text-muted mb-0">Di kalender, hari akhir pekan atau libur tetap tampil abu-abu atau merah walaupun instruktur mengajar. Karena itu angka Hari Aktif bisa lebih besar dari jumlah kotak hijau.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada karyawan aktif berjabatan Instruktur</td>
                                    <td>Pastikan data karyawan dan jabatannya benar</td>
                                </tr>
                                <tr>
                                    <td>Kelas belum tercatat atas nama instruktur</td>
                                    <td>Pastikan kode instruktur terisi sebagai instruktur 1, instruktur 2, atau asisten pada RKM</td>
                                </tr>
                                <tr>
                                    <td>Aktivitas di luar kelas belum dicatat</td>
                                    <td>Catat di menu Activity Instruktur. Aktivitas yang terhubung ke kelas tidak dihitung dari sini</td>
                                </tr>
                                <tr>
                                    <td>Hari libur nasional belum diinput</td>
                                    <td>Lengkapi tabel Hari Libur agar target jam tidak terlalu besar</td>
                                </tr>
                                <tr>
                                    <td>Banyak hari cuti</td>
                                    <td>Setiap hari cuti mengurangi hari aktif. Periksa data pengajuan cuti</td>
                                </tr>
                                <tr>
                                    <td>Progress di daftar lebih dari 100%, di detail tepat 100%</td>
                                    <td>Normal. Daftar tidak dibatasi, detail dibatasi 100</td>
                                </tr>
                                <tr>
                                    <td>Target jam berubah-ubah</td>
                                    <td>Untuk tahun berjalan, target bertambah setiap hari kerja karena periode berakhir di hari ini</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Catat aktivitas non-kelas di Activity Instruktur setiap hari agar hari tanpa kelas tetap terhitung aktif.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Presentase Kinerja Instruktur"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Jadwal Kelas</strong><br>Kelas dijadwalkan sebagai RKM dengan instruktur, instruktur 2, atau asisten.</li>
                        <li class="mb-2"><strong>Catat Aktivitas Lain</strong><br>Aktivitas di luar kelas dicatat di menu Activity Instruktur.</li>
                        <li class="mb-2"><strong>Cuti Dicatat</strong><br>Pengajuan cuti instruktur dicatat di sistem.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menggabungkan hari kelas dan aktivitas, mengurangi cuti, lalu membandingkan dengan target jam.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Kalender dan rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'kepuasan_peserta_pelatihan' => [
                'judul'          => 'Kepuasan Peserta Pelatihan',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>tingkat kepuasan peserta terhadap instruktur</strong> berdasarkan form feedback. Hasilnya berupa <strong>rata-rata skor yang diubah menjadi persen</strong> (skor 4 = 100%).</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Feedback Peserta</strong> (menu Feedback)</li>
                        <li>Tahun dilihat dari <strong>tanggal feedback dibuat</strong></li>
                        <li>Setiap jawaban berskala <strong>1 sampai 4</strong></li>
                        <li>Pertanyaan penilaian instruktur ada 8 butir: I1 sampai I8</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Pertanyaan Mana yang Dipakai?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%">Cara melihat</th>
                                    <th>Cara menghitung skor satu feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu instruktur</strong></td>
                                    <td>Sistem mencari kelas (RKM) tahun itu yang dipegang instruktur, lalu memakai kolom sesuai perannya:<br>Instruktur 1 → I1 sampai I8<br>Instruktur 2 → I1b sampai I8b<br>Asisten → I1as sampai I8as<br>Jumlah 8 jawaban dibagi 8.</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Setiap feedback memakai I1 sampai I8. Jika kolom instruktur 2 (I1b sampai I8b) juga terisi, keduanya digabung dan dibagi 16. Jika tidak, dibagi 8. Kolom asisten tidak ikut.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted">Hasil setiap feedback dibatasi antara 1 sampai 4. Feedback yang <strong>seluruh jawabannya kosong</strong> dilewati dan tidak dihitung.</p>
                    <div class="alert alert-warning mt-3"><strong>Berbeda dengan target kepuasan lain.</strong> Progress di sini adalah <strong>rata-rata skor</strong>, bukan persentase peserta yang puas. Pertanyaan yang kosong pada feedback yang terisi sebagian dihitung 0.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">skor rata-rata = rata-rata semua skor feedback
progress = (skor rata-rata / 4) x 100
gap      = progress - nilai target</pre>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025, tanpa memilih orang):</strong><ul class="mb-2 mt-2"><li>Feedback 1: jawaban 4, 4, 3, 4, 4, 3, 4, 4 = 30 → rata-rata 3,75</li><li>Feedback 2: semua jawaban 3 = 24 → rata-rata 3,0</li></ul>Skor rata-rata = (3,75 + 3,0) ÷ 2 = 3,375<br>Progress = (3,375 ÷ 4) × 100 = <strong>84,4%</strong><br>Jika nilai target = 90, gap = 84,4 − 90 = <strong>−5,6</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah feedback dengan skor 3,5 atau lebih (Above) dibanding yang di bawahnya (Below).</li>
                        <li><strong>Grafik bulanan:</strong> rata-rata skor (skala 1 sampai 4) pada bulan tersebut.</li>
                        <li><strong>Grafik harian:</strong> skor feedback pada tanggal tersebut. Jika ada beberapa feedback di hari yang sama, hanya yang terakhir ditampilkan.</li>
                        <li>Gap <strong>bisa positif</strong> jika progress melebihi target.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada feedback di tahun tersebut</td>
                                    <td>Progress otomatis 0. Pastikan peserta sudah mengisi feedback</td>
                                </tr>
                                <tr>
                                    <td>Instruktur belum tercatat pada RKM</td>
                                    <td>Saat dilihat per instruktur, kode karyawan harus terisi sebagai instruktur 1, instruktur 2, atau asisten pada RKM</td>
                                </tr>
                                <tr>
                                    <td>Kolom penilaian instruktur kosong semua</td>
                                    <td>Feedback seperti itu dilewati. Pastikan peserta mengisi pertanyaan penilaian instruktur</td>
                                </tr>
                                <tr>
                                    <td>Banyak jawaban kosong sebagian</td>
                                    <td>Pertanyaan kosong dihitung 0 sehingga skor turun</td>
                                </tr>
                                <tr>
                                    <td>Skor rendah</td>
                                    <td>Tingkatkan kualitas penyampaian materi dan pelayanan instruktur</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Ajak peserta mengisi seluruh pertanyaan penilaian instruktur sebelum meninggalkan kelas.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Kepuasan Peserta Pelatihan"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Kelas Berjalan</strong><br>Kelas dijadwalkan sebagai RKM dengan instruktur yang bertugas.</li>
                        <li class="mb-2"><strong>Peserta Mengisi Feedback</strong><br>Peserta menilai instruktur di form feedback. Tanggal pengisian tercatat otomatis.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem merata-ratakan skor feedback, lalu mengubahnya menjadi persen dari skor maksimal 4.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'upseling_lanjutan_materi' => [
                'judul'          => 'Upseling Lanjutan Materi',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen kelas yang sudah selesai dan menghasilkan rekomendasi materi lanjutan</strong> (upselling) ke peserta.</p>
                    <h6 class="fw-semibold mt-4">Kelas yang Dihitung</h6>
                    <p class="mb-2">Sebuah kelas (RKM) ikut dihitung jika <strong>semua</strong> syarat ini terpenuhi:</p>
                    <ul>
                        <li>Tanggal awal kelas ada di tahun target</li>
                        <li><strong>Tanggal akhir kelas sudah lewat</strong> (kelas yang belum selesai tidak dihitung)</li>
                        <li>Status RKM = <code>0</code></li>
                        <li>RKM belum dihapus</li>
                        <li>Peluangnya <strong>tidak berstatus tentatif</strong></li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Kelas yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu instruktur</strong></td>
                                    <td>Hanya kelas yang instruktur utamanya (instruktur 1) adalah orang tersebut</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Seluruh kelas yang memenuhi syarat</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <h6 class="fw-semibold mt-4">Sumber Rekomendasi</h6>
                    <p class="mb-3">Data <strong>Rekomendasi Lanjutan</strong> (menu Rekomendasi Lanjutan) yang dihubungkan ke kelas.</p>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah rekomendasi / jumlah kelas yang dihitung) x 100
gap      = progress - nilai target (pada halaman detail)</pre>
                    <div class="alert alert-warning mt-3"><strong>Angka daftar dan detail bisa berbeda.</strong><ul class="mb-0 mt-2"><li>Daftar target menghitung <strong>jumlah baris rekomendasi</strong>. Jika satu kelas punya lebih dari satu rekomendasi, progress bisa melebihi 100%. Halaman detail menghitung <strong>kelas</strong> yang punya rekomendasi.</li><li>Daftar target memakai <strong>tanggal awal kelas</strong> untuk menentukan tahun. Halaman detail memakai <strong>tanggal RKM dibuat</strong>.</li></ul></div>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Ada 40 kelas yang sudah selesai dan memenuhi syarat</li><li>28 kelas punya rekomendasi lanjutan</li></ul>Progress = (28 ÷ 40) × 100 = <strong>70%</strong><br>Jika nilai target = 80, gap = 70 − 80 = <strong>−10</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah kelas yang punya rekomendasi (Above) dibanding yang belum (Below).</li>
                        <li><strong>Grafik bulanan dan harian:</strong> persentase kelas yang punya rekomendasi, menurut bulan atau tanggal kelas dibuat.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada kelas yang sudah selesai</td>
                                    <td>Kelas baru dihitung setelah tanggal akhirnya lewat. Progress 0 di awal tahun adalah wajar</td>
                                </tr>
                                <tr>
                                    <td>Peluang kelas masih tentatif</td>
                                    <td>Kelas dengan peluang tentatif tidak dihitung. Ubah status peluang jika sudah pasti</td>
                                </tr>
                                <tr>
                                    <td>Kelas belum punya rekomendasi lanjutan</td>
                                    <td>Isi rekomendasi di menu Rekomendasi Lanjutan untuk kelas tersebut</td>
                                </tr>
                                <tr>
                                    <td>Instruktur bukan instruktur utama</td>
                                    <td>Saat dilihat per instruktur, hanya kelas yang ia pegang sebagai instruktur utama yang dihitung</td>
                                </tr>
                                <tr>
                                    <td>Angka daftar berbeda dengan detail</td>
                                    <td>Lihat penjelasan perbedaan acuan tahun dan cara menghitung di atas</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Biasakan mengisi rekomendasi lanjutan segera setelah kelas selesai.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Upseling Lanjutan Materi"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Kelas Berjalan</strong><br>Kelas dijadwalkan sebagai RKM dengan peluang yang sudah tidak tentatif.</li>
                        <li class="mb-2"><strong>Kelas Selesai</strong><br>Setelah tanggal akhir lewat, kelas mulai dihitung.</li>
                        <li class="mb-2"><strong>Isi Rekomendasi Lanjutan</strong><br>Instruktur atau tim mengisi rekomendasi materi lanjutan untuk kelas tersebut.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem membandingkan jumlah rekomendasi dengan jumlah kelas selesai.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'sertifikasi_kompetensi_internal' => [
                'judul'          => 'Sertifikasi Kompetensi Internal',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>jumlah sertifikasi kompetensi internal yang masih berlaku di tahun target</strong> pada karyawan yang ditugaskan di target.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li><strong>Daftar karyawan di target</strong> (karyawan yang dipilih saat target dibuat)</li>
                        <li>Data <strong>Sertifikasi</strong> milik karyawan tersebut (menu Development)</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Kapan Sertifikasi Dianggap Berlaku?</h6>
                    <p class="mb-2">Masa berlakunya <strong>bersinggungan dengan tahun target</strong>, yaitu keduanya terpenuhi:</p>
                    <ul>
                        <li>Tanggal <strong>berlaku dari</strong> sama dengan atau sebelum 31 Desember tahun target</li>
                        <li>Tanggal <strong>berlaku sampai</strong> sama dengan atau setelah 1 Januari tahun target, atau dikosongkan (tanpa batas)</li>
                    </ul>
                    <p class="small text-muted">Sertifikasi yang terbit tahun lalu tetapi masih berlaku di tahun target <strong>tetap dihitung</strong>. Sertifikasi tanpa tanggal berlaku dari <strong>tidak dihitung</strong>.</p>
                    <h6 class="fw-semibold mt-4">Cara Menghitung</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu karyawan</strong></td>
                                    <td>Jumlah sertifikasi berlaku miliknya, <strong>dibatasi tidak melebihi nilai target</strong></td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Jumlah <strong>karyawan</strong> yang punya minimal 1 sertifikasi berlaku</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Perlu diketahui:</strong> nilai target dihitung <strong>per karyawan</strong>, sehingga target total = jumlah karyawan × nilai target. Saat dilihat tanpa memilih orang, satu karyawan hanya dihitung 1 walaupun punya banyak sertifikasi. Jika nilai target per orang lebih dari 1, target total tidak mungkin tercapai.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress     = angka (lihat tabel di atas)
target total = jumlah karyawan x nilai target
gap          = progress - target total</pre>
                    <p class="small text-muted">Progress ditampilkan sebagai <strong>angka</strong>, bukan persen.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Ada 5 karyawan di target, nilai target = 1 per orang → target total = 5</li><li>3 karyawan punya sertifikasi yang berlaku di 2025</li></ul>Progress = <strong>3</strong>, gap = 3 − 5 = <strong>−2</strong><br><br><strong>Dilihat satu karyawan:</strong> ia punya 2 sertifikasi berlaku, nilai target 1 → progress = <strong>1</strong> (dibatasi target).</div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah yang tercapai (Above) dibanding sisanya (Below). Untuk satu karyawan, Below = 0.</li>
                        <li><strong>Grafik bulanan dan harian:</strong> hanya menandai tanggal mulai berlaku sertifikasi (sertifikasi yang mulai sebelum tahun target dihitung 1 Januari). Nilainya penanda, bukan jumlah.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada karyawan di target</td>
                                    <td>Progress 0 dan detail kosong. Pastikan target sudah diisi daftar karyawan</td>
                                </tr>
                                <tr>
                                    <td>Sertifikasi belum diinput</td>
                                    <td>Input sertifikasi di menu Development</td>
                                </tr>
                                <tr>
                                    <td>Masa berlaku sudah berakhir sebelum 1 Januari</td>
                                    <td>Sertifikasi kadaluarsa sebelum tahun target tidak dihitung. Perbarui sertifikasi</td>
                                </tr>
                                <tr>
                                    <td>Tanggal berlaku dari kosong</td>
                                    <td>Sertifikasi tanpa tanggal berlaku dari tidak dihitung. Lengkapi tanggalnya</td>
                                </tr>
                                <tr>
                                    <td>Target total terasa tidak mungkin tercapai</td>
                                    <td>Periksa nilai target per orang. Saat dilihat seluruh karyawan, satu orang hanya dihitung 1</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Pastikan setiap karyawan memiliki minimal satu sertifikasi yang masih berlaku dan tanggal berlakunya terisi.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Sertifikasi Kompetensi Internal"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Pilih Karyawan</strong><br>Saat membuat target, pilih karyawan atau jabatan yang ditugaskan.</li>
                        <li class="mb-2"><strong>Input Sertifikasi</strong><br>Sertifikasi dicatat di menu Development lengkap dengan tanggal berlaku.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem mengecek sertifikasi yang masa berlakunya bersinggungan dengan tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (angka) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'pelatihan_kompetensi_eksternal' => [
                'judul'          => 'Pelatihan Kompetensi Eksternal',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>jumlah pelatihan kompetensi eksternal yang selesai diikuti</strong> di tahun target oleh karyawan yang ditugaskan.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li><strong>Daftar karyawan di target</strong> (karyawan yang dipilih saat target dibuat)</li>
                        <li>Data <strong>Pelatihan</strong> milik karyawan tersebut (menu Development)</li>
                        <li>Pelatihan dihitung jika <strong>tanggal selesai</strong> ada di tahun target. Pelatihan tanpa tanggal selesai tidak dihitung.</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Cara Menghitung</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu karyawan</strong></td>
                                    <td>Jumlah pelatihan selesai miliknya, <strong>dibatasi tidak melebihi nilai target</strong></td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Jumlah <strong>karyawan</strong> yang pelatihannya sudah mencapai nilai target</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Perlu diketahui:</strong> nilai target dihitung <strong>per karyawan</strong>, sehingga target total = jumlah karyawan × nilai target. Saat dilihat tanpa memilih orang, progress menghitung <strong>orang</strong> yang sudah memenuhi target. Jika nilai target per orang lebih dari 1, progress hanya bisa mencapai jumlah karyawan, tidak sampai target total.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress     = angka (lihat tabel di atas)
target total = jumlah karyawan x nilai target
gap          = progress - target total</pre>
                    <p class="small text-muted">Progress ditampilkan sebagai <strong>angka</strong>, bukan persen.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Ada 4 karyawan di target, nilai target = 1 pelatihan per orang → target total = 4</li><li>3 karyawan sudah menyelesaikan minimal 1 pelatihan di 2025</li></ul>Progress = <strong>3</strong>, gap = 3 − 4 = <strong>−1</strong><br><br><strong>Dilihat satu karyawan:</strong> ia menyelesaikan 2 pelatihan, nilai target 2 → progress = <strong>2</strong>.</div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah yang tercapai (Above) dibanding sisanya (Below). Untuk satu karyawan, Below = 0.</li>
                        <li><strong>Grafik bulanan dan harian:</strong> hanya menandai tanggal selesai pelatihan. Nilainya penanda, bukan jumlah.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada karyawan di target</td>
                                    <td>Progress 0 dan detail kosong. Pastikan target sudah diisi daftar karyawan</td>
                                </tr>
                                <tr>
                                    <td>Pelatihan belum diinput</td>
                                    <td>Input pelatihan di menu Development</td>
                                </tr>
                                <tr>
                                    <td>Tanggal selesai kosong atau di tahun lain</td>
                                    <td>Hanya pelatihan dengan tanggal selesai di tahun target yang dihitung. Lengkapi tanggalnya</td>
                                </tr>
                                <tr>
                                    <td>Target total terasa tidak mungkin tercapai</td>
                                    <td>Periksa nilai target per orang. Saat dilihat seluruh karyawan, progress menghitung orang, bukan jumlah pelatihan</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Pastikan setiap pelatihan eksternal langsung dicatat beserta tanggal selesainya.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Pelatihan Kompetensi Eksternal"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Pilih Karyawan</strong><br>Saat membuat target, pilih karyawan atau jabatan yang ditugaskan.</li>
                        <li class="mb-2"><strong>Ikuti dan Catat Pelatihan</strong><br>Setelah pelatihan selesai, catat di menu Development dengan tanggal selesai.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menghitung pelatihan yang tanggal selesainya ada di tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (angka) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'target_penjualan_tahunan' => [
                'judul'          => 'Target Penjualan Tahunan',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>total penjualan kelas dibanding target Pemasukan Kotor perusahaan</strong>. Hasilnya berupa persen dari target tersebut.</p>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li><strong>Total penjualan</strong> = jumlah dari (harga jual × jumlah peserta) untuk semua RKM berstatus <code>0</code> yang <strong>tanggal awalnya</strong> ada di tahun target.</li>
                        <li><strong>Target pembanding</strong> = nilai target dari target KPI <em>Pemasukan Kotor</em>. Jika tidak ada nilainya, dipakai target tahunan perusahaan (data target dengan kuartal All).</li>
                        <li>Progress = total penjualan ÷ target pembanding × 100.</li>
                    </ol>
                    <h6 class="fw-semibold mt-4">Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Penjualan yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu sales</strong></td>
                                    <td>Hanya RKM yang kode sales-nya adalah sales tersebut</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Seluruh RKM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Progress adalah persen dari target perusahaan.</strong> Target Pemasukan Kotor harus sudah dibuat dan terisi. Tanpa itu progress tidak bisa dihitung.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (total penjualan / target Pemasukan Kotor) x 100
gap      = progress - nilai target KPI ini
           (negatif berarti masih di bawah target)</pre>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><ul class="mb-2 mt-2"><li>Target Pemasukan Kotor = Rp 10.000.000.000</li><li>Total penjualan RKM tahun 2025 = Rp 4.500.000.000</li></ul>Progress = (4,5 M ÷ 10 M) × 100 = <strong>45%</strong><br>Jika nilai target KPI ini = 80, gap = 45 − 80 = <strong>−35</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Ringkasan performa:</strong> <strong>Bulan Terakhir</strong> (bulan terakhir yang punya data beserta nilainya) dan <strong>Top Hari Tertinggi</strong> (tiga tanggal dengan nilai terbesar). Pada halaman ini tidak ada grafik lingkaran.</li>
                        <li><strong>Grafik Statistik:</strong> pilih <strong>Per Tahun</strong> untuk nilai per bulan, atau <strong>Per Bulan</strong> untuk nilai per tanggal.</li>
                        <li><strong>Kartu Triwulan:</strong> total penjualan per triwulan (Triwulan 1 = Januari sampai Maret, dan seterusnya) menurut tanggal awal kelas.</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Tabel Sales (saat dilihat tanpa memilih orang)</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:25%">Kolom</th>
                                    <th>Arti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Revenue</td>
                                    <td>Total penjualan RKM milik sales tersebut di tahun target</td>
                                </tr>
                                <tr>
                                    <td>Target (Editable)</td>
                                    <td>Target penjualan pribadi sales. <strong>Isi langsung di kolom</strong>, tersimpan otomatis sekitar 1 detik setelah berhenti mengetik</td>
                                </tr>
                                <tr>
                                    <td>Persentase</td>
                                    <td>Revenue ÷ Target pribadi × 100</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td><span class="badge bg-success">ACHIEVED</span> jika revenue sama dengan atau melebihi target pribadi, selain itu <span class="badge bg-warning">PENDING</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Sales yang target pribadinya belum diisi (0) tetap tampil ACHIEVED</strong>, karena revenue pasti sama dengan atau lebih dari 0. Kolom target berwarna merah dan tidak bisa diubah jika sales belum ditugaskan di target ini.</div>
                    <p class="small text-muted mb-0">Tabel menampilkan karyawan aktif berjabatan Sales, Sales Executive, atau Account Manager.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Target Pemasukan Kotor belum dibuat atau nilainya kosong</td>
                                    <td>Buat target Pemasukan Kotor dan isi nilainya. Tanpa itu halaman bisa gagal dimuat atau progress 0</td>
                                </tr>
                                <tr>
                                    <td>Belum ada RKM di tahun tersebut</td>
                                    <td>Pastikan RKM dibuat dengan tanggal awal di tahun target dan berstatus 0</td>
                                </tr>
                                <tr>
                                    <td>RKM belum terisi kode sales</td>
                                    <td>Saat dilihat per sales, RKM harus terisi kode sales-nya</td>
                                </tr>
                                <tr>
                                    <td>Harga jual atau jumlah peserta kosong</td>
                                    <td>Penjualan RKM tersebut dihitung 0. Lengkapi datanya</td>
                                </tr>
                                <tr>
                                    <td>Target pribadi sales belum diisi</td>
                                    <td>Isi di kolom Target (Editable). Jika belum, persentase 0 dan status tampil ACHIEVED</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Isi target pribadi setiap sales di awal tahun agar persentase dan status per sales bermakna.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Target Penjualan Tahunan"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Target Perusahaan Siap</strong><br>Target Pemasukan Kotor dibuat dan diisi nilainya.</li>
                        <li class="mb-2"><strong>RKM Dibuat</strong><br>Setiap penjualan kelas dicatat sebagai RKM lengkap dengan harga jual, jumlah peserta, dan kode sales.</li>
                        <li class="mb-2"><strong>Isi Target Pribadi</strong><br>Target tiap sales diisi di tabel Sales pada halaman detail.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menjumlahkan penjualan RKM lalu membandingkannya dengan target Pemasukan Kotor.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian triwulan dan per sales ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'biaya_akuisisi_perclient' => [
                'judul'          => 'Biaya Akuisisi Perclient',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen pengajuan penjualan yang biaya akuisisinya masih wajar</strong>. Biaya dianggap wajar jika tidak melebihi batas biaya, atau kelebihannya paling banyak <strong>10%</strong> dari nilai pendapatan.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Approval Pendapatan Sales</strong> (pengajuan penjualan), dengan data <strong>Pendapatan</strong> yang terhubung</li>
                        <li>Pengajuan dipilih menurut <strong>tanggal mulai</strong> di tahun target</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Pengajuan Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Pengajuan yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu sales</strong></td>
                                    <td>Pengajuan yang kelasnya (RKM) milik sales tersebut</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Seluruh pengajuan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menilai Setiap Pengajuan</h6>
                    <ol>
                        <li><strong>Nilai Pendapatan</strong> = jumlah peserta × harga net pada data Pendapatan</li>
                        <li><strong>Biaya Penjualan</strong> = total PA + oleh-oleh + entertainment + cashback + uang saku + akomodasi + biaya transport</li>
                        <li><strong>Batas Biaya</strong> = (harga net × jumlah peserta pada pengajuan) − Nilai Pendapatan</li>
                        <li><strong>Biaya Berlebih</strong> = Biaya Penjualan − Batas Biaya, jika Biaya Penjualan lebih besar. Jika tidak, Biaya Berlebih = 0</li>
                    </ol>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:70%">Kondisi</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Biaya Berlebih = 0 (biaya tidak melebihi batas)</td>
                                    <td><span class="badge bg-success">Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 0 dan <strong>10% atau kurang</strong> dari Nilai Pendapatan</td>
                                    <td><span class="badge bg-success">Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 10% dari Nilai Pendapatan</td>
                                    <td><span class="badge bg-danger">Tidak tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 0 tetapi Nilai Pendapatan 0 (data Pendapatan belum ada)</td>
                                    <td><span class="badge bg-danger">Tidak tercapai</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Periode berbeda antara daftar dan detail.</strong> Daftar target menghitung pengajuan dari 1 Januari sampai akhir bulan yang sama dengan bulan sekarang (di tahun target). Halaman detail menghitung satu tahun penuh. Karena itu angkanya bisa sedikit berbeda.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah pengajuan tercapai / total pengajuan) x 100
gap      = 0 jika progress sudah melewati target
           (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika belum ada pengajuan sama sekali, progress = 0.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh satu pengajuan:</strong><br>Harga net × peserta pada pengajuan = Rp 10.000.000, Nilai Pendapatan = Rp 9.000.000 → Batas Biaya = <strong>Rp 1.000.000</strong><ul class="mb-2 mt-2"><li>Biaya Penjualan Rp 800.000 → tidak melebihi batas → <strong>tercapai</strong></li><li>Biaya Penjualan Rp 1.600.000 → berlebih Rp 600.000 = 6,7% dari Rp 9.000.000 → <strong>tercapai</strong></li><li>Biaya Penjualan Rp 2.200.000 → berlebih Rp 1.200.000 = 13,3% → <strong>tidak tercapai</strong></li></ul><strong>Contoh progress:</strong> 50 pengajuan, 38 tercapai → (38 ÷ 50) × 100 = <strong>76%</strong>. Jika nilai target = 90, gap = 76 − 90 = <strong>−14</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> hanya satu penanda. Above = 1 jika progress sudah mencapai target, selain itu Below = 1. Ini bukan jumlah pengajuan.</li>
                        <li><strong>Grafik bulanan dan harian:</strong> jumlah pengajuan yang tercapai pada bulan atau tanggal mulai tersebut.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada pengajuan di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Progress 0 di sini bukan berarti kinerja buruk, hanya belum ada data</td>
                                </tr>
                                <tr>
                                    <td>Data Pendapatan belum terhubung ke pengajuan</td>
                                    <td>Jika biaya melebihi batas dan Nilai Pendapatan 0, pengajuan dihitung tidak tercapai. Lengkapi data Pendapatan</td>
                                </tr>
                                <tr>
                                    <td>Biaya penjualan jauh melebihi batas biaya</td>
                                    <td>Kelebihan di atas 10% dari Nilai Pendapatan membuat pengajuan tidak tercapai. Tekan biaya akuisisi</td>
                                </tr>
                                <tr>
                                    <td>Pengajuan dengan tanggal mulai di luar tahun target</td>
                                    <td>Hanya pengajuan dengan tanggal mulai di tahun target yang dihitung</td>
                                </tr>
                                <tr>
                                    <td>Angka daftar sedikit berbeda dengan detail</td>
                                    <td>Daftar menghitung sampai akhir bulan ini, detail menghitung satu tahun penuh</td>
                                </tr>
                                <tr>
                                    <td>Kode sales pada RKM tidak cocok</td>
                                    <td>Saat dilihat per sales, RKM pengajuan harus terisi kode sales yang benar</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Periksa dulu pengajuan yang biayanya melebihi batas dan belum punya data Pendapatan. Itu paling cepat memperbaiki progress.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Biaya Akuisisi Perclient"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Pengajuan Dibuat</strong><br>Pengajuan penjualan dicatat di Approval Pendapatan Sales lengkap dengan komponen biayanya.</li>
                        <li class="mb-2"><strong>Data Pendapatan Diisi</strong><br>Data Pendapatan dihubungkan ke pengajuan.</li>
                        <li class="mb-2"><strong>Sistem Menilai</strong><br>Sistem menghitung Batas Biaya dan Biaya Berlebih setiap pengajuan, lalu mengecek batas 10%.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (persentase pengajuan tercapai) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'peningkatan_kemampuan_kompetensi_sales' => [
                'judul'          => 'Peningkatan Kemampuan Kompetensi Sales',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen hasil penilaian kompetensi sales di e-learning yang berskor lebih dari 90</strong>.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data penilaian dari <strong>e-learning</strong> (Moodle) yang diambil otomatis lewat koneksi sistem</li>
                        <li>Penilaian dicocokkan ke karyawan lewat <strong>username</strong> (huruf besar dan kecil diabaikan)</li>
                        <li>Hanya akun sistem berjabatan <strong>Sales, SPV Sales, atau Adm Sales</strong> yang punya username yang dihitung</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li>Ambil seluruh penilaian milik akun yang berhak</li>
                        <li>Setiap penilaian dicek skornya. Penilaian <strong>lulus</strong> jika skor <strong>lebih dari 90</strong> (skor tepat 90 belum lulus)</li>
                        <li>Progress = penilaian lulus ÷ total penilaian × 100</li>
                    </ol>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Penilaian yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu orang</strong></td>
                                    <td>Hanya penilaian dengan username orang tersebut. Jika jabatannya bukan Sales, SPV Sales, atau Adm Sales, hasilnya 0</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Penilaian semua akun yang berhak</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Acuan tahun berbeda.</strong> Angka di daftar target menghitung <strong>semua penilaian tanpa batas tahun</strong>. Halaman detail hanya menghitung penilaian di <strong>tahun target</strong> (menurut tanggal pengumpulan atau pembuatan aktivitas). Jika e-learning gagal diakses, hasilnya 0.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah penilaian skor lebih dari 90 / total penilaian) x 100
gap      = 0 jika progress sudah melewati target
           (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata:</strong><br>Total penilaian = 60, yang skornya lebih dari 90 = 45<br>Progress = (45 ÷ 60) × 100 = <strong>75%</strong><br>Jika nilai target = 80, gap = 75 − 80 = <strong>−5</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> penilaian lulus (Above) dibanding yang belum lulus (Below).</li>
                        <li><strong>Grafik bulanan dan harian:</strong> <strong>jumlah penilaian yang lulus</strong> pada bulan atau tanggal tersebut.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Username di sistem berbeda dengan username e-learning</td>
                                    <td>Samakan username akun sistem dengan username e-learning. Jika berbeda, penilaiannya tidak terbaca</td>
                                </tr>
                                <tr>
                                    <td>Akun belum punya username atau jabatannya bukan Sales, SPV Sales, Adm Sales</td>
                                    <td>Lengkapi username dan pastikan jabatan akun benar</td>
                                </tr>
                                <tr>
                                    <td>Server e-learning tidak merespons</td>
                                    <td>Hasil otomatis 0. Coba lagi nanti atau hubungi admin e-learning</td>
                                </tr>
                                <tr>
                                    <td>Belum ada penilaian di e-learning</td>
                                    <td>Pastikan peserta sudah mengerjakan penilaian</td>
                                </tr>
                                <tr>
                                    <td>Skor tepat 90</td>
                                    <td>Skor harus lebih dari 90 agar dihitung lulus</td>
                                </tr>
                                <tr>
                                    <td>Penilaian tidak punya tanggal</td>
                                    <td>Penilaian tanpa tanggal tidak ikut dihitung di halaman detail</td>
                                </tr>
                                <tr>
                                    <td>Angka daftar berbeda dengan detail</td>
                                    <td>Daftar menghitung semua tahun, detail hanya tahun target</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Pastikan username akun sistem sama persis dengan username e-learning sebelum meminta peserta mengerjakan penilaian.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Peningkatan Kemampuan Kompetensi Sales"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Peserta Belajar di E-Learning</strong><br>Sales mengerjakan penilaian kompetensi di e-learning.</li>
                        <li class="mb-2"><strong>Sistem Mengambil Data</strong><br>Sistem mengambil hasil penilaian dari e-learning dan mencocokkannya lewat username.</li>
                        <li class="mb-2"><strong>Sistem Menilai</strong><br>Penilaian berskor lebih dari 90 dihitung lulus.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'meningkatkan_revenue_perusahaan' => [
                'judul'          => 'Meningkatkan Revenue Perusahaan',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>total penjualan bersih perusahaan</strong> di tahun yang dipilih dalam <strong>Rupiah</strong>.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Approval Pendapatan</strong>, kolom <code>total_penjualan_bersih</code></li>
                        <li>Tahun dilihat dari <strong>tanggal approval dibuat</strong></li>
                        <li>Semua approval di tahun itu dijumlahkan, tidak dibedakan per sales</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah total penjualan bersih (dibulatkan)
gap      = progress - nilai target
           (negatif berarti masih di bawah target, positif berarti melebihi)</pre>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><br>Nilai target = Rp 5.000.000.000<br>Total penjualan bersih seluruh approval 2025 = Rp 3.200.000.000<br>Progress = <strong>Rp 3.200.000.000</strong><br>Gap = 3,2 M − 5 M = <strong>−Rp 1.800.000.000</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Ringkasan performa:</strong> <strong>Bulan Terakhir</strong> (bulan terakhir yang punya data beserta nilainya) dan <strong>Top Hari Tertinggi</strong> (tiga tanggal dengan nilai terbesar). Pada halaman ini tidak ada grafik lingkaran.</li>
                        <li><strong>Grafik Statistik:</strong> pilih <strong>Per Tahun</strong> untuk nilai per bulan, atau <strong>Per Bulan</strong> untuk nilai per tanggal.</li>
                    </ul>
                    <p class="small text-muted mb-0">Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada data Approval Pendapatan di tahun tersebut</td>
                                    <td>Progress 0. Pastikan approval sudah dibuat</td>
                                </tr>
                                <tr>
                                    <td>Kolom total penjualan bersih kosong</td>
                                    <td>Lengkapi nilai penjualan bersih pada approval</td>
                                </tr>
                                <tr>
                                    <td>Approval dibuat di tahun yang berbeda</td>
                                    <td>Tahun dilihat dari tanggal approval dibuat, bukan tanggal kelas</td>
                                </tr>
                                <tr>
                                    <td>Nilai target kosong</td>
                                    <td>Isi nilai target agar gap dan persentase grafik bisa dihitung</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Pastikan setiap approval pendapatan langsung terisi total penjualan bersihnya.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Meningkatkan Revenue Perusahaan"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Approval Pendapatan Dibuat</strong><br>Setiap penjualan yang disetujui dicatat sebagai Approval Pendapatan beserta total penjualan bersih.</li>
                        <li class="mb-2"><strong>Sistem Menjumlahkan</strong><br>Sistem menjumlahkan total penjualan bersih seluruh approval di tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (Rupiah) muncul di dashboard. Rincian bulan dan hari ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'customer_acquisition_cost' => [
                'judul'          => 'Customer Acquisition Cost',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>berapa persen pengajuan penjualan yang biaya akuisisinya masih wajar</strong>. Biaya dianggap wajar jika tidak melebihi batas biaya, atau kelebihannya paling banyak <strong>10%</strong> dari nilai pendapatan.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Approval Pendapatan Sales</strong> (pengajuan penjualan), dengan data <strong>Pendapatan</strong> yang terhubung</li>
                        <li>Pengajuan dipilih menurut <strong>tanggal mulai</strong> di tahun target</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Pengajuan Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Pengajuan yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Selalu <strong>seluruh pengajuan</strong> semua sales</td>
                                    <td>Tidak dibedakan per orang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menilai Setiap Pengajuan</h6>
                    <ol>
                        <li><strong>Nilai Pendapatan</strong> = jumlah peserta × harga net pada data Pendapatan</li>
                        <li><strong>Biaya Penjualan</strong> = total PA + oleh-oleh + entertainment + cashback + uang saku + akomodasi + biaya transport</li>
                        <li><strong>Batas Biaya</strong> = (harga net × jumlah peserta pada pengajuan) − Nilai Pendapatan</li>
                        <li><strong>Biaya Berlebih</strong> = Biaya Penjualan − Batas Biaya, jika Biaya Penjualan lebih besar. Jika tidak, Biaya Berlebih = 0</li>
                    </ol>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:70%">Kondisi</th>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Biaya Berlebih = 0 (biaya tidak melebihi batas)</td>
                                    <td><span class="badge bg-success">Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 0 dan <strong>10% atau kurang</strong> dari Nilai Pendapatan</td>
                                    <td><span class="badge bg-success">Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 10% dari Nilai Pendapatan</td>
                                    <td><span class="badge bg-danger">Tidak tercapai</span></td>
                                </tr>
                                <tr>
                                    <td>Biaya Berlebih lebih dari 0 tetapi Nilai Pendapatan 0 (data Pendapatan belum ada)</td>
                                    <td><span class="badge bg-danger">Tidak tercapai</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Periode berbeda antara daftar dan detail.</strong> Daftar target menghitung pengajuan dari 1 Januari sampai akhir bulan yang sama dengan bulan sekarang (di tahun target). Halaman detail menghitung satu tahun penuh. Karena itu angkanya bisa sedikit berbeda.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah pengajuan tercapai / total pengajuan) x 100
gap      = 0 jika progress sudah melewati target
           (jika belum: progress - nilai target, hasilnya negatif)</pre>
                    <p class="small text-muted">Jika belum ada pengajuan sama sekali, progress = 0.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh satu pengajuan:</strong><br>Harga net × peserta pada pengajuan = Rp 10.000.000, Nilai Pendapatan = Rp 9.000.000 → Batas Biaya = <strong>Rp 1.000.000</strong><ul class="mb-2 mt-2"><li>Biaya Penjualan Rp 800.000 → tidak melebihi batas → <strong>tercapai</strong></li><li>Biaya Penjualan Rp 1.600.000 → berlebih Rp 600.000 = 6,7% dari Rp 9.000.000 → <strong>tercapai</strong></li><li>Biaya Penjualan Rp 2.200.000 → berlebih Rp 1.200.000 = 13,3% → <strong>tidak tercapai</strong></li></ul><strong>Contoh progress:</strong> 50 pengajuan, 38 tercapai → (38 ÷ 50) × 100 = <strong>76%</strong>. Jika nilai target = 90, gap = 76 − 90 = <strong>−14</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah pengajuan yang tercapai (Above) dibanding yang tidak tercapai (Below).</li>
                        <li><strong>Grafik bulanan dan harian:</strong> jumlah pengajuan yang tercapai pada bulan atau tanggal mulai tersebut.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada pengajuan di tahun tersebut</td>
                                    <td>Progress otomatis 0 dan halaman detail kosong. Progress 0 di sini bukan berarti kinerja buruk, hanya belum ada data</td>
                                </tr>
                                <tr>
                                    <td>Data Pendapatan belum terhubung ke pengajuan</td>
                                    <td>Jika biaya melebihi batas dan Nilai Pendapatan 0, pengajuan dihitung tidak tercapai. Lengkapi data Pendapatan</td>
                                </tr>
                                <tr>
                                    <td>Biaya penjualan jauh melebihi batas biaya</td>
                                    <td>Kelebihan di atas 10% dari Nilai Pendapatan membuat pengajuan tidak tercapai. Tekan biaya akuisisi</td>
                                </tr>
                                <tr>
                                    <td>Pengajuan dengan tanggal mulai di luar tahun target</td>
                                    <td>Hanya pengajuan dengan tanggal mulai di tahun target yang dihitung</td>
                                </tr>
                                <tr>
                                    <td>Angka daftar sedikit berbeda dengan detail</td>
                                    <td>Daftar menghitung sampai akhir bulan ini, detail menghitung satu tahun penuh</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Periksa dulu pengajuan yang biayanya melebihi batas dan belum punya data Pendapatan. Itu paling cepat memperbaiki progress.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Customer Acquisition Cost"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Pengajuan Dibuat</strong><br>Pengajuan penjualan dicatat di Approval Pendapatan Sales lengkap dengan komponen biayanya.</li>
                        <li class="mb-2"><strong>Data Pendapatan Diisi</strong><br>Data Pendapatan dihubungkan ke pengajuan.</li>
                        <li class="mb-2"><strong>Sistem Menilai</strong><br>Sistem menghitung Batas Biaya dan Biaya Berlebih setiap pengajuan, lalu mengecek batas 10%.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (persentase pengajuan tercapai) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'evaluasi_kinerja_sales' => [
                'judul'          => 'Evaluasi Kinerja Sales',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>seberapa rutin tim Sales mencatat aktivitas</strong> setiap hari kerja.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Aktivitas</strong> sales (menu Aktivitas di CRM)</li>
                        <li>Yang dilihat: kode sales dan tanggal aktivitas dibuat</li>
                        <li>Yang dihitung hanya karyawan aktif berjabatan <strong>Sales</strong> (SPV Sales dan Adm Sales tidak termasuk)</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Cara Sistem Menghitung</h6>
                    <ol>
                        <li><strong>Periode:</strong> 1 Januari sampai 31 Desember. Jika tahun target adalah tahun berjalan, sampai <strong>hari ini</strong>.</li>
                        <li><strong>Hari kerja:</strong> semua hari <strong>Senin sampai Jumat</strong>.</li>
                        <li><strong>Kemungkinan</strong> = jumlah hari kerja × jumlah sales.</li>
                        <li><strong>Aktif</strong> = jumlah pasangan (sales, hari) yang punya <strong>minimal 1 aktivitas</strong> di hari itu.</li>
                    </ol>
                    <div class="alert alert-warning mt-3"><strong>Perlu diketahui:</strong><ul class="mb-0 mt-2"><li>Satu aktivitas atau seratus aktivitas dalam sehari hasilnya sama.</li><li><strong>Hari libur nasional dan cuti tidak dikeluarkan.</strong> Hari libur di hari kerja tetap dihitung sebagai hari kerja sehingga bisa menurunkan progress.</li><li>Aktivitas di Sabtu atau Minggu tidak dihitung.</li></ul></div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = (jumlah pasangan sales-hari yang aktif / (hari kerja x jumlah sales)) x 100
gap      = progress - 100 (pada halaman detail)</pre>
                    <p class="small text-muted">Pada halaman detail, gap dibandingkan dengan 100, bukan dengan nilai target.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata:</strong><ul class="mb-2 mt-2"><li>20 hari kerja dan 4 sales → kemungkinan = 20 × 4 = 80</li><li>Pasangan sales-hari yang punya aktivitas = 60</li></ul>Progress = (60 ÷ 80) × 100 = <strong>75%</strong><br>Gap pada detail = 75 − 100 = <strong>−25</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> jumlah pasangan sales-hari yang aktif (Above) dibanding yang tidak aktif (Below).</li>
                        <li><strong>Grafik harian:</strong> <strong>jumlah sales</strong> yang punya aktivitas pada tanggal tersebut. Hari kerja tanpa aktivitas bernilai 0.</li>
                        <li><strong>Grafik bulanan:</strong> rata-rata jumlah sales yang aktif per hari kerja pada bulan tersebut.</li>
                    </ul>
                    <p class="small text-muted mb-0">Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada karyawan aktif berjabatan Sales</td>
                                    <td>Progress 0. Pastikan data karyawan dan jabatannya benar</td>
                                </tr>
                                <tr>
                                    <td>Aktivitas tidak tercatat atas kode sales yang benar</td>
                                    <td>Aktivitas harus terisi kode karyawan sales. Periksa data aktivitas</td>
                                </tr>
                                <tr>
                                    <td>Aktivitas dicatat di akhir pekan</td>
                                    <td>Aktivitas Sabtu dan Minggu tidak dihitung. Catat di hari kerja</td>
                                </tr>
                                <tr>
                                    <td>Banyak hari libur nasional di hari kerja</td>
                                    <td>Hari libur tetap dihitung sebagai hari kerja sehingga progress bisa lebih rendah</td>
                                </tr>
                                <tr>
                                    <td>Sales sedang cuti</td>
                                    <td>Hari cuti tetap dihitung sebagai hari kerja tanpa aktivitas</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Ingatkan tim Sales mencatat minimal satu aktivitas setiap hari kerja, walaupun hanya aktivitas singkat.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Evaluasi Kinerja Sales"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Sales Mencatat Aktivitas</strong><br>Setiap aktivitas dicatat di menu Aktivitas pada CRM. Tanggal tercatat otomatis.</li>
                        <li class="mb-2"><strong>Sistem Mengecek Per Hari</strong><br>Untuk setiap hari kerja, sistem mengecek sales mana yang punya aktivitas.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem membagi jumlah pasangan sales-hari yang aktif dengan seluruh kemungkinan.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress muncul di dashboard. Rincian per hari ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'pendapatan_penjualan_project' => [
                'judul'          => 'Pendapatan Penjualan Project',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>total nilai project yang berhasil dimenangkan</strong> di tahun yang dipilih dalam <strong>Rupiah</strong>.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Leads Project</strong> (menu Leads Project)</li>
                        <li>Hanya lead berstatus <strong>won</strong> (menang)</li>
                        <li>Tahun dilihat dari kolom <strong>Tahun Periode</strong> pada lead</li>
                        <li>Nilai yang dijumlahkan adalah <strong>estimasi nilai</strong> lead</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Siapa yang Dihitung?</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40%">Cara melihat</th>
                                    <th>Lead yang dihitung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Dilihat untuk <strong>satu sales</strong></td>
                                    <td>Hanya lead yang sales-nya adalah orang tersebut</td>
                                </tr>
                                <tr>
                                    <td>Dilihat <strong>tanpa memilih orang</strong></td>
                                    <td>Seluruh lead won</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3"><strong>Lead selain status won tidak dihitung</strong>, termasuk lead yang masih berjalan atau kalah.</div>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah estimasi nilai lead won di tahun target
gap      = progress - nilai target
           (negatif berarti masih di bawah target, positif berarti melebihi)</pre>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><br>Nilai target = Rp 2.000.000.000<br>Lead won tahun 2025: Rp 800.000.000 + Rp 600.000.000 + Rp 400.000.000<br>Progress = <strong>Rp 1.800.000.000</strong><br>Gap = 1,8 M − 2 M = <strong>−Rp 200.000.000</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Ringkasan performa:</strong> <strong>Bulan Terakhir</strong> (bulan terakhir yang punya data beserta nilainya) dan <strong>Top Hari Tertinggi</strong> (tiga tanggal dengan nilai terbesar). Pada halaman ini tidak ada grafik lingkaran.</li>
                        <li><strong>Grafik Statistik:</strong> pilih <strong>Per Tahun</strong> untuk nilai per bulan, atau <strong>Per Bulan</strong> untuk nilai per tanggal.</li>
                        <li>Pengelompokan per bulan dan hari mengikuti kolom <strong>Tahun Periode</strong> pada lead.</li>
                    </ul>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada lead berstatus won di tahun tersebut</td>
                                    <td>Progress 0. Ubah status lead menjadi won setelah deal tercapai</td>
                                </tr>
                                <tr>
                                    <td>Estimasi nilai lead kosong</td>
                                    <td>Lengkapi estimasi nilai pada lead</td>
                                </tr>
                                <tr>
                                    <td>Tahun Periode lead berbeda dengan tahun target</td>
                                    <td>Periksa dan perbaiki Tahun Periode pada lead</td>
                                </tr>
                                <tr>
                                    <td>Kode sales pada lead tidak sesuai</td>
                                    <td>Saat dilihat per sales, lead harus terisi kode sales yang benar</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Perbarui status lead menjadi won segera setelah deal tercapai agar nilainya langsung terhitung.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Pendapatan Penjualan Project"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Lead Dibuat</strong><br>Lead project dicatat di menu Leads Project lengkap dengan estimasi nilai dan Tahun Periode.</li>
                        <li class="mb-2"><strong>Lead Menang</strong><br>Ketika deal tercapai, ubah status lead menjadi won.</li>
                        <li class="mb-2"><strong>Sistem Menjumlahkan</strong><br>Sistem menjumlahkan estimasi nilai seluruh lead won di tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (Rupiah) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
            'leads_project' => [
                'judul'          => 'Leads Project',
                'dari_mana_data' => '
                    <p class="mb-3">Target ini mengukur <strong>jumlah lead project</strong> yang dihasilkan di tahun yang dipilih.</p>
                    <h6 class="fw-semibold mt-4">Sumber Data</h6>
                    <ul>
                        <li>Data <strong>Leads Project</strong> (menu Leads Project)</li>
                        <li>Tahun dilihat dari kolom <strong>Tahun Periode</strong> pada lead</li>
                        <li><strong>Semua status</strong> lead dihitung (won, kalah, maupun masih berjalan)</li>
                        <li>Semua sales dihitung, tidak dibedakan per orang</li>
                    </ul>
                    <h6 class="fw-semibold mt-4">Rumus Progress</h6>
                    <pre class="bg-light p-3 rounded border small">progress = jumlah lead di tahun target
gap      = progress - nilai target
           (negatif berarti masih di bawah target, positif berarti melebihi)</pre>
                    <p class="small text-muted">Progress ditampilkan sebagai <strong>angka</strong> (jumlah lead), bukan persen.</p>
                    <div class="alert alert-light border mt-3"><strong>Contoh nyata (tahun 2025):</strong><br>Nilai target = 50 lead<br>Jumlah lead dengan Tahun Periode 2025 = 36<br>Progress = <strong>36</strong><br>Gap = 36 − 50 = <strong>−14</strong></div>
                    <h6 class="fw-semibold mt-4">Membaca Halaman Detail</h6>
                    <ul>
                        <li><strong>Grafik lingkaran:</strong> hanya satu penanda. Above = 1 jika jumlah lead sudah mencapai target, selain itu Below = 1.</li>
                        <li><strong>Grafik Statistik:</strong> pilih <strong>Per Tahun</strong> untuk nilai per bulan, atau <strong>Per Bulan</strong> untuk nilai per tanggal.</li>
                        <li><strong>Kartu Triwulan:</strong> jumlah lead per triwulan. Pengelompokan mengikuti kolom <strong>Tahun Periode</strong> pada lead.</li>
                    </ul>
                    <p class="small text-muted mb-0">Angka ini data perusahaan, jadi semua orang yang ditugaskan di target melihat angka yang sama.</p>
                ',
                'error_kosong' => '
                    <p class="mb-3">Jika progress muncul <strong>0</strong>, kosong, atau rendah, biasanya karena:</p>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kemungkinan Penyebab</th>
                                    <th>Solusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tahun target belum diisi atau tidak valid</td>
                                    <td>Pastikan saat membuat target, tahun diisi dengan benar (contoh: 2025)</td>
                                </tr>
                                <tr>
                                    <td>Belum ada lead di tahun tersebut</td>
                                    <td>Progress 0 dan halaman detail kosong. Pastikan lead dicatat di menu Leads Project</td>
                                </tr>
                                <tr>
                                    <td>Tahun Periode lead kosong atau berbeda</td>
                                    <td>Periksa Tahun Periode pada setiap lead</td>
                                </tr>
                                <tr>
                                    <td>Halaman detail kosong padahal daftar target ada angkanya</td>
                                    <td>Pastikan nilai target sudah terisi dan lebih dari 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0"><strong>Tips:</strong> Catat setiap calon project baru sebagai lead dan pastikan Tahun Periodenya benar.</div>
                ',
                'alur_bisnis' => '
                    <ol class="mb-0">
                        <li class="mb-2"><strong>Buat Target</strong><br>Buat target KPI dengan Assistant Route <em>"Leads Project"</em> dan isi tahun.</li>
                        <li class="mb-2"><strong>Lead Dibuat</strong><br>Setiap calon project dicatat di menu Leads Project dengan Tahun Periode.</li>
                        <li class="mb-2"><strong>Sistem Menghitung</strong><br>Sistem menghitung jumlah seluruh lead di tahun target.</li>
                        <li class="mb-0"><strong>Lihat Hasil</strong><br>Progress (jumlah lead) muncul di dashboard. Rincian ada di halaman detail target.</li>
                    </ol>
                ',
            ],
        ];

        self::$data = $panduan;

        return self::$data;
    }
}
