<?php

use App\Http\Controllers\DataPasswordController;
use App\Http\Controllers\HR\ArsipRekrutmenController;
use App\Http\Controllers\HR\BonusTahunanController;
use App\Http\Controllers\HR\DaftarTugasHRController;
use App\Http\Controllers\HR\employeeController;
use App\Http\Controllers\HR\EmployeeJobDeskController;
use App\Http\Controllers\HR\EmployeeStructureController;
use App\Http\Controllers\HR\FeedbackInstukturController;
use App\Http\Controllers\HR\FolderController;
use App\Http\Controllers\HR\HireController;
use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\HR\InstrukturOlController;
use App\Http\Controllers\HR\KaryawanProfileController;
use App\Http\Controllers\HR\KaryawanTaskController;
use App\Http\Controllers\HR\LegalitasController;
use App\Http\Controllers\HR\LspLpkController;
use App\Http\Controllers\HR\MaintenanceController;
use App\Http\Controllers\HR\MasterKaryawanController;
use App\Http\Controllers\HR\payrollController;
use App\Http\Controllers\HR\presenceController;
use App\Http\Controllers\HR\RekapInventarisController;
use App\Http\Controllers\HR\RekapPenjualanController;
use App\Http\Controllers\HR\RekapSJPController;
use App\Http\Controllers\HR\RencanaPembelianHrController;
use App\Http\Controllers\HR\ReportController;
use App\Http\Controllers\HR\ReportSmartController;
use App\Http\Controllers\HR\SopController;
use App\Http\Controllers\HR\StructureInixindoController;
use App\Http\Controllers\HR\ThrController;
use App\Http\Controllers\KPI\TargetKPIController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// 16. HR DASHBOARD, EMPLOYEE & RENCANA PEMBELIAN
// =========================================================================
Route::prefix('rencana-pembelian')->name('rencanaPembelian.')->group(function () {
    Route::get('', [RencanaPembelianHrController::class, 'index'])->name('index');
    Route::get('/export/pdf', [RencanaPembelianHrController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/excel', [RencanaPembelianHrController::class, 'exportExcel'])->name('export.excel');
    Route::post('/store', [RencanaPembelianHrController::class, 'store'])->name('store');
    Route::post('/update/{id}', [RencanaPembelianHrController::class, 'update'])->name('update');
    Route::post('/delete/{id}', [RencanaPembelianHrController::class, 'delete'])->name('delete');
    Route::post('/update-nvoice/{id}', [RencanaPembelianHrController::class, 'updateInvoice'])->name('updateInvoice');
    Route::post('/update-status', [RencanaPembelianHrController::class, 'updateStatus'])->name('updateStatus');
    Route::get('/get-jurnal', [RencanaPembelianHrController::class, 'getJurnalAkuntansi']);
    Route::post('/store-rekap', [RencanaPembelianHrController::class, 'storeRekap'])->name('storeRekap');
});

Route::prefix('hr/daftar-tugas')->name('hr.DaftarTugas.')->group(function () {
    Route::get('/', [DaftarTugasHRController::class, 'index'])->name('index');
    Route::get('/get', [DaftarTugasHRController::class, 'get'])->name('get');
    Route::get('/chart-data', [DaftarTugasHRController::class, 'chartData'])->name('chartData');
});

Route::prefix('employee')->name('employee.')->group(function () {
    Route::get('/job-desk', [EmployeeJobDeskController::class, 'index'])->name('jobdesk.index');
    Route::get('/job-desk/api/{id}', [EmployeeJobDeskController::class, 'show'])->name('jobdesk.show');
    Route::get('/karyawan-profile/{karyawanId}', [EmployeeJobDeskController::class, 'showProfile'])->name('profile.show');
    Route::post('/karyawan-profile', [EmployeeJobDeskController::class, 'storeProfile'])->name('profile.store');
    Route::put('/karyawan-profile/{karyawanId}', [EmployeeJobDeskController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/karyawan-profile/{karyawanId}', [EmployeeJobDeskController::class, 'destroyProfile'])->name('profile.destroy');
    Route::get('/structure', [EmployeeStructureController::class, 'index'])->name('structure.index');
    Route::get('/structure/api/tree', [EmployeeStructureController::class, 'apiTree'])->name('structure.api.tree');
});

Route::prefix('HR-dashboard')->name('HR.')->group(function () {
    Route::get('/', [HRController::class, 'index'])->name('index');
    Route::get('/data', [HRController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/divisions', [HRController::class, 'getDivisions'])->name('dashboard.divisions');
    Route::get('/card-details', [HRController::class, 'getCardDetails'])->name('dashboard.card');
    Route::get('/dashboard/detail', [HRController::class, 'getDetail'])->name('dashboard.detail');
    Route::get('/dashboard/export', [HRController::class, 'exportDetail'])->name('dashboard.export');
    Route::get('/gate-status', [HRController::class, 'gateStatus'])->name('gate.status');
    Route::post('/unlock', [HRController::class, 'unlock'])->middleware('throttle:5,1')->name('unlock');
    Route::post('/lock', [HRController::class, 'lock'])->name('lock');
    Route::post('/setup', [HRController::class, 'storeMaster'])->name('setup');
    Route::post('/lupa', [HRController::class, 'lupaPassword'])->middleware('throttle:10,1')->name('lupa');
    Route::post('/master/setup', [HRController::class, 'storeMaster'])->name('master.setup');

    Route::prefix('instruktur-ol')->name('instruktur-ol.')->group(function () {
        Route::get('/', [InstrukturOlController::class, 'index'])->name('index');
        Route::get('data', [InstrukturOlController::class, 'getData'])->name('data');
        Route::post('/', [InstrukturOlController::class, 'store'])->name('store');

        Route::post('{instruktur}/lsp', [InstrukturOlController::class, 'lspStore'])->whereNumber('instruktur')->name('lsp.store');
        Route::put('lsp/{lsp}', [InstrukturOlController::class, 'lspUpdate'])->whereNumber('lsp')->name('lsp.update');
        Route::delete('lsp/{lsp}', [InstrukturOlController::class, 'lspDestroy'])->whereNumber('lsp')->name('lsp.destroy');

        Route::post('{instruktur}/dokumen/{tipe}', [InstrukturOlController::class, 'dokumenStore'])->whereNumber('instruktur')->where('tipe', 'sertifikasi|pelatihan|kerja-sama|silabus')->name('dokumen.store');
        Route::put('dokumen/{dokumen}', [InstrukturOlController::class, 'dokumenUpdate'])->whereNumber('dokumen')->name('dokumen.update');
        Route::delete('dokumen/{dokumen}', [InstrukturOlController::class, 'dokumenDestroy'])->whereNumber('dokumen')->name('dokumen.destroy');
        Route::get('karyawan-ol', [InstrukturOlController::class, 'karyawanOl'])->name('karyawan-ol');
        Route::post('migrasi-karyawan', [InstrukturOlController::class, 'migrasiKaryawan'])->name('migrasi-karyawan');
        Route::get('{instruktur}', [InstrukturOlController::class, 'show'])->whereNumber('instruktur')->name('show');
        Route::put('{instruktur}', [InstrukturOlController::class, 'update'])->whereNumber('instruktur')->name('update');
        Route::delete('{instruktur}', [InstrukturOlController::class, 'destroy'])->whereNumber('instruktur')->name('destroy');
        Route::get('export', [InstrukturOlController::class, 'export'])->name('export');

        Route::get('{instruktur}/riwayat', [InstrukturOlController::class, 'riwayat'])->whereNumber('instruktur')->name('riwayat');
        Route::get('{instruktur}/riwayat/export', [InstrukturOlController::class, 'exportRiwayat'])->whereNumber('instruktur')->name('riwayat.export');

        Route::get('{instruktur}/pembayaran', [InstrukturOlController::class, 'pembayaran'])->name('pembayaran.index');
        Route::post('{instruktur}/pembayaran', [InstrukturOlController::class, 'pembayaranStore'])->name('pembayaran.store');
        Route::put('pembayaran/{pembayaran}', [InstrukturOlController::class, 'pembayaranUpdate'])->name('pembayaran.update');
        Route::delete('pembayaran/{pembayaran}', [InstrukturOlController::class, 'pembayaranDestroy'])->name('pembayaran.destroy');

        Route::get('{instruktur}/silabus', [InstrukturOlController::class, 'silabus'])->name('silabus.index');
        Route::post('{instruktur}/silabus', [InstrukturOlController::class, 'silabusStore'])->name('silabus.store');
        Route::put('silabus/{silabus}', [InstrukturOlController::class, 'silabusUpdate'])->name('silabus.update');
        Route::delete('silabus/{silabus}', [InstrukturOlController::class, 'silabusDestroy'])->name('silabus.destroy');

        Route::get('/{instruktur}/rkm-pilihan', [InstrukturOlController::class, 'rkmPilihan'])->name('rkm-pilihan');

        // Riwayat mengajar manual (pilih dari RKM)
        Route::post('/{instruktur}/riwayat', [InstrukturOlController::class, 'riwayatStore'])->name('riwayat.store');
        Route::delete('/riwayat/{riwayat}', [InstrukturOlController::class, 'riwayatDestroy'])->name('riwayat.destroy');

        // Materi (sama seperti silabus)
        Route::get('/{instruktur}/materi', [InstrukturOlController::class, 'materi'])->name('materi.index');
        Route::post('/{instruktur}/materi', [InstrukturOlController::class, 'materiStore'])->name('materi.store');
        Route::put('/materi/{materi}', [InstrukturOlController::class, 'materiUpdate'])->name('materi.update');
        Route::delete('/materi/{materi}', [InstrukturOlController::class, 'materiDestroy'])->name('instruktur-ol.materi.destroy');
    });

    Route::prefix('data-password')->name('data-password.')->group(function () {
        Route::get('/', [DataPasswordController::class, 'index'])->name('index');
        Route::get('setup', [DataPasswordController::class, 'setup'])->name('setup');
        Route::post('setup', [DataPasswordController::class, 'storeMaster'])->name('setup.store');
        Route::post('unlock', [DataPasswordController::class, 'unlock'])->middleware('throttle:5,1')->name('unlock');
        Route::post('lock', [DataPasswordController::class, 'lock'])->name('lock');
        Route::get('data', [DataPasswordController::class, 'getData'])->name('data');
        Route::post('/', [DataPasswordController::class, 'store'])->name('store');
        Route::get('{id}/secret', [DataPasswordController::class, 'secret'])->whereNumber('id')->name('secret');
        Route::put('{id}', [DataPasswordController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('{id}', [DataPasswordController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::post('/reset-programmer', [DataPasswordController::class, 'resetProgrammer'])->middleware('throttle:3,1')->name('reset-programmer');
        Route::get('/log/data', [DataPasswordController::class, 'logData'])->name('log.data');
        Route::get('/log/export', [DataPasswordController::class, 'logExport'])->name('log.export');
        Route::post('/log/import', [DataPasswordController::class, 'logImport'])->name('log.import');
        Route::post('/log/cleanup', [DataPasswordController::class, 'logCleanup'])->middleware('throttle:10,1')->name('log.cleanup');
        Route::post('/lupa/verifikasi', [DataPasswordController::class, 'lupaVerifikasi'])->middleware('throttle:10,1')->name('lupa.verifikasi');
        Route::post('/lupa/reset', [DataPasswordController::class, 'lupaReset'])->middleware('throttle:10,1')->name('lupa.reset');
        Route::get('/lupa/riwayat', [DataPasswordController::class, 'lupaRiwayat'])->name('lupa.riwayat');
    });

    Route::prefix('master-data-karyawan')->name('master_karyawan.')->group(function () {
        Route::get('/', [MasterKaryawanController::class, 'index'])->name('index');
        Route::get('/data', [MasterKaryawanController::class, 'get'])->name('data');
        Route::get('/detail/{id}', [MasterKaryawanController::class, 'detail'])->name('detail');
        Route::get('/reminder', [MasterKaryawanController::class, 'reminder'])->name('reminder');
        Route::post('/store', [MasterKaryawanController::class, 'store'])->name('store');
        Route::post('/updateData', [MasterKaryawanController::class, 'UpdateData'])->name('update');
        Route::post('/reminder-update', [MasterKaryawanController::class, 'updateReminder'])->name('reminder_update');
        Route::post('/gaji', [MasterKaryawanController::class, 'storeGaji'])->name('gaji_store');
        Route::get('/{id}/download-dokumen', [MasterKaryawanController::class, 'downloadDokumen'])->name('download_dokumen');
        Route::post('/setup', [MasterKaryawanController::class, 'storeMaster'])->name('setup.store');
        Route::post('/unlock', [MasterKaryawanController::class, 'unlock'])->name('unlock');
        Route::post('/lock', [MasterKaryawanController::class, 'lock'])->name('lock');
        Route::get('/export', [MasterKaryawanController::class, 'export'])->name('export');
    });

    Route::prefix('legalitas')->name('legalitas.')->group(function () {
        Route::get('/', [LegalitasController::class, 'index'])->name('index');
        Route::get('/data', [LegalitasController::class, 'getData'])->name('data');
        Route::post('/store', [LegalitasController::class, 'store'])->name('store');
        Route::put('/update', [LegalitasController::class, 'update'])->name('update');
        Route::put('/delete', [LegalitasController::class, 'delete'])->name('delete');
        Route::post('/setup', [LegalitasController::class, 'storeMaster'])->name('setup.store');
        Route::post('/unlock', [LegalitasController::class, 'unlock'])->middleware('throttle:5,1')->name('unlock');
        Route::post('/lock', [LegalitasController::class, 'lock'])->name('lock');
        Route::get('/file/{id}', [LegalitasController::class, 'file'])->name('file');
    });

    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/index', [employeeController::class, 'index'])->name('index');
        Route::get('/category', [employeeController::class, 'getEmployeesByCategory'])->name('category');
        Route::get('/data', [employeeController::class, 'getEmployeeData'])->name('data');
        Route::get('/headcount/trend', [employeeController::class, 'getHeadcountTrend'])->name('trend');
        Route::get('/headcount/breakdown', [employeeController::class, 'getHeadcountBreakdown'])->name('breakdown');
        Route::get('/export/trend/csv', [employeeController::class, 'exportHeadcountTrendCsv'])->name('trend.export.csv');
        Route::get('/export/trend/pdf', [employeeController::class, 'exportHeadcountTrendPdf'])->name('trend.export.pdf');
        Route::get('/export/breakdown/csv', [employeeController::class, 'exportHeadcountBreakdownCsv'])->name('breakdown.export.csv');
        Route::get('/export/breakdown/pdf', [employeeController::class, 'exportHeadcountBreakdownPdf'])->name('breakdown.export.pdf');
        Route::get('/resigned', [employeeController::class, 'getResignedEmployees'])->name('resigned');
        Route::put('/resigned/{id}', [employeeController::class, 'updateResignData'])->name('resigned.update');
        Route::put('/{id}/move-to-resign', [employeeController::class, 'moveToResign'])->name('moveToResign');
        Route::put('/{id}/restore', [employeeController::class, 'restoreEmployee'])->name('restore');
        Route::get('/reminders', [employeeController::class, 'getEmployeeReminders'])->name('reminders');
        Route::get('/detail/{id}', [employeeController::class, 'getEmployeeDetail'])->name('detail');
        Route::get('/export/csv', [employeeController::class, 'exportEmployeesCsv'])->name('export.csv');
        Route::get('/export/pdf', [employeeController::class, 'exportEmployeesPdf'])->name('export.pdf');
        Route::get('/resigned/export/csv', [employeeController::class, 'exportResignedEmployeesCsv'])->name('resigned.export.csv');
        Route::get('/resigned/export/pdf', [employeeController::class, 'exportResignedEmployeesPdf'])->name('resigned.export.pdf');
        Route::get('/chart-detail', [employeeController::class, 'getChartDetailEmployees'])->name('chart.detail');
        Route::get('/chart-detail/export/csv', [employeeController::class, 'exportChartDetailCsv'])->name('chart.detail.export.csv');
        Route::get('/chart-detail/export/pdf', [employeeController::class, 'exportChartDetailPdf'])->name('chart.detail.export.pdf');
        Route::post('itsm/submit', [employeeController::class, 'submitItsm'])->name('itsm.submit');
    });

    Route::prefix('bonus-tahunan')->name('bonus-tahunan.')->group(function () {
        Route::get('/', [BonusTahunanController::class, 'index'])->name('index');
        Route::get('/data', [BonusTahunanController::class, 'getData'])->name('data');
        Route::post('/update-skema', [BonusTahunanController::class, 'updateSkema'])->name('update-skema');
        Route::post('/bulk-update', [BonusTahunanController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/recalculate', [BonusTahunanController::class, 'recalculateAll'])->name('recalculate');
        Route::get('/export/excel', [BonusTahunanController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [BonusTahunanController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/toggle-include', [BonusTahunanController::class, 'toggleInclude'])->name('toggle-include');
    });

    Route::prefix('thr')->name('thr.')->group(function () {
        Route::get('/', [ThrController::class, 'index'])->name('index');
        Route::get('/data', [ThrController::class, 'getData'])->name('data');
        Route::get('/periods', [ThrController::class, 'getPeriods'])->name('periods');
        Route::post('/generate', [ThrController::class, 'generate'])->name('generate');
        Route::post('/update-item/{id}', [ThrController::class, 'updateItem'])->name('update-item');
        Route::post('/update-setting/{periodId}', [ThrController::class, 'updateSetting'])->name('update-setting');
        Route::delete('/period/{id}', [ThrController::class, 'destroyPeriod'])->name('period.destroy');
        Route::get('/holiday-date', [ThrController::class, 'getHolidayDate'])->name('holiday-date');
        Route::get('/export/excel/{periodId}', [ThrController::class, 'exportExcel'])->name('export.excel');
        Route::post('/toggle-include', [ThrController::class, 'toggleInclude'])->name('toggle-include');
        Route::get('export/pdf', [ThrController::class, 'exportPdf'])->name('export.pdf');
    });

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/index', [payrollController::class, 'index'])->name('index');
        Route::get('/dashboard', [payrollController::class, 'getPayrollDashboard'])->name('dashboard');
        Route::get('/export/excel', [payrollController::class, 'exportPayrollExcel'])->name('export.excel');
        Route::get('/export/pdf', [payrollController::class, 'exportPayrollPdf'])->name('export.pdf');
    });

    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [presenceController::class, 'index'])->name('index');
        Route::get('/analytics', [presenceController::class, 'getAttendanceAnalytics'])->name('analytics');
        Route::get('/export', [presenceController::class, 'exportAttendanceReport'])->name('export');
        Route::get('/division-stats', [presenceController::class, 'getDivisionDailyStats'])->name('division.stats');
        Route::get('/top-late', [presenceController::class, 'getTopLateEmployees'])->name('top.late');
        Route::get('/calendar', [presenceController::class, 'getAttendanceCalendar'])->name('calendar');
        Route::get('/daily-details', [presenceController::class, 'getDailyAttendanceDetails'])->name('daily-details');
    });

    Route::prefix('executive')->name('executive.')->group(function () {
        Route::get('/dashboard', [TargetKPIController::class, 'executiveDashboard'])->name('index');
        Route::get('/export-report', [TargetKPIController::class, 'exportExecutiveReport'])->name('export-report');
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/trend', [TargetKPIController::class, 'getExecutiveTrend'])->name('trend');
            Route::get('/prediction', [TargetKPIController::class, 'getPredictiveAnalysis'])->name('prediction');
            Route::get('/matrix-unified', [TargetKPIController::class, 'getPotentialMatrixUnified'])->name('matrix.unified');
        });
    });

    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/dashboard', [TargetKPIController::class, 'performanceDashboard'])->name('index');
        Route::get('/dashboard/data', [TargetKPIController::class, 'getPerformanceDashboardData'])->name('dashboard.data');
        Route::get('/dashboard/assessment-360-detail-tab', [TargetKPIController::class, 'getAssessment360DetailTab'])->name('dashboard.assessment360.detailTab');
        Route::get('/dashboard/performance-employee-details/{id}', [TargetKPIController::class, 'getPerformanceEmployeeDetails'])->name('dashboard.performanceEmployeeDetails');
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/trend', [TargetKPIController::class, 'getPerformanceTrend'])->name('trend');
            Route::get('/prediction', [TargetKPIController::class, 'getPerformancePrediction'])->name('prediction');
            Route::get('/matrix', [TargetKPIController::class, 'getPerformanceMatrix'])->name('matrix');
        });
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/extract-text', [ReportController::class, 'extractText'])->name('extract.text');
        Route::post('/store', [ReportController::class, 'store'])->name('store');
        Route::get('/{template}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{template}', [ReportController::class, 'update'])->name('update');
        Route::delete('/delete/{template}', [ReportController::class, 'destroy'])->name('destroy');

        Route::get('/{template}/generate', [ReportController::class, 'generateForm'])->name('generate.form');
        Route::post('/{template}/generate', [ReportController::class, 'generate'])->name('generate');
        Route::get('/history', [ReportController::class, 'history'])->name('history');
        Route::get('/history/data', [ReportController::class, 'getHistoryData'])->name('history.data');
        Route::get('/{generation}/preview', [ReportController::class, 'preview'])->name('preview');

        Route::post('/placeholders', [ReportController::class, 'addPlaceholder'])->name('placeholders.store');
        Route::put('/placeholders/{placeholder}', [ReportController::class, 'updatePlaceholder'])->name('placeholders.update');
        Route::delete('/placeholders/{placeholder}', [ReportController::class, 'deletePlaceholder'])->name('placeholders.destroy');

        Route::post('/{template}/settings', [ReportController::class, 'updateSettings'])->name('settings.update');
        Route::get('/generations/{generation}/download', [ReportController::class, 'download'])->name('download');

        Route::get('/create-with-mapping', [ReportController::class, 'createWithMapping'])->name('create.mapping');
        Route::post('/upload-and-map', [ReportController::class, 'uploadAndMap'])->name('upload.map');
        Route::post('/save-with-mapping', [ReportController::class, 'saveWithMapping'])->name('save.mapping');

        Route::post('reports/preview-formula', [ReportController::class, 'previewFormula'])->name('preview.formula');
        Route::post('reports/{template}/reset-counter/{counterKey}', [ReportController::class, 'resetCounter'])->name('reset.counter');

        Route::put('/history/{generation}', [ReportController::class, 'updateHistory'])->name('history.update');
        Route::delete('/history/{generation}', [ReportController::class, 'destroyHistory'])->name('history.destroy');

        Route::post('/{template}/preview-generate', [ReportController::class, 'previewGenerate'])->name('preview.generate');
        Route::get('/{template}/edit-data', [ReportController::class, 'getEditData'])->name('.edit.data');
        Route::get('{template}/generate-modal', [ReportController::class, 'generateFormModal'])->name('.generate.modal');
        Route::post('/scan-docx', [ReportController::class, 'scanDocxForBlanks']);

        Route::prefix('smart')->name('smart.')->group(function () {
            Route::post('/scan', [ReportSmartController::class, 'scan'])->name('scan');
            Route::post('/save', [ReportSmartController::class, 'save'])->name('save');
            Route::get('/{template}/data', [ReportSmartController::class, 'data'])->name('data');
            Route::post('/{template}/update', [ReportSmartController::class, 'updateSmart'])->name('update');
        });
    });

    Route::prefix('hire')->name('hire.')->group(function () {
        Route::get('/', [HireController::class, 'index'])->name('index');
        Route::get('/data', [HireController::class, 'data'])->name('data');
        Route::post('/store', [HireController::class, 'store'])->name('store');
        Route::get('/show/{pelamar}', [HireController::class, 'show'])->name('show');
        Route::put('/update/{pelamar}', [HireController::class, 'update'])->name('update');
        Route::delete('/delete/{pelamar}', [HireController::class, 'destroy'])->name('destroy');
        Route::post('/{pelamar}/lanjut-tahap', [HireController::class, 'lanjutTahap'])->name('lanjutTahap');
        Route::post('/{pelamar}/tolak', [HireController::class, 'tolak'])->name('tolak');
        Route::post('/{pelamar}/jadwal-interview', [HireController::class, 'jadwalkanInterview'])->name('jadwalInterview');
        Route::post('/{pelamar}/kirim-offer', [HireController::class, 'kirimOffer'])->name('kirimOffer');
        Route::post('/{pelamar}/onboarding', [HireController::class, 'onboarding'])->name('onboarding');
        Route::post('/{pelamar}/kirim-email', [HireController::class, 'kirimEmail'])->name('kirimEmail');
        Route::post('/{pelamar}/catatan', [HireController::class, 'tambahCatatan'])->name('catatan');
        Route::get('/{pelamar}/riwayat', [HireController::class, 'riwayat'])->name('riwayat');
        Route::post('/{pelamar}/talent-pool', [HireController::class, 'toggleTalentPool'])->name('toggleTalentPool');
        Route::get('/export/data', [HireController::class, 'export'])->name('export');
        Route::get('/stats/funnel', [HireController::class, 'funnelStats'])->name('funnelStats');
        Route::get('/by-folder/{folderId?}', [HireController::class, 'getPelamarByFolder'])->name('by-folder');
        Route::get('/folder-list', [HireController::class, 'getFolderList'])->name('folder-list');
        Route::post('/penilaian', [HireController::class, 'storePenilaian'])->name('penilaian.store');
        Route::get('/{pelamar}/cv', [HireController::class, 'lihatCV'])->name('cv');
        Route::post('/scan-dokumen', [HireController::class, 'scanDokumen'])->name('scan-dokumen');
        Route::post('/{pelamar}/update', [HireController::class, 'update'])->name('update.post');
        Route::get('/analitik', [HireController::class, 'analitik'])->name('analitik');
        Route::get('/{pelamar}/psikotest', [HireController::class, 'psikotestIndex'])->name('psikotest.index');
        Route::post('/{pelamar}/psikotest', [HireController::class, 'psikotestSimpan'])->name('psikotest.store');
        Route::delete('/{pelamar}/psikotest/{kategori}', [HireController::class, 'psikotestHapus'])->name('psikotest.destroy');
        Route::get('/{pelamar}/psikotest/{kategori}/file', [HireController::class, 'psikotestFile'])->name('psikotest.file');
        Route::get('/{pelamar}/penilaian', [HireController::class, 'penilaianIndex'])->name('penilaian.index');
    });

    Route::prefix('folders')->name('folders.')->group(function () {
        Route::get('/', [FolderController::class, 'index'])->name('index');
        Route::get('/data', [FolderController::class, 'getFolders'])->name('data');
        Route::post('/store', [FolderController::class, 'storeFolder'])->name('store');
        Route::get('/pelamar/belum-folder', [FolderController::class, 'getPelamarBelumFolder'])->name('pelamar.belum-folder');
        Route::post('/pelamar/add', [FolderController::class, 'addPelamar'])->name('pelamar.add');
        Route::post('/pelamar/move', [FolderController::class, 'movePelamar'])->name('pelamar.move');
        Route::post('/pelamar/penilaian', [FolderController::class, 'storePenilaian'])->name('pelamar.penilaian');
        Route::get('/pelamar/{pelamar}/penilaian', [FolderController::class, 'getPenilaian'])->name('pelamar.penilaian.get');
        Route::delete('/pelamar/{pelamarId}/remove', [FolderController::class, 'removePelamar'])->name('pelamar.remove');
        Route::get('/pelamar/{pelamar}/detail', [FolderController::class, 'getDetailPelamar'])->name('pelamar.detail');
        Route::put('/{folder}/rename', [FolderController::class, 'renameFolder'])->name('rename');
        Route::post('/{folder}/pin', [FolderController::class, 'togglePin'])->name('pin');
        Route::post('/{folder}/archive', [FolderController::class, 'archiveFolder'])->name('archive');
        Route::delete('/{folder}', [FolderController::class, 'destroyFolder'])->name('destroy');
        Route::post('/{folder}/move', [FolderController::class, 'moveFolder'])->name('move');
        Route::get('/pelamar/{pelamar}/rekap', [FolderController::class, 'rekapPenilaian'])->name('pelamar.rekap');
    });

    Route::prefix('arsip')->name('arsip.')->group(function () {
        Route::get('/', [ArsipRekrutmenController::class, 'index'])->name('index');
        Route::get('/folders', [ArsipRekrutmenController::class, 'getArsipFolders'])->name('folders');
        Route::get('/folders/{folderId}/pelamar', [ArsipRekrutmenController::class, 'getArsipPelamarByFolder'])->name('folders.pelamar');
        Route::post('/pelamar/move', [ArsipRekrutmenController::class, 'movePelamarArsip'])->name('pelamar.move');
        Route::post('/folders/{folder}/move', [ArsipRekrutmenController::class, 'moveFolderArsip'])->name('folders.move');
        Route::post('/folders/{folder}/restore', [ArsipRekrutmenController::class, 'restoreFolder'])->name('folders.restore');
        Route::delete('/folders/{folder}', [ArsipRekrutmenController::class, 'permanentDeleteFolder'])->name('folders.delete');
        Route::delete('/pelamar/{pelamarFolderId}/restore', [ArsipRekrutmenController::class, 'restorePelamar'])->name('pelamar.restore');
    });

    Route::prefix('structure')->name('structure.')->group(function () {
        Route::get('/', [StructureInixindoController::class, 'index'])->name('index');
        Route::put('/toggle-menus', [StructureInixindoController::class, 'toggleMenus'])->name('toggle.menus');

        Route::get('/karyawans', [StructureInixindoController::class, 'getKaryawans'])->name('karyawans');
        Route::get('/jabatans', [StructureInixindoController::class, 'getJabatans'])->name('jabatans');

        Route::get('/charts', [StructureInixindoController::class, 'charts'])->name('charts.index');
        Route::post('/charts', [StructureInixindoController::class, 'storeChart'])->name('charts.store');
        Route::put('/charts/{chart}', [StructureInixindoController::class, 'updateChart'])->name('charts.update');
        Route::delete('/charts/{chart}', [StructureInixindoController::class, 'destroyChart'])->name('charts.destroy');
        Route::get('/charts/{chart}/tree', [StructureInixindoController::class, 'apiTree'])->name('charts.tree');
        Route::post('/charts/{chart}/nodes', [StructureInixindoController::class, 'store'])->name('nodes.store');

        Route::put('/nodes/{id}', [StructureInixindoController::class, 'update'])->name('nodes.update');
        Route::delete('/nodes/{id}', [StructureInixindoController::class, 'destroy'])->name('nodes.destroy');
        Route::post('/nodes/{id}/move', [StructureInixindoController::class, 'move'])->name('nodes.move');
        Route::post('/nodes/{id}/parents', [StructureInixindoController::class, 'setAdditionalParents'])->name('nodes.parents');

        Route::post('/nodes/positions', [StructureInixindoController::class, 'savePositions'])->name('nodes.positions');
        Route::post('/charts/{chart}/reset-positions', [StructureInixindoController::class, 'resetPositions'])->name('charts.reset.positions');
    });

    Route::prefix('job-desk')->name('job_desk.')->group(function () {
        Route::get('/', [KaryawanTaskController::class, 'index'])->name('index');
        Route::post('/store', [KaryawanTaskController::class, 'store'])->name('store');
        Route::get('/api/{id}', [KaryawanTaskController::class, 'show'])->name('show');
        Route::put('/{id}', [KaryawanTaskController::class, 'update'])->name('update');
        Route::delete('/{id}', [KaryawanTaskController::class, 'destroy'])->name('destroy');
        Route::get('/org/{orgId}', [KaryawanTaskController::class, 'getJobDesk'])->name('getByOrg');
    });

    Route::prefix('sop')->name('sop.')->group(function () {
        Route::post('/store', [SopController::class, 'store'])->name('store');
        Route::put('/{id}', [SopController::class, 'update'])->name('update');
        Route::delete('/{id}', [SopController::class, 'destroy'])->name('destroy');
        Route::get('/api/{id}', [SopController::class, 'show'])->name('show');
    });

    Route::prefix('karyawan-profile')->name('profile.')->group(function () {
        Route::get('/{karyawanId}', [KaryawanProfileController::class, 'show'])->name('show');
        Route::post('/', [KaryawanProfileController::class, 'store'])->name('store');
        Route::put('/{karyawanId}', [KaryawanProfileController::class, 'update'])->name('update');
        Route::delete('/{karyawanId}', [KaryawanProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::put('/{id}', [MaintenanceController::class, 'update'])->name('update')->whereNumber('id');
        Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy')->whereNumber('id');
        Route::get('/analytics', [MaintenanceController::class, 'analytics'])->name('analytics');
        Route::get('/detail', [MaintenanceController::class, 'detail'])->name('detail');
        Route::get('/jurnal', [MaintenanceController::class, 'jurnalList'])->name('jurnal');
        Route::post('/import-jurnal', [MaintenanceController::class, 'importJurnal'])->name('importJurnal');
        Route::get('/export-pdf', [MaintenanceController::class, 'export_pdf'])->name('export_pdf');
        Route::get('/export-excel', [MaintenanceController::class, 'export_excel'])->name('export_excel');
        Route::get('/template-excel', [MaintenanceController::class, 'template_excel'])->name('template_excel');
        Route::post('/import', [MaintenanceController::class, 'import'])->name('import');
    });

    Route::prefix('rekap-spj')->name('rekap_spj.')->group(function () {
        Route::get('/', [RekapSJPController::class, 'index'])->name('index');
        Route::get('/load-data', [RekapSJPController::class, 'getRekapData'])->name('load_data');
        Route::get('/export', [RekapSJPController::class, 'export'])->name('export');
        Route::get('/ajax/jabatan/{divisi}', [RekapSJPController::class, 'getJabatan'])->name('ajax.jabatan');
        Route::get('/ajax/karyawan/{jabatan}', [RekapSJPController::class, 'getKaryawan'])->name('ajax.karyawan');
        Route::get('/export-pdf', [RekapSJPController::class, 'exportPdf'])->name('export_pdf');
        Route::get('/detail-data', [RekapSJPController::class, 'getDetailData'])->name('detail_data');
    });

    Route::prefix('perhitungan-tunjangan')->name('perhitungan-tunjangan.')->group(function () {
        Route::get('/', [payrollController::class, 'indexPerhitungan'])->name('index');
        Route::get('/karyawan-data', [payrollController::class, 'getKaryawanDataPerhitungan'])->name('karyawan-data');
        Route::get('/get-data', [payrollController::class, 'getPayrollDataPerhitungan'])->name('get-data');
        Route::get('/stats', [payrollController::class, 'getStatsPerhitungan'])->name('stats');
        Route::post('/', [payrollController::class, 'storePerhitungan'])->name('store');
        Route::get('/logs-all', [payrollController::class, 'getAllLogsPerhitungan'])->name('logs.all');
        Route::post('/isi-otomatis', [payrollController::class, 'isiOtomatisBpjs'])->name('isi-otomatis');
        Route::post('/karyawan/{karyawanId}/status-bpjs', [payrollController::class, 'setBpjsStatus'])->name('status-bpjs');
        Route::get('/sampah-bpjs', [payrollController::class, 'getBpjsTrash'])->name('trash-bpjs');
        Route::post('/sampah-bpjs/{karyawanId}/restore', [payrollController::class, 'restoreBpjs'])->name('restore-bpjs');
        Route::delete('/delete-all', [payrollController::class, 'deleteAll'])->name('deleteAll');
        Route::get('/export/excel', [payrollController::class, 'exportExcelPerhitungan'])->name('export.excel');
        Route::get('/export/pdf', [payrollController::class, 'exportPdfPerhitungan'])->name('export.pdf');
        Route::get('/{id}', [payrollController::class, 'showPerhitungan'])->name('show');
        Route::put('/{id}', [payrollController::class, 'updatePerhitungan'])->name('update');
        Route::delete('/{id}', [payrollController::class, 'destroyPerhitungan'])->name('destroy');
        Route::post('/{id}/approve', [payrollController::class, 'approvePerhitungan'])->name('approve');
    });

    Route::prefix('rekap-inventaris')->name('rekap_inventaris.')->group(function () {
        Route::get('/', [RekapInventarisController::class, 'index'])->name('index');
        Route::get('/load-data', [RekapInventarisController::class, 'getRekapData'])->name('load_data');
        Route::get('/export', [RekapInventarisController::class, 'export'])->name('export');
        Route::get('/ajax/lokasi/{kategori}', [RekapInventarisController::class, 'getLokasi'])->name('ajax.lokasi');
        Route::get('/ajax/jenis/{lokasi}', [RekapInventarisController::class, 'getJenis'])->name('ajax.jenis');
        Route::get('/export-pdf', [RekapInventarisController::class, 'exportPdf'])->name('export_pdf');
        Route::get('/detail-data', [RekapInventarisController::class, 'getDetailData'])->name('detail_data');
        Route::post('/store', [RekapInventarisController::class, 'store'])->name('store');
        Route::post('/import', [RekapInventarisController::class, 'import'])->name('import');
        Route::get('/sync', [RekapInventarisController::class, 'syncFromInventaris'])->name('sync');
    });

    Route::prefix('perhitungan-pph')->name('perhitungan-pph.')->group(function () {
        Route::get('/', [payrollController::class, 'indexPph'])->name('index');
        Route::get('/data', [payrollController::class, 'getPphData'])->name('data');
        Route::post('/store', [payrollController::class, 'storePph'])->name('store');
        Route::post('/isi-otomatis', [payrollController::class, 'isiOtomatisPph'])->name('isi-otomatis');
        Route::post('/lapor-kortex', [payrollController::class, 'laporKortexPph'])->name('lapor-kortex');
        Route::post('/batal-kortex', [payrollController::class, 'batalKortexPph'])->name('batal-kortex');
        Route::post('/upload-bukti-bayar', [payrollController::class, 'uploadBuktiBayarPph'])->name('upload-bukti-bayar');
        Route::post('/upload-kode-billing', [payrollController::class, 'uploadKodeBillingPph'])->name('upload-kode-billing');
        Route::get('/file/{jenis}', [payrollController::class, 'downloadBerkasPph'])->name('file');
        Route::delete('/delete-all', [payrollController::class, 'deleteAllPph'])->name('delete-all');
        Route::get('/analisis', [payrollController::class, 'getPphAnalysis'])->name('analisis');
        Route::get('/preview', [payrollController::class, 'previewPph'])->name('preview');
        Route::get('/desember', [payrollController::class, 'getPphDesember'])->name('desember');
        Route::get('/desember/export-excel', [payrollController::class, 'exportPphDesemberExcel'])->name('desember.excel');
        Route::get('/desember/export-pdf', [payrollController::class, 'exportPphDesemberPdf'])->name('desember.pdf');
        Route::get('/export-data-excel', [payrollController::class, 'exportPphDataExcel'])->name('export-data.excel');
        Route::get('/export-data-pdf', [payrollController::class, 'exportPphDataPdf'])->name('export-data.pdf');
        Route::get('/export-analisis-excel', [payrollController::class, 'exportPphAnalisisExcel'])->name('export-analisis.excel');
        Route::get('/export-analisis-pdf', [payrollController::class, 'exportPphAnalisisPdf'])->name('export-analisis.pdf');
    });

    Route::prefix('rekapan-penjualan')->name('rekap-penjualan.')->group(function () {
        Route::get('/', [RekapPenjualanController::class, 'index'])->name('index');
        Route::get('/data/{year}/{monthStart?}/{monthEnd?}', [RekapPenjualanController::class, 'getData'])->name('data');
        Route::get('/materi/{year}', [RekapPenjualanController::class, 'getMateriData'])->name('materi');
        Route::get('/mingguan/{year}/{month}', [RekapPenjualanController::class, 'getMingguanData'])->name('mingguan');
        Route::get('/profitabilitas/{year}', [RekapPenjualanController::class, 'getProfitabilitas'])->name('profitabilitas');
    });

    Route::prefix('feedback-instruktur')->name('feedback-instruktur.')->group(function () {
        Route::get('/', [FeedbackInstukturController::class, 'index'])->name('index');
        Route::get('/export/pdf', [FeedbackInstukturController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [FeedbackInstukturController::class, 'exportExcel'])->name('export.excel');
    });

    Route::prefix('lsp-lpk')->name('lsp-lpk.')->group(function () {
        Route::get('/', [LspLpkController::class, 'index'])->name('index');
        Route::get('options', [LspLpkController::class, 'options'])->name('options');
        Route::get('lsp', [LspLpkController::class, 'lspList'])->name('lsp.list');
        Route::post('lsp', [LspLpkController::class, 'lspStore'])->name('lsp.store');
        Route::get('lsp/{lsp}', [LspLpkController::class, 'lspShow'])->name('lsp.show');
        Route::put('lsp/{lsp}', [LspLpkController::class, 'lspUpdate'])->name('lsp.update');
        Route::delete('lsp/{lsp}', [LspLpkController::class, 'lspDestroy'])->name('lsp.destroy');
        Route::post('lsp/{lsp}/asesor', [LspLpkController::class, 'lspAttachAsesor'])->name('lsp.attach');
        Route::delete('lsp/{lsp}/asesor/{asesor}', [LspLpkController::class, 'lspDetachAsesor'])->name('lsp.detach');

        Route::get('program/{jenis}', [LspLpkController::class, 'programList'])->whereIn('jenis', ['sertifikasi', 'pelatihan'])->name('program.list');
        Route::post('program', [LspLpkController::class, 'programStore'])->name('program.store');
        Route::put('program/{program}', [LspLpkController::class, 'programUpdate'])->name('program.update');
        Route::delete('program/{program}', [LspLpkController::class, 'programDestroy'])->name('program.destroy');

        Route::get('asesor', [LspLpkController::class, 'asesorList'])->name('asesor.list');
        Route::post('asesor', [LspLpkController::class, 'asesorStore'])->name('asesor.store');
        Route::get('asesor/{asesor}', [LspLpkController::class, 'asesorShow'])->name('asesor.show');
        Route::put('asesor/{asesor}', [LspLpkController::class, 'asesorUpdate'])->name('asesor.update');
        Route::delete('asesor/{asesor}', [LspLpkController::class, 'asesorDestroy'])->name('asesor.destroy');

        Route::get('riwayat', [LspLpkController::class, 'riwayatList'])->name('riwayat.list');
        Route::post('riwayat', [LspLpkController::class, 'riwayatStore'])->name('riwayat.store');
        Route::put('riwayat/{riwayat}', [LspLpkController::class, 'riwayatUpdate'])->name('riwayat.update');
        Route::delete('riwayat/{riwayat}', [LspLpkController::class, 'riwayatDestroy'])->name('riwayat.destroy');
    });
});
