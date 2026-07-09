<?php

namespace App\Traits;

use App\Services\KPI\Jabatan\GMKPIService;
use App\Services\KPI\Jabatan\CustomerCareKPIService;
use App\Services\KPI\Jabatan\FinanceAccountingKPIService;
use App\Services\KPI\Jabatan\HRDKPIService;
use App\Services\KPI\Jabatan\DriverKPIService;
use App\Services\KPI\Jabatan\OfficeBoyKPIService;
use App\Services\KPI\Jabatan\DivisiITSMKPIService;
use App\Services\KPI\Jabatan\KoordinatorITSMKPIService;
use App\Services\KPI\Jabatan\ProgrammerKPIService;
use App\Services\KPI\Jabatan\TimDigitalKPIService;
use App\Services\KPI\Jabatan\ProjectAdminKPIService;
use App\Services\KPI\Jabatan\TechnicalSupportKPIService;
use App\Services\KPI\Jabatan\InstrukturKPIService;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use App\Services\KPI\Jabatan\SalesKPIService;
use App\Services\KPI\Jabatan\SPVSalesKPIService;
use App\Services\KPI\Jabatan\ADMSalesKPIService;
use App\Services\KPI\Jabatan\AdminHoldingKPIService;

trait KPIResolverTrait
{
    protected function resolveProgress($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        $asistantRoute = strtolower($detail->dataTarget->asistant_route ?? '');

        $progress = match ($asistantRoute) {
            'kepuasan pelanggan' => app(GMKPIService::class)->calculateProgressKepuasanPelanggan($item, $personId),
            'pemasukan kotor' => app(GMKPIService::class)->calculatePemasukanKotor($item, $personId),
            'pemasukan bersih' => app(GMKPIService::class)->calculatePemasukanBersih($item, $personId),
            'target penjualan project tahunan' => app(GMKPIService::class)->calculateTargetPenjualanProjectTahunan($item, $personId),
            'rasio biaya operasional terhadap revenue' => app(GMKPIService::class)->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId),
            'performa kpi departemen' => app(GMKPIService::class)->calculatePerformaKPIDepartemen($item, $personId),

            'peserta puas dengan pelayanan dan fasilitas training' => app(CustomerCareKPIService::class)->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId),
            'dorong inovasi pelayanan' => app(CustomerCareKPIService::class)->calculateDorongInovasiPelayanan($item, $personId),
            'penanganan komplain perseta', 'penanganan komplain peserta' => app(CustomerCareKPIService::class)->calculatePenangananKomplainPerseta($item, $personId),
            'report persiapan kelas' => app(CustomerCareKPIService::class)->calculateReportPersiapanKelas($item, $personId),

            'outstanding' => app(FinanceAccountingKPIService::class)->calculateOutstanding($item, $personId),
            'inisiatif efisiensi keuangan' => app(FinanceAccountingKPIService::class)->calculateInisiatifEfisiensiKeuangan($item, $personId),
            'mengurangi manual work dan error' => app(FinanceAccountingKPIService::class)->calculateMengurangiManualWorkDanError($item, $personId),
            'laporan analisis keuangan' => app(FinanceAccountingKPIService::class)->calculateLaporanAnalisisKeuangan($item, $personId),
            'pencairan biaya operasional' => app(FinanceAccountingKPIService::class)->calculatePencairanBiayaOperasional($item, $personId),
            'penyelesaian tagihan perusahaan' => app(FinanceAccountingKPIService::class)->calculatePenyelesaianTagihanPerusahaan($item, $personId),
            'akurasi pencatatan masuk' => app(FinanceAccountingKPIService::class)->calculateAkurasiPencatatanMasuk($item, $personId),

            'pelaksanaan kegiatan karyawan' => app(HRDKPIService::class)->calculatePelaksanaanKegiatanKaryawan($item, $personId),
            'pengeluaran biaya karyawan' => app(HRDKPIService::class)->calculatePengeluaranBiayaKaryawan($item, $personId),
            'administrasi karyawan' => app(HRDKPIService::class)->calculateAdministrasiKaryawan($item, $personId),

            'perbaikan kendaraan' => app(DriverKPIService::class)->calculatePerbaikanKendaraan($item, $personId),
            'kontrol pengeluaran transportasi' => app(DriverKPIService::class)->calculateKontrolPengeluaranTransportasi($item, $personId),
            'report kondisi kendaraan' => app(DriverKPIService::class)->calculateReportKondisiKendaraan($item, $personId),
            'feedback kenyamanan berkendaran', 'feedback kenyamanan berkendara' => app(DriverKPIService::class)->calculateFeedbackKenyamananBerkendara($item, $personId),

            'ketepatan waktu po' => app(AdminHoldingKPIService::class)->calculateKetepatanWaktuPo($item, $personId),
            'kualitas dokumentasi support dan proctor' => app(AdminHoldingKPIService::class)->calculatekualitasDokumentasiSupportDanProctor($item, $personId),

            'feedback kebersihan dan kenyamanan' => app(OfficeBoyKPIService::class)->calculateFeedbackKebersihanDanKenyamanan($item, $personId),
            'penyelesaian tugas harian' => app(OfficeBoyKPIService::class)->calculatePenyelesaianTugasHarian($item, $personId),

            'kepuasan client itsm' => app(DivisiITSMKPIService::class)->calculateProgressKepuasanClientITSM($item, $personId),
            'inovation adaption rate' => app(DivisiITSMKPIService::class)->calculateInovationAdaptionRate($item, $personId),

            'availability sistem internal kritis' => app(KoordinatorITSMKPIService::class)->calculateAvailabilitySistemInternalKritis($item, $personId),
            'meningkatkan kepuasan dan loyalitas peserta/client' => app(KoordinatorITSMKPIService::class)->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId),
            'persentase gap kompetensi tim terhadap standar skill' => app(KoordinatorITSMKPIService::class)->calculatePersentaseGapKompetensi($item, $personId),

            'ketepatan waktu penyelesaian fitur' => app(ProgrammerKPIService::class)->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId),
            'mengukur kualitas aplikasi agar minim bug' => app(ProgrammerKPIService::class)->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId),

            'konsistensi campaign digital' => app(TimDigitalKPIService::class)->calculateKonsistensiCampaignDigital($item, $personId),
            'efektifitas digital marketing' => app(TimDigitalKPIService::class)->calculateEfektifitasDiitalMarketing($item, $personId),

            'pendapatan penjualan project' => app(ProjectAdminKPIService::class)->calculatePendapatanPenjualanProject($item, $personId),
            'leads project' => app(ProjectAdminKPIService::class)->calculateLeadsProject($item, $personId),

            'keberhasilan support memenuhi sla' => app(TechnicalSupportKPIService::class)->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId),
            'kualitas layanan exam' => app(TechnicalSupportKPIService::class)->calculateKualitasLayananExam($item, $personId),

            'kepuasan peserta pelatihan' => app(InstrukturKPIService::class)->calculateKepuasanPesertaPelatihan($item, $personId),
            'upseling lanjutan materi' => app(InstrukturKPIService::class)->calculateUpselingLanjutanMateri($item, $personId),
            'sertifikasi kompetensi internal' => app(InstrukturKPIService::class)->calculateSertifikasiKompetensiInternal($item, $personId),
            'pelatihan kompetensi eksternal' => app(InstrukturKPIService::class)->calculatePelatihanKompetensiEksternal($item, $personId),
            'presentase kinerja instruktur' => app(InstrukturKPIService::class)->calculatePresentaseKinerjaInstruktur($item, $personId),

            'pengembangan kurikulum pelatihan' => app(EducationManagerKPIService::class)->calculatePengembanganKurikulumPelatihan($item, $personId),
            'peningkatan knowledge sharing' => app(EducationManagerKPIService::class)->calculatePeningkatanKnowledgeSharing($item, $personId),
            'peningkatan kontribusi pelatihan' => app(EducationManagerKPIService::class)->calculatePeningkatanKontribusiPelatihan($item, $personId),
            'evaluasi kinerja instruktur' => app(EducationManagerKPIService::class)->calculateEvaluasiKinerjaInstruktur($item, $personId),
            'pembuatan artikel' => app(EducationManagerKPIService::class)->calculatePembuatanArtikel($item, $personId),

            'target penjualan tahunan' => app(SalesKPIService::class)->calculateTargetPenjualanTahunan($item, $personId),
            'peningkatan kemampuan kompetensi sales' => app(SalesKPIService::class)->calculatePeningkatanKemampuanKompetensiSales($item, $personId),
            'customer acquisition cost' => app(SalesKPIService::class)->calculateCustomerAcquisitionCost($item, $personId),

            'meningkatkan revenue perusahaan' => app(SPVSalesKPIService::class)->calculateMeningkatkanRevenuePerusahaan($item, $personId),
            'evaluasi kinerja sales' => app(SPVSalesKPIService::class)->calculateEvaluasiKinerjaSales($item, $personId),
            'biaya akuisisi perclient' => app(SPVSalesKPIService::class)->calculateBiayaAkuisisiClient($item, $personId),

            'laporan mom' => app(ADMSalesKPIService::class)->calculateLaporanMOM($item, $personId),
            'akurasi kelengkapan data penjualan' => app(ADMSalesKPIService::class)->calculateAkurasiKelengkapanDataPenjualan($item, $personId),
            'todo administrasi' => app(ADMSalesKPIService::class)->calculateTodoAdministrasi($item, $personId),

            default => 0
        };

        $nilaiTarget = (float) ($detail->dataTarget->nilai_target ?? $detail->nilai_target ?? 0);
        return $nilaiTarget > 0 ? min($progress, $nilaiTarget) : $progress;
    }

    protected function getCalculationByRoute($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();
        $route  = strtolower($detail->dataTarget?->asistant_route ?? '');

        return match ($route) {
            'kepuasan pelanggan' => app(GMKPIService::class)->calculateProgressKepuasanPelangganDetail($itemDetail, $personId),
            'pemasukan kotor' => app(GMKPIService::class)->calculatePemasukanKotorDetail($itemDetail, $personId),
            'pemasukan bersih' => app(GMKPIService::class)->calculatePemasukanBersihDetail($itemDetail, $personId),
            'target penjualan project tahunan' => app(GMKPIService::class)->calculateTargetPenjualanProjectTahunanDetail($itemDetail, $personId),
            'rasio biaya operasional terhadap revenue' => app(GMKPIService::class)->calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail, $personId),
            'performa kpi departemen' => app(GMKPIService::class)->calculatePerformaKPIDepartemenDetail($itemDetail, $personId),

            'peserta puas dengan pelayanan dan fasilitas training' => app(CustomerCareKPIService::class)->calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail, $personId),
            'dorong inovasi pelayanan' => app(CustomerCareKPIService::class)->calculateDorongInovasiPelayananDetail($itemDetail, $personId),
            'penanganan komplain perseta', 'penanganan komplain peserta' => app(CustomerCareKPIService::class)->calculatePenangananKomplainPersetaDetail($itemDetail, $personId),
            'report persiapan kelas' => app(CustomerCareKPIService::class)->calculateReportPersiapanKelasDetail($itemDetail, $personId),

            'outstanding' => app(FinanceAccountingKPIService::class)->calculateOutstandingDetail($itemDetail, $personId),
            'inisiatif efisiensi keuangan' => app(FinanceAccountingKPIService::class)->calculateInisiatifEfisiensiKeuanganDetail($itemDetail, $personId),
            'mengurangi manual work dan error' => app(FinanceAccountingKPIService::class)->calculateMengurangiManualWorkDanErrorDetail($itemDetail, $personId),
            'laporan analisis keuangan' => app(FinanceAccountingKPIService::class)->calculateLaporanAnalisisKeuanganDetail($itemDetail, $personId),
            'pencairan biaya operasional' => app(FinanceAccountingKPIService::class)->calculatePencairanBiayaOperasionalDetail($itemDetail, $personId),
            'penyelesaian tagihan perusahaan' => app(FinanceAccountingKPIService::class)->calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId),
            'akurasi pencatatan masuk' => app(FinanceAccountingKPIService::class)->calculateAkurasiPencatatanMasukDetail($itemDetail, $personId),

            'pelaksanaan kegiatan karyawan' => app(HRDKPIService::class)->calculatePelaksanaanKegiatanKaryawanDetail($itemDetail, $personId),
            'pengeluaran biaya karyawan' => app(HRDKPIService::class)->calculatePengeluaranBiayaKaryawanDetail($itemDetail, $personId),
            'administrasi karyawan' => app(HRDKPIService::class)->calculateAdministrasiKaryawanDetail($itemDetail, $personId),

            'perbaikan kendaraan' => app(DriverKPIService::class)->calculatePerbaikanKendaraanDetail($itemDetail, $personId),
            'kontrol pengeluaran transportasi' => app(DriverKPIService::class)->calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId),
            'report kondisi kendaraan' => app(DriverKPIService::class)->calculateReportKondisiKendaraanDetail($itemDetail, $personId),
            'feedback kenyamanan berkendara', 'feedback kenyamanan berkendaran' => app(DriverKPIService::class)->calculateFeedbackKenyamananBerkendaraDetail($itemDetail, $personId),

            'ketepatan waktu po' => app(AdminHoldingKPIService::class)->calculateKetepatanWaktuPoDetail($itemDetail, $personId),
            'kualitas dokumentasi support dan proctor' => app(AdminHoldingKPIService::class)->calculatekualitasDokumentasiSupportDanProctorDetail($itemDetail, $personId),

            'feedback kebersihan dan kenyamanan' => app(OfficeBoyKPIService::class)->calculateFeedbackKebersihanDanKenyamananDetail($itemDetail, $personId),
            'penyelesaian tugas harian' => app(OfficeBoyKPIService::class)->calculatePenyelesaianTugasHarianDetail($itemDetail, $personId),

            'kepuasan client itsm' => app(DivisiITSMKPIService::class)->calculateProgressKepuasanClientITSMDetail($itemDetail, $personId),
            'inovation adaption rate' => app(DivisiITSMKPIService::class)->calculateInovationAdaptionRateDetail($itemDetail, $personId),

            'availability sistem internal kritis' => app(KoordinatorITSMKPIService::class)->calculateAvailabilitySistemInternalKritisDetail($itemDetail, $personId),
            'meningkatkan kepuasan dan loyalitas peserta/client' => app(KoordinatorITSMKPIService::class)->calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail, $personId),
            'persentase gap kompetensi tim terhadap standar skill' => app(KoordinatorITSMKPIService::class)->calculatePersentaseGapKompetensiDetail($itemDetail, $personId),

            'ketepatan waktu penyelesaian fitur' => app(ProgrammerKPIService::class)->calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId),
            'mengukur kualitas aplikasi agar minim bug' => app(ProgrammerKPIService::class)->calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId),

            'konsistensi campaign digital' => app(TimDigitalKPIService::class)->calculateKonsistensiCampaignDigitalDetail($itemDetail, $personId),
            'efektifitas digital marketing' => app(TimDigitalKPIService::class)->calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId),

            'pendapatan penjualan project' => app(ProjectAdminKPIService::class)->calculatePendapatanPenjualanProjectDetail($itemDetail, $personId),
            'leads project' => app(ProjectAdminKPIService::class)->calculateLeadsProjectDetail($itemDetail, $personId),

            'keberhasilan support memenuhi sla' => app(TechnicalSupportKPIService::class)->calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId),
            'kualitas layanan exam' => app(TechnicalSupportKPIService::class)->calculateKualitasLayananExamDetail($itemDetail, $personId),

            'kepuasan peserta pelatihan' => app(InstrukturKPIService::class)->calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId),
            'upseling lanjutan materi' => app(InstrukturKPIService::class)->calculateUpselingLanjutanMateriDetail($itemDetail, $personId),
            'sertifikasi kompetensi internal' => app(InstrukturKPIService::class)->calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId),
            'pelatihan kompetensi eksternal' => app(InstrukturKPIService::class)->calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId),
            'presentase kinerja instruktur' => app(InstrukturKPIService::class)->calculatePresentaseKinerjaInstrukturDetail($itemDetail, $personId),

            'pengembangan kurikulum pelatihan' => app(EducationManagerKPIService::class)->calculatePengembanganKurikulumPelatihanDetail($itemDetail, $personId),
            'peningkatan knowledge sharing' => app(EducationManagerKPIService::class)->calculatePeningkatanKnowledgeSharingDetail($itemDetail, $personId),
            'peningkatan kontribusi pelatihan' => app(EducationManagerKPIService::class)->calculatePeningkatanKontribusiPelatihanDetail($itemDetail, $personId),
            'evaluasi kinerja instruktur' => app(EducationManagerKPIService::class)->calculateEvaluasiKinerjaInstrukturDetail($itemDetail, $personId),
            'pembuatan artikel' => app(EducationManagerKPIService::class)->calculatePembuatanArtikelDetail($itemDetail, $personId),

            'target penjualan tahunan' => app(SalesKPIService::class)->calculateTargetPenjualanTahunanDetail($itemDetail, $personId),
            'peningkatan kemampuan kompetensi sales' => app(SalesKPIService::class)->calculatePeningkatanKemampuanKompetensiSalesDetail($itemDetail, $personId),
            'customer acquisition cost' => app(SalesKPIService::class)->calculateCustomerAcquisitionCostDetail($itemDetail, $personId),

            'meningkatkan revenue perusahaan' => app(SPVSalesKPIService::class)->calculateMeningkatkanRevenuePerusahaanDetail($itemDetail, $personId),
            'evaluasi kinerja sales' => app(SPVSalesKPIService::class)->calculateEvaluasiKinerjaSalesDetail($itemDetail, $personId),
            'biaya akuisisi perclient' => app(SPVSalesKPIService::class)->calculateBiayaAkuisisiClientDetail($itemDetail, $personId),

            'laporan mom' => app(ADMSalesKPIService::class)->calculateLaporanMOMDetail($itemDetail, $personId),
            'akurasi kelengkapan data penjualan' => app(ADMSalesKPIService::class)->calculateAkurasiKelengkapanDataPenjualanDetail($itemDetail, $personId),
            'todo administrasi' => app(ADMSalesKPIService::class)->calculateTodoAdministrasiDetail($itemDetail, $personId),

            default => [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
                'monthly_progress' => [],
                'daily_progress_per_month' => [],
            ]
        };
    }
}
