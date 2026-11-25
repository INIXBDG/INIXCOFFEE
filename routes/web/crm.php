<?php

use App\Http\Controllers\Crm\AktivitasController;
use App\Http\Controllers\Crm\ApprovalPendapatanSalesController;
use App\Http\Controllers\Crm\CatatanSalesController;
use App\Http\Controllers\Crm\checklistRKMController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\CRMController;
use App\Http\Controllers\Crm\ImportPerusahaanAndContactController;
use App\Http\Controllers\Crm\LaporanPenjualanController;
use App\Http\Controllers\Crm\MapController;
use App\Http\Controllers\Crm\PaymentAdvanceController;
use App\Http\Controllers\Crm\PeluangController;
use App\Http\Controllers\Crm\PicController;
use App\Http\Controllers\Crm\RegisFormController;
use App\Http\Controllers\Crm\salesPribadiController;
use App\Http\Controllers\Crm\TargetAktivitas;
use App\Http\Controllers\Crm\TodoAdministrasiController;
use App\Http\Controllers\dbklienController;
use App\Http\Controllers\LaporanHarianSalesController;
use App\Http\Controllers\PerpindahanDBController;
use App\Http\Controllers\PesertaWebinarSalesController;
use App\Http\Controllers\Webinar\CalendarController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 14. CRM & SALES
// =========================================================================
Route::get('/db-klien', [dbklienController::class, 'index'])->name('dbklien.index');
Route::post('import-klien', [dbklienController::class, 'import']);
Route::get('download-template-klien', [dbklienController::class, 'downloadTemplate'])->name('excel.dbklien');

Route::get('laporan/penjualan', [LaporanPenjualanController::class, 'indexJson'])->name('jsonLaporan');
Route::get('/laporan/penjualan/win/excel', [LaporanPenjualanController::class, 'downloadWinExcel'])->name('laporan.win.excel');
Route::get('/laporan/penjualan/lost/excel', [LaporanPenjualanController::class, 'downloadLostExcel'])->name('laporan.lost.excel');
Route::get('/laporan/penjualan/win/pdf', [LaporanPenjualanController::class, 'downloadPdfWin'])->name('laporan.win.pdf');
Route::get('/laporan/penjualan/lost/pdf', [LaporanPenjualanController::class, 'downloadPdfLost'])->name('laporan.lost.pdf');

Route::prefix('crm')->group(function () {
    Route::get('/', [CRMController::class, 'index'])->name('CRM.index');
    Route::get('/my-dashboard', [salesPribadiController::class, 'index'])->name('CRM.myDasboard');
    Route::get('/profile', [CRMController::class, 'getProfile'])->name('crm.profile');

    // Dashboard Global
    Route::get('/chartRKM', [CRMController::class, 'chartRKM'])->name('chartRKM');
    Route::get('/chartPerusahaan', [CRMController::class, 'chartPerusahaan'])->name('chartPerusahaan');
    Route::get('/chartClosed', [CRMController::class, 'chartClosed'])->name('chartClosed');
    Route::get('/detail-aktivitas-api', [CRMController::class, 'detailAktivitasApi'])->name('crm.detail.aktivitas.api');
    Route::get('/prospek-minggu-ini', [CRMController::class, 'apiProspekMingguan'])->name('crm.api.prospek');
    Route::get('/incomplete-pa', [CRMController::class, 'apiIncompletePA'])->name('crm.api.pa');
    Route::get('/pivot-status', [CRMController::class, 'apiPivotStatus'])->name('crm.api.pivot');
    Route::get('/total-win-lost', [CRMController::class, 'apiTotalWinLost'])->name('crm.api.winlost');
    Route::get('/target-filter-aktivitas', [CRMController::class, 'apiTargetAktivitas'])->name('crm.api.aktivitas');
    Route::post('/checklist/update', [CRMController::class, 'updateChecklist'])->name('checklist.update');

    // Koordinasi Driver
    Route::get('/koordinasi-driver', [CRMController::class, 'indexKoordinasi'])->name('CRM.index.koordinasi');
    Route::get('/koordinasi-driver/create', [CRMController::class, 'createKoordinasi'])->name('CRM.create.koordinasi');

    // Contact & PIC CRM
    Route::get('/contact/index', [ContactController::class, 'index'])->name('index.contact');
    Route::get('/contact/{id}/detail', [ContactController::class, 'detail'])->name('detail.contact');
    Route::post('/contact/store', [ContactController::class, 'store'])->name('store.contact');
    Route::delete('/contact/delete/{id}', [ContactController::class, 'delete'])->name('delete.contact');
    Route::put('/contact/update/{id}', [ContactController::class, 'update'])->name('update.contact');
    Route::get('/contact/data', [ContactController::class, 'getPerusahaan'])->name('contact.data');
    Route::get('/contact/export-pdf', [ContactController::class, 'exportPdf'])->name('contact.export_pdf');
    Route::get('/contact/history-status/all', [ContactController::class, 'allHistoryStatus'])->name('crm.contact.all_history_status');
    Route::get('/contact/history-analytics-api', [ContactController::class, 'apiHistoryAnalytics'])->name('crm.contact.history_analytics_api');
    Route::get('/contact/history-status/data', [ContactController::class, 'allHistoryStatusData'])->name('crm.contact.all_history_status_data');

    Route::get('/pic', [PicController::class, 'index'])->name('index.pic');
    Route::get('/index/pic', [PicController::class, 'indexJson'])->name('index.json.pic');
    Route::post('/pic/store', [PicController::class, 'store'])->name('store.pic');
    Route::put('/update/pic', [PicController::class, 'updatePIC'])->name('pic.update');
    Route::delete('/delete/pic/{id}', [PicController::class, 'deletePIC'])->name('pic.delete');
    Route::get('/export/data/pic', [PicController::class, 'exportData'])->name('pic.exportData');

    // Checklist RKM Admin Sales
    Route::prefix('checklist-rkm')->name('crm.checklist-rkm.')->group(function () {
        Route::get('/', [checklistRKMController::class, 'index'])->name('index');
        Route::get('/data', [checklistRKMController::class, 'getData'])->name('data');
        Route::get('/{id}/detail', [checklistRKMController::class, 'getDetailData'])->name('detail');
        Route::patch('/{id}/checklist', [checklistRKMController::class, 'updateChecklist'])->name('checklist.update');
        Route::patch('/detail/{id}', [checklistRKMController::class, 'updateDetailChecklist'])->name('detail.update');
        Route::patch('/{id}/checklists', [checklistRKMController::class, 'updateMultiple'])->name('checklist.update-multiple');
    });

    // Timeline Webinar Admin Sales
    Route::prefix('/timeline-webinar')->name('crm.timeline-webinar.')->group(function () {
        Route::get('/', [CalendarController::class, 'indexAdmSales'])->name('index');

        Route::get('/peserta', [PesertaWebinarSalesController::class, 'index'])->name('peserta.index');
        Route::get('/peserta/meta', [PesertaWebinarSalesController::class, 'meta'])->name('peserta.meta');
        Route::get('/peserta/perusahaan', [PesertaWebinarSalesController::class, 'perusahaan'])->name('peserta.perusahaan');
        Route::post('/peserta', [PesertaWebinarSalesController::class, 'store'])->name('peserta.store');
        Route::put('/peserta/{id}', [PesertaWebinarSalesController::class, 'update'])->name('peserta.update');
        Route::delete('/peserta/{id}', [PesertaWebinarSalesController::class, 'destroy'])->name('peserta.destroy');
        Route::get('/peserta/export', [PesertaWebinarSalesController::class, 'export'])->name('peserta.export');
    });

    // Peluang CRM & Closed Win/Lost
    Route::get('/peluang/index', [PeluangController::class, 'index'])->name('index.peluang');
    Route::get('/index/peluang', [PeluangController::class, 'indexJson'])->name('index.peluang.json');
    Route::get('/peluang/detail/{id}', [PeluangController::class, 'detail'])->name('detail.peluang');
    Route::post('/peluang/store', [PeluangController::class, 'store'])->name('store.peluang');
    Route::put('/peluang/delete/{id}', [PeluangController::class, 'delete'])->name('delete.peluang');
    Route::put('/peluang/edit/{id}', [PeluangController::class, 'update'])->name('edit.peluang');
    Route::put('/peluang/update/{id}', [PeluangController::class, 'updateTahap'])->name('update.tahap');
    Route::get('/ambil/aktivitas/{id}', [PeluangController::class, 'AmbilAktivitas']);
    Route::post('/peluang/paymentAdvance', [PeluangController::class, 'storePaymentAdvance'])->name('store.payment.advance');
    Route::post('/peluang/restore/{id}', [PeluangController::class, 'restore'])->name('restore.peluang');
    Route::delete('/peluang/force-delete/{id}', [PeluangController::class, 'forceDelete'])->name('forceDelete.peluang');
    Route::get('/peluang/search-perusahaan', [PeluangController::class, 'searchPerusahaan'])->name('peluang.searchPerusahaan');
    Route::get('/peluang/search-materi', [PeluangController::class, 'searchMateri'])->name('peluang.searchMateri');

    Route::get('/closed/win', [PeluangController::class, 'ringkasanPeluang'])->name('index.ringkasanPeluang');
    Route::get('/detail/closed/win/{id}', [PeluangController::class, 'detailRingkasan'])->name('detail.ringkasanPeluang');
    Route::get('/closed/lost', [PeluangController::class, 'ringkasanPeluanglost'])->name('index.ringkasanlost');
    Route::get('/detail/closed/lost/{id}', [PeluangController::class, 'detailRingkasanlost'])->name('detail.Ringkasanlost');

    // Aktivitas & Target Aktivitas CRM
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('index.aktivitas');
    Route::get('/index/aktivitas', [AktivitasController::class, 'indexJson'])->name('index.aktivitas.json');
    Route::post('/aktivitas/store/new', [AktivitasController::class, 'storeNew'])->name('store.aktivitas.new');
    Route::delete('/aktivitas/delete/{id}', [AktivitasController::class, 'delete'])->name('delete.aktivitas');
    Route::put('/aktivitas/update/{id}', [AktivitasController::class, 'update'])->name('update.aktivitas');
    Route::get('/get-contacts-peserta/{id}', [AktivitasController::class, 'getContactsAndPeserta'])->name('get.contacts');
    Route::get('/target-aktivitas/{id_sales}', [AktivitasController::class, 'targetAktivitas'])->name('get.target');
    Route::get('/semua-target-aktivitas', [AktivitasController::class, 'semuaTargetAktivitas'])->name('getall.target');

    Route::get('/target/activity', [TargetAktivitas::class, 'index'])->name('index.target');
    Route::post('/target/activity/store', [TargetAktivitas::class, 'store'])->name('index.target.store');
    Route::put('/target/activity/{id}/update', [TargetAktivitas::class, 'update'])->name('index.target.update');
    Route::delete('/target/activity/{id}/delete', [TargetAktivitas::class, 'delete'])->name('index.target.delete');

    // Catatan Sales CRM
    Route::post('/catatan/sales/store', [CatatanSalesController::class, 'store'])->name('store.catatan.sales');
    Route::delete('/catatan/sales/delete/{id}', [CatatanSalesController::class, 'delete'])->name('delete.catatan.sales');
    Route::put('/catatan/sales/update/{id}', [CatatanSalesController::class, 'update'])->name('update.catatan.sales');

    // Surat Penawaran dan Registrasi
    Route::get('/ketentuan', [RegisFormController::class, 'ketentuan'])->name('crm.ketentuan');
    Route::post('/add/ketentuan', [RegisFormController::class, 'storeKetentuan'])->name('crm.store.ketentuan');
    Route::post('/upload/regisform', [RegisFormController::class, 'upload'])->name('crm.upload.regis');
    Route::put('/update/ketentuan/{id}', [RegisFormController::class, 'updateKetentuan'])->name('crm.update.ketentuan');
    Route::delete('/delete/ketentuan/{id}', [RegisFormController::class, 'deleteKetentuan'])->name('crm.delete.ketentuan');
    Route::get('/generate/regis/form/{id}', [RegisFormController::class, 'index'])->name('crm.index.regis');
    Route::get('/generate/penawaran/form', [RegisFormController::class, 'indexPenawaran'])->name('crm.index.penawaran');
    Route::post('/generate/word', [RegisFormController::class, 'generateWord'])->name('crm.generate.word');
    Route::post('/store/deskripsi', [RegisFormController::class, 'storeDeskripsi'])->name('crm.store.deskripsi');
    Route::put('/update/deskripsi/{id}', [RegisFormController::class, 'updateDeskripsi'])->name('crm.update.deskripsi');
    Route::delete('/delete/deskripsi/{id}', [RegisFormController::class, 'deleteDeskripsi'])->name('crm.delete.deskripsi');
    Route::post('/store-prospect-penawaran', [RegisFormController::class, 'storeProspectAktivitas'])->name('crm.store.prospect.penawaran');

    // Lokasi
    Route::get('lokasi', [MapController::class, 'index'])->name('crm.lokasi');
    Route::post('lokasi/store', [MapController::class, 'store'])->name('crm.lokasi.store');
    Route::put('lokasi/update', [MapController::class, 'update'])->name('crm.lokasi.update');
    Route::delete('lokasi/delete/{id}', [MapController::class, 'delete'])->name('crm.lokasi.delete');

    // Laporan Penjualan & Laporan Harian
    Route::get('laporanPenjualan', [LaporanPenjualanController::class, 'index'])->name('crm.laporanPenjualan');
    Route::get('/edit/{id}/pa', [LaporanPenjualanController::class, 'editPA'])->name('editPA');
    Route::put('/update/pa/{id}', [LaporanPenjualanController::class, 'updatePA'])->name('updatePA');
    Route::get('/laporan-for-gm', [LaporanPenjualanController::class, 'laporanForGm'])->name('laporan.for.gm');
    Route::post('/laporan-penjualan/target', [LaporanPenjualanController::class, 'storeTarget'])->name('target_penjualan.store');

    Route::get('laporan-harian', [LaporanHarianSalesController::class, 'index'])->name('laporan.harian');
    Route::get('laporan-harian/create', [LaporanHarianSalesController::class, 'create'])->name('laporan.harian.create');
    Route::get('laporan-harian/{id}/edit', [LaporanHarianSalesController::class, 'edit'])->name('laporan.harian.edit');
    Route::post('laporan-harian/store', [LaporanHarianSalesController::class, 'store'])->name('laporan.harian.store');
    Route::post('laporan-harian/{id}/update', [LaporanHarianSalesController::class, 'update'])->name('laporan.harian.update');
    Route::delete('laporan-harian/delete/{id}', [LaporanHarianSalesController::class, 'delete'])->name('laporan.harian.delete');
    Route::get('laporan-harian/export/{id}/{type}', [LaporanHarianSalesController::class, 'exportPdf'])->name('laporan.harian.pdf');
    Route::post('laporan-harian/autosave', [LaporanHarianSalesController::class, 'autoSave'])->name('laporan.harian.autosave');
    Route::get('/laporan-harian-data', [LaporanHarianSalesController::class, 'getDataTables'])->name('laporan.harian.data');

    // Import Contact / Perusahaan
    Route::post('/perusahaan/import/perusahaan', [ImportPerusahaanAndContactController::class, 'importPerusahaan'])->name('perusahaan.import');
    Route::post('/perusahaan/import/contacts', [ImportPerusahaanAndContactController::class, 'importContacts'])->name('contact.import');

    // Todo Admin Sales
    Route::get('todo-administrasi', [TodoAdministrasiController::class, 'index'])->name('todo-administrasi.index');
    Route::post('todo-administrasi/store', [TodoAdministrasiController::class, 'store'])->name('todo-administrasi.store');
    Route::put('todo-administrasi/update/{id}', [TodoAdministrasiController::class, 'update'])->name('todo-administrasi.update');
    Route::delete('todo-administrasi/delete/{id}', [TodoAdministrasiController::class, 'destroy'])->name('todo-administrasi.delete');

    // Perpindahan Database
    Route::prefix('perpindahandb')->name('perpindahan-db.')->group(function () {
        Route::get('/', [PerpindahanDBController::class, 'index'])->name('index');
        Route::get('/data', [PerpindahanDBController::class, 'getData'])->name('data');
        Route::get('/sales', [PerpindahanDBController::class, 'getSalesList'])->name('sales');
        Route::post('/transfer', [PerpindahanDBController::class, 'transfer'])->name('transfer');
        Route::get('/history/{id}', [PerpindahanDBController::class, 'exportHistory'])->name('history');
    });

    // Payment Advance CRM
    Route::prefix('paymentAdvance')->name('crm.paymentAdvance.')->group(function () {
        Route::get('/', [PaymentAdvanceController::class, 'index'])->name('index');
        Route::get('/data', [PaymentAdvanceController::class, 'getData'])->name('data');
        Route::get('/form/{idRkm}', [PaymentAdvanceController::class, 'form'])->name('form');
        Route::post('/print', [PaymentAdvanceController::class, 'printPdf'])->name('printPdf');
    });

    // Approval Pendapatan Sales
    Route::get('/approval-pendapatan-sales', [ApprovalPendapatanSalesController::class, 'index'])->name('crm.approval.index');
    Route::get('/approval-pendapatan-sales/get/{tahun}/{bulan}', [ApprovalPendapatanSalesController::class, 'get']);
    Route::post('/approval-pendapatan-sales/update/{id}', [ApprovalPendapatanSalesController::class, 'update']);
    Route::get('/approval-pendapatan-sales/rkm-search', [ApprovalPendapatanSalesController::class, 'searchRkm']);
    Route::post('/approval-pendapatan-sales/store', [ApprovalPendapatanSalesController::class, 'store']);
    Route::post('/approval-pendapatan-sales/validasi-all', [ApprovalPendapatanSalesController::class, 'validasiAll']);
    Route::post('/approval-pendapatan-sales/validasi/{id}', [ApprovalPendapatanSalesController::class, 'validasi']);
});
