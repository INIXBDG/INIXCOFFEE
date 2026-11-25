<?php

use App\Http\Controllers\KPI\DatabaseKPIController as KPIDatabaseKPIController;
use App\Http\Controllers\KPI\DataTargetController;
use App\Http\Controllers\KPI\TargetKPIController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 13. KPI & PENILAIAN 360
// =========================================================================
Route::get('GetDatabasekpi', [KPIDatabaseKPIController::class, 'getData'])->name('GetDatabaseKPI');
Route::get('GetDatabasekpi/profile', [KPIDatabaseKPIController::class, 'getDataProfile'])->name('GetDataProfile.kpi');
Route::get('activity-log', [KPIDatabaseKPIController::class, 'activityLog'])->name('activity.log');
Route::get('activity-log/data', [KPIDatabaseKPIController::class, 'getActivityChart'])->name('activity.log.chart');
Route::get('project/table-data', [KPIDatabaseKPIController::class, 'indexProject'])->name('project.index');
Route::get('project/control-project', [KPIDatabaseKPIController::class, 'controlProject'])->name('project.control');

Route::prefix('kpi-data')->name('kpi.')->group(function () {
    Route::prefix('data-target')->name('dataTarget.')->group(function () {
        Route::get('/index', [DataTargetController::class, 'index'])->name('index');
        Route::get('/template', [DataTargetController::class, 'downloadTemplate'])->name('template');
        Route::post('/import', [DataTargetController::class, 'import'])->name('import');
        Route::put('/{id}', [DataTargetController::class, 'update'])->name('update');
        Route::delete('/{id}', [DataTargetController::class, 'destroy'])->name('destroy');
        Route::get('/api', [DataTargetController::class, 'getDataTargets'])->name('api');
    });

    Route::get('/get-routes-by-jabatan', [TargetKPIController::class, 'getAssistantRoutesByJabatan'])->name('getRoutesByJabatan');
    Route::get('/get-target-by-route', [TargetKPIController::class, 'getDataTargetByRoute'])->name('getTargetByRoute');
    Route::get('/divisi-drilldown', [TargetKPIController::class, 'divisiDrilldown'])->name('divisiDrilldown');

    Route::prefix('overview')->name('overview.')->group(function () {
        Route::get('/index', [TargetKPIController::class, 'kpiOverview'])->name('index');
        Route::get('/get', [TargetKPIController::class, 'getDataOverview'])->name('get');
        Route::get('/index/personal/{id?}', [TargetKPIController::class, 'personalIndex'])->name('indexPersonal');
        Route::get('/kpi/personal/data', [TargetKPIController::class, 'getDataOverviewPersonal'])->name('dataPersonal');
        Route::post('/update-target-per-sales', [TargetKPIController::class, 'updateTargetPerSales'])->name('updateTargetPerSales');
    });

    Route::get('/deadline-detail', [KPIDatabaseKPIController::class, 'getDeadlineDetail'])->name('deadlineDetail');
    Route::get('/activity-detail', [KPIDatabaseKPIController::class, 'getActivityDetail'])->name('activityDetail');
    Route::get('/achievement-detail', [KPIDatabaseKPIController::class, 'getAchievementDetail'])->name('achievementDetail');
    Route::get('/news-detail', [KPIDatabaseKPIController::class, 'getNewsDetail'])->name('newsDetail');

    Route::get('/monitoring/export/pdf', [TargetKPIController::class, 'exportMonitoringPdf'])->name('monitoring.export.pdf');
    Route::get('/monitoring/export/excel', [TargetKPIController::class, 'exportMonitoringExcel'])->name('monitoring.export.excel');
    Route::get('departement/export/excel', [TargetKPIController::class, 'exportDeptExcel'])->name('departement.export.excel');
    Route::get('departement/export/pdf', [TargetKPIController::class, 'exportDeptPdf'])->name('departement.export.pdf');
    Route::get('/export', [TargetKPIController::class, 'exportKpiTemplate'])->name('export');
    Route::get('/export/pdf', [TargetKPIController::class, 'exportKpiTemplatePdf'])->name('export.pdf');

    Route::get('/table-data', [TargetKPIController::class, 'kpiIndex'])->name('index');
    Route::post('/create-target', [TargetKPIController::class, 'createTarget'])->name('createTarget');
    Route::get('/get-data-target', [TargetKPIController::class, 'getDataTarget'])->name('getDataTarget');
    Route::get('/detail-data-target', [TargetKPIController::class, 'detailData'])->name('detail');
    Route::post('/update-target/{id}', [TargetKPIController::class, 'updateTarget'])->name('updateTarget');
    Route::delete('/hapus-data-target/{id}', [TargetKPIController::class, 'hapusTarget'])->name('hapus');
    Route::post('/update-data-target', [TargetKPIController::class, 'updateTarget'])->name('update');
    Route::get('/edit-data-target', [TargetKPIController::class, 'editTarget'])->name('edit');
    Route::get('get-karyawan-by-jabatan', [TargetKPIController::class, 'getKaryawanByJabatan'])->name('getKaryawanByJabatan');
    Route::get('get-dashboard', [TargetKPIController::class, 'getProgressDashboard'])->name('getProgressDasboard');
    Route::get('get-statistika', [TargetKPIController::class, 'getChartStatistics']);

    Route::post('/import-target', [TargetKPIController::class, 'importTarget'])->name('importTarget');
    Route::get('/download-template', [TargetKPIController::class, 'downloadTemplate'])->name('downloadTemplate');
    Route::get('/assistant-routes', [TargetKPIController::class, 'getAssistantRoutesByJabatan'])->name('assistantRoutes');
    Route::post('/update-manual-value', [TargetKPIController::class, 'manualValue'])->name('manualValue');
    Route::post('/update-gap-kompetensi', [TargetKPIController::class, 'updateGapKompetensi'])->name('updateGapKompetensi');
    Route::get('/cleaning-database', [TargetKPIController::class, 'cleaningDatabase'])->name('cleaningDatabase');

    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/get', [TargetKPIController::class, 'getDataTarget'])->name('get');
    });

    Route::get('/panduan/{key}', [TargetKPIController::class, 'getPanduan'])->name('panduan');
    Route::get('/panduan-opsi', [TargetKPIController::class, 'getPanduanOpsi'])->name('panduanOpsi');
    Route::get('/panduan-pdf', [TargetKPIController::class, 'downloadPanduanPdf'])->name('panduanPdf');
});

// Penilaian KPI
Route::get('penilaian/data-form/edit/{kode_form}', [KPIDatabaseKPIController::class, 'formPenilaianEdit']);
Route::post('penilaian/data-form/update', [KPIDatabaseKPIController::class, 'formPenilaianUpdate'])->name('penilaian.form.update');
Route::get('/penilaian/form', [KPIDatabaseKPIController::class, 'formPenilaianData'])->name('penilaian.form.data');
Route::get('/penilaian/form/get', [KPIDatabaseKPIController::class, 'getFormPenilaianData'])->name('penilaian.form.get');
Route::post('/penilaian/clean', [KPIDatabaseKPIController::class, 'clean']);
Route::post('/penilaian/hapus', [KPIDatabaseKPIController::class, 'hapus']);
Route::post('/penilaian/hapus-evaluator/{kodeJenis}/{id_evaluator}/{kodeFormGlobal}', [KPIDatabaseKPIController::class, 'hapusEvaluator']);
Route::get('/penilaian/content/dahsboardKPI/get', [KPIDatabaseKPIController::class, 'contentDashboard'])->name('databaseKPI.dashboardContent');
Route::post('/penilaian/content/dahsboardKPI/download-penilaian-perDivisi', [KPIDatabaseKPIController::class, 'downloadDivisi'])->name('databaseKPI.downloadDivisi');
Route::post('/penilaian/detail/send/catatan', [KPIDatabaseKPIController::class, 'sendCatatan'])->name('penilaian.sendCatatan');
Route::get('/penilaian/preview-pdf', [KPIDatabaseKPIController::class, 'downloadPDF'])->name('penilaian.download.pdf');
Route::post('/kirimPenilaian', [KPIDatabaseKPIController::class, 'kirimEmailData'])->name('penilaian.email');
Route::get('/penilaian/detail/data-penilaian/{kodeForm}/{id_karyawan}/{tipe}', [KPIDatabaseKPIController::class, 'detailPenilaian'])->name('penilaian.detail');
Route::post('/penilaian/get/detail/data-penilaian', [KPIDatabaseKPIController::class, 'GetDetailPenilaian'])->name('penilaian.detail.get');
Route::post('/penilaian/get/detail/data-chart-penilaian', [KPIDatabaseKPIController::class, 'getDetailChartPenilaian'])->name('penilaian.detailChart.get');
Route::post('penilaian/reviewPenilaian', [KPIDatabaseKPIController::class, 'penilaianReview'])->name('penilaianReview');
Route::get('reviewPenilaian/{kodeForm}/{evaluatorId}/{jenis_penilaian}/{idKaryawan}', [KPIDatabaseKPIController::class, 'reviewPenilaian']);
Route::post('penilaianEvaluator/kirim', [KPIDatabaseKPIController::class, 'penilaianEvaluator'])->name('penilaianEvaluator');
Route::get('/getFormPenilaian/{kode_form}/{id_karyawan}', [KPIDatabaseKPIController::class, 'getFromPenilaian'])->name('penilaian.share');
Route::get('/getFormPenilaianUser/{id_evaluator}', [KPIDatabaseKPIController::class, 'getFromPenilaianUser'])->name('penilaian.shareUser');
Route::post('/shareFormPenilaian', [KPIDatabaseKPIController::class, 'shareForm'])->name('penilaian.shareForm');
Route::get('/getDataPenilaian', [KPIDatabaseKPIController::class, 'getDataPenilaian'])->name('penilaian.get.data');
Route::get('/getKategorikpi', [KPIDatabaseKPIController::class, 'indexKategori'])->name('ketegoriKPI.get');
Route::get('/beranda-KPI', [KPIDatabaseKPIController::class, 'indexBerandaKpi'])->name('berandaKPI.get');
Route::get('/createKategorikpi', [KPIDatabaseKPIController::class, 'createKategori'])->name('ketegori.kpi.create');
Route::post('/storeKategorikpi', [KPIDatabaseKPIController::class, 'kategoriStore'])->name('ketegori.kpi.store');
Route::get('/penilaian360/index/{id_karyawan}', [KPIDatabaseKPIController::class, 'index360'])->name('penilaian360');
Route::get('/penilaian360/get/{id_karyawan}', [KPIDatabaseKPIController::class, 'get360'])->name('get360');
Route::get('/template/list', [KPIDatabaseKPIController::class, 'getTemplateList'])->name('template.list');
Route::get('/template/load/{kodeKategori}', [KPIDatabaseKPIController::class, 'loadTemplate'])->name('template.load');
Route::get('/penilaian/form/evaluators', [KPIDatabaseKPIController::class, 'getEvaluatorsByForm'])->name('penilaian.form.evaluators');
Route::post('/penilaian/form/update-divisi-evaluator', [KPIDatabaseKPIController::class, 'updateDivisiEvaluator'])->name('penilaian.form.updateDivisiEvaluator');
