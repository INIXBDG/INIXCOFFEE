<?php

use App\Http\Controllers\CodeDocumentationController;
use App\Http\Controllers\DashboardItsmController;
use App\Http\Controllers\DashboardSLAController;
use App\Http\Controllers\DocumentationImportController;
use App\Http\Controllers\FeatureDocumentationController;
use App\Http\Controllers\KnowledgeManagementController;
use App\Http\Controllers\KPI\DatabaseKPIController as KPIDatabaseKPIController;
use App\Http\Controllers\laporanInsidentController;
use App\Http\Controllers\RekapPembelianItsmController;
use App\Http\Controllers\ScheduleLogController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

// =========================================================================
// 19. ITSM, TICKETING, SLA, INSIDEN & SYSTEM DOCUMENTATION
// =========================================================================
Route::get('/ticketing-data', [DashboardItsmController::class, 'getJumlahPermintaan']);
Route::get('/jumlah-pic', [DashboardItsmController::class, 'getJumlahPIC']);
Route::get('/rerata-durasi-data', [DashboardItsmController::class, 'getRerataDurasi']);
Route::get('/rerata-ketepatan-response-data', [DashboardItsmController::class, 'getRerataKetepatanResponse']);
Route::get('/jumlah-permintaan-per-bulan', [DashboardItsmController::class, 'getJumlahPermintaanPerBulan']);
Route::get('/permintaan-sering-diajukan', [DashboardItsmController::class, 'getPermintaanSeringDiajukan']);
Route::get('/list-bulan', [DashboardItsmController::class, 'getListBulan']);

// Rekap Pembelian ITSM (Khusus Koordinator ITSM / Ardhan)
Route::prefix('itsm/rekap-pembelian')->name('itsm.rekap-pembelian.')->group(function () {
    Route::get('/', [RekapPembelianItsmController::class, 'index'])->name('index');
    Route::get('/detail/{id}', [RekapPembelianItsmController::class, 'getDetail'])->name('detail');
});

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::get('/tickets/rekap', [TicketController::class, 'rekap'])->name('tickets.rekap');
Route::get('/tickets/rekap/data', [TicketController::class, 'rekapData'])->name('tickets.rekap.data');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
Route::post('/tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
Route::post('/tickets/{ticket}/finish', [TicketController::class, 'finish'])->name('tickets.finish');
Route::post('/tickets/{ticket}/block', [TicketController::class, 'block'])->name('tickets.block');
Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
Route::put('/tickets/{ticket}/edit-user', [TicketController::class, 'editUser'])->name('tickets.editUser');
Route::put('/tickets/{ticket}/edit-itsm', [TicketController::class, 'editItsm'])->name('tickets.editItsm');
Route::get('/getTickets', [TicketController::class, 'getTickets'])->name('getTickets');
Route::post('/internal/update-tickets', [TicketController::class, 'handleInternalUpdate']);
Route::get('/internal/open-tickets', [TicketController::class, 'getOpenTickets']);

// SLA & Uptime Monitoring
Route::get('/sla-management', [DashboardSLAController::class, 'index'])->name('sla.management');
Route::prefix('dashboard-sla/{team}')->whereIn('team', ['programmer', 'tech-support'])->group(function () {
    Route::get('/tim', [DashboardSLAController::class, 'dashboardTim']);
    Route::get('/user', [DashboardSLAController::class, 'dashboardUser']);
    Route::get('/kritis', [DashboardSLAController::class, 'dashboardKritis']);
});
Route::get('/dashboard-sla/event/overall', [DashboardSLAController::class, 'overallEventSla']);
Route::get('/dashboard-sla/event/{mappingId}', [DashboardSLAController::class, 'dashboardEventSla']);
Route::get('/dashboard-sla/digital', [DashboardSLAController::class, 'dashboardDigital']);
Route::get('/dashboard-sla/survey-yearly', [DashboardSLAController::class, 'surveyYearly']);
Route::get('/dashboard/uptime/monitoring', [KPIDatabaseKPIController::class, 'UptimePresentase'])->name('dashboard.uptimeMonitoring');

// Laporan Insiden
Route::get('/laporan-insiden', [laporanInsidentController::class, 'index'])->name('index.laporanInsiden');
Route::get('/laporan-insiden/get', [laporanInsidentController::class, 'get'])->name('get.laporanInsiden');
Route::get('/laporan-insiden/form', [laporanInsidentController::class, 'create'])->name('create.laporanInsiden');
Route::post('/laporan-insiden/store', [laporanInsidentController::class, 'store'])->name('store.laporanInsiden');
Route::post('/laporan-insiden/respon', [laporanInsidentController::class, 'respon'])->name('respon.laporanInsiden');
Route::get('/laporan-insiden/detail/{id}', [laporanInsidentController::class, 'detail'])->name('detail.laporanInsiden');
Route::get('/laporan-insiden/edit/{id}', [laporanInsidentController::class, 'edit'])->name('edit.laporanInsiden');
Route::delete('/laporan-insiden/hapus/{id}', [laporanInsidentController::class, 'hapus'])->name('hapus.laporanInsiden');
Route::post('/laporan-insiden/update', [laporanInsidentController::class, 'update'])->name('uodate.laporanInsiden');

// Knowledge Management & System Documentation
Route::get('/knowledge-management/{id}/download', [KnowledgeManagementController::class, 'downloadFile'])->name('knowledge-management.download');
Route::get('features/options/list', [FeatureDocumentationController::class, 'options'])->name('documentation.features.options');

Route::prefix('system/documentation')->name('documentation.')->group(function () {
    Route::get('/features', [FeatureDocumentationController::class, 'index'])->name('features.index');
    Route::post('/features', [FeatureDocumentationController::class, 'store'])->name('features.store');
    Route::get('/features/{id}', [FeatureDocumentationController::class, 'show'])->name('features.show');
    Route::get('/features/{id}/edit-data', [FeatureDocumentationController::class, 'editData'])->name('features.edit-data');
    Route::put('/features/{id}', [FeatureDocumentationController::class, 'update'])->name('features.update');
    Route::delete('/features/{id}', [FeatureDocumentationController::class, 'destroy'])->name('features.destroy');
    Route::get('/features/{id}/manual', [FeatureDocumentationController::class, 'downloadManual'])->name('features.manual');

    Route::get('/import/template', [DocumentationImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/import', [DocumentationImportController::class, 'import'])->name('import');
    Route::get('/export/all', [DocumentationImportController::class, 'exportAll'])->name('export.all');
    Route::get('/export/feature/{id}', [DocumentationImportController::class, 'exportFeature'])->name('export.feature');

    Route::get('/features/{featureId}/codes', [CodeDocumentationController::class, 'index'])->name('codes.index');
    Route::post('/features/{featureId}/codes', [CodeDocumentationController::class, 'store'])->name('codes.store');
    Route::get('/codes/{id}', [CodeDocumentationController::class, 'show'])->name('codes.show');
    Route::put('/codes/{id}', [CodeDocumentationController::class, 'update'])->name('codes.update');
    Route::delete('/codes/{id}', [CodeDocumentationController::class, 'destroy'])->name('codes.destroy');
});

// =========================================================================
// 20. LOGS, SCHEDULE & DEVELOPMENT UTILITIES
// =========================================================================
Route::get('inixcoffeeloglarapelixb95', [LogViewerController::class, 'index'])->middleware('logviewer.access');
Route::get('/dbstan', [\Itpathsolutions\DBStan\Http\Controllers\DBStanController::class, 'index'])
    ->middleware('permission:Akses Development')
    ->name('dbstan.index');

Route::get('/schedule-logs', [ScheduleLogController::class, 'index'])->name('schedule.index');
Route::get('/schedule-logs/data', [ScheduleLogController::class, 'data'])->name('schedule.data');
Route::delete('/schedule-logs/clear-all', [ScheduleLogController::class, 'clearAll'])->name('schedule.clear');

if (app()->isLocal()) {
    Route::get('/testdata', [TestController::class, 'index'])->name('testdata');
}
