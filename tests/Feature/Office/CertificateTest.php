<?php

namespace Tests\Feature\Office;

use App\Models\Certificate;
use App\Models\CertificateSummary;
use App\Models\Karyawan;
use App\Models\Peserta;
use App\Models\RKM;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Factories\CertificateFactory;
use Database\Factories\KaryawanFactory;
use Database\Factories\PesertaFactory;
use Database\Factories\RKMFactory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\ViewException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\InteractsWithAccess;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use DatabaseTransactions, InteractsWithAccess;

    private const VIEW   = 'View Inixcert';
    private const STORE  = 'Store Inixcert';
    private const DELETE = 'Delete Inixcert';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAccess();
        $this->withoutVite();
        Storage::fake();
    }

    // ---------------------------------------------------------------- helper

    private function sebagai(string ...$permissions): static
    {
        return $this->loginAs($this->userWithPermissions($permissions));
    }

    /** dompdf tidak dijalankan sungguhan; yang diuji alur controller-nya. */
    private function mockPdf(): void
    {
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('setPaper')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');
        Pdf::shouldReceive('stream')->andReturnUsing(
            fn () => response('%PDF-fake', 200, ['Content-Type' => 'application/pdf'])
        );
    }

    /** Controller memakai Karyawan::find(4) sebagai penandatangan. */
    private function siapkanPenandatangan(): void
    {
        if (! Karyawan::find(4)) {
            KaryawanFactory::new()->create(['id' => 4]);
        }
    }

    private function payload(RKM $rkm, Peserta $peserta, array $override = []): array
    {
        return array_merge([
            'nomor_sertifikat' => '026083',
            'rkm_id'           => $rkm->id,
            'id_peserta'       => $peserta->id,
            'nama_peserta'     => $peserta->nama,
            'nama_materi'      => 'Microsoft Excel',
            'tanggal_awal'     => '2026-10-05',
            'tanggal_akhir'    => '2026-10-07',
        ], $override);
    }

    private function dataSummary(array $override = []): array
    {
        return array_merge([
            'type'           => 'Webinar',
            'no_sertifikat'  => 'WEB-001',
            'nama_peserta'   => 'Budi Santoso',
            'perusahaan'     => 'PT Contoh Sejahtera',
            'materi'         => 'Excel Dasar',
            'awal_training'  => '2026-10-05',
            'akhir_training' => '2026-10-07',
            'keterangan'     => 'Catatan uji',
        ], $override);
    }

    private function buatSummary(array $override = []): CertificateSummary
    {
        return CertificateSummary::forceCreate($this->dataSummary($override));
    }

    // ----------------------------------------------------------------- index

    public function test_index_menampilkan_halaman(): void
    {
        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.index'))
            ->assertOk()
            ->assertViewIs('office.certificate.index')
            ->assertViewHasAll(['materis', 'perusahaans']);
    }

    // --------------------------------------------------------------- getData

    public function test_getData_hanya_rkm_status_0_dan_urut_tanggal_terbaru(): void
    {
        $lama = RKMFactory::new()->create(['tanggal_awal' => '2099-01-01', 'tanggal_akhir' => '2099-01-03']);
        $baru = RKMFactory::new()->create(['tanggal_awal' => '2099-03-01', 'tanggal_akhir' => '2099-03-03']);
        // Status lain dengan tanggal paling baru: tidak boleh muncul
        $lain = RKMFactory::new()->status('1')->create(['tanggal_awal' => '2099-06-01', 'tanggal_akhir' => '2099-06-03']);

        $response = $this->sebagai(self::VIEW)
            ->getJson(route('office.certificate.getData'))
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'pagination' => ['total', 'from', 'to', 'current_page', 'last_page', 'per_page'],
            ]);

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertSame([$baru->id, $lama->id], array_slice($ids, 0, 2));
        $this->assertNotContains($lain->id, $ids);
    }

    public function test_getData_pencarian_tanpa_hasil(): void
    {
        RKMFactory::new()->create(['tanggal_awal' => '2099-01-01', 'tanggal_akhir' => '2099-01-03']);

        $this->sebagai(self::VIEW)
            ->getJson(route('office.certificate.getData', ['search' => 'tidak-ada-xyz']))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // ---------------------------------------------------------------- detail

    public function test_detail_menampilkan_rkm(): void
    {
        $rkm = RKMFactory::new()->create();

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.detail', $rkm->id))
            ->assertOk()
            ->assertViewIs('office.certificate.detail')
            ->assertViewHas('rkm', fn ($v) => (int) $v->id === (int) $rkm->id);
    }

    public function test_detail_rkm_tidak_ada_404(): void
    {
        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.detail', 999999999))
            ->assertNotFound();
    }

    // ---------------------------------------------------------------- create

    public function test_create_peserta_belum_terdaftar_diarahkan_ke_detail(): void
    {
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();

        $this->sebagai(self::STORE)
            ->get(route('office.certificate.create', [$rkm->id, $peserta->id]))
            ->assertRedirect(route('office.certificate.detail', $rkm->id))
            ->assertSessionHas('error', 'Peserta tidak terdaftar di RKM ini.');
    }

    public function test_create_rkm_tidak_ada_404(): void
    {
        $peserta = PesertaFactory::new()->create();

        $this->sebagai(self::STORE)
            ->get(route('office.certificate.create', [999999999, $peserta->id]))
            ->assertNotFound();
    }

    // ----------------------------------------------------------------- store

    public function test_store_membuat_sertifikat_dan_menyimpan_pdf(): void
    {
        $this->mockPdf();
        $this->siapkanPenandatangan();
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create(); // sengaja tanpa Registrasi (lihat temuan)

        $response = $this->sebagai(self::STORE)
            ->post(route('office.certificate.store'), $this->payload($rkm, $peserta));

        $cert = Certificate::where('nomor_sertifikat', '026083')->firstOrFail();

        $response->assertRedirect(route('office.certificate.show', $cert->id))
            ->assertSessionHas('success', 'Sertifikat berhasil di-generate!');
        $this->assertEquals($rkm->id, $cert->rkm_id);
        $this->assertEquals($peserta->id, $cert->id_peserta);
        $this->assertSame('2026-10-05 - 2026-10-07', $cert->tanggal_pelatihan);
        $this->assertNull($cert->tanggal_pelatihan2);
        $this->assertSame('certificates/026083.pdf', $cert->pdf_path);
        Storage::assertExists('public/certificates/026083.pdf');
    }

    public function test_store_menyimpan_rentang_tanggal_kedua(): void
    {
        $this->mockPdf();
        $this->siapkanPenandatangan();
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();

        $this->sebagai(self::STORE)->post(route('office.certificate.store'), $this->payload($rkm, $peserta, [
            'tanggal_awal2'  => '2026-11-02',
            'tanggal_akhir2' => '2026-11-04',
        ]));

        $this->assertSame(
            '2026-11-02 - 2026-11-04',
            Certificate::where('nomor_sertifikat', '026083')->firstOrFail()->tanggal_pelatihan2
        );
    }

    public static function payloadTidakValid(): array
    {
        return [
            'rkm_id kosong'                => [['rkm_id' => null], 'rkm_id'],
            'rkm_id tidak ada'             => [['rkm_id' => 999999999], 'rkm_id'],
            'id_peserta kosong'            => [['id_peserta' => null], 'id_peserta'],
            'id_peserta tidak ada'         => [['id_peserta' => 999999999], 'id_peserta'],
            'nama_peserta kosong'          => [['nama_peserta' => ''], 'nama_peserta'],
            'nama_materi kosong'           => [['nama_materi' => ''], 'nama_materi'],
            'tanggal_awal bukan tanggal'   => [['tanggal_awal' => 'bukan-tanggal'], 'tanggal_awal'],
            'tanggal_akhir sebelum awal'   => [['tanggal_akhir' => '2026-10-01'], 'tanggal_akhir'],
            'tanggal_akhir2 sebelum awal2' => [['tanggal_awal2' => '2026-11-05', 'tanggal_akhir2' => '2026-11-01'], 'tanggal_akhir2'],
        ];
    }

    #[DataProvider('payloadTidakValid')]
    public function test_store_validasi_gagal(array $override, string $field): void
    {
        $this->mockPdf();
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();
        $sebelum = Certificate::count();

        $this->sebagai(self::STORE)
            ->post(route('office.certificate.store'), $this->payload($rkm, $peserta, $override))
            ->assertSessionHasErrors($field);

        $this->assertSame($sebelum, Certificate::count());
    }

    /** TEMUAN: nomor_sertifikat tidak divalidasi, jadi kosong = error SQL, bukan pesan validasi. */
    public function test_store_tanpa_nomor_sertifikat_error_sql(): void
    {
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();

        $this->withoutExceptionHandling();
        $this->expectException(QueryException::class);

        $this->sebagai(self::STORE)->post(
            route('office.certificate.store'),
            $this->payload($rkm, $peserta, ['nomor_sertifikat' => null])
        );
    }

    /** TEMUAN: nomor duplikat juga error SQL (kolom UNIQUE), bukan validasi. */
    public function test_store_nomor_duplikat_error_sql(): void
    {
        CertificateFactory::new()->create(['nomor_sertifikat' => '026083']);
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();

        $this->withoutExceptionHandling();
        $this->expectException(QueryException::class);

        $this->sebagai(self::STORE)->post(
            route('office.certificate.store'),
            $this->payload($rkm, $peserta)
        );
    }

    // ------------------------------------------------- show / preview / download

    public function test_show_menampilkan_sertifikat(): void
    {
        $this->siapkanPenandatangan();
        $cert = CertificateFactory::new()->create();

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.show', $cert->id))
            ->assertOk()
            ->assertViewIs('office.certificate.show')
            ->assertViewHasAll(['certificate', 'penandatangan']);
    }

    public function test_show_tidak_ada_404(): void
    {
        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.show', 999999999))
            ->assertNotFound();
    }

    /** TEMUAN: view show error kalau tanggal_pelatihan tidak berformat "awal - akhir". */
    public function test_show_error_kalau_format_tanggal_pelatihan_tidak_sesuai(): void
    {
        $this->siapkanPenandatangan();
        $cert = CertificateFactory::new()->create(['tanggal_pelatihan' => '06 Oktober 2026']);

        $this->withoutExceptionHandling();
        $this->expectException(ViewException::class);

        $this->sebagai(self::VIEW)->get(route('office.certificate.show', $cert->id));
    }

    public function test_preview_mengirim_pdf(): void
    {
        $this->mockPdf();
        $cert = CertificateFactory::new()->create(['nomor_sertifikat' => 'CERT/026083']);

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.preview', $cert->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_download_mengirim_file_dengan_nama_aman(): void
    {
        Storage::put('public/certificates/abc.pdf', '%PDF-fake');
        $cert = CertificateFactory::new()->denganPdf('certificates/abc.pdf')
            ->create(['nomor_sertifikat' => 'CERT/026083']);

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.download', $cert->id))
            ->assertOk()
            ->assertDownload('CERT-026083.pdf'); // "/" diganti "-"
    }

    public function test_download_tanpa_file_kembali_dengan_error(): void
    {
        $cert = CertificateFactory::new()->create(); // pdf_path null

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.download', $cert->id))
            ->assertRedirect()
            ->assertSessionHas('error', 'File PDF tidak ditemukan');
    }

    public function test_downloadByPeserta_satu_sertifikat_langsung_diunduh(): void
    {
        Storage::put('public/certificates/abc.pdf', '%PDF-fake');
        $cert = CertificateFactory::new()->denganPdf('certificates/abc.pdf')
            ->create(['nomor_sertifikat' => 'CERT/026083']);

        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.downloadByPeserta', [$cert->rkm_id, $cert->id_peserta]))
            ->assertOk()
            ->assertDownload('CERT-026083.pdf');
    }

    public function test_downloadByPeserta_tanpa_sertifikat_error(): void
    {
        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.downloadByPeserta', [1, 1]))
            ->assertRedirect()
            ->assertSessionHas('error', 'File PDF tidak ditemukan');
    }

    // Cabang ZIP (>1 sertifikat) belum diuji: controller menulis ke storage_path()
    // langsung, bukan lewat Storage facade, jadi perlu penanganan terpisah.

    // ---------------------------------------------------------------- delete

    public function test_delete_menghapus_sertifikat_pasangan_rkm_dan_peserta(): void
    {
        $cert = CertificateFactory::new()->create();
        $lain = CertificateFactory::new()->create();

        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.delete', [$cert->rkm_id, $cert->id_peserta]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Sertifikat berhasil dihapus!');

        $this->assertDatabaseMissing(Certificate::class, ['id' => $cert->id]);
        $this->assertDatabaseHas(Certificate::class, ['id' => $lain->id]);
    }

    public function test_delete_menghapus_semua_sertifikat_pasangan_yang_sama(): void
    {
        $rkm     = RKMFactory::new()->create();
        $peserta = PesertaFactory::new()->create();
        CertificateFactory::new()->count(2)->create(['rkm_id' => $rkm->id, 'id_peserta' => $peserta->id]);

        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.delete', [$rkm->id, $peserta->id]));

        $this->assertSame(0, Certificate::where('rkm_id', $rkm->id)->where('id_peserta', $peserta->id)->count());
    }

    /** TEMUAN: tetap "berhasil" walau tidak ada baris yang terhapus. */
    public function test_delete_pasangan_tidak_ada_tetap_menjawab_berhasil(): void
    {
        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.delete', [999999999, 999999999]))
            ->assertSessionHas('success', 'Sertifikat berhasil dihapus!');
    }

    /** TEMUAN: file PDF tidak ikut terhapus. */
    public function test_delete_tidak_menghapus_file_pdf(): void
    {
        Storage::put('public/certificates/abc.pdf', '%PDF-fake');
        $cert = CertificateFactory::new()->denganPdf('certificates/abc.pdf')->create();

        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.delete', [$cert->rkm_id, $cert->id_peserta]));

        Storage::assertExists('public/certificates/abc.pdf');
    }

    // ------------------------------------------------------------ rekap (summary)

    public function test_halaman_rekap(): void
    {
        $this->sebagai(self::VIEW)
            ->get(route('office.certificate.certificateSummary'))
            ->assertOk()
            ->assertViewIs('office.certificate.rekap')
            ->assertViewHasAll(['perusahaans', 'materis']);
    }

    public function test_rekap_json_struktur_dan_hitungan(): void
    {
        $this->buatSummary(['type' => 'Reg Digital']);
        CertificateFactory::new()->create();

        $response = $this->sebagai(self::VIEW)
            ->getJson(route('office.certificate.certificateSummaryJson'))
            ->assertOk()
            ->assertJsonStructure([
                'filter'     => ['period', 'year', 'month', 'quarter', 'start_date', 'end_date'],
                'regDigital' => ['count', 'data'],
                'authorized' => ['count', 'data'],
                'bandung'    => ['count', 'data'],
                'bnsp'       => ['count', 'data'],
                'inixcert'   => ['count', 'data'],
                'workshop'   => ['count', 'data'],
                'webinar'    => ['count', 'data'],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('regDigital.count'));
        $this->assertGreaterThanOrEqual(1, $response->json('bandung.count'));
    }

    public static function periode(): array
    {
        return [
            'tahun'     => [['period' => 'year', 'year' => 2026], '2026-01-01', '2026-12-31'],
            'kuartal 2' => [['period' => 'quarter', 'year' => 2026, 'quarter' => 2], '2026-04-01', '2026-06-30'],
            'bulan feb' => [['period' => 'month', 'year' => 2026, 'month' => 2], '2026-02-01', '2026-02-28'],
        ];
    }

    #[DataProvider('periode')]
    public function test_rekap_json_rentang_tanggal_sesuai_periode(array $query, string $awal, string $akhir): void
    {
        $this->sebagai(self::VIEW)
            ->getJson(route('office.certificate.certificateSummaryJson', $query))
            ->assertOk()
            ->assertJsonPath('filter.start_date', $awal)
            ->assertJsonPath('filter.end_date', $akhir);
    }

    public function test_storeSummary_membuat_data(): void
    {
        $this->sebagai(self::STORE)
            ->post(route('office.certificate.storeSummary'), $this->dataSummary())
            ->assertRedirect()
            ->assertSessionHas('success', 'Certificate summary created successfully.');

        $this->assertDatabaseHas(CertificateSummary::class, [
            'nama_peserta' => 'Budi Santoso',
            'perusahaan'   => 'PT Contoh Sejahtera',
            'materi'       => 'Excel Dasar',
            'type'         => 'Webinar',
        ]);
    }

    public static function summaryTidakValid(): array
    {
        return [
            'type tidak dikenal'          => [['type' => 'Lainnya'], 'type'],
            'nama_peserta kosong'         => [['nama_peserta' => ''], 'nama_peserta'],
            'perusahaan kosong'           => [['perusahaan' => ''], 'perusahaan'],
            'materi kosong'               => [['materi' => ''], 'materi'],
            'awal_training kosong'        => [['awal_training' => ''], 'awal_training'],
            'akhir_training sebelum awal' => [['akhir_training' => '2026-10-01'], 'akhir_training'],
        ];
    }

    #[DataProvider('summaryTidakValid')]
    public function test_storeSummary_validasi_gagal(array $override, string $field): void
    {
        $sebelum = CertificateSummary::count();

        $this->sebagai(self::STORE)
            ->post(route('office.certificate.storeSummary'), $this->dataSummary($override))
            ->assertSessionHasErrors($field);

        $this->assertSame($sebelum, CertificateSummary::count());
    }

    public function test_updateSummary_mengubah_data(): void
    {
        $summary = $this->buatSummary();

        $this->sebagai(self::STORE)
            ->put(route('office.certificate.updateSummary', $summary->id), $this->dataSummary([
                'nama_peserta' => 'Nama Baru',
                'type'         => 'Reg Digital',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Certificate summary updated successfully.');

        $this->assertDatabaseHas(CertificateSummary::class, [
            'id'           => $summary->id,
            'nama_peserta' => 'Nama Baru',
            'type'         => 'Reg Digital',
        ]);
    }

    #[DataProvider('summaryTidakValid')]
    public function test_updateSummary_validasi_gagal(array $override, string $field): void
    {
        $summary = $this->buatSummary();

        $this->sebagai(self::STORE)
            ->put(route('office.certificate.updateSummary', $summary->id), $this->dataSummary($override))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas(CertificateSummary::class, ['id' => $summary->id, 'nama_peserta' => 'Budi Santoso']);
    }

    public function test_updateSummary_tidak_ada_404(): void
    {
        $this->sebagai(self::STORE)
            ->put(route('office.certificate.updateSummary', 999999999), $this->dataSummary())
            ->assertNotFound();
    }

    public function test_deleteSummary_menghapus_data(): void
    {
        $summary = $this->buatSummary();

        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.deleteSummary', $summary->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Certificate summary deleted successfully.');

        $this->assertNull(CertificateSummary::find($summary->id));
    }

    public function test_deleteSummary_tidak_ada_404(): void
    {
        $this->sebagai(self::DELETE)
            ->delete(route('office.certificate.deleteSummary', 999999999))
            ->assertNotFound();
    }
}