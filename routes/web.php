<?php

use App\Http\Controllers\AbsensiKaryawanController;
use App\Http\Controllers\ActivityInstrukturController;
use App\Http\Controllers\Api\apiController;
use App\Http\Controllers\Api\RKMController;
use App\Http\Controllers\ApprovedNetSalesController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\colaboratorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentScheduleController;
use App\Http\Controllers\creditcardController;
use App\Http\Controllers\CVInstrukturController;
use App\Http\Controllers\DaftarPesertaExamController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\DashboardLayoutController;
use App\Http\Controllers\examController;
use App\Http\Controllers\ExpenseHubController;
use App\Http\Controllers\feedbackController;
use App\Http\Controllers\ForumSSOController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IdeInovasiController;
use App\Http\Controllers\IncomeStatementController;
use App\Http\Controllers\InstructorDevelopmentController;
use App\Http\Controllers\InvoiceRKMController;
use App\Http\Controllers\izinTigaJamController;
use App\Http\Controllers\jabatanController;
use App\Http\Controllers\JurnalAkuntansiController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KelasAnalisisController;
use App\Http\Controllers\KelasSettingController;
use App\Http\Controllers\KlaimModulController;
use App\Http\Controllers\KnowledgeManagementController;
use App\Http\Controllers\KomplainPesertaController;
use App\Http\Controllers\KPI\DatabaseKPIController as KPIDatabaseKPIController;
use App\Http\Controllers\LaporanPengajuanPendingController;
use App\Http\Controllers\LeadProjectController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\listexamController;
use App\Http\Controllers\managementKelasController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\netSalesController;
use App\Http\Controllers\nilaifeedbackController;
use App\Http\Controllers\NoAkunController;
use App\Http\Controllers\notifController;
use App\Http\Controllers\NotificationUtilityController;
use App\Http\Controllers\office\DashboardSouvenirController;
use App\Http\Controllers\office\OfficeController;
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
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PesertaController;
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
use App\Http\Controllers\ReportSalesProjectController;
use App\Http\Controllers\RKMController as ControllersRKMController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SouvenirController;
use App\Http\Controllers\SuratPerjalananController;
use App\Http\Controllers\SurveyKepuasanController;
use App\Http\Controllers\TargetController;
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

/*
|--------------------------------------------------------------------------
| Public & Authentication Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Auth::routes([
    'register' => false,
    'reset'    => false,
    'verify'   => false,
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
    Route::get('/partials/dashboard', [HomeController::class, 'dashboardPartial']);
    Route::post('/session/keep-alive', [HomeController::class, 'keepAlive'])->name('session.keep-alive');

    Route::get('/forum', [ForumSSOController::class, 'redirect'])->name('forum.sso');
    Route::get('/forum/logout', [ForumSSOController::class, 'logout']);
    Route::post('/pusher/auth', [PusherAuthController::class, 'auth']);

    Route::get('/admin/layout-setting', [DashboardLayoutController::class, 'index'])->name('admin.layout-setting');
    Route::post('/admin/layout-setting/update', [DashboardLayoutController::class, 'update'])->name('admin.layout-setting.update');
    Route::post('/admin/layout-setting/reset', [DashboardLayoutController::class, 'reset'])->name('admin.layout-setting.reset');

    // =========================================================================
    // 2. USER, KARYAWAN, ROLES & PERMISSIONS
    // =========================================================================
    Route::middleware(['Admin'])->group(function () {
        Route::get('/user/register', [UserController::class, 'create'])->name('user.register');
        Route::post('/registkaryawan', [UserController::class, 'regist'])->name('user.registkaryawan');
    });

    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/excel', [UserController::class, 'ExportExcel'])->name('user.excel');
    Route::get('/profile/{hashid}', [UserController::class, 'show'])->name('user.show');
    Route::get('/user/{hashid}/password', [UserController::class, 'editPassword'])->name('user.editPassword');
    Route::put('/user/{hashid}/password', [UserController::class, 'updatePassword'])->name('user.updatePassword');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/get-users', [UserController::class, 'getUsers'])->name('get.users');
    Route::get('/datas', [UserController::class, 'datas'])->name('datauser');

    Route::middleware(['permission:Akses Development'])->group(function () {
        Route::post('/change-user', [UserController::class, 'changeUser'])->name('change.user');
        Route::get('/user-dropdown', [UserController::class, 'showUserDropdown'])->name('user.dropdown');

        Route::get('/userRolePermissions', [UserController::class, 'indexUser'])->name('indexUser');
        Route::get('/userRolePermissions/{id}/edit', [UserController::class, 'editUser'])->name('editUser');
        Route::put('/userRolePermissions/{id}/update', [UserController::class, 'updateUser'])->name('updateUser');
    });

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

    Route::get('/notifications/fetch', [NotificationUtilityController::class, 'fetch'])->name('notifications.fetch');
    Route::get('/notifications/unread-count', [NotificationUtilityController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/modal-content', [NotificationUtilityController::class, 'modalContent'])->name('notifications.modal-content');
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

    // =========================================================================
    // LAPORAN PENGAJUAN PENDING (Cuti, Barang, SPJ khusus Koordinator ITSM, SPV Sales, Education Manager, GM)
    // =========================================================================
    Route::get('/laporan-pengajuan-pending', [LaporanPengajuanPendingController::class, 'index'])->name('laporan-pending.index');
    Route::get('/laporan-pengajuan-pending/summary', [LaporanPengajuanPendingController::class, 'getSummary'])->name('laporan-pending.summary');
    Route::get('/laporan-pengajuan-pending/data-cuti', [LaporanPengajuanPendingController::class, 'getDataCuti'])->name('laporan-pending.data-cuti');
    Route::get('/laporan-pengajuan-pending/data-barang', [LaporanPengajuanPendingController::class, 'getDataBarang'])->name('laporan-pending.data-barang');
    Route::get('/laporan-pengajuan-pending/data-spj', [LaporanPengajuanPendingController::class, 'getDataSPJ'])->name('laporan-pending.data-spj');
    Route::get('/laporan-pengajuan-pending/data-semua', [LaporanPengajuanPendingController::class, 'getDataSemua'])->name('laporan-pending.data-semua');
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
    Route::get('content-schedules/dashboard', [ContentScheduleController::class, 'dashboard'])->name('content-schedules.dashboard');
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
    Route::resource('management-kelas', managementKelasController::class)->except(['show']);
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
    Route::get('getSalesBelumPengajuanExam', [examController::class, 'getSalesBelumPengajuanExam'])->name('getSalesBelumPengajuanExam');
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
    Route::delete('/survey/kepuasan/destroy/{id}', [SurveyKepuasanController::class, 'destroy']);

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
    Route::post('/api/master-labs', [PengajuanLabdanSubsController::class, 'storeMasterLab'])->name('api.master-labs.store');
    Route::post('/api/master-labs/{id}/assign', [PengajuanLabdanSubsController::class, 'assignLabUser'])->name('api.master-labs.assign');
    Route::post('/api/master-labs/unassign/{pengajuanId}', [PengajuanLabdanSubsController::class, 'unassignLabUser'])->name('api.master-labs.unassign');

    Route::get('getPengajuanSubs/{month}/{year}', [PengajuanSubsController::class, 'getPengajuanSubs'])->name('getPengajuanSubs');
    Route::put('pengajuansubs/updatesubs/{id}', [PengajuanSubsController::class, 'updateLabSubs'])->name('pengajuansubs.updatesubs');
    Route::put('pengajuansubs/updatesubssubs/{id}', [PengajuanSubsController::class, 'updateSubsSubs'])->name('pengajuansubs.updatesubssubs');
    Route::post('/pengajuansubs/{id}/upload-invoice', [PengajuanSubsController::class, 'uploadInvoice'])->name('pengajuansubs.uploadInvoice');
    Route::get('pengajuansubs/export-pdf/{id}', [PengajuanSubsController::class, 'exportPDF'])->name('pengajuansubs.exportpdf');
    Route::post('/api/master-subs', [PengajuanSubsController::class, 'storeMasterSubs'])->name('api.master-subs.store');
    Route::put('/api/master-subs/{id}', [PengajuanSubsController::class, 'updateMasterSubs'])->name('api.master-subs.update');
    Route::post('/api/master-subs/{id}/renew', [PengajuanSubsController::class, 'renewSubs'])->name('api.master-subs.renew');
    Route::post('/api/master-subs/{id}/assign', [PengajuanSubsController::class, 'assignSubsUser'])->name('api.master-subs.assign');
    Route::post('/api/master-subs/unassign/{pengajuanId}', [PengajuanSubsController::class, 'unassignSubsUser'])->name('api.master-subs.unassign');
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
        Route::delete('/destroy/{id}', [ExpenseHubController::class, 'destroy'])->name('destroy');
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
    // 13–16. MODULAR DOMAIN ROUTES (KPI, CRM, OFFICE, HR)
    // =========================================================================
    require __DIR__ . '/web/kpi.php';
    require __DIR__ . '/web/crm.php';
    require __DIR__ . '/web/office.php';
    require __DIR__ . '/web/hr.php';

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
    Route::get('/registry/summary', [RegistryFeatureController::class, 'getSummary'])->name('registry.summary');

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
    // 19–20. MODULAR DOMAIN ROUTES (ITSM, DOCUMENTATION, LOGS & DEV UTILITIES)
    // =========================================================================
    require __DIR__ . '/web/itsm.php';
});

