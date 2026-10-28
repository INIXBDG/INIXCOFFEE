<?php

use App\Http\Controllers\AbsensiKaryawanController;
use App\Http\Controllers\ActivityInstrukturController;
use App\Http\Controllers\AdministrasiKaryawanController;
use App\Http\Controllers\Api\apiController;
use App\Http\Controllers\Api\RKMController;
use App\Http\Controllers\ApprovalPendapatanController;
use App\Http\Controllers\ApprovedNetSalesController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CodeDocumentationController;
use App\Http\Controllers\colaboratorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentScheduleController;
use App\Http\Controllers\creditcardController;
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
use App\Http\Controllers\CVInstrukturController;
use App\Http\Controllers\DaftarPesertaExamController;
use App\Http\Controllers\DaftarTugasController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\DashboardItsmController;
use App\Http\Controllers\DashboardLayoutController;
use App\Http\Controllers\DashboardSLAController;
use App\Http\Controllers\DataPasswordController;
use App\Http\Controllers\dbklienController;
use App\Http\Controllers\DocumentationImportController;
use App\Http\Controllers\examController;
use App\Http\Controllers\ExpenseHubController;
use App\Http\Controllers\FeatureDocumentationController;
use App\Http\Controllers\feedbackController;
use App\Http\Controllers\ForumSSOController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HR\ArsipRekrutmenController;
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
use App\Http\Controllers\IdeInovasiController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\InstructorDevelopmentController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\InvoiceRKMController;
use App\Http\Controllers\izinTigaJamController;
use App\Http\Controllers\jabatanController;
use App\Http\Controllers\JurnalAkuntansiController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KelasAnalisisController;
use App\Http\Controllers\KelasSettingController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KlaimModulController;
use App\Http\Controllers\KnowledgeManagementController;
use App\Http\Controllers\KomisiSalesController;
use App\Http\Controllers\KomplainPesertaController;
use App\Http\Controllers\KoordinasiOfficeBoyController;
use App\Http\Controllers\KPI\DatabaseKPIController as KPIDatabaseKPIController;
use App\Http\Controllers\KPI\DataTargetController;
use App\Http\Controllers\KPI\TargetKPIController;
use App\Http\Controllers\LaporanHarianSalesController;
use App\Http\Controllers\laporanInsidentController;
use App\Http\Controllers\LeadProjectController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\listexamController;
use App\Http\Controllers\managementKelasController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\netSalesController;
use App\Http\Controllers\nilaifeedbackController;
use App\Http\Controllers\NoAkunController;
use App\Http\Controllers\notifController;
use App\Http\Controllers\office\AnalysisReportController;
use App\Http\Controllers\office\BiayaTransportasiController;
use App\Http\Controllers\office\CertificateController;
use App\Http\Controllers\office\DashboardSouvenirController;
use App\Http\Controllers\office\KondisiToolsController;
use App\Http\Controllers\office\ModulController;
use App\Http\Controllers\office\OfficeController;
use App\Http\Controllers\office\OfficeExamController;
use App\Http\Controllers\office\pickupDriverController;
use App\Http\Controllers\office\PoExamSertifaController;
use App\Http\Controllers\office\TagihanPerusahaanController;
use App\Http\Controllers\office\vendorOfficeController;
use App\Http\Controllers\OutstandingController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PenambahanSouvenirController;
use App\Http\Controllers\PengajuanBarangController;
use App\Http\Controllers\PengajuancutiController;
use App\Http\Controllers\pengajuanKlaimController;
use App\Http\Controllers\PengajuanLabdanSubsController;
use App\Http\Controllers\PengajuanSouvenirController;
use App\Http\Controllers\PengajuanSubsController;
use App\Http\Controllers\PenukaranSouvenirController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PerpindahanDBController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PicPenagihanController;
use App\Http\Controllers\ProjectAdministrationController;
use App\Http\Controllers\ProjectHandoverController;
use App\Http\Controllers\ProjectKanbanController;
use App\Http\Controllers\PusherAuthController;
use App\Http\Controllers\RegistrasiController;
use App\Http\Controllers\registexamController;
use App\Http\Controllers\RegistryFeatureController;
use App\Http\Controllers\rekapInstrukturController;
use App\Http\Controllers\RekapitulasiAbsenController;
use App\Http\Controllers\RekapPenilaianExamController;
use App\Http\Controllers\RekomendasiLanjutanController;
use App\Http\Controllers\ReportSalesProjectController;
use App\Http\Controllers\RKMController as ControllersRKMController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleLogController;
use App\Http\Controllers\SouvenirController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SuratPerjalananController;
use App\Http\Controllers\SurveyKepuasanController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TunjanganController;
use App\Http\Controllers\tunjanganEducationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitProjectController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Webinar\CalendarController;
use App\Http\Controllers\Webinar\ChecklistController;
use App\Http\Controllers\Webinar\TimelineItemController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Auth::routes([
    'register'         => false,
    'password.request' => false,
    'password.email'   => false,
    'password.reset'   => false,
    'password.update'  => false,
]);

/*
|--------------------------------------------------------------------------
| Protected Routes (Middleware: auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // =========================================================================
    // 1. CORE, HOME, FORUM SSO, PUSHER & SESSION
    // =========================================================================
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/partials/dashboard', fn() => view('partials.dashboard'));
    Route::post('/session/keep-alive', fn() => response()->json(['status' => 'alive']))->name('session.keep-alive');

    Route::get('/forum', [ForumSSOController::class, 'redirect'])->name('forum.sso');
    Route::get('/forum/logout', [ForumSSOController::class, 'logout']);
    Route::post('/pusher/auth', [PusherAuthController::class, 'auth']);

    Route::get('/admin/layout-setting', [DashboardLayoutController::class, 'index'])->name('admin.layout-setting');
    Route::post('/admin/layout-setting/update', [DashboardLayoutController::class, 'update'])->name('admin.layout-setting.update');

    // =========================================================================
    // 2. USER, KARYAWAN, ROLES & PERMISSIONS
    // =========================================================================
    Route::middleware(['Admin'])->group(function () {
        Route::get('/user/register', [UserController::class, 'create'])->name('user.register');
    });

    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/excel', [UserController::class, 'ExportExcel'])->name('user.excel');
    Route::get('/profile/{hashid}', [UserController::class, 'show'])->name('user.show');
    Route::get('/user/{hashid}/password', [UserController::class, 'editPassword'])->name('user.editPassword');
    Route::put('/user/{hashid}/password', [UserController::class, 'updatePassword'])->name('user.updatePassword');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('/registkaryawan', [UserController::class, 'regist'])->name('user.registkaryawan');
    Route::post('/change-user', [UserController::class, 'changeUser'])->name('change.user');
    Route::get('/get-users', [UserController::class, 'getUsers'])->name('get.users');
    Route::get('/user-dropdown', [UserController::class, 'showUserDropdown'])->name('user.dropdown');
    Route::get('/datas', [UserController::class, 'datas'])->name('datauser');

    Route::get('/userRolePermissions', [UserController::class, 'indexUser'])->name('indexUser');
    Route::get('/userRolePermissions/{id}/edit', [UserController::class, 'editUser'])->name('editUser');
    Route::put('/userRolePermissions/{id}/update', [UserController::class, 'updateUser'])->name('updateUser');

    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::get('/roles/{id}/give-permissions', [RoleController::class, 'addPermissionToRole'])->name('addPermissionToRole');
    Route::put('/roles/{id}/give-permissions', [RoleController::class, 'givePermissionToRole'])->name('givePermissionToRole');

    Route::get('/karyawan/{hashid}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit');
    Route::put('/karyawan/{hashid}', [KaryawanController::class, 'updateData'])->name('karyawan.update');
    Route::get('/gantifoto/{id}', [KaryawanController::class, 'gantiFoto'])->name('karyawan.gantiFoto');
    Route::put('/gantifoto/{id}', [KaryawanController::class, 'updateFoto'])->name('karyawan.updateFoto');

    // Gaji & Slip Gaji
    Route::get('/gaji/karyawan', [KaryawanController::class, 'gajiIndex'])->name('gaji.index');
    Route::post('/gaji', [KaryawanController::class, 'storeGaji'])->name('gaji.store');
    Route::put('/gaji/{id}', [KaryawanController::class, 'updateGaji'])->name('gaji.update');
    Route::get('/slip/gaji', [KaryawanController::class, 'slip'])->name('SlipGaji');

    // =========================================================================
    // 3. NOTIFICATIONS & WEBPUSH
    // =========================================================================
    Route::prefix('webpush')->name('webpush.')->group(function () {
        Route::post('/subscribe', [WebPushController::class, 'subscribe'])->name('subscribe');
        Route::post('/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('unsubscribe');
        Route::get('/vapid-key', [WebPushController::class, 'getVapidKey'])->name('vapid-key');
        Route::post('/test', [WebPushController::class, 'testNotification'])->name('test');
    });
    Route::get('/webpush/status', [WebPushController::class, 'subscriptionStatus']);
    Route::post('/save-push-subscription', [WebhookController::class, 'saveSubscription']);

    Route::get('/notifications/fetch', fn() => response()->json(auth()->user()->unreadNotifications))->name('notifications.fetch');
    Route::get('/notifications/unread-count', fn() => response()->json(['count' => auth()->user()->unreadNotifications()->count()]))->name('notifications.unread-count');
    Route::get('/notifications/modal-content', fn() => view('partials.notifications'))->name('notifications.modal-content');
    Route::put('notifications/{notification}/read', [CommentController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::put('/notifications/markAllAsRead', [CommentController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // =========================================================================
    // 4. STANDARD RESOURCE ROUTES
    // =========================================================================
    Route::resource('/comment', CommentController::class);
    Route::resource('/perusahaan', PerusahaanController::class);
    Route::resource('/materi', MateriController::class);
    Route::resource('/rkm', ControllersRKMController::class);
    Route::resource('/peserta', PesertaController::class);
    Route::resource('/registrasi', RegistrasiController::class);
    Route::resource('/feedback', feedbackController::class);
    Route::resource('/jabatan', jabatanController::class);
    Route::resource('/nilaifeedback', nilaifeedbackController::class);
    Route::resource('/notif', notifController::class);
    Route::resource('/exam', examController::class);
    Route::resource('/listexams', listexamController::class);
    Route::resource('/creditcard', creditcardController::class);
    Route::resource('/registexam', registexamController::class);
    Route::resource('/souvenir', SouvenirController::class);
    Route::resource('/pengajuancuti', PengajuancutiController::class);
    Route::resource('/pengajuanizin', izinTigaJamController::class);
    Route::resource('/pengajuanbarang', PengajuanBarangController::class);
    Route::resource('/suratperjalanan', SuratPerjalananController::class);
    Route::resource('/rekapitulasiabsen', RekapitulasiAbsenController::class);
    Route::resource('/kelasanalisis', KelasAnalisisController::class);
    Route::resource('/paymantAdvance', netSalesController::class)->except(['show']);
    Route::resource('/databasekpi', KPIDatabaseKPIController::class);
    Route::resource('/target', TargetController::class);
    Route::resource('/outstanding', OutstandingController::class);
    Route::resource('/tunjangan', TunjanganController::class);
    Route::resource('/tunjanganEducation', tunjanganEducationController::class);
    Route::resource('/rekapmengajarinstruktur', rekapInstrukturController::class);
    Route::resource('/lembur', LemburController::class);
    Route::resource('/overtime', OvertimeController::class);
    Route::resource('/pengajuanlabsdansubs', PengajuanLabdanSubsController::class);
    Route::resource('/pengajuansubs', PengajuanSubsController::class);
    Route::resource('/pengajuansouvenir', PengajuanSouvenirController::class);
    Route::resource('/daily-activities', DailyActivityController::class);
    Route::resource('/registry', RegistryFeatureController::class)->parameters(['registry' => 'tugas'])->except(['show']);
    Route::resource('penambahansouvenir', PenambahanSouvenirController::class);
    Route::resource('penukaransouvenir', PenukaranSouvenirController::class);
    Route::resource('content-schedules', ContentScheduleController::class);
    Route::resource('colaborator', colaboratorController::class)->except(['show']);
    Route::resource('ide-inovasi', IdeInovasiController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('knowledge-management', KnowledgeManagementController::class);

    // =========================================================================
    // 5. RKM, KELAS ANALISIS, MATERI, PESERTA & REGISTRASI
    // =========================================================================
    Route::get('/datarkm/{tahun}/{bulan}', [PerusahaanController::class, 'datas'])->name('datarkm');
    Route::get('getPerusahaanall', [apiController::class, 'getPerusahaanall'])->name('getPerusahaanall');
    Route::get('getPerusahaanById', [PerusahaanController::class, 'getPerusahaanById'])->name('getPerusahaanById');
    Route::post('/perusahaan/merge', [PerusahaanController::class, 'merge'])->name('perusahaan.merge');

    Route::get('/rkmEditInstruktur/{id}', [ControllersRKMController::class, 'editInstruktur'])->name('editInstruktur');
    Route::put('/rkmUpdateInstruktur', [ControllersRKMController::class, 'updateInstruktur'])->name('updateInstruktur');
    Route::get('/rkmEdit', [ControllersRKMController::class, 'editRKM'])->name('rkmEdit');
    Route::put('/rkmUpdate', [ControllersRKMController::class, 'updateRKM'])->name('rkmUpdate');
    Route::get('/rkm/checklist/{id}', [ControllersRKMController::class, 'getChecklist'])->name('rkm.checklist.get');
    Route::post('/rkm/checklist/store', [ControllersRKMController::class, 'storeChecklist'])->name('rkm.checklist.store');
    Route::post('/rkm/toggle-souvenir', [ControllersRKMController::class, 'toggleSouvenir'])->name('rkm.toggle-souvenir');
    Route::get('cekregisform/{id}', [ControllersRKMController::class, 'cekregisform'])->name('cekregisform');
    Route::get('/rkm/{id}/absensi', [ControllersRKMController::class, 'absensiPeserta'])->name('absensiPeserta');
    Route::get('/rkm/{id}/registform', [ControllersRKMController::class, 'createRegistForm'])->name('createRegistForm');
    Route::put('/rkm/{id}/registformupdate', [ControllersRKMController::class, 'uploadRegistForm'])->name('uploadRegistForm');
    Route::post('/rkm/download/excel', [ControllersRKMController::class, 'excelDownload'])->name('excel');
    Route::post('/rkm/download/excel/adm-sales', [ControllersRKMController::class, 'excelDownloadAdmSales'])->name('excel.rkmAdmSales');
    Route::get('/rkm/upload/page', [ControllersRKMController::class, 'uploadPage'])->name('uploadPage');
    Route::get('/rkm/data/page', [ControllersRKMController::class, 'dataPage'])->name('dataPage');
    Route::get('/rkm/uploadAbsensi/{id}', [ControllersRKMController::class, 'uploadAbsensi'])->name('uploadAbsensi');
    Route::post('/rkm/store/absensi', [ControllersRKMController::class, 'storeAbsensi'])->name('storeAbsensi');
    Route::post('/rkm/delete/absensi', [ControllersRKMController::class, 'deleteAbsensi'])->name('deleteAbsensi');
    Route::get('/rkm/uploadSertifikat/{id}', [ControllersRKMController::class, 'uploadSertifikat'])->name('uploadSertifikat');
    Route::post('/rkm/upload-no-resi/{id}', [ControllersRKMController::class, 'uploadNoResi']);
    Route::post('/rkm/store/sertifikat', [ControllersRKMController::class, 'storeSertifikat'])->name('storeSertifikat');
    Route::post('/rkm/delete/sertifikat', [ControllersRKMController::class, 'deleteSertifikat'])->name('deleteSertifikat');
    Route::post('/rkm/update-makanan', [ControllersRKMController::class, 'updateMakanan'])->name('rkm.updateMakanan');
    Route::post('/export-rkm-excel-admsales', [RKMController::class, 'exportExcel'])->name('export.rkm.excel');
    Route::get('/fetch-attendance', [RKMController::class, 'fetchAttendance'])->name('attendance.fetch');

    // Management Kelas & Kelas Setting
    Route::get('/management-kelas/get', [managementKelasController::class, 'get'])->name('managementKelas.get');
    Route::post('/management-kelas/{id}/batalkan', [managementKelasController::class, 'batalkan']);
    Route::resource('management-kelas', managementKelasController::class);

    Route::prefix('kelas-setting')->name('KelasSetting.')->group(function () {
        Route::get('/index', [KelasSettingController::class, 'index'])->name('index');
        Route::get('/', [KelasSettingController::class, 'getData'])->name('data');
        Route::post('/store', [KelasSettingController::class, 'store'])->name('store');
        Route::patch('/update/{id}', [KelasSettingController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [KelasSettingController::class, 'destroy'])->name('destroy');
        Route::post('/restore/{id}', [KelasSettingController::class, 'restore'])->name('restore');
        Route::post('/clear-all', [KelasSettingController::class, 'clearAll'])->name('clear-all');
    });

    // Kelas Analisis
    Route::get('sinkronDataKelasAnalisis/{year}/{monthStart}/{monthEnd}', [KelasAnalisisController::class, 'sinkronDataKelasAnalisis'])->name('sinkronDataKelasAnalisis');
    Route::get('analisisrkm/{year}/{monthStart}/{monthEnd}', [KelasAnalisisController::class, 'getRkmDataPerBulanPerMinggu']);
    Route::get('analisisrkm/{id}/create', [KelasAnalisisController::class, 'create']);
    Route::get('getAnalisisRKM/{year}/{month}/{week}', [KelasAnalisisController::class, 'getRkmDataByMonthAndWeek']);
    Route::post('analisisrkm/{year}/{month}/{week}/post', [KelasAnalisisController::class, 'postAnalisisMingguan']);
    Route::put('analisisrkm/{year}/{month}/{week}/update', [KelasAnalisisController::class, 'updateAnalisisMingguan']);
    Route::get('analisisrkm/{year}/{month}', [KelasAnalisisController::class, 'getAnalisisMargin']);
    Route::get('kalkulator/analisis/{id}/kelas', [KelasAnalisisController::class, 'kalkulatorview'])->name('kalkulatorview');

    // Materi, Peserta & Registrasi
    Route::get('getMateri/{id}', [MateriController::class, 'getMateriById'])->name('getMateriById');
    Route::get('editstatusmateri/{id}', [MateriController::class, 'editstatusmateri'])->name('editstatusmateri');
    Route::get('getRegistrasiall', [RegistrasiController::class, 'getRegistrasiall'])->name('getRegistrasiall');
    Route::get('registrasi/export/excel', [RegistrasiController::class, 'exportExcel'])->name('registrasi.exportExcel');
    Route::get('registrasi/export/pdf', [RegistrasiController::class, 'exportPDF'])->name('registrasi.exportPDF');
    Route::get('registrasi/export/excels', [RegistrasiController::class, 'exportExcelKhusus'])->name('registrasi.exportExcels');
    Route::get('registrasi/export/pdfs', [RegistrasiController::class, 'exportPDFKhusus'])->name('registrasi.exportPDFs');
    Route::post('/registrasi/souvenir', [RegistrasiController::class, 'storeSouvenir'])->name('registrasi.storeSouvenir');

    Route::get('getPesertaall', [PesertaController::class, 'getPesertaall'])->name('getPesertaall');
    Route::get('getPesertaById/{id}', [PesertaController::class, 'getPesertaById'])->name('getPesertaById');
    Route::get('peserta/export/excel', [PesertaController::class, 'exportExcel'])->name('peserta.exportExcel');
    Route::get('peserta/export/pdf', [PesertaController::class, 'exportPDF'])->name('peserta.exportPDF');
    Route::get('peserta/export/excels', [PesertaController::class, 'exportExcelKhusus'])->name('peserta.exportExcels');
    Route::get('peserta/export/pdfs', [PesertaController::class, 'exportPDFKhusus'])->name('peserta.exportPDFs');

    // =========================================================================
    // 6. EXAM & SERTIFIKASI
    // =========================================================================
    Route::get('/manual-create-pengajuan/exam', [examController::class, 'manualCreate'])->name('manualCreate.exam');
    Route::get('getExam', [examController::class, 'getExam'])->name('getExam');
    Route::get('getExamKondisi', [examController::class, 'getExamKondisi'])->name('getExamKondisi');
    Route::get('/get-po-exam-sertifa', [examController::class, 'getPoExamSertifa'])->name('getPoExamSertifa');
    Route::post('/exam/{id}/upload-invoice', [examController::class, 'uploadInvoice'])->name('exam.uploadInvoice');
    Route::post('/exam/{id}/update-kurs', [examController::class, 'updateKurs'])->name('exam.updateKurs');
    Route::get('/exam/get-kurs/{id}', [examController::class, 'getKurs'])->name('exam.getKurs');
    Route::post('/exam/{id}/update-tanggal', [examController::class, 'updateTanggal'])->name('exam.updateTanggal');
    Route::delete('/exam/{id}/delete-invoice/{filename}', [examController::class, 'deleteSpecificInvoice'])->name('exam.deleteSpecificInvoice');
    Route::put('/exam/update/status', [examController::class, 'updateStatus'])->name('exam.updateStatus');
    Route::get('getHistoriExam', [examController::class, 'getHistoriExam'])->name('getHistoriExam');
    Route::get('/create-only', [examController::class, 'createOnly'])->name('exam.createOnly');
    Route::post('/store-only', [examController::class, 'storeOnly'])->name('exam.storeOnly');
    Route::get('/hargaExam', [examController::class, 'hargaExam']);
    Route::get('/detailHargaExam/{id}', [examController::class, 'detailHargaExam']);
    Route::get('/pengajuanUpdateExam/{id}', [examController::class, 'pengajuanUpdateExam']);
    Route::get('/pengajuanExam/{id}', [examController::class, 'create'])->name('pengajuanExam');
    Route::get('/approvalexam/{id}', [examController::class, 'approvalexam'])->name('approvalexam');
    Route::put('/sendapprovalexam/{id}', [examController::class, 'sendapprovalexam'])->name('exam.approval');
    Route::get('/exam/{id}/add-pengajuan-barang', [examController::class, 'AddPengajuanBarang'])->name('exam.addPengajuanBarang');
    Route::post('/invoice/{data}', [examController::class, 'invoice'])->name('exam.invoice');
    Route::get('/exam/assign-room/{id}', [examController::class, 'assignRoom'])->name('exam.assignRoom')->middleware('can:Edit Exam');
    Route::post('/exam/process-room-assignment', [examController::class, 'processRoomAssignment'])->name('exam.processRoomAssignment');
    Route::get('/rekapexam', [examController::class, 'rekapExam'])->name('exam.rekapexam');
    Route::get('/getRekapExamByMonth/{year}/{month}', [examController::class, 'getRekapExam'])->name('exam.getRekapExam');
    Route::get('/rekapExamExportExcel/{year}/{month}', [examController::class, 'rekapExamExportExcel'])->name('exam.rekapExamExportExcel');

    Route::get('getListExam', [listexamController::class, 'getListExam'])->name('getListExam');
    Route::post('/listexam/delete/{id}', [listexamController::class, 'destroy']);
    Route::post('/providers', [listexamController::class, 'storeProviders'])->name('providers.store');
    Route::post('/vendors', [listexamController::class, 'storeVendor'])->name('vendors.store');
    Route::get('getCC', [creditcardController::class, 'getCC'])->name('getCC');

    Route::get('getRegistrasiexam', [registexamController::class, 'getRegistrasiexam'])->name('getRegistrasiexam');
    Route::get('getRegistrasiexamByIdExam/{id}', [registexamController::class, 'getRegistrasiexamByIdExam'])->name('getRegistrasiexamByIdExam');
    Route::post('/generate/exam/absensi', [registexamController::class, 'generateAbsensi'])->name('absensi.exam');
    Route::get('/invoice/registexam/{id}', [registexamController::class, 'invoice'])->name('registexam.invoice');
    Route::get('/uploadinvoice/{id}', [registexamController::class, 'uploadInvoice'])->name('registexam.uploadInvoice');
    Route::put('/uploadinvoice/{id}', [registexamController::class, 'uploadInvoicePost'])->name('exam.uploadInvoicePost');
    Route::get('/hasilexam/{id}', [registexamController::class, 'createHasilUjian'])->name('exam.createHasilUjian');
    Route::post('/hasilexam/{id}', [registexamController::class, 'storeHasilUjian'])->name('exam.storeHasilUjian');
    Route::get('/hasilexam/{id}/edit', [registexamController::class, 'createHasilUjian'])->name('exam.editHasilUjian');
    Route::put('/hasilexam/{id}/update', [registexamController::class, 'updateHasilUjian'])->name('exam.updateHasilUjian');
    Route::get('/hasilexam/{id}/detail', [registexamController::class, 'showHasilUjian'])->name('exam.showHasilUjian');
    Route::get('/registexam/cc/{id}', [registexamController::class, 'createcc'])->name('exam.createcc');
    Route::put('/registexam/cc/{id}', [registexamController::class, 'storecc'])->name('exam.storecc');

    Route::prefix('daftar-peserta-exam')->name('daftar-peserta-exam.')->group(function () {
        Route::get('/', [DaftarPesertaExamController::class, 'index'])->name('index');
        Route::get('/create/{id}', [DaftarPesertaExamController::class, 'create'])->name('create');
        Route::post('/store', [DaftarPesertaExamController::class, 'store'])->name('store');
        Route::get('/get-data', [DaftarPesertaExamController::class, 'getData'])->name('get-data');
        Route::get('/{id}/edit', [DaftarPesertaExamController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DaftarPesertaExamController::class, 'update'])->name('update');
        Route::post('/store-peserta-ajax', [DaftarPesertaExamController::class, 'storePesertaAjax'])->name('storeAjax');
    });

    Route::get('/rekap-penilaian-exam', [RekapPenilaianExamController::class, 'indexRekap'])->name('exam.rekap-penilaian');
    Route::get('/rekap-penilaian-exam/data', [RekapPenilaianExamController::class, 'getRekapPenilaian'])->name('exam.rekap-penilaian.data');

    // =========================================================================
    // 7. FEEDBACK, KOMPLAIN & SURVEY KEPUASAN
    // =========================================================================
    Route::get('getFeedbacksByMonth/{year}/{month}', [feedbackController::class, 'getFeedbacksByMonth'])->name('getFeedbacksByMonth');
    Route::get('/getTotalFeedbackPertahun', [feedbackController::class, 'getTotalFeedbackPertahun'])->name('office.feedback.get');
    Route::get('getNilaiFeedbackInstRKM/{id}', [feedbackController::class, 'getNilaiFeedbackInstRKM'])->name('getNilaiFeedbackInstRKM');
    Route::get('/feedbackPelayanan', [feedbackController::class, 'pelayananFeedbackShow'])->name('feedbackPelayanan');
    Route::get('/detailfeedbacks', [feedbackController::class, 'detailfeedbacks'])->name('detailfeedbacks');
    Route::get('feedback/export/excels/{id}', [feedbackController::class, 'exportExcelKhusus'])->name('feedback.exportExcels');
    Route::get('feedback/export/pdfs/{id}', [feedbackController::class, 'exportPDFKhusus'])->name('feedback.exportPDFs');
    Route::get('nilaifeedbackexport/{year}/{month}', [nilaifeedbackController::class, 'export'])->name('nilaifeedbackexport');
    Route::get('/dashboard/feedback', [OfficeController::class, 'index']);

    Route::get('/komplain-peserta', [KomplainPesertaController::class, 'index'])->name('komplain-peserta');
    Route::get('/dataNilaiPenilaian/{id}', [KomplainPesertaController::class, 'dataNilaiPenilaian'])->name('dataNilaiPenilaian');
    Route::get('komplain-peserta/create', [KomplainPesertaController::class, 'create'])->name('createKomplain');
    Route::post('komplain-peserta/store', [KomplainPesertaController::class, 'store'])->name('storeKomplain');
    Route::get('/komplain-peserta/{nilaifeedback_id}/edit', [KomplainPesertaController::class, 'edit'])->name('editKomplain');
    Route::post('komplain-peserta/{nilaifeedback_id}/update', [KomplainPesertaController::class, 'update'])->name('updateKomplain');
    Route::get('komplain-peserta/dataKomplain', [KomplainPesertaController::class, 'dataKomplain'])->name('dataKomplain');
    Route::post('komplain-peserta/delete/{id}', [KomplainPesertaController::class, 'destroy'])->name('hapusKomplain');

    Route::get('/survey/kepuasan', [SurveyKepuasanController::class, 'index'])->name('surveykepuasan.index');
    Route::post('/survey/kepuasan/send', [SurveyKepuasanController::class, 'store'])->name('surveykepuasan.store');
    Route::get('/survey/kepuasan/table', [SurveyKepuasanController::class, 'indexTable'])->name('surveyKepuasan.indexTable');
    Route::get('/survey/kepuasan/destroy/{id}', [SurveyKepuasanController::class, 'destroy']);

    // =========================================================================
    // 8. SOUVENIR & CATERING
    // =========================================================================
    Route::prefix('catering')->name('catering.')->group(function () {
        Route::get('index', [CateringController::class, 'index'])->name('index');
        Route::get('get', [CateringController::class, 'getData'])->name('get');
        Route::post('store', [CateringController::class, 'store'])->name('store');
        Route::post('{id}', [CateringController::class, 'update'])->name('update');
        Route::post('{id}/upgrade', [CateringController::class, 'upgradeToCatering'])->name('upgrade');
        Route::post('approved', [CateringController::class, 'approved'])->name('approved');
        Route::delete('{id}', [CateringController::class, 'destroy'])->name('destroy');
        Route::post('{id}/invoice', [CateringController::class, 'updateInvoice'])->name('updateInvoice');
        Route::post('pdf', [CateringController::class, 'PDF'])->name('pdf');
        Route::get('vendors', [CateringController::class, 'getVendors'])->name('getVendors');
        Route::get('detail/{id}', [CateringController::class, 'getDetail'])->name('detail');
    });

    Route::get('getSouvenir', [SouvenirController::class, 'getSouvenir'])->name('getSouvenir');
    Route::get('getSouvenirInactive', [SouvenirController::class, 'getSouvenirInactive'])->name('getSouvenirInactive');
    Route::get('getSouvenirPeserta', [SouvenirController::class, 'getSouvenirPeserta'])->name('getSouvenirPeserta');
    Route::put('/souvenir/{id}/updatestok', [SouvenirController::class, 'updateStok'])->name('souvenir.updateStok');
    Route::get('/souvenir/{id}/editstok', [SouvenirController::class, 'editStok'])->name('souvenir.editStok');
    Route::post('/souvenir/{id}/inactive', [SouvenirController::class, 'inactive'])->name('souvenir.inactive');
    Route::post('/souvenir/{id}/active', [SouvenirController::class, 'active'])->name('souvenir.active');
    Route::get('/rkm/{id}/souvenir', [SouvenirController::class, 'createSouvenirInhouse'])->name('createSouvenirInhouse');
    Route::get('/souvenir/filter/{keyword}', [SouvenirController::class, 'filterSouvenir'])->name('filterSouvenir');
    Route::post('/rkm/storesouvenir', [SouvenirController::class, 'storeSouvenirInhouse'])->name('storeSouvenirInhouse');
    Route::put('/rkm/{id}/updatesouvenir', [SouvenirController::class, 'updateSouvenirInhouse'])->name('updateSouvenirInhouse');

    Route::get('/getPengajuanSouvenir/{month}/{year}', [PengajuanSouvenirController::class, 'getPengajuanSouvenir'])->name('getPengajuanSouvenir');
    Route::put('/pengajuansouvenir/{id}/updateitems', [PengajuanSouvenirController::class, 'updateItems'])->name('pengajuansouvenir.updateItems');
    Route::put('/pengajuansouvenir/{id}/upload-invoice', [PengajuanSouvenirController::class, 'updateInvoice'])->name('pengajuansouvenir.updateInvoice');
    Route::get('/pengajuansouvenir/{id}/export-pdf', [PengajuanSouvenirController::class, 'exportPDF'])->name('pengajuansouvenir.exportpdf');
    Route::get('/getPenambahanSouvenir/{month}/{year}', [PenambahanSouvenirController::class, 'getPenambahanSouvenir'])->name('getPenambahanSouvenir');
    Route::get('/penukaransouvenir/getRiwayat/{month}/{year}', [PenukaranSouvenirController::class, 'getRiwayat'])->name('getRiwayat');
    Route::get('/get-peserta/{rkmId}', [PenukaranSouvenirController::class, 'getPesertaByRKM'])->name('getPeserta');
    Route::get('/dashboard/souvenir', [DashboardSouvenirController::class, 'index'])->name('dashboard.souvenir');

    // =========================================================================
    // 9. PENGAJUAN (CUTI, IZIN, BARANG, LABS, SUBS, KLAIM, SPJ)
    // =========================================================================
    // Cuti & Izin 3 Jam
    Route::get('getPengajuanCuti', [PengajuancutiController::class, 'getPengajuanCuti'])->name('getPengajuanCuti');
    Route::get('getPengajuanCuti/{month}/{year}', [PengajuancutiController::class, 'getPengajuanCutiBulanTahun'])->name('getPengajuanCutiBulanTahun');
    Route::get('pengajuancutirekap', [PengajuancutiController::class, 'rekap'])->name('pengajuancuti.rekap');
    Route::get('pengajuancutiexport/{month}/{year}', [PengajuancutiController::class, 'exportexcel'])->name('pengajuancutiexport');
    Route::get('getPengajuanIzin', [izinTigaJamController::class, 'getPengajuanIzin'])->name('getPengajuanIzin');
    Route::get('/pengajuan-izin-3-jam/excel-download', [izinTigaJamController::class, 'pengajuanJamExcel'])->name('pengajuanIzin.excelDownload');
    Route::get('/pengajuan-izin-3-jam/pdf-download', [izinTigaJamController::class, 'pengajuanJamPDF'])->name('pengajuanIzin.PDFDownload');

    // Surat Perjalanan (SPJ)
    Route::get('getSuratPerjalanan', [SuratPerjalananController::class, 'getSuratPerjalanan'])->name('getSuratPerjalanan');
    Route::get('index/SuratPerjalanan/to/print', [SuratPerjalananController::class, 'createPrint'])->name('createPrint');
    Route::get('get/SuratPerjalanan/to/print', [SuratPerjalananController::class, 'getToPrint'])->name('getToPrint');
    Route::post('download/SuratPerjalanan/to/excel', [SuratPerjalananController::class, 'getToExcelMonth'])->name('getToExcelMonth');
    Route::post('download/SuratPerjalanan/to/excel-year', [SuratPerjalananController::class, 'getToExcelYear'])->name('getToExcelYear');
    Route::post('download/SuratPerjalanan/to/pdf', [SuratPerjalananController::class, 'getToPdfMonth'])->name('getToPdfMonth');
    Route::post('download/SuratPerjalanan/to/pdf-year', [SuratPerjalananController::class, 'getToPdfYear'])->name('getToPdfYear');
    Route::get('/suratperjalanan/{id}/editspj', [SuratPerjalananController::class, 'editspj'])->name('suratperjalanan.editspj');
    Route::put('/suratperjalanan/{id}/approval', [SuratPerjalananController::class, 'approval'])->name('suratperjalanan.approval');
    Route::put('/suratperjalanan/{id}/approve-direksi/{status}', [SuratPerjalananController::class, 'approveDireksi'])->name('suratperjalanan.approve.direksi');
    Route::put('/suratperjalanan/{id}/upload-bukti', [SuratPerjalananController::class, 'uploadBuktiTransfer'])->name('suratperjalanan.uploadBukti');

    // Pengajuan Barang
    Route::get('getPengajuanBarang/{month}/{year}', [PengajuanBarangController::class, 'getPengajuanBarang'])->name('getPengajuanBarang');
    Route::get('getPengajuanBarang/{id}', [KegiatanController::class, 'getPengajuanBarang'])->name('getPengajuanBarangKegiatan');
    Route::get('pengajuanbarang/uploadinvoice/{id}', [PengajuanBarangController::class, 'uploadInvoice'])->name('uploadInvoice');
    Route::put('pengajuanbarang/updateinvoice/{id}', [PengajuanBarangController::class, 'updateInvoice'])->name('updateInvoice');
    Route::put('pengajuanbarang/updatebarang/{id}', [PengajuanBarangController::class, 'updateBarang'])->name('pengajuanbarang.updateBarang');
    Route::get('pengajuanbarang/pdf/{id}', [PengajuanBarangController::class, 'exportPDF'])->name('pengajuanbarang.pdf');

    // Pengajuan Labs & Subs
    Route::get('getPengajuanLabSubs/{month}/{year}', [PengajuanLabdanSubsController::class, 'getPengajuanLabSubs'])->name('getPengajuanLabSubs');
    Route::put('pengajuanlabsdansubs/updatelabsubs/{id}', [PengajuanLabdanSubsController::class, 'updateLabSubs'])->name('pengajuanlabsdansubs.updatelabsubs');
    Route::post('/pengajuanlabsdansubs/{id}/upload-invoice', [PengajuanLabdanSubsController::class, 'uploadInvoice'])->name('pengajuanlabsdansubs.uploadInvoice');
    Route::get('pengajuanlabsdansubs/export-pdf/{id}', [PengajuanLabdanSubsController::class, 'exportPDF'])->name('pengajuanlabsdansubs.exportpdf');
    Route::post('/api/master-labs/{id}/renew', [PengajuanLabdanSubsController::class, 'renewLab']);
    Route::put('/api/master-labs/{id}', [PengajuanLabdanSubsController::class, 'updateMasterLab']);
    Route::get('/api/get-labs-by-rkm/{id}', [PengajuanLabdanSubsController::class, 'getLabsByRkm']);
    Route::get('/api/get-master-labs', [PengajuanLabdanSubsController::class, 'getMasterLabs'])->name('api.master-labs');

    Route::get('getPengajuanSubs/{month}/{year}', [PengajuanSubsController::class, 'getPengajuanSubs'])->name('getPengajuanSubs');
    Route::put('pengajuansubs/updatesubs/{id}', [PengajuanSubsController::class, 'updateLabSubs'])->name('pengajuansubs.updatesubs');
    Route::put('pengajuansubs/updatesubssubs/{id}', [PengajuanSubsController::class, 'updateSubsSubs'])->name('pengajuansubs.updatesubssubs');
    Route::post('/pengajuansubs/{id}/upload-invoice', [PengajuanSubsController::class, 'uploadInvoice'])->name('pengajuansubs.uploadInvoice');
    Route::get('pengajuansubs/export-pdf/{id}', [PengajuanSubsController::class, 'exportPDF'])->name('pengajuansubs.exportpdf');
    Route::post('/api/master-subs', [PengajuanSubsController::class, 'storeMasterSubs'])->name('api.master-subs.store');
    Route::put('/api/master-subs/{id}', [PengajuanSubsController::class, 'updateMasterSubs']);
    Route::post('/api/master-subs/{id}/renew', [PengajuanSubsController::class, 'renewSubs']);
    Route::get('/api/get-subs-by-rkm/{id}', [PengajuanSubsController::class, 'getSubsByRkm']);
    Route::get('/api/get-master-subs', [PengajuanSubsController::class, 'getMasterSubs'])->name('api.master-subs');

    // Pengajuan Klaim & Klaim Modul
    Route::prefix('pengajuan-klaim')->name('pengajuanklaim.')->group(function () {
        Route::get('/', [pengajuanKlaimController::class, 'index'])->name('index');
        Route::post('/excel-download', [pengajuanKlaimController::class, 'pengajuanKlaimExcel'])->name('excel');
        Route::post('/pdf-download', [pengajuanKlaimController::class, 'pengajuanKlaimPDF'])->name('PDF');
        Route::get('/create/no-record', [pengajuanKlaimController::class, 'noRecord'])->name('NoRecord');
        Route::get('/create/scheme-work', [pengajuanKlaimController::class, 'schemeWork'])->name('SchemeWork');
        Route::get('/create/cancel-leave', [pengajuanKlaimController::class, 'cancelLeave'])->name('CancelLeave');
        Route::post('/add/no-record', [pengajuanKlaimController::class, 'createNoRecord'])->name('addNoRecord');
        Route::post('/add/scheme-work', [pengajuanKlaimController::class, 'createSchemeWork'])->name('addSchemeWork');
        Route::post('/add/cancel-leave', [pengajuanKlaimController::class, 'createCancelLeave'])->name('addCancelLeave');
        Route::post('/approval', [pengajuanKlaimController::class, 'approval'])->name('approval');
        Route::post('/reject', [pengajuanKlaimController::class, 'reject'])->name('reject');
        Route::post('/delete/no-record', [pengajuanKlaimController::class, 'deleteNoRecord'])->name('deleteNoRecord');
        Route::post('/delete/cancel-leave', [pengajuanKlaimController::class, 'deleteCancelLeave'])->name('deleteCancelLeave');
        Route::post('/delete/scheme-work', [pengajuanKlaimController::class, 'deleteSchemeWork'])->name('deleteSchemeWork');
    });

    Route::prefix('pengajuanklaimmodul')->name('pengajuanklaimmodul.')->group(function () {
        Route::get('/', [KlaimModulController::class, 'index'])->name('index');
        Route::get('/create', [KlaimModulController::class, 'create'])->name('create');
        Route::post('/', [KlaimModulController::class, 'store'])->name('store');
        Route::get('/data/{month}/{year}', [KlaimModulController::class, 'getKlaimModul'])->name('data');
        Route::get('/{id}', [KlaimModulController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KlaimModulController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KlaimModulController::class, 'update'])->name('update');
        Route::delete('/{id}', [KlaimModulController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/approve', [KlaimModulController::class, 'approve'])->name('approve');
    });

    // =========================================================================
    // 10. ABSENSI, LEMBUR, OVERTIME & TUNJANGAN
    // =========================================================================
    Route::get('getAbsen', [RekapitulasiAbsenController::class, 'getAbsen'])->name('getAbsen');
    Route::get('RekapitulasiAbsenperKaryawanExport/{year}/{month}', [RekapitulasiAbsenController::class, 'exportperKaryawan'])->name('RekapitulasiAbsenperKaryawanExport');
    Route::get('RekapitulasiAbsenperBulanExport/{year}/{month}', [RekapitulasiAbsenController::class, 'exportperBulan'])->name('RekapitulasiAbsenperBulanExport');
    Route::get('RekapitulasiWaktuKeterlambatanExport/{year}', [RekapitulasiAbsenController::class, 'exportKeterlambatan'])->name('RekapitulasiWaktuKeterlambatanExport');
    Route::get('jumlahAbsensi/{id_karyawan}/{bulan}/{tahun}', [AbsensiKaryawanController::class, 'jumlahAbsensi'])->name('jumlahAbsensi');
    Route::get('/cekip', [AbsensiKaryawanController::class, 'cekip'])->name('cekip');

    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::post('/', [AbsensiKaryawanController::class, 'storeAbsensi'])->name('masuk');
        Route::post('/update', [AbsensiKaryawanController::class, 'storeKeluar'])->name('keluar');
        Route::get('/create', [AbsensiKaryawanController::class, 'create'])->name('create');
        Route::post('/manual', [AbsensiKaryawanController::class, 'absenManual'])->name('manual');
        Route::get('/karyawan', [AbsensiKaryawanController::class, 'absensiKaryawan'])->name('karyawan');
        Route::get('/{id}/edit', [RekapitulasiAbsenController::class, 'edit'])->name('edit');

        Route::get('/pengajuan-klaim/no-recorded', [AbsensiKaryawanController::class, 'noRecord'])->name('noRecord');
        Route::post('/create/pengajuan-klaim/no-recorded', [AbsensiKaryawanController::class, 'createNoRecord'])->name('createNoRecord');
        Route::post('/approve/pengajuan-klaim/no-recorded', [AbsensiKaryawanController::class, 'ApproveNoRecord'])->name('approveNoRecord');
        Route::post('/delete/pengajuan-klaim/no-recorded', [AbsensiKaryawanController::class, 'deleteNoRecord'])->name('deleteNoRecord');

        Route::get('/pengajuan-klaim/scheme-work', [AbsensiKaryawanController::class, 'schemeWork'])->name('schemeWork');
        Route::post('/create/pengajuan-klaim/scheme_work', [AbsensiKaryawanController::class, 'createSchemeWork'])->name('createSchemeWork');
        Route::post('/approve/pengajuan-klaim/scheme-work', [AbsensiKaryawanController::class, 'ApproveSchemeWork'])->name('approveSchemeWork');
        Route::post('/delete/pengajuan-klaim/scheme-work', [AbsensiKaryawanController::class, 'deleteSchemeWork'])->name('deleteSchemeWork');

        Route::get('/pengajuan-klaim/cancel-leave', [AbsensiKaryawanController::class, 'cancelLeave'])->name('cancelLeave');
        Route::post('/create/pengajuan-klaim/cancel-leave', [AbsensiKaryawanController::class, 'createCancelLeave'])->name('createCancelLeave');
        Route::post('/approve/pengajuan-klaim/cancel-leave', [AbsensiKaryawanController::class, 'ApproveCancelLeave'])->name('approveCancelLeave');
        Route::post('/delete/pengajuan-klaim/cancel-leave', [AbsensiKaryawanController::class, 'deleteCancelLeave'])->name('deleteCancelLeave');
    });

    // Lembur & Overtime
    Route::get('getSuratPerintahLembur', [LemburController::class, 'getSuratPerintahLembur'])->name('getSuratPerintahLembur');
    Route::get('getLemburKaryawan', [LemburController::class, 'getLemburKaryawan'])->name('getLemburKaryawan');
    Route::put('/lembur/{id}/updateKaryawan', [LemburController::class, 'updateKaryawan'])->name('lembur.updateKaryawan');
    Route::get('/lembur/{id}/editKaryawan', [LemburController::class, 'editKaryawan'])->name('lembur.editKaryawan');
    Route::post('/lembur/masuk', [LemburController::class, 'absenMasuk'])->name('lembur.masuk');
    Route::post('/lembur/pulang', [LemburController::class, 'absenPulang'])->name('lembur.pulang');
    Route::put('/lembur/approval/{id}', [LemburController::class, 'approvalLemburKaryawan'])->name('lembur.approvalLemburKaryawan');

    Route::get('getOvertimeLembur/{month}/{year}', [OvertimeController::class, 'getOvertimeLembur'])->name('getOvertimeLembur');
    Route::get('getOvertimeLemburByKaryawan/{id}/{month}/{year}', [OvertimeController::class, 'getOvertimeLemburByKaryawan'])->name('getOvertimeLemburByKaryawan');
    Route::put('/overtimeApproving', [OvertimeController::class, 'approvalHitungLemburKaryawan'])->name('overtime.approvalHitungLemburKaryawan');
    Route::get('/export-lembur-excel/{year}/{month}', [OvertimeController::class, 'exportExcel'])->name('overtime.exportExcel');
    Route::get('/export-lembur-pdf/{id}/{year}/{month}', [OvertimeController::class, 'exportPDF'])->name('overtime.exportPDF');

    // Tunjangan, BPJS & Rekap Instruktur
    Route::get('getJenisTunjanganIndex', [TunjanganController::class, 'getJenisTunjanganIndex'])->name('getJenisTunjanganIndex');
    Route::get('getJenisTunjanganUmum', [TunjanganController::class, 'getJenisTunjanganUmum'])->name('getJenisTunjanganUmum');
    Route::get('getTunjanganSaya/{id}/{month}/{year}', [TunjanganController::class, 'getTunjanganSaya'])->name('getTunjanganSaya');
    Route::get('getDataBPJS/{id}', [TunjanganController::class, 'ambilDataBPJS'])->name('ambilDataBPJS');
    Route::get('getTunjanganSayaGenerate/{id}/{month}/{year}', [TunjanganController::class, 'getTunjanganSayaGenerate'])->name('getTunjanganSayaGenerate');
    Route::get('/tunjangan/approval', [TunjanganController::class, 'indexApproval'])->name('tunjangan.approval');
    Route::get('/getTunjanganPendingApproval/{month}/{year}', [TunjanganController::class, 'getTunjanganPendingApproval'])->name('getTunjanganPendingApproval');
    Route::post('/approveTunjangan', [TunjanganController::class, 'approveTunjangan'])->name('approveTunjangan');
    Route::post('/rejectTunjangan', [TunjanganController::class, 'rejectTunjangan'])->name('rejectTunjangan');
    Route::post('/bulkApproveTunjangan', [TunjanganController::class, 'bulkApproveTunjangan'])->name('bulkApproveTunjangan');
    Route::get('/getApprovalHistory/{month}/{year}', [TunjanganController::class, 'getApprovalHistory'])->name('getApprovalHistory');
    Route::get('/checkTunjanganStatus/{id}/{month}/{year}', [TunjanganController::class, 'checkTunjanganStatus'])->name('checkTunjanganStatus');
    Route::get('generate-tunjangan-pdf/{id}/{month}/{year}', [TunjanganController::class, 'generateTunjanganPDF'])->name('generateTunjanganPDF');
    Route::get('penghitunganTunjangan', [TunjanganController::class, 'penghitunganTunjangan'])->name('penghitunganTunjangan');
    Route::get('tunjanganManualCreate', [TunjanganController::class, 'createManual'])->name('createManual');
    Route::post('penghitunganTunjanganManual', [TunjanganController::class, 'storeManual'])->name('tunjangan.storeManual');
    Route::post('tunjangankelompok', [TunjanganController::class, 'storeManualTunjangan'])->name('tunjangan.storekelompok');
    Route::get('getTunjanganEdu', [TunjanganController::class, 'getJenisTunjanganEdu'])->name('getTunjanganEducation');
    Route::get('getTunjanganOffice', [TunjanganController::class, 'getJenisTunjanganOffice'])->name('getTunjanganOffice');
    Route::get('getTunjanganSales', [TunjanganController::class, 'getJenisTunjanganSales'])->name('getTunjanganSales');
    Route::get('/tunjangangenerate', [TunjanganController::class, 'indexGenerate'])->name('tunjangangenerate.index');
    Route::get('tunjanganExportPDF/{month}/{year}', [TunjanganController::class, 'tunjanganExportPDF'])->name('tunjanganExportPDF');
    Route::get('tunjanganExportExcel/{month}/{year}', [TunjanganController::class, 'tunjanganExportExcel'])->name('tunjanganExportExcel');

    Route::get('getListRekapInstruktur/{bulan}/{tahun}', [tunjanganEducationController::class, 'getListRekapInstruktur'])->name('getListRekapInstruktur');
    Route::get('tunjanganEduExportExcel/{month}/{year}', [tunjanganEducationController::class, 'tunjanganEduExportExcel'])->name('tunjanganEduExportExcel');

    Route::get('editMengajarInstruktur/{id}', [rekapInstrukturController::class, 'editMengajarInstruktur'])->name('editMengajarInstruktur');
    Route::get('getMengajarInstruktur/{id}/{month}/{year}', [rekapInstrukturController::class, 'getMengajarInstruktur'])->name('getMengajarInstruktur');
    Route::get('cekLevel/{id}', [rekapInstrukturController::class, 'cekLevel'])->name('cekLevel');
    Route::get('getListMengajar/{bulan}/{tahun}', [rekapInstrukturController::class, 'getListMengajar'])->name('getListMengajar');
    Route::get('sinkronData', [rekapInstrukturController::class, 'sinkronData'])->name('sinkronData');
    Route::get('getTunjanganEdu/{id}/{month}/{year}', [rekapInstrukturController::class, 'getTunjanganEdu'])->name('getTunjanganEdu');

    // =========================================================================
    // 11. FINANCE, AKUNTANSI, INVOICE, OUTSTANDING & EXPENSE HUB
    // =========================================================================
    // Payment Advance / NetSales
    Route::get('/paymantAdvance/edit/{id}', [netSalesController::class, 'edit'])->name('netSales.edit.index');
    Route::get('paymantAdvance/{year}/{month}', [netSalesController::class, 'getRkmDataPerBulanPerMinggu']);
    Route::get('paymantAdvance/{id}/create/form-view', [netSalesController::class, 'create']);
    Route::get('/paymantAdvance/detail/{id}/view', [netSalesController::class, 'detail'])->name('netsales.detail');
    Route::post('/paymantAdvance/detail/data/get', [netSalesController::class, 'dataDetail'])->name('netsales.data.detail.get');
    Route::post('/paymantAdvance/approved', [ApprovedNetSalesController::class, 'approve'])->name('netsales.approved');
    Route::post('/paymantAdvance/data/get/', [netSalesController::class, 'dataEdit'])->name('netSales.edit.get');
    Route::put('/paymantAdvance/data/update', [netSalesController::class, 'updateNetSales'])->name('netSales.update');
    Route::get('/download/pdf/netsales/{year}/{month}', [netSalesController::class, 'DownloadPDF'])->name('netSales.download');
    Route::get('/download/pdf/netsales/{id}', [netSalesController::class, 'pdfSendiri'])->name('netSales.download.pdfSendiri');
    Route::get('/netsales/{id}/detail', [netSalesController::class, 'detailNetSales']);

    // Outstanding
    Route::get('getOutstandingLunas', [OutstandingController::class, 'getOutstandingLunas'])->name('getOutstandingLunas');
    Route::get('getOutstandingHutang', [OutstandingController::class, 'getOutstandingHutang'])->name('getOutstandingHutang');
    Route::get('getOutstandingRKM/{year}/{month}', [OutstandingController::class, 'getOutstandingRKM'])->name('getOutstandingRKM');
    Route::get('getOutstandingPA', [OutstandingController::class, 'getOutstandingPA'])->name('getOutstandingPA');
    Route::get('/outstanding/{id}/detail', [OutstandingController::class, 'detailPA'])->name('detailDataOutstanding');
    Route::get('singkronDataOutstandingRKM', [OutstandingController::class, 'singkronDataOutstanding'])->name('outstanding.singkronDataOutstanding');
    Route::get('/outstanding/export/excel', [OutstandingController::class, 'exportExcel'])->name('outstanding.export.excel');
    Route::get('/outstanding/export/pdf', [OutstandingController::class, 'exportPdf'])->name('outstanding.export.pdf');
    Route::get('/download/dokumen/{id}', [OutstandingController::class, 'dokumenGabungan'])->name('dokumenGabungan');
    Route::post('/generate/pdf/peserta/{id}', [OutstandingController::class, 'generatePdfPeserta'])->name('generatePdfPeserta');

    // Invoice & Kwitansi
    Route::get('/invoice', [InvoiceRKMController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/create/{id}', [InvoiceRKMController::class, 'create'])->name('invoice.create');
    Route::post('/invoice', [InvoiceRKMController::class, 'store'])->name('invoice.store');
    Route::get('/invoice/{id}', [InvoiceRKMController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{id}/edit', [InvoiceRKMController::class, 'edit'])->name('invoice.edit');
    Route::put('/invoice/{id}', [InvoiceRKMController::class, 'update'])->name('invoice.update');
    Route::delete('/invoice/{id}', [InvoiceRKMController::class, 'destroy'])->name('invoice.destroy');
    Route::get('/invoices/{id}/export-pdf', [InvoiceRKMController::class, 'exportPdf'])->name('invoices.export-pdf');
    Route::get('/invoices/{id}/export-excel', [InvoiceRKMController::class, 'exportExcel'])->name('invoices.export-excel');
    Route::get('/invoice/download/{id}', [InvoiceRKMController::class, 'downloadPDF'])->name('download.pdf');
    Route::post('/invoice-bulk', [InvoiceRKMController::class, 'bulkStore'])->name('invoice.bulk.store');
    Route::post('/invoice/{invoice}/peserta-pdf', [InvoiceRKMController::class, 'pesertaPdf'])->name('invoice.peserta.pdf');
    Route::post('/invoice/{invoice}/peserta-kwitansi', [InvoiceRKMController::class, 'pesertaKwitansiPdf'])->name('invoice.peserta.kwitansi');
    Route::post('/invoice/{invoice}/update-number', [InvoiceRKMController::class, 'updateNumber'])->name('invoice.number.update');
    Route::get('/invoice/{id}/kwitansi', [InvoiceRKMController::class, 'kwitansi'])->name('invoice.kwitansi');
    Route::get('/invoice/{invoiceId}/kwitansi/create', [InvoiceRKMController::class, 'createKwitansi'])->name('kwitansi.create');
    Route::post('/kwitansi/store', [InvoiceRKMController::class, 'storeKwitansi'])->name('kwitansi.store');
    Route::get('/kwitansi/{id}', [InvoiceRKMController::class, 'showKwitansi'])->name('kwitansi.show');
    Route::get('/kwitansi/download/{id}', [InvoiceRKMController::class, 'downloadPdfKwitansi'])->name('kwitansi.pdf');

    // Jurnal Akuntansi, No Akun & Income Statement
    Route::prefix('jurnalakuntansi')->group(function () {
        Route::get('/', [JurnalAkuntansiController::class, 'index'])->name('jurnalakuntansi.index');
        Route::get('/data', [JurnalAkuntansiController::class, 'getData'])->name('getJurnalAkuntansi');
        Route::get('/belum-jurnal', [JurnalAkuntansiController::class, 'getBelumJurnal'])->name('jurnalakuntansi.belumJurnal');
        Route::post('/store-manual/{id}', [JurnalAkuntansiController::class, 'storeManual'])->name('jurnalakuntansi.storeManual');
        Route::get('/{id}/edit', [JurnalAkuntansiController::class, 'edit'])->name('jurnalakuntansi.edit');
        Route::put('/{id}', [JurnalAkuntansiController::class, 'update'])->name('jurnalakuntansi.update');
        Route::post('/petty-cash', [JurnalAkuntansiController::class, 'storePettyCash'])->name('jurnalakuntansi.storePettyCash');
        Route::post('/import-excel', [JurnalAkuntansiController::class, 'importExcel'])->name('jurnalakuntansi.importExcel');
        Route::get('/belum-jurnal-netsales', [JurnalAkuntansiController::class, 'getBelumJurnalNetSales'])->name('jurnalakuntansi.belumJurnalNetSales');
        Route::post('/store-manual-netsales/{id}', [JurnalAkuntansiController::class, 'storeManualNetSales']);
        Route::get('/export', [JurnalAkuntansiController::class, 'export'])->name('jurnalakuntansi.export');
        Route::get('/pdf/{id}', [JurnalAkuntansiController::class, 'eksportPdf'])->name('jurnalakuntansi.pdf');
        Route::post('/otomatisasi/data', [JurnalAkuntansiController::class, 'otomatisasiJurnal'])->name('jurnalakuntansi.otomatisasiJurnal');
        Route::get('/belum-jurnal-sp', [JurnalAkuntansiController::class, 'getBelumJurnalSuratPerjalanan'])->name('jurnalakuntansi.belumJurnalSuratPerjalanan');
        Route::post('/store-manual-surat-perjalanan/{id}', [JurnalAkuntansiController::class, 'storeManualSuratPerjalanan'])->name('jurnalakuntansi.storeManualSuratPerjalanan');
        Route::get('/export-pdf-kk', [JurnalAkuntansiController::class, 'exportPdfKK'])->name('jurnalakuntansi.exportPdfKK');
    });

    Route::prefix('no-akun')->name('no_akun.')->group(function () {
        Route::get('/data', [NoAkunController::class, 'getData'])->name('data');
        Route::post('/', [NoAkunController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [NoAkunController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NoAkunController::class, 'update'])->name('update');
        Route::delete('/{id}', [NoAkunController::class, 'destroy'])->name('destroy');
        Route::post('/import', [NoAkunController::class, 'importExcel'])->name('import');
    });

    Route::prefix('income-statement')->name('income-statement.')->group(function () {
        Route::get('/', [IncomeStatementController::class, 'index'])->name('index');
        Route::post('/store', [IncomeStatementController::class, 'store'])->name('store');
        Route::get('/laporan', [IncomeStatementController::class, 'laporan'])->name('laporan');
    });

    // Expense Hub
    Route::prefix('expense-hub')->name('expensehub.')->group(function () {
        Route::get('/index', [ExpenseHubController::class, 'index'])->name('index');
        Route::get('/get', [ExpenseHubController::class, 'get'])->name('get');
        Route::get('/create', [ExpenseHubController::class, 'create'])->name('create');
        Route::post('/store', [ExpenseHubController::class, 'store'])->name('store');
        Route::post('/export-pdf', [ExpenseHubController::class, 'PDF'])->name('pdf');
        Route::put('/approved', [ExpenseHubController::class, 'approved'])->name('approved');
        Route::get('/show/{id}', [ExpenseHubController::class, 'show'])->name('show');
        Route::get('/destroy/{id}', [ExpenseHubController::class, 'destroy'])->name('destroy');
        Route::get('/invoice/{id}', [ExpenseHubController::class, 'invoice'])->name('invoice');
        Route::put('/updateinvoice/{id}', [ExpenseHubController::class, 'updateInvoice'])->name('updateInvoice');
        Route::put('/update/{id}', [ExpenseHubController::class, 'update'])->name('update');
    });

    // =========================================================================
    // 12. CHARTS & ANALYTICS
    // =========================================================================
    Route::get('getTarget', [TargetController::class, 'getTarget'])->name('getTarget');
    Route::get('/chart/jumlah-update-materi-perbulan', [MateriController::class, 'chartJumlahUpdateMateriPerbulan'])->name('chart.jumlah-update-materi-perbulan');
    Route::get('/chart/silabus-per-instruktur-per-tahun', [MateriController::class, 'chartSilabusPerInstrukturPerTahun']);
    Route::get('/chart/hari-mengajar-instruktur-per-tahun', [ControllersRKMController::class, 'chartHariMengajarInstrukturPerTahun'])->name('chart.hari-mengajar-instruktur-per-tahun');
    Route::get('getYearlySales/{year}', [HomeController::class, 'getYearSales'])->name('getYearSales');
    Route::get('/getProjectTarget/{year}/target-project', [HomeController::class, 'getProjectTarget'])->name('getProjectTarget');

    Route::get('getPenjualanPerBulan/{year}', [ChartController::class, 'getPenjualanPerBulan'])->name('getPenjualanPerBulan');
    Route::get('getPerSalesPerTahun/{year}', [ChartController::class, 'getPerSalesPerTahun'])->name('getPerSalesPerTahun');
    Route::get('getPerSalesPerQuartal/{year}', [ChartController::class, 'getPerSalesPerQuartal'])->name('getPerSalesPerQuartal');
    Route::get('getAnalisisMarginByYear/{year}', [ChartController::class, 'getAnalisisMarginByYear'])->name('getAnalisisMarginByYear');
    Route::get('getAbsensiYearly/{year}', [ChartController::class, 'getAbsensiYearly'])->name('getAbsensiYearly');
    Route::get('getTabInix/{year}', [ChartController::class, 'getTabInix'])->name('getTabInix');
    Route::get('getSouvenirYearly/{year}', [ChartController::class, 'getSouvenirYearly'])->name('getSouvenirYearly');
    Route::get('getTotalFeedbackPerbulan/{year}/{month}', [ChartController::class, 'getTotalFeedbackPerbulan'])->name('getTotalFeedbackPerbulan');
    Route::get('getTotalMengajarPerbulan/{year}/{month}', [ChartController::class, 'getTotalMengajarPerbulan'])->name('getTotalMengajarPerbulan');
    Route::get('getTotalMateriPerbulan/{year}/{month}', [ChartController::class, 'getTotalMateriPerbulan'])->name('getTotalMateriPerbulan');
    Route::get('getTotalMengajarPerJenisMateriPerTahun/{year}/{month}', [ChartController::class, 'getTotalMengajarPerJenisMateriPerTahun'])->name('getTotalMengajarPerJenisMateriPerTahun');
    Route::get('getAbsenPerbulan/{year}/{month}', [ChartController::class, 'getAbsenPerbulan'])->name('getAbsenPerbulan');

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
    });

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
        Route::get('/data-tagihan/{id}', [TagihanPerusahaanController::class, 'dataTagihan']);
        Route::get('/karyawan-information/{id}', [OfficeController::class, 'dataKaryawan']);
        Route::get('/laporan/status-karyawan', [OfficeController::class, 'laporanStatusKaryawan'])->name('office.laporan.status-karyawan');
        Route::get('/laporan/status-karyawan/detail', [OfficeController::class, 'detailKaryawanStatus'])->name('office.laporan.status-karyawan.detail');
        Route::get('/laporan/trend-karyawan', [OfficeController::class, 'laporanTrendKaryawan'])->name('office.laporan.trend-karyawan');
        Route::get('/export/status-karyawan-pdf', [OfficeController::class, 'exportStatusKaryawanPdf'])->name('office.export.status-karyawan-pdf');
        Route::get('/dashboard/tunjangan', [OfficeController::class, 'getDashboardTunjanganOffice'])->name('office.dashboard.tunjangan');
        Route::get('/export/tunjangan-pdf', [OfficeController::class, 'exportTunjanganPdf'])->name('office.export.tunjangan.pdf');
        Route::get('/export-gaji-pdf', [OfficeController::class, 'exportPdfGaji']);

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

        // Approval Pendapatan & Komisi Sales
        Route::prefix('approval-pendapatan')->name('approvalPendapatan.')->group(function () {
            Route::get('/index', [ApprovalPendapatanController::class, 'index'])->name('index');
            Route::get('/get/{tahun}/{bulan}', [ApprovalPendapatanController::class, 'get'])->name('get');
            Route::post('update/{id}', [ApprovalPendapatanController::class, 'update'])->name('update');
            Route::get('total-tahunan/{tahun}/{bulan}', [ApprovalPendapatanController::class, 'totalTahunan']);
            Route::get('/lock-status', [ApprovalPendapatanController::class, 'checkLockStatus']);
            Route::post('/setup-lock', [ApprovalPendapatanController::class, 'setupLockPassword']);
            Route::post('/unlock', [ApprovalPendapatanController::class, 'unlock']);
            Route::post('/change-lock-password', [ApprovalPendapatanController::class, 'changeLockPassword']);
            Route::post('/change-accounting-password', [ApprovalPendapatanController::class, 'changeAccountingPassword']);
            Route::post('/setup-accounting-password', [ApprovalPendapatanController::class, 'setupAccountingPassword']);
            Route::post('/export-excel', [ApprovalPendapatanController::class, 'exportExcel'])->name('export.excel');
        });

        Route::prefix('komisi-sales')->name('komisiSales.')->group(function () {
            Route::get('/index', [KomisiSalesController::class, 'index'])->name('index');
            Route::get('/get/{tahun}/{quartal}/{bulan?}', [KomisiSalesController::class, 'get'])->name('get');
            Route::post('/export-pdf', [KomisiSalesController::class, 'exportPdf'])->name('export-pdf');
            Route::post('/export-excel', [KomisiSalesController::class, 'exportExcel'])->name('export-excel');
            Route::post('/update-inline/{id_rkm}', [KomisiSalesController::class, 'updateInline'])->name('update-inline');
            Route::post('/update-row/{id_rkm}', [KomisiSalesController::class, 'updateRow']);
            Route::get('/lock-status', [KomisiSalesController::class, 'checkLockStatus']);
            Route::post('/unlock', [KomisiSalesController::class, 'unlock']);
            Route::post('/setup-lock', [KomisiSalesController::class, 'setupLockPassword']);
            Route::post('/change-lock-password', [KomisiSalesController::class, 'changeLockPassword']);
            Route::post('/change-accounting-password', [KomisiSalesController::class, 'changeAccountingPassword']);
            Route::post('/setup-accounting-password', [KomisiSalesController::class, 'setupAccountingPassword']);
            Route::delete('/delete-row/{id_rkm}', [KomisiSalesController::class, 'deleteRow']);
        });

        Route::prefix('exam')->name('office.exam.')->group(function () {
            Route::get('/index', [OfficeExamController::class, 'indexOffice'])->name('index');
            Route::get('/detail/{id}', [OfficeExamController::class, 'showDetailExam']);
            Route::post('/update-bundling', [OfficeExamController::class, 'updateBundling'])->name('updateBundling');
            Route::get('/rekap', [OfficeExamController::class, 'indexRekap'])->name('rekap.index');
            Route::get('/rekap/json', [OfficeExamController::class, 'rekapJson'])->name('rekap.json');
            Route::get('/{year}/{month}', [OfficeExamController::class, 'showExamMonth'])->where(['year' => '[0-9]+', 'month' => '[0-9]+']);
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
            Route::get('/delete/{id}', [StockOpnameController::class, 'delete'])->name('delete');
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

    // =========================================================================
    // 16. HR DASHBOARD, EMPLOYEE & RENCANA PEMBELIAN
    // =========================================================================
    Route::prefix('rencana-pembelian')->name('rencanaPembelian.')->group(function () {
        Route::get('', [RencanaPembelianHrController::class, 'index'])->name('index');
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
            Route::post('itsm/submit', function () {
                return response()->json(['success' => true, 'message' => 'Request submitted']);
            })->name('itsm.submit');
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
            Route::get('/api/tree', [StructureInixindoController::class, 'apiTree'])->name('api.tree');
            Route::post('/sync', [StructureInixindoController::class, 'sync'])->name('sync');
            Route::post('/reorder', [StructureInixindoController::class, 'reorder'])->name('reorder');
            Route::put('/update/{id}', [StructureInixindoController::class, 'update'])->name('update');
            Route::delete('/{id}', [StructureInixindoController::class, 'destroy'])->name('destroy');
            Route::post('/store', [StructureInixindoController::class, 'store'])->name('store');
            Route::get('/karyawans', [StructureInixindoController::class, 'getKaryawans'])->name('karyawans');
            Route::post('/multi-parent', [StructureInixindoController::class, 'setMultiParent'])->name('multiParent');
            Route::put('/toggle-menus', [StructureInixindoController::class, 'toggleMenus'])->name('toggle.menus');
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
            Route::get('/ajax/jabatan/{divisi}', [RekapSJPController::class, 'getJabatan']);
            Route::get('/ajax/karyawan/{jabatan}', [RekapSJPController::class, 'getKaryawan']);
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
            Route::get('/ajax/lokasi/{kategori}', [RekapInventarisController::class, 'getLokasi']);
            Route::get('/ajax/jenis/{lokasi}', [RekapInventarisController::class, 'getJenis']);
            Route::get('/export-pdf', [RekapInventarisController::class, 'exportPdf'])->name('export_pdf');
            Route::get('/detail-data', [RekapInventarisController::class, 'getDetailData'])->name('detail_data');
            Route::post('/store', [RekapInventarisController::class, 'store'])->name('store');
            Route::post('/import', [RekapInventarisController::class, 'import'])->name('import');
            Route::get('/sync', [RekapInventarisController::class, 'syncFromInventaris'])->name('sync');
        });

        Route::prefix('perhitungan-pph')->name('perhitungan-pph.')->group(function () {
            Route::get('/', [payrollController::class, 'indexPph'])->name('index');
            Route::get('/data', [payrollController::class, 'getPphData']);
            Route::post('/store', [payrollController::class, 'storePph']);
            Route::post('/isi-otomatis', [payrollController::class, 'isiOtomatisPph']);
            Route::post('/lapor-kortex', [payrollController::class, 'laporKortexPph']);
            Route::post('/batal-kortex', [payrollController::class, 'batalKortexPph']);
            Route::post('/upload-bukti-bayar', [payrollController::class, 'uploadBuktiBayarPph']);
            Route::post('/upload-kode-billing', [payrollController::class, 'uploadKodeBillingPph']);
            Route::get('/file/{jenis}', [payrollController::class, 'downloadBerkasPph']);
            Route::delete('/delete-all', [payrollController::class, 'deleteAllPph'])->name('delete-all');
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
    });

    // =========================================================================
    // 17. INSTRUCTOR DEVELOPMENT, CV & ACTIVITIES
    // =========================================================================
    Route::get('/activityinstruktur-data', [ActivityInstrukturController::class, 'getActivitiesData'])->name('api.activities');
    Route::post('/activityinstruktur-store', [ActivityInstrukturController::class, 'store'])->name('api.activities.store');
    Route::post('/activityinstruktur-update', [ActivityInstrukturController::class, 'update'])->name('api.activities.proof_update');
    Route::get('/activityinstruktur', [ActivityInstrukturController::class, 'index'])->name('activities.index');
    Route::get('/activityinstruktur-data/summary', [ActivityInstrukturController::class, 'getSummaryData'])->name('api.activities.summary');

    Route::get('/development', [InstructorDevelopmentController::class, 'index'])->name('development.index');
    Route::post('/development/sertifikasi', [InstructorDevelopmentController::class, 'storeSertifikasi'])->name('sertifikasi.store');
    Route::delete('/development/sertifikasi/{id}', [InstructorDevelopmentController::class, 'destroySertifikasi'])->name('sertifikasi.destroy');
    Route::post('/development/sertifikasi/{id}/approve', [InstructorDevelopmentController::class, 'approveSertifikasi'])->name('sertifikasi.approve');
    Route::put('/development/sertifikasi/{id}', [InstructorDevelopmentController::class, 'updateSertifikasi'])->name('sertifikasi.update');
    Route::post('/development/sertifikasi/{id}/upload-bukti', [InstructorDevelopmentController::class, 'uploadBuktiSertifikasi'])->name('sertifikasi.uploadBukti');
    Route::post('/development/sertifikasi/{id}/renew', [InstructorDevelopmentController::class, 'storeRenewal'])->name('sertifikasi.renew');

    Route::post('/development/pelatihan', [InstructorDevelopmentController::class, 'storePelatihan'])->name('pelatihan.store');
    Route::delete('/development/pelatihan/{id}', [InstructorDevelopmentController::class, 'destroyPelatihan'])->name('pelatihan.destroy');
    Route::post('/development/pelatihan/{id}/approve', [InstructorDevelopmentController::class, 'approvePelatihan'])->name('pelatihan.approve');
    Route::put('/development/pelatihan/{id}', [InstructorDevelopmentController::class, 'updatePelatihan'])->name('pelatihan.update');
    Route::post('/development/pelatihan/{id}/upload-bukti', [InstructorDevelopmentController::class, 'uploadBukti'])->name('pelatihan.uploadBukti');

    Route::post('/specialization', [InstructorDevelopmentController::class, 'storeSpecialization'])->name('specialization.store');
    Route::put('/specialization/{id}', [InstructorDevelopmentController::class, 'updateSpecialization'])->name('specialization.update');
    Route::delete('/specialization/{id}', [InstructorDevelopmentController::class, 'destroySpecialization'])->name('specialization.destroy');

    Route::prefix('cv-instruktur')->name('cv-instruktur.')->group(function () {
        Route::get('/', [CVInstrukturController::class, 'index'])->name('index');
        Route::get('/data', [CVInstrukturController::class, 'data'])->name('data');
        Route::get('/{id}', [CVInstrukturController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [CVInstrukturController::class, 'downloadPdf'])->name('pdf');
    });

    // =========================================================================
    // 18. PROJECTS, KANBAN, TIMELINE & COLLABORATOR
    // =========================================================================
    Route::get('/daily-activities-data', [DailyActivityController::class, 'activitiesData']);
    Route::patch('/daily-activities/{dailyActivity}/quick-update', [DailyActivityController::class, 'quickUpdate'])->name('daily-activities.quick-update');

    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/tasks', [KanbanController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/update-state', [KanbanController::class, 'updateState'])->name('tasks.update-state');
    Route::patch('/tasks/{id}', [KanbanController::class, 'update'])->name('tasks.update');
    Route::get('/tasks/{task}/activities', [KanbanController::class, 'getTaskActivities'])->name('tasks.activities');
    Route::delete('tasks/{task}', [KanbanController::class, 'destroy'])->name('tasks.destroy');

    Route::patch('/registry/{tugas}/start', [RegistryFeatureController::class, 'startTask'])->name('registry.start');
    Route::patch('/registry/{tugas}/finish', [RegistryFeatureController::class, 'finishTask'])->name('registry.finish');
    Route::get('/registry/data', [RegistryFeatureController::class, 'getRegistry'])->name('registry.data');

    Route::get('/projects/administrasi/get-data', [ProjectAdministrationController::class, 'getAdministrasi'])->name('getAdministrasi');
    Route::post('/projects/administrasi/{id}/update-stage', [ProjectAdministrationController::class, 'updateStage'])->name('administrasi.updateStage');
    Route::resource('/projects/administrasi', ProjectAdministrationController::class);
    Route::post('/projects/{id}/update-info', [ProjectAdministrationController::class, 'updateProjectInfo'])->name('projects.update_info');

    Route::get('/projects/kanban', [ProjectKanbanController::class, 'index'])->name('projects.kanban.index');
    Route::get('/projects/kanban/get-tasks', [ProjectKanbanController::class, 'getTasks']);
    Route::get('/projects/kanban/{id}/team-members', [ProjectKanbanController::class, 'getTeamMembers']);
    Route::post('/projects/kanban/{id}/assign-team', [ProjectKanbanController::class, 'assignTeam']);
    Route::post('/projects/kanban/tasks', [ProjectKanbanController::class, 'storeTask']);
    Route::patch('/projects/kanban/tasks/{id}', [ProjectKanbanController::class, 'updateTask']);
    Route::delete('/projects/kanban/tasks/{id}', [ProjectKanbanController::class, 'deleteTask']);
    Route::patch('/projects/kanban/tasks/{task}/status', [ProjectKanbanController::class, 'updateStatus'])->name('tasks.status.update');
    Route::post('/projects/kanban/tasks/{id}/activities', [ProjectKanbanController::class, 'storeActivity']);
    Route::get('/projects/kanban/tasks/{id}/activities', [ProjectKanbanController::class, 'getActivities']);

    Route::get('/projects/handovers', [ProjectHandoverController::class, 'index'])->name('handovers.index');
    Route::get('/projects/handovers/data', [ProjectHandoverController::class, 'getHandovers'])->name('handovers.data');
    Route::post('/projects/handovers/{id}/upload-bast', [ProjectHandoverController::class, 'uploadBAST'])->name('handovers.upload_bast');

    Route::prefix('projects/leads')->name('leads.')->group(function () {
        Route::get('/', [LeadProjectController::class, 'index'])->name('index');
        Route::get('/data', [LeadProjectController::class, 'getLeads'])->name('data');
        Route::post('/store', [LeadProjectController::class, 'store'])->name('store');
        Route::delete('/{id}', [LeadProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/update-status', [LeadProjectController::class, 'updateStatus'])->name('update_status');
        Route::post('/{id}/update-data', [LeadProjectController::class, 'updateLead'])->name('update_data');
    });

    Route::prefix('projects/reports')->group(function () {
        Route::get('/sales', [ReportSalesProjectController::class, 'index'])->name('reports.sales');
        Route::get('/sales/data', [ReportSalesProjectController::class, 'getRecapData'])->name('reports.sales.data');
    });

    Route::prefix('visit-projects')->name('visit-projects.')->group(function () {
        Route::get('/', [VisitProjectController::class, 'index'])->name('index');
        Route::get('/get', [VisitProjectController::class, 'get'])->name('get');
        Route::post('/store', [VisitProjectController::class, 'store'])->name('store');
        Route::put('/{visitProject}', [VisitProjectController::class, 'update'])->name('update');
        Route::delete('/{visitProject}', [VisitProjectController::class, 'destroy'])->name('destroy');
    });

    // Webinar / Timeline & Content Schedules
    Route::get('/timeline', [CalendarController::class, 'index'])->name('timeline.index');
    Route::get('/api/checklist/{mappingId}', [ChecklistController::class, 'index']);
    Route::patch('/api/checklist/{id}/toggle', [ChecklistController::class, 'toggle']);
    Route::put('/api/checklist/{id}/detail', [ChecklistController::class, 'updateDetail']);
    Route::post('/api/timeline-item', [TimelineItemController::class, 'store']);
    Route::post('/api/event/{id}/update', [CalendarController::class, 'updateEvent']);

    Route::patch('content-schedules/{contentSchedule}/mark-uploaded', [ContentScheduleController::class, 'markAsUploaded'])->name('content-schedules.mark-uploaded');
    Route::get('/colaborator/data', [colaboratorController::class, 'getData'])->name('colaborator.data');

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

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
    Route::post('/tickets/{ticket}/finish', [TicketController::class, 'finish'])->name('tickets.finish');
    Route::post('/tickets/{ticket}/block', [TicketController::class, 'block'])->name('tickets.block');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');
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
    Route::get('/dashboard/uptime/monitoring', [KPIDatabaseKPIController::class, 'UptimePresentase'])->name('dashboard.uptimeMonitoring');

    // Laporan Insiden
    Route::get('/laporan-insiden', [laporanInsidentController::class, 'index'])->name('index.laporanInsiden');
    Route::get('/laporan-insiden/get', [laporanInsidentController::class, 'get'])->name('get.laporanInsiden');
    Route::get('/laporan-insiden/form', [laporanInsidentController::class, 'create'])->name('create.laporanInsiden');
    Route::post('/laporan-insiden/store', [laporanInsidentController::class, 'store'])->name('store.laporanInsiden');
    Route::post('/laporan-insiden/respon', [laporanInsidentController::class, 'respon'])->name('respon.laporanInsiden');
    Route::get('/laporan-insiden/detail/{id}', [laporanInsidentController::class, 'detail'])->name('detail.laporanInsiden');
    Route::get('/laporan-insiden/edit/{id}', [laporanInsidentController::class, 'edit'])->name('edit.laporanInsiden');
    Route::get('/laporan-insiden/hapus/{id}', [laporanInsidentController::class, 'hapus'])->name('hapus.laporanInsiden');
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
    // 20. LOGS, SCHEDULE & TESTING UTILITIES
    // =========================================================================
    Route::get('inixcoffeeloglarapelixb95', [LogViewerController::class, 'index'])->middleware('logviewer.access');

    Route::get('/schedule-logs', [ScheduleLogController::class, 'index'])->name('schedule.index');
    Route::get('/schedule-logs/data', [ScheduleLogController::class, 'data'])->name('schedule.data');
    Route::delete('/schedule-logs/clear-all', [ScheduleLogController::class, 'clearAll'])->name('schedule.clear');

    Route::get('/testdata', [TestController::class, 'index'])->name('testdata');
    Route::get('/test-error', function () {
        throw new Exception('Test error from Handler.php');
    });
});
