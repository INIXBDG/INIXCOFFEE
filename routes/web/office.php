<?php

use App\Http\Controllers\AdministrasiKaryawanController;
use App\Http\Controllers\ApprovalPendapatanController;
use App\Http\Controllers\DaftarTugasController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KomisiSalesController;
use App\Http\Controllers\KoordinasiOfficeBoyController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\office\AnalysisFinanceController;
use App\Http\Controllers\office\AnalysisReportController;
use App\Http\Controllers\office\BiayaTransportasiController;
use App\Http\Controllers\office\CertificateController;
use App\Http\Controllers\office\KelengkapanDataController;
use App\Http\Controllers\office\KondisiToolsController;
use App\Http\Controllers\office\ModulController;
use App\Http\Controllers\office\OfficeController;
use App\Http\Controllers\office\OfficeExamController;
use App\Http\Controllers\office\pickupDriverController;
use App\Http\Controllers\office\PoExamSertifaController;
use App\Http\Controllers\office\TagihanPerusahaanController;
use App\Http\Controllers\office\vendorOfficeController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PicPenagihanController;
use App\Http\Controllers\RekomendasiLanjutanController;
use App\Http\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 15. OFFICE & OPERATIONAL
// =========================================================================
// Inventaris
Route::prefix('inventaris')->group(function () {
    Route::get('/index', [InventarisController::class, 'index'])->name('IndexInventaris');
    Route::post('/input/barang', [InventarisController::class, 'inputinventaris'])->name('InputInventaris');
    Route::get('/show/data/{id}', [InventarisController::class, 'editview'])->name('EditView');
    Route::put('/update/{id}', [InventarisController::class, 'user'])->name('UpdatePengguna');
    Route::post('/add/service/{id}', [InventarisController::class, 'addservice'])->name('AddService');
    Route::post('/add/check/{id}', [InventarisController::class, 'addcheck'])->name('AddCheck');
    Route::delete('/delete/data/{id}', [InventarisController::class, 'deletedata'])->name('DeleteDataInventaris');
    Route::post('/create/kode', [InventarisController::class, 'createKode'])->name('CreateKodeIinvetaris');
    Route::post('/import', [InventarisController::class, 'import'])->name('ImportDataInventaris');
    Route::get('/export', [InventarisController::class, 'export'])->name('inventaris.export');
});

// Shift Management
Route::get('/shift/pending', [DaftarTugasController::class, 'getPendingShift'])->name('shift.pending');
Route::post('/shift/approve', [DaftarTugasController::class, 'approveShift'])->name('shift.approve');
Route::post('/shift/reject', [DaftarTugasController::class, 'rejectShift'])->name('shift.reject');

// Rekomendasi Lanjutan (Global Prefix)
Route::prefix('rekomendasi-lanjutan')->name('rekomendasiLanjutan.')->group(function () {
    Route::get('/index', [RekomendasiLanjutanController::class, 'index'])->name('index');
    Route::get('/get/{year}/{month}', [RekomendasiLanjutanController::class, 'showMonth']);
    Route::post('/store', [RekomendasiLanjutanController::class, 'store'])->name('store');
});

// Office Prefix (Tanpa Name Prefix Global agar nama route asli tetap terjaga)
Route::prefix('office')->group(function () {
    Route::get('/dashboard', [OfficeController::class, 'dashboard'])->name('office.dashboard');
    Route::get('/api/dashboard/karyawan', [OfficeController::class, 'apiDashboardKaryawan'])->name('office.api.karyawan');
    Route::get('/api/dashboard/kehadiran', [OfficeController::class, 'apiDashboardKehadiran'])->name('office.api.kehadiran');
    Route::get('/api/dashboard/ticket', [OfficeController::class, 'apiDashboardTicket'])->name('office.api.ticket');
    Route::get('/api/dashboard/rkm', [OfficeController::class, 'apiDashboardRKM'])->name('office.api.rkm');
    Route::get('/api/dashboard/tagihan', [OfficeController::class, 'apiDashboardTagihan'])->name('office.api.tagihan');
    Route::get('/api/dashboard/administrasi', [OfficeController::class, 'apiDashboardAdministrasi'])->name('office.api.administrasi');
    Route::get('/api/dashboard/all-data', [OfficeController::class, 'apiDashboardAllData'])->name('office.api.all-data');
    Route::get('/data-cuti', [OfficeController::class, 'dataCuti']);
    Route::get('/data-mengajar', [OfficeController::class, 'dataMengajar']);
    Route::get('/detail-data-mengajar/{id}', [OfficeController::class, 'detailMengajar']);
    Route::post('/import-tagihan', [TagihanPerusahaanController::class, 'importTagihanPerusahaan'])->name('importTagihanPerusahaan');
    Route::post('/hapus-semua-tagihan', [TagihanPerusahaanController::class, 'hapusSemuaTagihanPerusahaan'])->name('hapusSemuaTagihanPerusahaan');
    Route::get('/karyawan-information/{id}', [OfficeController::class, 'dataKaryawan']);
    Route::get('/laporan/status-karyawan', [OfficeController::class, 'laporanStatusKaryawan'])->name('office.laporan.status-karyawan');
    Route::get('/laporan/status-karyawan/detail', [OfficeController::class, 'detailKaryawanStatus'])->name('office.laporan.status-karyawan.detail');
    Route::get('/laporan/trend-karyawan', [OfficeController::class, 'laporanTrendKaryawan'])->name('office.laporan.trend-karyawan');
    Route::get('/export/status-karyawan-pdf', [OfficeController::class, 'exportStatusKaryawanPdf'])->name('office.export.status-karyawan-pdf');
    Route::get('/dashboard/tunjangan', [OfficeController::class, 'getDashboardTunjanganOffice'])->name('office.dashboard.tunjangan');
    Route::get('/export/tunjangan-pdf', [OfficeController::class, 'exportTunjanganPdf'])->name('office.export.tunjangan.pdf');
    Route::get('/export-gaji-pdf', [OfficeController::class, 'exportPdfGaji']);
    Route::post('/store-manual-tagihan', [TagihanPerusahaanController::class, 'storeManualBanyak'])->name('storeManualTagihanPerusahaan');
    Route::get('/detail-tagihan-json/{id}', [TagihanPerusahaanController::class, 'detailJson'])->name('detailJsonTagihanPerusahaan');

    Route::post('/store-hari-libur', [OfficeController::class, 'storeHariLibur'])->name('storeHariLibur');
    Route::get('/data-hari-libur/{year}', [OfficeController::class, 'dataHariLibur']);
    Route::get('/data-hari-libur/edit/{id}', [OfficeController::class, 'editHariLibur'])->name('editHariLibur');
    Route::post('/data-hari-libur/update/{id}', [OfficeController::class, 'updateHariLibur'])->name('updateHariLibur');
    Route::post('/data-hari-libur/delete/{id}', [OfficeController::class, 'deleteHariLibur'])->name('deleteHariLibur');

    Route::get('/table/outstanding', [OfficeController::class, 'TableOutstanding'])->name('office.table.outstanding');
    Route::get('/grafik/outstanding', [OfficeController::class, 'GrafikOutstanding'])->name('office.grafik.outstanding');
    Route::get('/grafik/ketepatan-waktu', [OfficeController::class, 'GrafikKetepatanWaktu'])->name('office.grafik.ketepatan.waktu');

    Route::get('/get-exam', [OfficeController::class, 'getAllExam'])->name('office.table.exam');
    Route::get('/checklist-exam/{id}', [OfficeController::class, 'getExam'])->name('office.checklist-exam');
    Route::post('/checklist-exam/store', [OfficeController::class, 'storeChecklistExam'])->name('office.store.checklist-exam');

    Route::get('/data-checklist/{tahun}/{bulan}', [OfficeController::class, 'getChecklistRKM'])->name('get.checklist.rkm');
    Route::get('/export-checklist/pdf/{id}', [OfficeController::class, 'exportChecklistPdf'])->name('export.pdf.checklist');
    Route::get('/export-checklist/excel/{id}', [OfficeController::class, 'exportChecklistExcel'])->name('export.excel.checklist');

    // Tagihan & SOP Perusahaan
    Route::post('/store-tagihan', [TagihanPerusahaanController::class, 'storeTagihanPerusahaan'])->name('storeTagihanPerusahaan');
    Route::post('/update-tagihan/{id}', [TagihanPerusahaanController::class, 'updateTagihanPerusahaan'])->name('updateTagihanPerusahaan');
    Route::get('/detail-tagihan/{id}', [TagihanPerusahaanController::class, 'detailTagihanPerusahaan'])->name('detailTagihanPerusahaan');
    Route::post('/delete-tagihan/{id}', [TagihanPerusahaanController::class, 'hapusTagihanPerusahaan'])->name('hapusTagihanPerusahaan');
    Route::get('/tagihan-perusahaan', [TagihanPerusahaanController::class, 'index'])->name('office.tagihanPerusahaan.index');
    Route::post('/toggle-urgent/{id}', [TagihanPerusahaanController::class, 'toggleUrgent'])->name('toggleUrgentTagihan');
    Route::post('/update-inline/{id}', [TagihanPerusahaanController::class, 'updateInline'])->name('updateInlineTagihan');
    Route::get('/data-tagihan/{id}', [TagihanPerusahaanController::class, 'dataTagihan'])->name('dataTagihan');
    Route::post('/reorder-urgent', [TagihanPerusahaanController::class, 'reorderUrgent'])->name('reorderUrgentTagihan');

    Route::get('/sop-perusahaan', [PerusahaanController::class, 'indexSop'])->name('sop.perusahaan.index');
    Route::get('/sop-perusahaan/{id}', [PerusahaanController::class, 'detailSop'])->name('sop.perusahaan.detail');
    Route::post('/sop-perusahaan/store', [PerusahaanController::class, 'storeSop'])->name('sop.perusahaan.store');
    Route::put('/sop-perusahaan/update/{id}', [PerusahaanController::class, 'updateSop'])->name('sop.perusahaan.update');
    Route::delete('/sop-perusahaan/delete/{id}', [PerusahaanController::class, 'deleteSop'])->name('sop.perusahaan.delete');

    // Administrasi Karyawan
    Route::get('data-administrasi/{id}', [AdministrasiKaryawanController::class, 'getData']);
    Route::get('administrasi-karyawan', [AdministrasiKaryawanController::class, 'index'])->name('administrasi.karyawan');
    Route::post('administrasi-karyawan/store', [AdministrasiKaryawanController::class, 'store'])->name('administrasi.karyawan.store');
    Route::get('administrasi-karyawan/{id}', [AdministrasiKaryawanController::class, 'edit'])->name('administrasi.karyawan.edit');
    Route::post('administrasi-karyawan/update/{id}', [AdministrasiKaryawanController::class, 'update'])->name('administrasi.karyawan.update');
    Route::delete('administrasi-karyawan/destroy/{id}', [AdministrasiKaryawanController::class, 'destroy'])->name('administrasi.karyawan.destroy');
    Route::post('administrasi-karyawan/eksport', [AdministrasiKaryawanController::class, 'eksport'])->name('administrasi.karyawan.eksport');

    // Analysis Report
    Route::get('analysis', [AnalysisReportController::class, 'index'])->name('index.analysis');
    Route::post('analysis/store', [AnalysisReportController::class, 'store'])->name('store.analysis');
    Route::put('analysis/update/{id}', [AnalysisReportController::class, 'update'])->name('update.analysis');
    Route::delete('analysis/destroy/{id}', [AnalysisReportController::class, 'destroy'])->name('destroy.analysis');
    Route::get('analysis/download/{id}/{index}', [AnalysisReportController::class, 'download'])->name('download.analysis');
    Route::post('analysis/year-description', [AnalysisReportController::class, 'updateYearDescription'])->name('update.year.description.analysis');
    Route::post('analysis/update-quarter-description', [AnalysisReportController::class, 'updateQuarterDescription'])->name('update.quarter.description.analysis');
    Route::get('/analysis/download-quarter/{year}/{quarter}/{index}', [AnalysisReportController::class, 'downloadQuarter'])->name('download.quarter.analysis');
    Route::post('/analysis/update-annual', [AnalysisReportController::class, 'updateAnnualReport'])->name('update.annual.description.analysis');
    Route::get('/analysis/download-annual/{year}/{index}', [AnalysisReportController::class, 'downloadAnnual'])->name('download.annual.analysis');

    // PIC Penagihan
    Route::get('/pic/penagihan', [PicPenagihanController::class, 'index'])->name('picpenagihan.index');
    Route::post('/pic/penagihan/store', [PicPenagihanController::class, 'store'])->name('picpenagihan.store');
    Route::get('/pic/penagihan/data', [PicPenagihanController::class, 'getData'])->name('picpenagihan.data');
    Route::put('/pic-penagihan/update/{id}', [PicPenagihanController::class, 'update'])->name('picpenagihan.update');
    Route::delete('/pic-penagihan/delete/{id}', [PicPenagihanController::class, 'destroy'])->name('picpenagihan.delete');
    Route::get('/pic-penagihan/pdf/{id}', [PicPenagihanController::class, 'exportPdf'])->name('picpenagihan.pdf');

    // Analisys Finance
    Route::prefix('analisys-finance')->name('analysis-finance.')->group(function () {
        Route::get('/', [AnalysisFinanceController::class, 'index'])->name('index');
        Route::post('/setting', [AnalysisFinanceController::class, 'saveSetting'])->name('setting');
        Route::post('/internal-process', [AnalysisFinanceController::class, 'saveInternalProcess'])->name('internal');
        Route::post('/initiatives', [AnalysisFinanceController::class, 'saveInitiatives'])->name('initiatives');
        Route::post('/quarterly', [AnalysisFinanceController::class, 'saveQuarterly'])->name('quarterly');
        Route::post('/import/{type}', [AnalysisFinanceController::class, 'import'])->name('import');
        Route::get('/template/{type}', [AnalysisFinanceController::class, 'template'])->name('template');
        Route::post('/indicators', [AnalysisFinanceController::class, 'saveIndicators'])->name('indicators');
        Route::post('/initiatives/add', [AnalysisFinanceController::class, 'addInitiative'])->name('initiatives.add');
        Route::delete('/initiatives/{id}', [AnalysisFinanceController::class, 'destroyInitiative'])->name('initiatives.destroy');
        Route::post('/changes', [AnalysisFinanceController::class, 'saveChanges'])->name('changes');
        Route::post('overview', [AnalysisFinanceController::class, 'saveOverview'])->name('overview');
        Route::post('/refresh/{type}', [AnalysisFinanceController::class, 'refresh'])->name('refresh');
    });

    // Approval Pendapatan & Komisi Sales
    Route::prefix('approval-pendapatan')->name('approvalPendapatan.')->group(function () {
        Route::get('/index', [ApprovalPendapatanController::class, 'index'])->name('index');
        Route::get('/get/{tahun?}/{bulan?}', [ApprovalPendapatanController::class, 'get'])->name('get');
        Route::post('update/{id}', [ApprovalPendapatanController::class, 'update'])->name('update');
        Route::get('total-tahunan/{tahun}/{bulan}', [ApprovalPendapatanController::class, 'totalTahunan'])->name('total-tahunan');
        Route::get('/lock-status', [ApprovalPendapatanController::class, 'checkLockStatus'])->name('lock-status');
        Route::post('/setup-lock', [ApprovalPendapatanController::class, 'setupLockPassword'])->name('setup-lock');
        Route::post('/unlock', [ApprovalPendapatanController::class, 'unlock'])->name('unlock');
        Route::post('/change-lock-password', [ApprovalPendapatanController::class, 'changeLockPassword'])->name('change-lock-password');
        Route::post('/change-accounting-password', [ApprovalPendapatanController::class, 'changeAccountingPassword'])->name('change-accounting-password');
        Route::post('/setup-accounting-password', [ApprovalPendapatanController::class, 'setupAccountingPassword'])->name('setup-accounting-password');
        Route::post('/export-excel', [ApprovalPendapatanController::class, 'exportExcel'])->name('export.excel');
    });

    Route::prefix('komisi-sales')->name('komisiSales.')->group(function () {
        Route::get('/index', [KomisiSalesController::class, 'index'])->name('index');
        Route::get('/get/{tahun}/{quartal}/{bulan?}', [KomisiSalesController::class, 'get'])->name('get');
        Route::post('/export-pdf', [KomisiSalesController::class, 'exportPdf'])->name('export-pdf');
        Route::post('/export-excel', [KomisiSalesController::class, 'exportExcel'])->name('export-excel');
        Route::post('/update-inline/{id_rkm}', [KomisiSalesController::class, 'updateInline'])->name('update-inline');
        Route::post('/update-row/{id_rkm}', [KomisiSalesController::class, 'updateRow'])->name('update-row');
        Route::get('/lock-status', [KomisiSalesController::class, 'checkLockStatus'])->name('lock-status');
        Route::post('/unlock', [KomisiSalesController::class, 'unlock'])->name('unlock');
        Route::post('/setup-lock', [KomisiSalesController::class, 'setupLockPassword'])->name('setup-lock');
        Route::post('/change-lock-password', [KomisiSalesController::class, 'changeLockPassword'])->name('change-lock-password');
        Route::post('/change-accounting-password', [KomisiSalesController::class, 'changeAccountingPassword'])->name('change-accounting-password');
        Route::post('/setup-accounting-password', [KomisiSalesController::class, 'setupAccountingPassword'])->name('setup-accounting-password');
        Route::delete('/delete-row/{id_rkm}', [KomisiSalesController::class, 'deleteRow'])->name('delete-row');
    });

    Route::prefix('exam')->name('office.exam.')->group(function () {
        Route::get('/index', [OfficeExamController::class, 'indexOffice'])->name('index');
        Route::get('/detail/{id}', [OfficeExamController::class, 'showDetailExam'])->name('detail');
        Route::post('/update-bundling', [OfficeExamController::class, 'updateBundling'])->name('updateBundling');
        Route::get('/rekap', [OfficeExamController::class, 'indexRekap'])->name('rekap.index');
        Route::get('/rekap/json', [OfficeExamController::class, 'rekapJson'])->name('rekap.json');
        Route::get('/{year}/{month}', [OfficeExamController::class, 'showExamMonth'])->where(['year' => '[0-9]+', 'month' => '[0-9]+'])->name('month');
    });

    Route::prefix('certifa')->name('office.certifa.')->group(function () {
        Route::get('/data', [PoExamSertifaController::class, 'getData'])->name('data');
        Route::get('index', [PoExamSertifaController::class, 'index'])->name('index');
        Route::post('/store', [PoExamSertifaController::class, 'store'])->name('store');
        Route::put('/update/{id}', [PoExamSertifaController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [PoExamSertifaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('alias')->name('office.alias.')->group(function () {
        Route::get('/index', [MateriController::class, 'indexAlias'])->name('index');
        Route::get('/index/json', [MateriController::class, 'indexJsonAlias'])->name('index.json');
        Route::put('/update/{id}', [MateriController::class, 'updateAlias'])->name('update');
    });

    // Kelengkapan Data Penjualan
    Route::prefix('kelengkapan-data')->name('kelengkapan-data.')->group(function () {
        Route::get('/', [KelengkapanDataController::class, 'index'])->name('index');
        Route::get('/pa/data', [KelengkapanDataController::class, 'paData'])->name('pa.data');
        Route::get('/pa/template', [KelengkapanDataController::class, 'downloadTemplate'])->name('pa.template');
        Route::post('/pa/preview', [KelengkapanDataController::class, 'preview'])->name('pa.preview');
        Route::post('/pa/commit', [KelengkapanDataController::class, 'commit'])->name('pa.commit');
        Route::post('/pa/perbaiki', [KelengkapanDataController::class, 'perbaiki'])->name('pa.perbaiki');

        Route::get('/sinkron/data', [KelengkapanDataController::class, 'sinkronData'])->name('sinkron.data');
        Route::post('/sinkron/konfirmasi', [KelengkapanDataController::class, 'sinkronKonfirmasi'])->name('sinkron.konfirmasi');
        Route::post('/sinkron/batal', [KelengkapanDataController::class, 'sinkronBatal'])->name('sinkron.batal');
        Route::post('/sinkron/simpan', [KelengkapanDataController::class, 'sinkronSimpan'])->name('sinkron.simpan');
        Route::post('/sinkron/catatan', [KelengkapanDataController::class, 'sinkronCatatan'])->name('sinkron.catatan');
    });
});

// Office Prefix (Dengan Name Prefix 'office.')
Route::prefix('office')->name('office.')->group(function () {
    Route::prefix('pickup-driver')->name('pickupDriver.')->group(function () {
        Route::get('/index', [pickupDriverController::class, 'index'])->name('index');
        Route::get('/create', [pickupDriverController::class, 'create'])->name('create');
        Route::get('/get', [pickupDriverController::class, 'get'])->name('get');
        Route::post('/store', [pickupDriverController::class, 'store'])->name('store');
        Route::post('/update-kepulangan', [pickupDriverController::class, 'updateKepulangan'])->name('updateKepulangan');
        Route::post('/update-status/{id}', [pickupDriverController::class, 'updateStatus']);
        Route::delete('/delete/{id}', [pickupDriverController::class, 'delete'])->name('delete');
        Route::post('/update-koordinasi', [pickupDriverController::class, 'updateKoordinasi'])->name('updateKoordinasi');
        Route::get('/get-onlineStatus', [pickupDriverController::class, 'getDriverStatus'])->name('getDriverStatus');
        Route::get('/export/excel', [pickupDriverController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [pickupDriverController::class, 'exportPdf'])->name('export.pdf');
    });

    Route::prefix('stock-opname')->name('stockOpname.')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::get('/data', [StockOpnameController::class, 'getData'])->name('getData');
        Route::post('/store', [StockOpnameController::class, 'store'])->name('store');
        Route::post('/update', [StockOpnameController::class, 'update'])->name('update');
        Route::post('/clean-log', [StockOpnameController::class, 'cleanLog'])->name('cleanLog');
        Route::post('/inline-update', [StockOpnameController::class, 'inlineUpdate'])->name('inlineUpdate');
        Route::post('/sync-baseline', [StockOpnameController::class, 'syncBaseline'])->name('syncBaseline');
        Route::post('/store-keluar', [StockOpnameController::class, 'storeKeluar'])->name('storeKeluar');
        Route::delete('/delete/{id}', [StockOpnameController::class, 'delete'])->name('delete');
        Route::get('/export/excel', [StockOpnameController::class, 'exportExcel'])->name('exportExcel');
        Route::get('/export/pdf', [StockOpnameController::class, 'exportPdf'])->name('exportPdf');
        Route::get('/export/log/excel', [StockOpnameController::class, 'exportLogExcel'])->name('exportLogExcel');
        Route::get('/export/log/pdf', [StockOpnameController::class, 'exportLogPdf'])->name('exportLogPdf');
        Route::get('/get-log', [StockOpnameController::class, 'getLog'])->name('getLog');
    });

    Route::prefix('biaya-transportasi')->name('biayaTransportasi.')->group(function () {
        Route::get('/index', [BiayaTransportasiController::class, 'index'])->name('index');
        Route::post('/create', [BiayaTransportasiController::class, 'create'])->name('create');
        Route::get('/get', [BiayaTransportasiController::class, 'get'])->name('get');
        Route::post('/update/{id_pickup_driver}', [BiayaTransportasiController::class, 'update'])->name('update');
        Route::delete('/delete/{id_pickup_driver}', [BiayaTransportasiController::class, 'destroy'])->name('destroy');
        Route::get('/export-excel', [BiayaTransportasiController::class, 'exportExcel'])->name('exportExcel');
        Route::get('/export-pdf', [BiayaTransportasiController::class, 'exportPdf'])->name('exportPdf');
        Route::post('upload-invoice/{id}', [BiayaTransportasiController::class, 'uploadInvoice']);
        Route::get('search-pickup', [BiayaTransportasiController::class, 'searchPickup'])->name('searchPickup');
        Route::get('search-spj', [BiayaTransportasiController::class, 'searchSpj'])->name('searchSpj');
        Route::get('/budget-summary', [BiayaTransportasiController::class, 'budgetWeeklySummary'])->name('budgetSummary');
        Route::get('/budget-summary-all', [BiayaTransportasiController::class, 'budgetAllSummary'])->name('budgetSummaryAll');
        Route::post('/budget-operasional', [BiayaTransportasiController::class, 'storeBudgetOperasional'])->name('storeBudgetOperasional');
        Route::post('/update-tipe/{id}', [BiayaTransportasiController::class, 'updateTipeBiaya'])->name('updateTipe');
        Route::get('/export-budget-summary', [BiayaTransportasiController::class, 'exportBudgetSummary'])->name('exportBudgetSummary');
    });

    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('chartFeedback', [OfficeController::class, 'getNilaiInstruktur'])->name('chartFeedback');
    });
    Route::get('/feedbackinstrukturpdf', [OfficeController::class, 'exportPdf'])->name('feedbackinstrukturpdf');

    Route::prefix('modul')->group(function () {
        Route::get('/index', [ModulController::class, 'indexNomor'])->name('modul.index');
        Route::post('/', [ModulController::class, 'storeNomor'])->name('modul.store.nomor');
        Route::put('/update/nomor/{id}', [ModulController::class, 'updateNomor'])->name('modul.update.nomor');
        Route::delete('/delete/nomor/{id}', [ModulController::class, 'deleteNomor'])->name('modul.delete.nomor');
        Route::put('/update/nomor/status/{id}', [ModulController::class, 'uploaded'])->name('modul.update.status.nomor');
        Route::put('/update/subscode/{id}', [ModulController::class, 'updateSubscode'])->name('modul.update.subscode');

        Route::get('/detail/{id}', [ModulController::class, 'indexModul'])->name('modul.detail');
        Route::post('/store', [ModulController::class, 'storeModul'])->name('modul.store');
        Route::put('/update/{id}', [ModulController::class, 'updateModul'])->name('modul.update');
        Route::delete('delete/{id}', [ModulController::class, 'deleteModul'])->name('modul.delete');

        Route::post('/store/peserta', [ModulController::class, 'storePeserta'])->name('modul.store.peserta');
        Route::put('/update/peserta/{id}', [ModulController::class, 'updatePeserta'])->name('modul.update.peserta');
        Route::delete('/delete/peserta/{id}', [ModulController::class, 'deletePeserta'])->name('modul.delete.peserta');
        Route::put('/update/status/{id}', [ModulController::class, 'updateStatus'])->name('modul.update.status');

        Route::put('/download/pdf/{id}', [ModulController::class, 'pdfModul'])->name('modul.download.pdf');
        Route::put('/download/pdf/{id}/peserta', [ModulController::class, 'pdfPeserta'])->name('modul.download.pdf.peserta');
        Route::put('/download/excel/{id}/peserta', [ModulController::class, 'excelPeserta'])->name('modul.download.excel.peserta');

        Route::get('/rekap/json', [ModulController::class, 'rekapJson'])->name('modul.rekap.json');
        Route::get('/rekap', [ModulController::class, 'rekapIndex'])->name('modul.rekap');
    });

    Route::prefix('kegiatan')->group(function () {
        Route::get('/index', [KegiatanController::class, 'index'])->name('indexKegiatan');
        Route::post('/store', [KegiatanController::class, 'storeKegiatan'])->name('storeKegiatan');
        Route::put('/update/{id}', [KegiatanController::class, 'updateKegiatan'])->name('updateKegiatan');
        Route::delete('/delete/{id}', [KegiatanController::class, 'deleteKegiatan'])->name('deleteKegiatan');
        Route::get('{id}/available-pengajuan', [KegiatanController::class, 'getAvailablePengajuanBarang'])->name('availablePengajuan');
        Route::post('{id}/link-pengajuan', [KegiatanController::class, 'linkPengajuanBarang'])->name('linkPengajuan');
        Route::get('/show/{id}', [KegiatanController::class, 'show'])->name('showRincian');
        Route::put('/update/status/gm/{id}', [KegiatanController::class, 'gm'])->name('UpdateStatusGM');
        Route::put('/update/status/finance/{id}', [KegiatanController::class, 'finance'])->name('UpdateStatusFinance');
        Route::put('/update/status/selesai/{id}', [KegiatanController::class, 'selesai'])->name('UpdateStatusSelesai');
        Route::put('/tambah/peserta/{id}', [KegiatanController::class, 'storePeserta'])->name('StorePesertaKegiatan');
        Route::get('/download/pdf/{id}', [KegiatanController::class, 'downloadPDF'])->name('downloadPdfRab');
        Route::post('/update/realisasi', [KegiatanController::class, 'updateRealisasi'])->name('kegiatan.updateRealisasi');
        Route::post('/store/rincian', [KegiatanController::class, 'storeDetail'])->name('store.detail');
        Route::get('/get/rincian/{id}', [KegiatanController::class, 'getDetailKegiatan'])->name('getDetailKegiatan');
        Route::post('/edit/rincian/{id}', [KegiatanController::class, 'updateDetail'])->name('updateDetail');
        Route::delete('/delete/rincian/{id}', [KegiatanController::class, 'deleteRincian'])->name('deleteRincian');
    });

    // To Do List & Koordinasi OB
    Route::prefix('daftar-tugas')->name('DaftarTugas.')->group(function () {
        Route::get('/', [DaftarTugasController::class, 'index'])->name('index');
        Route::post('store', [DaftarTugasController::class, 'store'])->name('store');
        Route::get('data', [DaftarTugasController::class, 'get'])->name('get');
        Route::get('get/kategori', [DaftarTugasController::class, 'getKategori'])->name('getKategori');
        Route::get('chart-data', [DaftarTugasController::class, 'chartData'])->name('chartData');
        Route::post('status/update', [DaftarTugasController::class, 'updateStatus'])->name('updateStatus');
        Route::post('bukti/upload', [DaftarTugasController::class, 'uploadBukti'])->name('uploadBukti');
        Route::delete('hapus/{id}', [DaftarTugasController::class, 'delete'])->name('delete');
        Route::delete('/bulk-delete', [DaftarTugasController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/export/excel', [DaftarTugasController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [DaftarTugasController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/aktifkan-tugas', [DaftarTugasController::class, 'aktifkanTugas'])->name('aktifkanTugas');
        Route::post('kategori/update', [DaftarTugasController::class, 'updateKategori'])->name('updateKategori');
        Route::post('kategori/hapus', [DaftarTugasController::class, 'deleteKategori'])->name('deleteKategori');
        Route::get('/available-categories', [DaftarTugasController::class, 'getAvailableCategories'])->name('availableCategories');
        Route::post('kategori/bulk-update-turunan', [DaftarTugasController::class, 'bulkUpdateTipeTurunan'])->name('bulkUpdateTipeTurunan');
        Route::post('/kategori/reorder', [DaftarTugasController::class, 'reorderKategori'])->name('reorderKategori');
        Route::post('/reorder', [DaftarTugasController::class, 'reorderTugas'])->name('reorderTugas');
        Route::get('/perbaikan-data', [DaftarTugasController::class, 'perbaikanData'])->name('perbaikanData');
        Route::get('/perbaikan-data/list', [DaftarTugasController::class, 'getForPerbaikan'])->name('getForPerbaikan');
        Route::post('/perbaikan-data/update', [DaftarTugasController::class, 'updatePerbaikan'])->name('updatePerbaikan');
        Route::post('/perbaikan-data/bulk-update', [DaftarTugasController::class, 'bulkUpdatePerbaikan'])->name('bulkUpdatePerbaikan');
        Route::post('/perbaikan/bulk-save', [DaftarTugasController::class, 'bulkSavePerbaikan'])->name('bulkSavePerbaikan');
        Route::post('import', [DaftarTugasController::class, 'importExcel'])->name('import');
        Route::get('/double/scan', [DaftarTugasController::class, 'scanDouble'])->name('scanDouble');
        Route::post('/double/clear', [DaftarTugasController::class, 'clearDouble'])->name('clearDouble');
    });

    Route::prefix('koordinasi-ob')->name('KoordinasiOb.')->group(function () {
        Route::get('/', [KoordinasiOfficeBoyController::class, 'index'])->name('index');
        Route::get('/get-data', [KoordinasiOfficeBoyController::class, 'getData'])->name('getData');
        Route::post('/store', [KoordinasiOfficeBoyController::class, 'store'])->name('store');
        Route::post('/update', [KoordinasiOfficeBoyController::class, 'update'])->name('update');
        Route::post('/update-status-{action}/{id}', [KoordinasiOfficeBoyController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/delete/{id}', [KoordinasiOfficeBoyController::class, 'delete'])->name('delete');
        Route::get('/detail/{id}', [KoordinasiOfficeBoyController::class, 'telegramDetail'])->name('telegram.detail');
    });

    Route::prefix('kondisi-tools')->name('KondisiTools.')->group(function () {
        Route::get('/', [KondisiToolsController::class, 'index'])->name('index');
        Route::post('/store', [KondisiToolsController::class, 'store'])->name('store');
        Route::post('/update/{id}', [KondisiToolsController::class, 'update'])->name('update');
        Route::post('/delete/{id}', [KondisiToolsController::class, 'delete'])->name('delete');
        Route::get('/get-tools', [KondisiToolsController::class, 'getTools'])->name('get-tools');
        Route::get('/get-pemeriksaan', [KondisiToolsController::class, 'getPemeriksaan'])->name('get-pemeriksaan');
        Route::get('/get-template', [KondisiToolsController::class, 'getTemplate'])->name('get-template');
        Route::post('/import-alat', [KondisiToolsController::class, 'importAlat'])->name('importAlat');
        Route::get('/export-exel', [KondisiToolsController::class, 'exportExcel'])->name('exportExcel');
        Route::get('/export-pdf', [KondisiToolsController::class, 'exportPdf'])->name('exportPdf');
    });

    Route::prefix('rekomendasi-lanjutan')->name('rekomendasiLanjutan.')->group(function () {
        Route::get('/index', [RekomendasiLanjutanController::class, 'index'])->name('index');
        Route::get('/get/{year}/{month}', [RekomendasiLanjutanController::class, 'showMonth']);
        Route::post('/store', [RekomendasiLanjutanController::class, 'store'])->name('store');
    });

    Route::prefix('certificate')->name('certificate.')->group(function () {
        Route::get('/', [CertificateController::class, 'index'])->name('index');
        Route::get('/data', [CertificateController::class, 'getData'])->name('getData');
        Route::get('/detail/{rkm_id}', [CertificateController::class, 'detail'])->name('detail');
        Route::get('/create/{rkm_id}/{peserta_id}', [CertificateController::class, 'create'])->name('create');
        Route::post('/store', [CertificateController::class, 'store'])->name('store');
        Route::get('/show/{id}', [CertificateController::class, 'show'])->name('show');
        Route::get('/download/{id}', [CertificateController::class, 'download'])->name('download');
        Route::get('/download-by-peserta/{rkm_id}/{peserta_id}', [CertificateController::class, 'downloadByPeserta'])->name('downloadByPeserta');
        Route::get('/preview/{id}', [CertificateController::class, 'preview'])->name('preview');
        Route::delete('/delete/{rkm_id}/{peserta_id}', [CertificateController::class, 'delete'])->name('delete');
        Route::get('/rekap', [CertificateController::class, 'certificateSummary'])->name('certificateSummary');
        Route::get('/rekap/json', [CertificateController::class, 'certificateSummaryJson'])->name('certificateSummaryJson');
        Route::post('/rekap/certif/store', [CertificateController::class, 'storeSummary'])->name('storeSummary');
        Route::put('/rekap/certif/put/{id}', [CertificateController::class, 'updateSummary'])->name('updateSummary');
        Route::delete('/rekap/certif/delete/{id}', [CertificateController::class, 'deleteSummary'])->name('deleteSummary');
        Route::put('/update/kategori/{rkm_id}/{peserta_id}', [CertificateController::class, 'updateKategoriExam'])->name('updateKategoriExam');
    });

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::resource('/souvenir', vendorOfficeController::class);
        Route::resource('/makansiang', vendorOfficeController::class);
        Route::resource('/coffeebreak', vendorOfficeController::class);
        Route::resource('/bengkel', vendorOfficeController::class);
    });

    Route::prefix('kendaraan')->group(function () {
        Route::get('/index/kondisi', [KendaraanController::class, 'indexKondisi'])->name('indexKondisiKendaraan');
        Route::get('/detail/kondisi/{id}', [KendaraanController::class, 'detailKondisi'])->name('detailKondisiKendaraan');
        Route::post('/store/kondisi', [KendaraanController::class, 'storeKondisi'])->name('storeKondisiKendaraan');
        Route::put('/update/kondisi/{id}', [KendaraanController::class, 'updateKondisi'])->name('updateKondisiKendaraan');
        Route::delete('/delete/kondisi/{id}', [KendaraanController::class, 'deleteKondisi'])->name('deleteKondisiKendaraan');

        Route::get('/index/perbaikan', [KendaraanController::class, 'indexPerbaikan'])->name('indexPerbaikanKendaraan');
        Route::get('/detail/perbaikan/{id}', [KendaraanController::class, 'detailPerbaikan'])->name('detailPerbaikanKendaraan');
        Route::post('/store/perbaikan', [KendaraanController::class, 'storePerbaikan'])->name('storePerbaikanKendaraan');
        Route::put('/update/perbaikan/{id}', [KendaraanController::class, 'updatePerbaikan'])->name('updatePerbaikanKendaraan');
        Route::delete('/delete/perbaikan/{id}', [KendaraanController::class, 'deletePerbaikan'])->name('deletePerbaikanKendaraan');
        Route::post('/update/status/perbaikan', [KendaraanController::class, 'updateStatusPerbaikan'])->name('updateStatusPerbaikanKendaraan');
        Route::post('/update/selesai/perbaikan', [KendaraanController::class, 'SelesaiPerbaikan'])->name('selesaiPerbaikanKendaraan');

        Route::get('/download/pdf', [KendaraanController::class, 'pdfExport'])->name('pdfExportPerbaikan');
        Route::get('/download/excel', [KendaraanController::class, 'excelExport'])->name('excelExportPerbaikan');
    });

    Route::get('/rekap/rkm/json', [OfficeController::class, 'rekapRkmJson'])->name('rekapRkmJson');
    Route::get('/rekap/rkm', [OfficeController::class, 'rekapRkm'])->name('rekapRkm');
    Route::get('/rekap/rkm/select', [OfficeController::class, 'selectHide'])->name('rekapRkm.select');
    Route::post('/rekap/rkm/toggle-hide', [OfficeController::class, 'toggleHide'])->name('rekapRkm.toggleHide');
    Route::post('/rekap/rkm/bulk-toggle-hide', [OfficeController::class, 'bulkToggleHide'])->name('rekapRkm.bulkToggleHide');
});
