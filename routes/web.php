<?php

use App\Models\Contact;
use App\Models\Inventaris;
use App\Models\izinTigaJam;
use App\Events\ServerTimeUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\examController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RekapPenilaianExamController;
use App\Http\Controllers\Api\RKMController;
use App\Http\Controllers\Crm\CRMController;
use App\Http\Controllers\Crm\MapController;
use App\Http\Controllers\Crm\PicController;
use App\Http\Controllers\CateringController;
use App\Http\Controllers\feedbackController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\netSalesController;
use App\Http\Controllers\SouvenirController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\Crm\TargetAktivitas;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\InvoiceRKMController;
use App\Http\Controllers\MakananRkmController;
use App\Http\Controllers\PusherAuthController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\PeluangController;
use App\Http\Controllers\izinTigaJamController;
use App\Http\Controllers\OutstandingController;
use App\Http\Controllers\DashboardSLAController;
use App\Http\Controllers\office\ModulController;
use App\Http\Controllers\Crm\AktivitasController;
use App\Http\Controllers\Crm\RegisFormController;
use App\Http\Controllers\DailyActivityController;
use App\Http\Controllers\DashboardItsmController;
use App\Http\Controllers\KelasAnalisisController;
use App\Http\Controllers\office\OfficeController;
use App\Http\Controllers\pengajuanKlaimController;
use App\Http\Controllers\laporanInsidentController;
use App\Http\Controllers\managementKelasController;
use App\Http\Controllers\approvedNetSalesController;
use App\Http\Controllers\Crm\CatatanSalesController;
use App\Http\Controllers\Crm\salesPribadiController;
use App\Http\Controllers\Webinar\CalendarController;
use App\Http\Controllers\PenukaranSouvenirController;
use App\Http\Controllers\Webinar\ChecklistController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use App\Http\Controllers\ActivityInstrukturController;
use App\Http\Controllers\Office\CertificateController;
use App\Http\Controllers\office\vendorOfficeController;
use App\Http\Controllers\RekomendasiLanjutanController;
use App\Http\Controllers\Crm\LaporanPenjualanController;
use App\Http\Controllers\Webinar\TimelineItemController;
use App\Http\Controllers\office\DashboardSouvenirController;
use App\Http\Controllers\Crm\ImportPerusahaanAndContactController;
use App\Http\Controllers\RKMController as ControllersRKMController;
use App\Http\Controllers\colaboratorController;
use App\Http\Controllers\dbklienController;
use App\Http\Controllers\ForumSSOController;
use App\Http\Controllers\InstructorDevelopmentController;
use App\Http\Controllers\KPI\DatabaseKPIController as KPIDatabaseKPIController;
use App\Http\Controllers\KPI\TargetKPIController;
use App\Http\Controllers\WebPushController;
use App\Http\Controllers\office\BiayaTransportasiController;
use App\Http\Controllers\Office\pickupDriverController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\registexamController;
use App\Http\Controllers\KomplainPesertaController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('auth.login');
// });

Route::get('/forum', [ForumSSOController::class, 'redirect'])
    ->middleware('auth')
    ->name('forum.sso');
Route::get('/forum/logout', [ForumSSOController::class, 'logout']);



Route::post('/pusher/auth', [PusherAuthController::class, 'auth'])->middleware('web');

Route::get('/partials/dashboard', function () {
    return view('partials.dashboard');
});

Route::redirect('/', '/login');

Auth::routes(['register' => false, 'password.request' => false, 'password.email' => false, 'password.reset' => false, 'password.update' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/user', [App\Http\Controllers\UserController::class, 'index'])->name('user.index');

    Route::get('/karyawan/{hashid}/edit', [App\Http\Controllers\KaryawanController::class, 'edit'])->name('karyawan.edit'); //fixing route
    Route::put('/karyawan/{hashid}', [App\Http\Controllers\KaryawanController::class, 'updateData'])->name('karyawan.update'); //fixing route
    Route::get('/profile/{hashid}', [App\Http\Controllers\UserController::class, 'show'])->name('user.show'); //fixing route
    Route::get('/user/{hashid}/password', [App\Http\Controllers\UserController::class, 'editPassword'])->name('user.editPassword'); //fixing route
    Route::put('/user/{hashid}/password', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('user.updatePassword'); //fixing route
    Route::delete('/user/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy');
    // Route::post('/user/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/gantifoto/{id}', [App\Http\Controllers\KaryawanController::class, 'gantiFoto'])->name('karyawan.gantiFoto');
    Route::put('/gantifoto/{id}', [App\Http\Controllers\KaryawanController::class, 'updateFoto'])->name('karyawan.updateFoto');
    Route::post('/registkaryawan', [App\Http\Controllers\UserController::class, 'regist'])->name('user.registkaryawan');

    Route::post('/webpush/subscribe', [WebPushController::class, 'subscribe'])->name('webpush.subscribe');
    Route::post('/webpush/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('webpush.unsubscribe');
    Route::get('/webpush/vapid-key', [WebPushController::class, 'getVapidKey'])->name('webpush.vapid-key');
    Route::post('/webpush/test', [WebPushController::class, 'testNotification'])->name('webpush.test');
    Route::get( '/webpush/status',      [WebPushController::class, 'subscriptionStatus']);
});
// test
Route::get('/testdata', [App\Http\Controllers\TestController::class, 'index'])->name('testdata');
Route::get('/datas', [App\Http\Controllers\UserController::class, 'datas'])->name('datauser');
Route::get('/datarkm/{tahun}/{bulan}', [App\Http\Controllers\PerusahaanController::class, 'datas'])->name('datarkm');
// Route::post('/change-year', 'HomeController@changeYear')->name('changeYear');
// test

// routes/web.php
// Route::get('/notifications/fetch', function () {
//     return auth()->user()->unreadNotifications;
//     // return auth()->user()->unreadNotifications->take(5);
// })->name('notifications.fetch');
Route::get('/notifications/fetch', function () {
    return response()->json(
        auth()->user()->unreadNotifications
    );
})->name('notifications.fetch');
Route::middleware('auth')->get('/notifications/unread-count', function () {
    return response()->json([
        'count' => auth()->user()->unreadNotifications()->count(),
    ]);
})->name('notifications.unread-count');

Route::get('/daily-activities-data', [DailyActivityController::class, 'activitiesData']);


Route::get('/paymantAdvance/edit/{id}', [netSalesController::class, 'edit'])->name('netSales.edit.index');
Route::get('paymantAdvance/{year}/{month}', [App\Http\Controllers\netSalesController::class, 'getRkmDataPerBulanPerMinggu']);
Route::resource('/comment', \App\Http\Controllers\CommentController::class);

Route::resource('/perusahaan', \App\Http\Controllers\PerusahaanController::class);
Route::resource('/materi', \App\Http\Controllers\MateriController::class);
Route::resource('/rkm', App\Http\Controllers\RKMController::class);
Route::resource('/peserta', \App\Http\Controllers\PesertaController::class);
Route::resource('/registrasi', \App\Http\Controllers\RegistrasiController::class);
Route::resource('/feedback', \App\Http\Controllers\feedbackController::class);
Route::resource('/jabatan', \App\Http\Controllers\jabatanController::class);
Route::resource('/nilaifeedback', \App\Http\Controllers\nilaifeedbackController::class);
Route::resource('/notif', \App\Http\Controllers\notifController::class);
Route::resource('/exam', examController::class);
Route::resource('/listexams', App\Http\Controllers\ListExamController::class);
Route::resource('/creditcard', \App\Http\Controllers\creditcardController::class);
Route::resource('/registexam', \App\Http\Controllers\registexamController::class);
Route::resource('/souvenir', SouvenirController::class);
Route::resource('/pengajuancuti', \App\Http\Controllers\PengajuancutiController::class);
Route::resource('/pengajuanizin', izinTigaJamController::class);
Route::resource('/pengajuanbarang', \App\Http\Controllers\PengajuanBarangController::class);
Route::resource('/suratperjalanan', \App\Http\Controllers\SuratPerjalananController::class);
Route::resource('/rekapitulasiabsen', \App\Http\Controllers\RekapitulasiAbsenController::class);
Route::resource('/kelasanalisis', \App\Http\Controllers\KelasAnalisisController::class);
Route::resource('/paymantAdvance', \App\Http\Controllers\netSalesController::class)->except(['show']);
Route::resource('/databasekpi', KPIDatabaseKPIController::class);

Route::resource('/target', \App\Http\Controllers\targetController::class);
Route::resource('/outstanding', OutstandingController::class);
Route::resource('/tunjangan', \App\Http\Controllers\TunjanganController::class);
Route::resource('/tunjanganEducation', \App\Http\Controllers\tunjanganEducationController::class);
Route::resource('/rekapmengajarinstruktur', \App\Http\Controllers\rekapInstrukturController::class);
Route::resource('/lembur', \App\Http\Controllers\LemburController::class);
Route::resource('/overtime', \App\Http\Controllers\OvertimeController::class);
Route::resource('/pengajuanlabsdansubs', \App\Http\Controllers\PengajuanLabdanSubsController::class);
Route::resource('/pengajuansouvenir', \App\Http\Controllers\PengajuanSouvenirController::class);
Route::resource('/daily-activities', DailyActivityController::class);
Route::resource('/registry', \App\Http\Controllers\RegistryFeatureController::class)->parameters(['registry' => 'tugas']);
Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
Route::resource('roles', \App\Http\Controllers\RoleController::class);
Route::resource('penambahansouvenir', \App\Http\Controllers\PenambahanSouvenirController::class);
Route::resource('penukaransouvenir', PenukaranSouvenirController::class);
Route::resource('content-schedules', \App\Http\Controllers\ContentScheduleController::class);
Route::resource('colaborator', \App\Http\Controllers\colaboratorController::class)->except(['show']);


Route::get('/komplain-peserta', [KomplainPesertaController::class, 'index'])->name('komplain-peserta');
Route::get('/dataNilaiPenilaian/{id}', [KomplainPesertaController::class, 'dataNilaiPenilaian'])->name('dataNilaiPenilaian');
Route::get('komplain-peserta/create', [KomplainPesertaController::class, 'create'])->name('createKomplain');
Route::post('komplain-peserta/store', [KomplainPesertaController::class, 'store'])->name('storeKomplain');
Route::get('/komplain-peserta/{nilaifeedback_id}/edit', [KomplainPesertaController::class, 'edit'])->name('editKomplain');
Route::post('komplain-peserta/{nilaifeedback_id}/update', [KomplainPesertaController::class, 'update'])->name('updateKomplain');
Route::get('komplain-peserta/dataKomplain', [KomplainPesertaController::class, 'dataKomplain'])->name('dataKomplain');
Route::post('komplain-peserta/delete/{id}', [KomplainPesertaController::class, 'destroy'])->name('hapusKomplain');

Route::get('/rkmEditInstruktur/{id}', [App\Http\Controllers\RKMController::class, 'editInstruktur'])->name('editInstruktur');
Route::put('/rkmUpdateInstruktur', [App\Http\Controllers\RKMController::class, 'updateInstruktur'])->name('updateInstruktur');
Route::get('/rkmEdit', [App\Http\Controllers\RKMController::class, 'editRKM'])->name('rkmEdit');
Route::put('/rkmUpdate', [App\Http\Controllers\RKMController::class, 'updateRKM'])->name('rkmUpdate');
Route::get('/rkm/checklist/{id}', [App\Http\Controllers\RKMController::class, 'getChecklist'])->name('rkm.checklist.get');
Route::post('/rkm/checklist/store', [App\Http\Controllers\RKMController::class, 'storeChecklist'])->name('rkm.checklist.store');

Route::group(['middleware' => 'Admin'], function () {
    Route::get('/user/register', [App\Http\Controllers\UserController::class, 'create'])->name('user.register');
    // Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
});

Route::get('/roles/{id}/give-permissions', [App\Http\Controllers\RoleController::class, 'addPermissionToRole'])->name('addPermissionToRole');
Route::put('/roles/{id}/give-permissions', [App\Http\Controllers\RoleController::class, 'givePermissionToRole'])->name('givePermissionToRole');
Route::get('/userRolePermissions', [App\Http\Controllers\UserController::class, 'indexUser'])->name('indexUser');
Route::get('/userRolePermissions/{id}/edit', [App\Http\Controllers\UserController::class, 'editUser'])->name('editUser');
Route::put('/userRolePermissions/{id}/update', [App\Http\Controllers\UserController::class, 'updateUser'])->name('updateUser');

Route::get('inixcoffeeloglarapelixb95', [LogViewerController::class, 'index'])
    ->middleware('logviewer.access');

Route::get('GetDatabasekpi', [KPIDatabaseKPIController::class, 'getData'])->name('GetDatabaseKPI');
Route::get('GetDatabasekpi/profile', [KPIDatabaseKPIController::class, 'getDataProfile'])->name('GetDataProfile.kpi');
Route::get('getPerusahaanById', [App\Http\Controllers\PerusahaanController::class, 'getPerusahaanById'])->name('getPerusahaanById');
Route::get('getRegistrasiall', [App\Http\Controllers\RegistrasiController::class, 'getRegistrasiall'])->name('getRegistrasiall');
Route::get('getPesertaall', [App\Http\Controllers\PesertaController::class, 'getPesertaall'])->name('getPesertaall');
Route::get('getExam', [App\Http\Controllers\examController::class, 'getExam'])->name('getExam');
Route::get('getHistoriExam', [App\Http\Controllers\examController::class, 'getHistoriExam'])->name('getHistoriExam');
Route::get('getListExam', [App\Http\Controllers\listexamController::class, 'getListExam'])->name('getListExam');
Route::get('getCC', [App\Http\Controllers\creditcardController::class, 'getCC'])->name('getCC');
Route::get('getRegistrasiexam', [App\Http\Controllers\registexamController::class, 'getRegistrasiexam'])->name('getRegistrasiexam');
Route::get('getRegistrasiexamByIdExam/{id}', [App\Http\Controllers\registexamController::class, 'getRegistrasiexamByIdExam'])->name('getRegistrasiexamByIdExam');   
Route::post('/generate/exam/absensi', [registexamController::class, 'generateAbsensi'])->name('absensi.exam');
Route::post('/upload/exam/absensi', [registexamController::class, 'uploadAbsensi'])->name('upload.absensi.exam');
Route::get('getSouvenir', [App\Http\Controllers\SouvenirController::class, 'getSouvenir'])->name('getSouvenir');
Route::get('getSouvenirPeserta', [App\Http\Controllers\SouvenirController::class, 'getSouvenirPeserta'])->name('getSouvenirPeserta');
Route::get('getFeedbacksByMonth/{year}/{month}', [App\Http\Controllers\feedbackController::class, 'getFeedbacksByMonth'])->name('getFeedbacksByMonth');
Route::get('/getTotalFeedbackPertahun', [App\Http\Controllers\feedbackController::class, 'getTotalFeedbackPertahun'])->name('office.feedback.get');
Route::get('/chart/jumlah-update-materi-perbulan', [MateriController::class, 'chartJumlahUpdateMateriPerbulan'])->name('chart.jumlah-update-materi-perbulan')->middleware('auth');Route::get('/chart/silabus-per-instruktur-per-tahun', [App\Http\Controllers\MateriController::class, 'chartSilabusPerInstrukturPerTahun'])->middleware('auth');
Route::get('/chart/hari-mengajar-instruktur-per-tahun', [App\Http\Controllers\RKMController::class, 'chartHariMengajarInstrukturPerTahun'])->name('chart.hari-mengajar-instruktur-per-tahun');Route::get('getPengajuanCuti', [App\Http\Controllers\PengajuancutiController::class, 'getPengajuanCuti'])->name('getPengajuanCuti');
Route::get('getPengajuanIzin', [App\Http\Controllers\izinTigaJamController::class, 'getPengajuanIzin'])->name('getPengajuanIzin');
Route::get('/pengajuan-izin-3-jam/excel-download', [izinTigaJamController::class, 'pengajuanJamExcel'])->name('pengajuanIzin.excelDownload');
Route::get('/pengajuan-izin-3-jam/pdf-download', [izinTigaJamController::class, 'pengajuanJamPDF'])->name('pengajuanIzin.PDFDownload');
Route::get('getPengajuanCuti/{month}/{year}', [App\Http\Controllers\PengajuancutiController::class, 'getPengajuanCutiBulanTahun'])->name('getPengajuanCutiBulanTahun');
Route::get('getPesertaById/{id}', [App\Http\Controllers\PesertaController::class, 'getPesertaById'])->name('getPesertaById');
Route::get('getSuratPerjalanan', [App\Http\Controllers\SuratPerjalananController::class, 'getSuratPerjalanan'])->name('getSuratPerjalanan');
Route::get('index/SuratPerjalanan/to/print', [App\Http\Controllers\SuratPerjalananController::class, 'createPrint'])->name('createPrint');
Route::get('get/SuratPerjalanan/to/print', [App\Http\Controllers\SuratPerjalananController::class, 'getToPrint'])->name('getToPrint');
Route::post('download/SuratPerjalanan/to/excel', [App\Http\Controllers\SuratPerjalananController::class, 'getToExcelMonth'])->name('getToExcelMonth');
Route::post('download/SuratPerjalanan/to/excel-year', [App\Http\Controllers\SuratPerjalananController::class, 'getToExcelYear'])->name('getToExcelYear');
Route::post('download/SuratPerjalanan/to/pdf', [App\Http\Controllers\SuratPerjalananController::class, 'getToPdfMonth'])->name('getToPdfMonth');
Route::post('download/SuratPerjalanan/to/pdf-year', [App\Http\Controllers\SuratPerjalananController::class, 'getToPdfYear'])->name('getToPdfYear');
Route::get('getPengajuanBarang/{month}/{year}', [App\Http\Controllers\PengajuanBarangController::class, 'getPengajuanBarang'])->name('getPengajuanBarang');
Route::get('getPengajuanBarang/{id}', [KegiatanController::class, 'getPengajuanBarang'])->name('getPengajuanBarangKegiatan');
Route::get('getPengajuanLabSubs/{month}/{year}', [App\Http\Controllers\PengajuanLabdanSubsController::class, 'getPengajuanLabSubs'])->name('getPengajuanLabSubs');
Route::put('pengajuanlabsdansubs/updatelabsubs/{id}', [App\Http\Controllers\PengajuanLabdanSubsController::class, 'updateLabSubs'])->name('pengajuanlabsdansubs.updatelabsubs');
Route::post('/pengajuanlabsdansubs/{id}/upload-invoice', [App\Http\Controllers\PengajuanLabdanSubsController::class, 'uploadInvoice'])->name('pengajuanlabsdansubs.uploadInvoice');
Route::get('pengajuanlabsdansubs/export-pdf/{id}', [App\Http\Controllers\PengajuanLabdanSubsController::class, 'exportPDF'])->name('pengajuanlabsdansubs.exportpdf');
Route::get('getAbsen', [App\Http\Controllers\RekapitulasiAbsenController::class, 'getAbsen'])->name('getAbsen');
Route::get('getTarget', [App\Http\Controllers\TargetController::class, 'getTarget'])->name('getTarget');
Route::get('getOutstandingLunas', [App\Http\Controllers\OutstandingController::class, 'getOutstandingLunas'])->name('getOutstandingLunas');
Route::get('getOutstandingHutang', [App\Http\Controllers\OutstandingController::class, 'getOutstandingHutang'])->name('getOutstandingHutang');
Route::get('getOutstandingRKM/{year}/{month}', [App\Http\Controllers\OutstandingController::class, 'getOutstandingRKM'])->name('getOutstandingRKM');
Route::get('getOutstandingPA', [OutstandingController::class, 'getOutstandingPA'])->name('getOutstandingPA');
Route::get('/outstanding/{id}/detail', [OutstandingController::class, 'detailPA'])->name('detailDataOutstanding');
Route::get('singkronDataOutstandingRKM', [App\Http\Controllers\OutstandingController::class, 'singkronDataOutstanding'])->name('outstanding.singkronDataOutstanding');
Route::get('/download/dokumen/{id}', [OutstandingController::class, 'dokumenGabungan'])->name('dokumenGabungan');
Route::get('cekregisform/{id}', [App\Http\Controllers\RKMController::class, 'cekregisform'])->name('cekregisform');
Route::get('getMateri/{id}', [App\Http\Controllers\MateriController::class, 'getMateriById'])->name('getMateriById');
Route::get('getNilaiFeedbackInstRKM/{id}', [App\Http\Controllers\feedbackController::class, 'getNilaiFeedbackInstRKM'])->name('getNilaiFeedbackInstRKM');
Route::get('editMengajarInstruktur/{id}', [App\Http\Controllers\rekapInstrukturController::class, 'editMengajarInstruktur'])->name('editMengajarInstruktur');
Route::get('getMengajarInstruktur/{id}/{month}/{year}', [App\Http\Controllers\rekapInstrukturController::class, 'getMengajarInstruktur'])->name('getMengajarInstruktur');
Route::get('cekLevel/{id}', [App\Http\Controllers\rekapInstrukturController::class, 'cekLevel'])->name('cekLevel');
Route::get('getJenisTunjanganIndex', [App\Http\Controllers\TunjanganController::class, 'getJenisTunjanganIndex'])->name('getJenisTunjanganIndex');
Route::get('getJenisTunjanganUmum', [App\Http\Controllers\TunjanganController::class, 'getJenisTunjanganUmum'])->name('getJenisTunjanganUmum');
Route::get('getTunjanganSaya/{id}/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'getTunjanganSaya'])->name('getTunjanganSaya');
Route::get('getTunjanganSayaGenerate/{id}/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'getTunjanganSayaGenerate'])->name('getTunjanganSayaGenerate');
Route::get('/tunjangan/approval', [App\Http\Controllers\TunjanganController::class, 'indexApproval'])->name('tunjangan.approval');
Route::get('/getTunjanganPendingApproval/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'getTunjanganPendingApproval'])->name('getTunjanganPendingApproval');
Route::post('/approveTunjangan', [App\Http\Controllers\TunjanganController::class, 'approveTunjangan'])->name('approveTunjangan');
Route::post('/rejectTunjangan', [App\Http\Controllers\TunjanganController::class, 'rejectTunjangan'])->name('rejectTunjangan');
Route::post('/bulkApproveTunjangan', [App\Http\Controllers\TunjanganController::class, 'bulkApproveTunjangan'])->name('bulkApproveTunjangan');
Route::get('/getApprovalHistory/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'getApprovalHistory'])->name('getApprovalHistory');
Route::get('/checkTunjanganStatus/{id}/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'checkTunjanganStatus'])->name('checkTunjanganStatus');
// =====================================================
Route::get('generate-tunjangan-pdf/{id}/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'generateTunjanganPDF'])->name('generateTunjanganPDF');
Route::get('penghitunganTunjangan', [App\Http\Controllers\TunjanganController::class, 'penghitunganTunjangan'])->name('penghitunganTunjangan');
Route::get('tunjanganManualCreate', [App\Http\Controllers\TunjanganController::class, 'createManual'])->name('createManual');
Route::post('penghitunganTunjanganManual', [App\Http\Controllers\TunjanganController::class, 'storeManual'])->name('tunjangan.storeManual');
Route::post('tunjangankelompok', [App\Http\Controllers\TunjanganController::class, 'storeManualTunjangan'])->name('tunjangan.storekelompok');
Route::get('jumlahAbsensi/{id_karyawan}/{bulan}/{tahun}', [App\Http\Controllers\AbsensiKaryawanController::class, 'jumlahAbsensi'])->name('jumlahAbsensi');
Route::get('getListMengajar/{bulan}/{tahun}', [App\Http\Controllers\rekapInstrukturController::class, 'getListMengajar'])->name('getListMengajar');
Route::get('sinkronData', [App\Http\Controllers\rekapInstrukturController::class, 'sinkronData'])->name('sinkronData');
Route::get('sinkronDataKelasAnalisis/{year}/{monthStart}/{monthEnd}', [App\Http\Controllers\KelasAnalisisController::class, 'sinkronDataKelasAnalisis'])->name('sinkronDataKelasAnalisis');
Route::get('getListRekapInstruktur/{bulan}/{tahun}', [App\Http\Controllers\tunjanganEducationController::class, 'getListRekapInstruktur'])->name('getListRekapInstruktur');
Route::get('getTunjanganEdu/{id}/{month}/{year}', [App\Http\Controllers\rekapInstrukturController::class, 'getTunjanganEdu'])->name('getTunjanganEdu');
Route::get('getTunjanganEdu', [App\Http\Controllers\TunjanganController::class, 'getJenisTunjanganEdu'])->name('getTunjanganEducation');
Route::get('getTunjanganOffice', [App\Http\Controllers\TunjanganController::class, 'getJenisTunjanganOffice'])->name('getTunjanganOffice');
Route::get('getTunjanganSales', [App\Http\Controllers\TunjanganController::class, 'getJenisTunjanganSales'])->name('getTunjanganSales');
Route::get('getSuratPerintahLembur', [App\Http\Controllers\LemburController::class, 'getSuratPerintahLembur'])->name('getSuratPerintahLembur');
Route::get('getLemburKaryawan', [App\Http\Controllers\LemburController::class, 'getLemburKaryawan'])->name('getLemburKaryawan');
Route::get('getOvertimeLembur/{month}/{year}', [App\Http\Controllers\OvertimeController::class, 'getOvertimeLembur'])->name('getOvertimeLembur');
Route::get('getOvertimeLemburByKaryawan/{id}/{month}/{year}', [App\Http\Controllers\OvertimeController::class, 'getOvertimeLemburByKaryawan'])->name('getOvertimeLemburByKaryawan');
Route::post('/export-rkm-excel-admsales', [RKMController::class, 'exportExcel'])->name('export.rkm.excel');

Route::get('getYearlySales/{year}', [App\Http\Controllers\HomeController::class, 'getYearSales'])->name('getYearSales');
Route::get('getPenjualanPerBulan/{year}', [App\Http\Controllers\ChartController::class, 'getPenjualanPerBulan'])->name('getPenjualanPerBulan');
Route::get('getPerSalesPerTahun/{year}', [App\Http\Controllers\ChartController::class, 'getPerSalesPerTahun'])->name('getPerSalesPerTahun');
Route::get('getPerSalesPerQuartal/{year}', [App\Http\Controllers\ChartController::class, 'getPerSalesPerQuartal'])->name('getPerSalesPerQuartal');
Route::get('getAnalisisMarginByYear/{year}', [App\Http\Controllers\ChartController::class, 'getAnalisisMarginByYear'])->name('getAnalisisMarginByYear');
Route::get('getAbsensiYearly/{year}', [App\Http\Controllers\ChartController::class, 'getAbsensiYearly'])->name('getAbsensiYearly');
Route::get('getTabInix/{year}', [App\Http\Controllers\ChartController::class, 'getTabInix'])->name('getTabInix');
Route::get('getSouvenirYearly/{year}', [App\Http\Controllers\ChartController::class, 'getSouvenirYearly'])->name('getSouvenirYearly');
Route::get('getTotalFeedbackPerbulan/{year}/{month}', [App\Http\Controllers\ChartController::class, 'getTotalFeedbackPerbulan'])->name('getTotalFeedbackPerbulan');
Route::get('getTotalMengajarPerbulan/{year}/{month}', [App\Http\Controllers\ChartController::class, 'getTotalMengajarPerbulan'])->name('getTotalMengajarPerbulan');
Route::get('getTotalMateriPerbulan/{year}/{month}', [App\Http\Controllers\ChartController::class, 'getTotalMateriPerbulan'])->name('getTotalMateriPerbulan');
Route::get('getTotalMengajarPerJenisMateriPerTahun/{year}/{month}', [App\Http\Controllers\ChartController::class, 'getTotalMengajarPerJenisMateriPerTahun'])->name('getTotalMengajarPerJenisMateriPerTahun');
Route::get('getAbsenPerbulan/{year}/{month}', [App\Http\Controllers\ChartController::class, 'getAbsenPerbulan'])->name('getAbsenPerbulan');
Route::get('/getPengajuanSouvenir/{month}/{year}', [App\Http\Controllers\PengajuanSouvenirController::class, 'getPengajuanSouvenir'])->name('getPengajuanSouvenir');
Route::put('/pengajuansouvenir/{id}/updateitems', [App\Http\Controllers\PengajuanSouvenirController::class, 'updateItems'])->name('pengajuansouvenir.updateItems');
Route::put('/pengajuansouvenir/{id}/upload-invoice', [App\Http\Controllers\PengajuanSouvenirController::class, 'updateInvoice'])->name('pengajuansouvenir.updateInvoice');
Route::get('/pengajuansouvenir/{id}/export-pdf', [App\Http\Controllers\PengajuanSouvenirController::class, 'exportPDF'])->name('pengajuansouvenir.exportpdf');

Route::get('/create-only', [App\Http\Controllers\examController::class, 'createOnly'])->name('exam.createOnly');
Route::post('/store-only', [App\Http\Controllers\examController::class, 'storeOnly'])->name('exam.storeOnly');
Route::get('/hargaExam', [App\Http\Controllers\examController::class, 'hargaExam']);
Route::get('/detailHargaExam/{id}', [App\Http\Controllers\examController::class, 'detailHargaExam']);
Route::get('/pengajuanUpdateExam/{id}', [App\Http\Controllers\examController::class, 'pengajuanUpdateExam']);

Route::get('/feedbackPelayanan', [App\Http\Controllers\feedbackController::class, 'pelayananFeedbackShow'])->name('feedbackPelayanan');
Route::get('/pengajuanExam/{id}', [App\Http\Controllers\examController::class, 'create'])->name('pengajuanExam');
Route::get('/approvalexam/{id}', [App\Http\Controllers\examController::class, 'approvalexam'])->name('approvalexam');
Route::put('/sendapprovalexam/{id}', [App\Http\Controllers\examController::class, 'sendapprovalexam'])->name('exam.approval');
Route::post('/invoice/{data}', [App\Http\Controllers\examController::class, 'invoice'])->name('exam.invoice');
Route::get('/invoice/registexam/{id}', [App\Http\Controllers\registexamController::class, 'invoice'])->name('registexam.invoice');
Route::get('/uploadinvoice/{id}', [App\Http\Controllers\registexamController::class, 'uploadInvoice'])->name('exam.uploadInvoice');
Route::put('/uploadinvoice/{id}', [App\Http\Controllers\registexamController::class, 'uploadInvoicePost'])->name('exam.uploadInvoicePost');
Route::get('/hasilexam/{id}', [App\Http\Controllers\registexamController::class, 'createHasilUjian'])->name('exam.createHasilUjian');
Route::post('/hasilexam/{id}', [App\Http\Controllers\registexamController::class, 'storeHasilUjian'])->name('exam.storeHasilUjian');
Route::get('/hasilexam/{id}/edit', [App\Http\Controllers\registexamController::class, 'createHasilUjian'])->name('exam.editHasilUjian');
Route::put('/hasilexam/{id}/update', [App\Http\Controllers\registexamController::class, 'updateHasilUjian'])->name('exam.updateHasilUjian');
Route::get('/hasilexam/{id}/detail', [App\Http\Controllers\registexamController::class, 'showHasilUjian'])->name('exam.showHasilUjian');
Route::get('/registexam/cc/{id}', [App\Http\Controllers\registexamController::class, 'createcc'])->name('exam.createcc');
Route::put('/registexam/cc/{id}', [App\Http\Controllers\registexamController::class, 'storecc'])->name('exam.storecc');
Route::put('/souvenir/{id}/updatestok', [App\Http\Controllers\SouvenirController::class, 'updateStok'])->name('souvenir.updateStok');
Route::get('/souvenir/{id}/editstok', [App\Http\Controllers\SouvenirController::class, 'editStok'])->name('souvenir.editStok');
Route::get('/suratperjalanan/{id}/editspj', [App\Http\Controllers\SuratPerjalananController::class, 'editspj'])->name('suratperjalanan.editspj');
Route::get('/tunjangangenerate', [App\Http\Controllers\TunjanganController::class, 'indexGenerate'])->name('tunjangangenerate.index');
Route::put('/lembur/{id}/updateKaryawan', [App\Http\Controllers\lemburController::class, 'updateKaryawan'])->name('lembur.updateKaryawan');
Route::get('/lembur/{id}/editKaryawan', [App\Http\Controllers\lemburController::class, 'editKaryawan'])->name('lembur.editKaryawan');
Route::post('/lembur/masuk', [App\Http\Controllers\lemburController::class, 'absenMasuk'])->name('lembur.masuk');
Route::post('/lembur/pulang', [App\Http\Controllers\lemburController::class, 'absenPulang'])->name('lembur.pulang');
Route::put('/lembur/approval/{id}', [App\Http\Controllers\lemburController::class, 'approvalLemburKaryawan'])->name('lembur.approvalLemburKaryawan');
Route::put('/overtimeApproving', [App\Http\Controllers\OvertimeController::class, 'approvalHitungLemburKaryawan'])->name('overtime.approvalHitungLemburKaryawan');
Route::get('/export-lembur-excel/{year}/{month}', [App\Http\Controllers\OvertimeController::class, 'exportExcel'])->name('overtime.exportExcel');
Route::get('/export-lembur-pdf/{id}/{year}/{month}', [App\Http\Controllers\OvertimeController::class, 'exportPDF'])->name('overtime.exportPDF');

Route::post('/change-user', [App\Http\Controllers\UserController::class, 'changeUser'])->name('change.user');
Route::get('/get-users', [App\Http\Controllers\UserController::class, 'getUsers'])->name('get.users');
Route::get('/user-dropdown', [App\Http\Controllers\UserController::class, 'showUserDropdown'])->name('user.dropdown');
// Route::get('/webhook/sentry', [SentryWebhookController::class, 'index']);


Route::get('/rkm/{id}/souvenir', [App\Http\Controllers\SouvenirController::class, 'createSouvenirInhouse'])->name('createSouvenirInhouse');
Route::get('/souvenir/filter/{keyword}', [SouvenirController::class, 'filterSouvenir'])->name('filterSouvenir');
Route::post('/rkm/storesouvenir', [App\Http\Controllers\SouvenirController::class, 'storeSouvenirInhouse'])->name('storeSouvenirInhouse');
Route::put('/rkm/{id}/updatesouvenir', [App\Http\Controllers\SouvenirController::class, 'updateSouvenirInhouse'])->name('updateSouvenirInhouse');

Route::post('/providers', [App\Http\Controllers\listexamController::class, 'storeProviders'])->name('providers.store');
Route::post('/vendors', [App\Http\Controllers\listexamController::class, 'storeVendor'])->name('vendors.store');

Route::get('/detailfeedbacks', [App\Http\Controllers\feedbackController::class, 'detailfeedbacks'])->name('detailfeedbacks');
Route::get('/paymantAdvance/edit/{id}', [App\Http\Controllers\netSalesController::class, 'edit'])->name('netSales.edit.index');

// Route::get('nilaifeedback/export', [App\Http\Controllers\feedbackController::class, 'export'])->name('nilaifeedback.export');
Route::get('nilaifeedbackexport/{year}/{month}', [App\Http\Controllers\nilaifeedbackController::class, 'export'])->name('nilaifeedbackexport');
Route::get('RekapitulasiAbsenperKaryawanExport/{year}/{month}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportperKaryawan'])->name('RekapitulasiAbsenperKaryawanExport');
Route::get('RekapitulasiAbsenperBulanExport/{year}/{month}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportperBulan'])->name('RekapitulasiAbsenperBulanExport');
Route::get('RekapitulasiWaktuKeterlambatanExport/{year}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportKeterlambatan'])->name('RekapitulasiWaktuKeterlambatanExport');

//KPI
Route::prefix('kpi-data/')->name('kpi.')->middleware(['auth'])->group(function () {
    //Overview KPI
    route::prefix('overview/')->name('overview.')->middleware(['auth'])->group(function () {
        route::get('/index', [TargetKPIController::class, 'kpiOverview'])->name('index');
        route::get('/get', [TargetKPIController::class, 'getDataOverview'])->name('get');
        route::get('/index/personal', [TargetKPIController::class, 'personalIndex'])->name('indexPersonal');
        route::get('/kpi/personal/data', [TargetKPIController::class, 'getDataOverviewPersonal'])->name('dataPersonal');
    });

    //Target Departement
    route::get('/table-data', [TargetKPIController::class, 'kpiIndex'])->name('index');
    route::post('/create-target', [TargetKPIController::class, 'createTarget'])->name('createTarget');
    route::get('/get-data-target', [TargetKPIController::class, 'getDataTarget'])->name('getDataTarget');
    route::get('/detail-data-target', [TargetKPIController::class, 'detailData'])->name('detail');
    route::delete('/hapus-data-target/{id}', [TargetKPIController::class, 'hapusTarget'])->name('hapus');
    route::post('/update-data-target', [TargetKPIController::class, 'updateTarget'])->name('update');
    route::get('/edit-data-target', [TargetKPIController::class, 'editTarget'])->name('edit');
    route::get('get-karyawan-by-jabatan', [TargetKPIController::class, 'getKaryawanByJabatan'])->name('getKaryawanByJabatan');
    route::get('get-dashboard', [TargetKPIController::class, 'getProgressDasboard'])->name('getProgressDasboard');

    //manual value
    route::post('/update-manual-value', [TargetKPIController::class, 'manualValue'])->name('manualValue');

    //Target KPI karyawan
    route::prefix('karyawan/')->name('karyawan.')->middleware(['auth'])->group(function() {
        route::get('/get', [TargetKPIController::class, 'getDataTarget'])->name('get');
    });
});

//Project KPI
route::get('project/table-data', [KPIDatabaseKPIController::class, 'indexProject'])->name('project.index');
route::get('project/control-project', [KPIDatabaseKPIController::class, 'controlProject'])->name('project.control');

//Penilaian
route::get('penilaian/data-form/edit/{kode_form}', [KPIDatabaseKPIController::class, 'formPenilaianEdit']);
route::post('penilaian/data-form/update', [KPIDatabaseKPIController::class, 'formPenilaianUpdate'])->name('penilaian.form.update');
Route::get('/penilaian/form', [KPIDatabaseKPIController::class, 'formPenilaianData'])->name('penilaian.form.data');
Route::get('/penilaian/form/get', [KPIDatabaseKPIController::class, 'getFormPenilaianData'])->name('penilaian.form.get');
Route::post('/penilaian/clean', [KPIDatabaseKPIController::class, 'clean']);
Route::post('/penilaian/hapus', [KPIDatabaseKPIController::class, 'hapus']);
Route::post('/penilaian/hapus-evaluator/{kodeJenis}/{id_evaluator}/{kodeFormGlobal}', [KPIDatabaseKPIController::class, 'hapusEvaluator']);
Route::get('/penilaian/content/dahsboardKPI/get', [KPIDatabaseKPIController::class, 'contentDashboard'])->name('databaseKPI.dashboardContent');
Route::post('/penilaian/content/dahsboardKPI/download-penilaian-perDivisi', [KPIDatabaseKPIController::class, 'downloadDivisi'])->name('databaseKPI.downloadDivisi');
Route::post('/penilaian/detail/send/catatan', [KPIDatabaseKPIController::class, 'sendCatatan'])->name('penilaian.sendCatatan');
Route::post('/download-pdf/penilaian-360', [KPIDatabaseKPIController::class, 'downloadPDF'])->name('penilaian.download.pdf');
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

Route::post('/pengajuan-klaim/excel-download', [pengajuanKlaimController::class, 'pengajuanKlaimExcel'])->name('pengajuanklaim.excel');
Route::post('/pengajuan-klaim/pdf-download', [pengajuanKlaimController::class, 'pengajuanKlaimPDF'])->name('pengajuanklaim.PDF');
Route::get('/pengajuan-klaim/create/no-record', [pengajuanKlaimController::class, 'noRecord'])->name('pengajuanklaim.NoRecord');
Route::get('/pengajuan-klaim/create/scheme-work', [pengajuanKlaimController::class, 'schemeWork'])->name('pengajuanklaim.SchemeWork');
Route::get('/pengajuan-klaim/create/cancel-leave', [pengajuanKlaimController::class, 'cancelLeave'])->name('pengajuanklaim.CancelLeave');
Route::post('/pengajuan-klaim/add/no-record', [pengajuanKlaimController::class, 'createNoRecord'])->name('pengajuanklaim.addNoRecord');
Route::post('/pengajuan-klaim/add/scheme-work', [pengajuanKlaimController::class, 'createSchemeWork'])->name('pengajuanklaim.addSchemeWork');
Route::post('/pengajuan-klaim/add/cancel-leave', [pengajuanKlaimController::class, 'createCancelLeave'])->name('pengajuanklaim.addCancelLeave');
Route::post('/pengajuan-klaim/approval', [pengajuanKlaimController::class, 'approval'])->name('pengajuanklaim.approval');
Route::post('/pengajuan-klaim/reject', [pengajuanKlaimController::class, 'reject'])->name('pengajuanklaim.reject');
Route::post('/pengajuan-klaim/delete/no-record', [pengajuanKlaimController::class, 'deleteNoRecord'])->name('pengajuanklaim.deleteNoRecord');
Route::post('/pengajuan-klaim/delete/cancel-leave', [pengajuanKlaimController::class, ''])->name('pengajuanklaim.deleteCancelLeave');
Route::post('/pengajuan-klaim/delete/scheme-work', [pengajuanKlaimController::class, 'deleteSchemeWork'])->name('pengajuanklaim.deleteSchemeWork');
Route::get('/pengajuan-klaim', [pengajuanKlaimController::class, 'index'])->name('pengajuanklaim.index');
Route::put('notifications/{notification}/read', [App\Http\Controllers\CommentController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::put('/notifications/markAllAsRead', [App\Http\Controllers\CommentController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
Route::get('/rkm/{id}/absensi', [App\Http\Controllers\RKMController::class, 'absensiPeserta'])->name('absensiPeserta');
Route::put('/suratperjalanan/{id}/approval', [App\Http\Controllers\SuratPerjalananController::class, 'approval'])->name('suratperjalanan.approval');
Route::get('/fetch-attendance', [RKMController::class, 'fetchAttendance'])->name('attendance.fetch');
Route::post('/absensi', [\App\Http\Controllers\AbsensiKaryawanController::class, 'storeAbsensi'])->name('absensi.masuk');
Route::get('/absensi/karyawan', [App\Http\Controllers\AbsensiKaryawanController::class, 'absensiKaryawan'])->name('absensi.karyawan');
Route::get('/absensi/pengajuan-klaim/no-recorded', [App\Http\Controllers\AbsensiKaryawanController::class, 'noRecord'])->name('absensi.noRecord');
Route::post('/absensi/approve/pengajuan-klaim/no-recorded', [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveNoRecord'])->name('absensi.approveNoRecord');
Route::post('/absensi/delete/pengajuan-klaim/no-recorded', [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteNoRecord'])->name('absensi.deleteNoRecord');
Route::get('/absensi/pengajuan-klaim/scheme-work', [App\Http\Controllers\AbsensiKaryawanController::class, 'schemeWork'])->name('absensi.schemeWork');
Route::post('/absensi/approve/pengajuan-klaim/scheme-work', [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveSchemeWork'])->name('absensi.approveSchemeWork');
Route::post('/absensi/delete/pengajuan-klaim/scheme-work', [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteSchemeWork'])->name('absensi.deleteSchemeWork');
Route::get('/absensi/pengajuan-klaim/cancel-leave', [App\Http\Controllers\AbsensiKaryawanController::class, 'cancelLeave'])->name('absensi.cancelLeave');
Route::post('/absensi/approve/pengajuan-klaim/cancel-leave', [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveCancelLeave'])->name('absensi.approveCancelLeave');
Route::post('/absensi/delete/pengajuan-klaim/cancel-leave', [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteCancelLeave'])->name('absensi.deleteCancelLeave');
Route::get('/absensi/karyawan',  [App\Http\Controllers\AbsensiKaryawanController::class, 'absensiKaryawan'])->name('absensi.karyawan');
Route::get('/absensi/pengajuan-klaim/no-recorded',  [App\Http\Controllers\AbsensiKaryawanController::class, 'noRecord'])->name('absensi.noRecord');
Route::post('/absensi/approve/pengajuan-klaim/no-recorded',  [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveNoRecord'])->name('absensi.approveNoRecord');
Route::post('/absensi/delete/pengajuan-klaim/no-recorded',  [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteNoRecord'])->name('absensi.deleteNoRecord');
Route::get('/absensi/pengajuan-klaim/scheme-work',  [App\Http\Controllers\AbsensiKaryawanController::class, 'schemeWork'])->name('absensi.schemeWork');
Route::post('/absensi/approve/pengajuan-klaim/scheme-work',  [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveSchemeWork'])->name('absensi.approveSchemeWork');
Route::post('/absensi/delete/pengajuan-klaim/scheme-work',  [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteSchemeWork'])->name('absensi.deleteSchemeWork');
Route::get('/absensi/pengajuan-klaim/cancel-leave',  [App\Http\Controllers\AbsensiKaryawanController::class, 'cancelLeave'])->name('absensi.cancelLeave');
Route::post('/absensi/approve/pengajuan-klaim/cancel-leave',  [App\Http\Controllers\AbsensiKaryawanController::class, 'ApproveCancelLeave'])->name('absensi.approveCancelLeave');
Route::post('/absensi/delete/pengajuan-klaim/cancel-leave',  [App\Http\Controllers\AbsensiKaryawanController::class, 'deleteCancelLeave'])->name('absensi.deleteCancelLeave');
Route::post('/absensi/create/pengajuan-klaim/no-recorded', [App\Http\Controllers\AbsensiKaryawanController::class, 'createNoRecord'])->name('absensi.createNoRecord');
Route::post('/absensi/create/pengajuan-klaim/cancel-leave', [App\Http\Controllers\AbsensiKaryawanController::class, 'createCancelLeave'])->name('absensi.createCancelLeave');
Route::post('/absensi/create/pengajuan-klaim/scheme_work', [App\Http\Controllers\AbsensiKaryawanController::class, 'createSchemeWork'])->name('absensi.createSchemeWork');
Route::post('/absensi/update', [\App\Http\Controllers\AbsensiKaryawanController::class, 'storeKeluar'])->name('absensi.keluar');
Route::get('/absensi/{id}/edit', [App\Http\Controllers\RekapitulasiAbsenController::class, 'edit'])->name('absensi.edit');
Route::get('/absensi/create', [App\Http\Controllers\AbsensiKaryawanController::class, 'create'])->name('absensi.create');
Route::post('/absensi/manual', [\App\Http\Controllers\AbsensiKaryawanController::class, 'absenManual'])->name('absensi.manual');
// Route::put('/absensi/{id}/update', [App\Http\Controllers\RekapitulasiAbsenController::class, 'update'])->name('rekapitulasiabsen.update');
Route::get('/cekip', [App\Http\Controllers\AbsensiKaryawanController::class, 'cekip'])->name('cekip');
Route::get('/rkm/{id}/registform', [App\Http\Controllers\RKMController::class, 'createRegistForm'])->name('createRegistForm');
Route::put('/rkm/{id}/registformupdate', [App\Http\Controllers\RKMController::class, 'uploadRegistForm'])->name('uploadRegistForm');
Route::post('/rkm/download/excel', [App\Http\Controllers\RKMController::class, 'excelDownload'])->name('excel');
Route::post('/rkm/download/excel/adm-sales', [App\Http\Controllers\RKMController::class, 'excelDownloadAdmSales'])->name('excel.rkmAdmSales');
Route::get('analisisrkm/{year}/{monthStart}/{monthEnd}', [App\Http\Controllers\KelasAnalisisController::class, 'getRkmDataPerBulanPerMinggu']);
Route::get('analisisrkm/{id}/create', [App\Http\Controllers\KelasAnalisisController::class, 'create']);
Route::get('getAnalisisRKM/{year}/{month}/{week}', [App\Http\Controllers\KelasAnalisisController::class, 'getRkmDataByMonthAndWeek']);
Route::post('analisisrkm/{year}/{month}/{week}/post', [App\Http\Controllers\KelasAnalisisController::class, 'postAnalisisMingguan']);
Route::put('analisisrkm/{year}/{month}/{week}/update', [App\Http\Controllers\KelasAnalisisController::class, 'updateAnalisisMingguan']);
Route::get('analisisrkm/{year}/{month}', [App\Http\Controllers\KelasAnalisisController::class, 'getAnalisisMargin']);
Route::get('peserta/export/excel', [App\Http\Controllers\PesertaController::class, 'exportExcel'])->name('peserta.exportExcel');
Route::get('peserta/export/pdf', [App\Http\Controllers\PesertaController::class, 'exportPDF'])->name('peserta.exportPDF');
Route::get('registrasi/export/excel', [App\Http\Controllers\RegistrasiController::class, 'exportExcel'])->name('registrasi.exportExcel');
Route::get('registrasi/export/pdf', [App\Http\Controllers\RegistrasiController::class, 'exportPDF'])->name('registrasi.exportPDF');
Route::get('registrasi/export/excels', [App\Http\Controllers\RegistrasiController::class, 'exportExcelKhusus'])->name('registrasi.exportExcels');
Route::get('registrasi/export/pdfs', [App\Http\Controllers\RegistrasiController::class, 'exportPDFKhusus'])->name('registrasi.exportPDFs');
Route::get('peserta/export/excels', [App\Http\Controllers\PesertaController::class, 'exportExcelKhusus'])->name('peserta.exportExcels');
Route::get('peserta/export/pdfs', [App\Http\Controllers\PesertaController::class, 'exportPDFKhusus'])->name('peserta.exportPDFs');
Route::get('feedback/export/excels/{id}', [App\Http\Controllers\feedbackController::class, 'exportExcelKhusus'])->name('feedback.exportExcels');
Route::get('feedback/export/pdfs/{id}', [App\Http\Controllers\feedbackController::class, 'exportPDFKhusus'])->name('feedback.exportPDFs');
Route::get('tunjanganExportPDF/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'tunjanganExportPDF'])->name('tunjanganExportPDF');
Route::get('tunjanganExportExcel/{month}/{year}', [App\Http\Controllers\TunjanganController::class, 'tunjanganExportExcel'])->name('tunjanganExportExcel');
Route::get('editstatusmateri/{id}', [App\Http\Controllers\MateriController::class, 'editstatusmateri'])->name('editstatusmateri');
Route::get('pengajuanbarang/uploadinvoice/{id}', [App\Http\Controllers\PengajuanBarangController::class, 'uploadInvoice'])->name('uploadInvoice');
Route::put('pengajuanbarang/updateinvoice/{id}', [App\Http\Controllers\PengajuanBarangController::class, 'updateInvoice'])->name('updateInvoice');
Route::put('pengajuanbarang/updatebarang/{id}', [App\Http\Controllers\PengajuanBarangController::class, 'updateBarang'])->name('pengajuanbarang.updateBarang');
Route::get('pengajuanbarang/pdf/{id}', [App\Http\Controllers\PengajuanBarangController::class, 'exportPDF'])->name('pengajuanbarang.pdf');
Route::get('paymantAdvance/{id}/create/form-view', [App\Http\Controllers\netSalesController::class, 'create']);
Route::get('tunjanganEduExportExcel/{month}/{year}', [App\Http\Controllers\tunjanganEducationController::class, 'tunjanganEduExportExcel'])->name('tunjanganEduExportExcel');
Route::get('pengajuancutirekap', [App\Http\Controllers\PengajuancutiController::class, 'rekap'])->name('pengajuancuti.rekap');
Route::get('pengajuancutiexport/{month}/{year}', [App\Http\Controllers\PengajuancutiController::class, 'exportexcel'])->name('pengajuancutiexport');

Route::get('kalkulator/analisis/{id}/kelas', [KelasAnalisisController::class, 'kalkulatorview'])->name('kalkulatorview'); // Return view kalkulator kelas analisis

Route::get('/rkm/upload/page', [ControllersRKMController::class, 'uploadPage'])->name('uploadPage');
Route::get('/rkm/data/page', [ControllersRKMController::class, 'dataPage'])->name('dataPage');
Route::get('/rkm/uploadAbsensi/{id}', [ControllersRKMController::class, 'uploadAbsensi'])->name('uploadAbsensi');
Route::post('/generate/pdf/peserta/{id}', [OutstandingController::class, 'generatePdfPeserta'])->name('generatePdfPeserta');
Route::post('/rkm/store/absensi', [ControllersRKMController::class, 'storeAbsensi'])->name('storeAbsensi');
Route::post('/rkm/delete/absensi', [ControllersRKMController::class, 'deleteAbsensi'])->name('deleteAbsensi');
Route::get('/rkm/uploadSertifikat/{id}', [ControllersRKMController::class, 'uploadSertifikat'])->name('uploadSertifikat');
Route::post('/rkm/store/sertifikat', [ControllersRKMController::class, 'storeSertifikat'])->name('storeSertifikat');
Route::post('/rkm/delete/sertifikat', [ControllersRKMController::class, 'deleteSertifikat'])->name('deleteSertifikat');
// web.php
Route::post('/rkm/update-makanan', [App\Http\Controllers\RKMController::class, 'updateMakanan'])
    ->name('rkm.updateMakanan');


Route::get('/rekap-penilaian-exam', [RekapPenilaianExamController::class, 'indexRekap'])->name('exam.rekap-penilaian');
Route::get('/rekap-penilaian-exam/data', [RekapPenilaianExamController::class, 'getRekapPenilaian'])->name('exam.rekap-penilaian.data');


Route::get('/paymantAdvance/detail/{id}/view', [netSalesController::class, 'detail'])->name('netsales.detail');
Route::post('/paymantAdvance/detail/data/get', [netSalesController::class, 'dataDetail'])->name('netsales.data.detail.get');
Route::post('/paymantAdvance/approved', [approvedNetSalesController::class, 'approve'])->name('netsales.approved');
Route::post('/paymantAdvance/data/get/', [netSalesController::class, 'dataEdit'])->name('netSales.edit.get');
Route::put('/paymantAdvance/data/update', [netSalesController::class, 'updateNetSales'])->name('netSales.update');
Route::get('/download/pdf/netsales/{year}/{month}', [netSalesController::class, 'DownloadPDF'])->name('netSales.download');
Route::get('/download/pdf/netsales/{id}', [netSalesController::class, 'pdfSendiri'])->name('netSales.download.pdfSendiri');

// Inventaris Route
Route::get('/inventaris/index', [InventarisController::class, 'index'])->name('IndexInventaris');
Route::post('/inventaris/input/barang', [InventarisController::class, 'inputinventaris'])->name('InputInventaris');
Route::get('/inventaris/show/data/{id}', [InventarisController::class, 'editview'])->name('EditView');
Route::put('/inventaris/update/{id}', [InventarisController::class, 'user'])->name('UpdatePengguna');
Route::post('/inventaris/add/service/{id}', [InventarisController::class, 'addservice'])->name('AddService');
Route::post('/inventaris/add/check/{id}', [InventarisController::class, 'addcheck'])->name('AddCheck');
Route::delete('/inventaris/delete/data/{id}', [InventarisController::class, 'deletedata'])->name('DeleteDataInventaris');
Route::post('/inventaris/create/kode', [InventarisController::class, 'createKode'])->name('CreateKodeIinvetaris');

Route::post('/inventaris/import', [InventarisController::class, 'import'])->name('ImportDataInventaris');
Route::get('/inventaris/export', [InventarisController::class, 'export'])->name('inventaris.export');

Route::get('/ticketing-data', [DashboardItsmController::class, 'getJumlahPermintaan']);
Route::get('/jumlah-pic', [DashboardItsmController::class, 'getJumlahPIC']);
Route::get('/rerata-durasi-data', [DashboardItsmController::class, 'getRerataDurasi']);
Route::get('/rerata-ketepatan-response-data', [DashboardItsmController::class, 'getRerataKetepatanResponse']);
Route::get('/jumlah-permintaan-per-bulan', [DashboardItsmController::class, 'getJumlahPermintaanPerBulan']);
Route::get('/permintaan-sering-diajukan', [DashboardItsmController::class, 'getPermintaanSeringDiajukan']);
Route::get('/list-bulan', [DashboardItsmController::class, 'getListBulan']);

Route::prefix('crm')->group(function () {
    Route::get('/', [CRMController::class, 'index'])->name('CRM.index');
    Route::get('/my-dashboard', [salesPribadiController::class, 'index'])->name('CRM.myDasboard');
    Route::get('/profile', [CRMController::class, 'getProfile'])->middleware('auth')->name('crm.profile');
    Route::get('/chartRKM', [CRMController::class, 'chartRKM'])->name('chartRKM');
    Route::get('/chartPerusahaan', [CRMController::class, 'chartPerusahaan'])->name('chartPerusahaan');
    Route::get('/chartClosed', [CRMController::class, 'chartClosed'])->name('chartClosed');

    // Contact CRM
    Route::get('/contact/index', [ContactController::class, 'index'])->name('index.contact');
    Route::get('/contact/{id}/detail', [ContactController::class, 'detail'])->name('detail.contact');
    Route::post('/contact/store', [ContactController::class, 'store'])->name('store.contact');
    Route::delete('/contact/delete/{id}', [ContactController::class, 'delete'])->name('delete.contact');
    Route::put('/contact/update/{id}', [ContactController::class, 'update'])->name('update.contact');
    Route::get('/contact/data', [ContactController::class, 'getPerusahaan'])->name('contact.data');
    Route::put('/update/pic', [PicController::class, 'updatePIC'])->name('pic.update');
    Route::delete('/delete/pic/{id}', [PicController::class, 'deletePIC'])->name('pic.delete');

    // Peluang CRM
    Route::get('/peluang/index', [PeluangController::class, 'index'])->name('index.peluang');
    Route::get('/index/peluang', [PeluangController::class, 'indexJson'])->name('index.peluang.json');
    Route::get('/peluang/detail/{id}', [PeluangController::class, 'detail'])->name('detail.peluang');
    Route::post('/peluang/store', [PeluangController::class, 'store'])->name('store.peluang');
    Route::delete('/peluang/delete/{id}', [PeluangController::class, 'delete'])->name('delete.peluang');
    Route::put('/peluang/edit/{id}', [PeluangController::class, 'update'])->name('edit.peluang');
    Route::put('/peluang/update/{id}', [PeluangController::class, 'updateTahap'])->name('update.tahap');
    Route::get('/ambil/aktivitas/{id}', [PeluangController::class, 'AmbilAktivitas']);
    Route::post('/peluang/paymentAdvance', [PeluangController::class, 'storePaymentAdvance'])->name('store.payment.advance');


    // Aktivitas CRM
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

    // Closed Win
    Route::get('/closed/win', [PeluangController::class, 'ringkasanPeluang'])->name('index.ringkasanPeluang');
    Route::get('/detail/closed/win/{id}', [PeluangController::class, 'detailRingkasan'])->name('detail.ringkasanPeluang');
    Route::get('/closed/lost', [PeluangController::class, 'ringkasanPeluanglost'])->name('index.ringkasanlost');
    Route::get('/detail/closed/lost/{id}', [PeluangController::class, 'detailRingkasanlost'])->name('detail.Ringkasanlost');

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

    Route::get('/pic', [PicController::class, 'index'])->name('index.pic');
    Route::get('/index/pic', [PicController::class, 'indexJson'])->name('index.json.pic');
    Route::post('/pic/store', [PicController::class, 'store'])->name('store.pic');

    // Lokasi
    Route::get('lokasi', [MapController::class, 'index'])->name('crm.lokasi');
    Route::post('lokasi/store', [MapController::class, 'store'])->name('crm.lokasi.store');
    Route::put('lokasi/update', [MapController::class, 'update'])->name('crm.lokasi.update');
    Route::delete('lokasi/delete/{id}', [MapController::class, 'delete'])->name('crm.lokasi.delete');

    // Laporan Penjualan
    Route::get('laporanPenjualan', [LaporanPenjualanController::class, 'index'])->name('crm.laporanPenjualan');
    Route::get('/edit/{id}/pa', [LaporanPenjualanController::class, 'editPA'])->name('editPA');
    Route::put('/update/pa/{id}', [LaporanPenjualanController::class, 'updatePA'])->name('updatePA');

    // Import Contact / Perusahaan
    Route::post('/perusahaan/import/perusahaan', [ImportPerusahaanAndContactController::class, 'importPerusahaan'])->name('perusahaan.import');
    Route::post('/perusahaan/import/contacts', [ImportPerusahaanAndContactController::class, 'importContacts'])->name('contact.import');
});

//INVOICE
Route::get('/invoice', [InvoiceRKMController::class, 'index'])->name('invoice.index');
Route::get('/invoice/create/{id}', [InvoiceRKMController::class, 'create'])->name('invoice.create');
Route::post('/invoice', [InvoiceRKMController::class, 'store'])->name('invoice.store');
Route::get('/invoice/{id}', [InvoiceRKMController::class, 'show'])->name('invoice.show');
Route::get('/invoice/{id}/edit', [InvoiceRKMController::class, 'edit'])->name('invoice.edit');
Route::put('/invoice/{id}', [InvoiceRKMController::class, 'update'])->name('invoice.update');
Route::delete('/invoice/{id}', [InvoiceRKMController::class, 'destroy'])->name('invoice.destroy');
Route::get('/invoices/{id}/export-pdf', [InvoiceRKMController::class, 'exportPdf'])
    ->name('invoices.export-pdf');
Route::get('/invoices/{id}/export-excel', [InvoiceRKMController::class, 'exportExcel'])
    ->name('invoices.export-excel');
Route::get('/invoice/download/{id}', [InvoiceRKMController::class, 'downloadPDF'])->name('download.pdf');

//Kwitansi

Route::get('/invoice/{id}/kwitansi', [InvoiceRKMController::class, 'kwitansi'])->name('invoice.kwitansi');
Route::get('/invoice/{invoiceId}/kwitansi/create', [InvoiceRKMController::class, 'createKwitansi'])
    ->name('kwitansi.create');
Route::post('/kwitansi/store', [InvoiceRKMController::class, 'storeKwitansi'])
    ->name('kwitansi.store');
// Contoh rute untuk menampilkan detail kwitansi
Route::get('/kwitansi/{id}', [InvoiceRKMController::class, 'showKwitansi'])->name('kwitansi.show');
Route::get('/kwitansi/download/{id}', [InvoiceRKMController::class, 'downloadPdfKwitansi'])->name('kwitansi.pdf');

//laporan-insiden-route
Route::get('/laporan-insiden', [laporanInsidentController::class, 'index'])->name('index.laporanInsiden');
Route::get('/laporan-insiden/get', [laporanInsidentController::class, 'get'])->name('get.laporanInsiden');
Route::get('/laporan-insiden/form', [laporanInsidentController::class, 'create'])->name('create.laporanInsiden');
Route::post('/laporan-insiden/store', [laporanInsidentController::class, 'store'])->name('store.laporanInsiden');
Route::post('/laporan-insiden/respon', [laporanInsidentController::class, 'respon'])->name('respon.laporanInsiden');
Route::get('/laporan-insiden/detail/{id}', [laporanInsidentController::class, 'detail'])->name('detail.laporanInsiden');
Route::get('/laporan-insiden/edit/{id}', [laporanInsidentController::class, 'edit'])->name('edit.laporanInsiden');
Route::get('/laporan-insiden/hapus/{id}', [laporanInsidentController::class, 'hapus'])->name('hapus.laporanInsiden');
Route::post('/laporan-insiden/update', [laporanInsidentController::class, 'update'])->name('uodate.laporanInsiden');
//management-kelas-offline
Route::get('/management-kelas/get', [managementKelasController::class, 'get'])->name('managementKelas.get');
Route::get('/management-kelas/store', [managementKelasController::class, 'store'])->name('managementKelas.store');
Route::resource('management-kelas', managementKelasController::class);
Route::post('/management-kelas/{id}/batalkan', [ManagementKelasController::class, 'batalkan']);


Route::get('/exam/assign-room/{id}', [examController::class, 'assignRoom'])
    ->name('exam.assignRoom')
    ->middleware('can:Edit Exam');

// Routes management kelas yang sudah ada
Route::get('/management-kelas', [managementKelasController::class, 'index'])
    ->name('managementKelas.index');
Route::get('/management-kelas/get', [managementKelasController::class, 'get'])
    ->name('managementKelas.get');
Route::post('/management-kelas/store', [managementKelasController::class, 'store'])
    ->name('managementKelas.store');
// di web.php atau routes file
Route::post('/exam/process-room-assignment', [examController::class, 'processRoomAssignment'])->name('exam.processRoomAssignment');

//rekapexam
Route::get('/rekapexam', [examController::class, 'rekapExam'])->name('exam.rekapexam');
Route::get('/getRekapExamByMonth/{year}/{month}', [examController::class, 'getRekapExam'])->name('exam.getRekapExam');
Route::get('/rekapExamExportExcel/{year}/{month}', [examController::class, 'rekapExamExportExcel'])->name('exam.rekapExamExportExcel');

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
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
Route::post('/tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
Route::post('/tickets/{ticket}/finish', [TicketController::class, 'finish'])->name('tickets.finish');
Route::post('/tickets/{ticket}/block', [TicketController::class, 'block'])->name('tickets.block');
Route::get('/getTickets', [TicketController::class, 'getTickets'])->name('getTickets');

// Gaji
Route::get('/gaji/karyawan', [KaryawanController::class, 'gajiIndex'])->name('gaji.index');
Route::post('/gaji', [KaryawanController::class, 'storeGaji'])->name('gaji.store');
Route::put('/gaji/{id}', [KaryawanController::class, 'updateGaji'])->name('gaji.update');
Route::delete('/gaji/{id}', [KaryawanController::class, 'destroyGaji'])->name('gaji.destroy');

//Slip Gaji
Route::get('/slip/gaji', [KaryawanController::class, 'slip'])->name('SlipGaji');

Route::get('/test-error', function () {
    // ini error manual
    throw new \Exception('Test error from Handler.php');
});
Route::middleware('auth')->get('/notifications/unread-count', function () {
    return response()->json([
        'count' => auth()->user()->unreadNotifications()->count(),
    ]);
})->name('notifications.unread-count');

Route::post('/save-push-subscription', [WebhookController::class, 'saveSubscription']);

Route::get('laporan/penjualan', [LaporanPenjualanController::class, 'indexJson'])->name('jsonLaporan');
Route::get('/laporan/penjualan/win/excel', [LaporanPenjualanController::class, 'downloadWinExcel'])->name('laporan.win.excel');
Route::get('/laporan/penjualan/lost/excel', [LaporanPenjualanController::class, 'downloadLostExcel'])->name('laporan.lost.excel');
Route::get('/laporan/penjualan/win/pdf', [LaporanPenjualanController::class, 'downloadPdfWin'])->name('laporan.win.pdf');
Route::get('/laporan/penjualan/lost/pdf', [LaporanPenjualanController::class, 'downloadPdfLost'])->name('laporan.lost.pdf');
// Kanban
Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
Route::post('/tasks', [KanbanController::class, 'store'])->name('tasks.store');
Route::post('/tasks/update-state', [KanbanController::class, 'updateState'])->name('tasks.update-state');
Route::patch('/tasks/{id}', [KanbanController::class, 'update'])->name('tasks.update');
Route::get('/tasks/{task}/activities', [KanbanController::class, 'getTaskActivities'])->name('tasks.activities');
Route::delete('tasks/{task}', [KanbanController::class, 'destroy'])->name('tasks.destroy');
Route::patch('/daily-activities/{dailyActivity}/quick-update', [DailyActivityController::class, 'quickUpdate'])->name('daily-activities.quick-update');

// RegistryFeature
Route::patch('/registry/{tugas}/start', [App\Http\Controllers\RegistryFeatureController::class, 'startTask'])
    ->name('registry.start');
Route::patch('/registry/{tugas}/finish', [App\Http\Controllers\RegistryFeatureController::class, 'finishTask'])
    ->name('registry.finish');

route::get('activity-log', [KPIDatabaseKPIController::class, 'activityLog'])->name('activity.log');
route::get('activity-log/data', [KPIDatabaseKPIController::class, 'getActivityChart'])->name('activity.log.chart');

// survey kepuasan
Route::get('/survey/kepuasan', [App\Http\Controllers\SurveyKepuasanController::class, 'index'])->name('surveykepuasan.index');
Route::post('/survey/kepuasan/send', [App\Http\Controllers\SurveyKepuasanController::class, 'store'])->name('surveykepuasan.store');
Route::get('/survey/kepuasan/table', [App\Http\Controllers\SurveyKepuasanController::class, 'indexTable'])->name('surveyKepuasan.indexTable');
Route::get('/survey/kepuasan/destroy/{id}', [App\Http\Controllers\SurveyKepuasanController::class, 'destroy']);

// expense-hub
Route::get('/expense-hub/index', [App\Http\Controllers\ExpenseHubController::class, 'index'])->name('expensehub.index');
Route::get('/expense-hub/get', [App\Http\Controllers\ExpenseHubController::class, 'get'])->name('expensehub.get');
Route::get('/expense-hub/create', [App\Http\Controllers\ExpenseHubController::class, 'create'])->name('expensehub.create');
Route::post('/expense-hub/store', [App\Http\Controllers\ExpenseHubController::class, 'store'])->name('expensehub.store');
Route::post('/expense-hub/export-pdf', [App\Http\Controllers\ExpenseHubController::class, 'PDF'])->name('expensehub.pdf');
Route::put('/expense-hub/approved', [App\Http\Controllers\ExpenseHubController::class, 'approved'])->name('expensehub.approved');
Route::get('/expense-hub/show/{id}', [App\Http\Controllers\ExpenseHubController::class, 'show'])->name('expensehub.show');
Route::get('/expense-hub/destroy/{id}', [App\Http\Controllers\ExpenseHubController::class, 'destroy'])->name('expensehub.destroy');
Route::get('/expense-hub/invoice/{id}', [App\Http\Controllers\ExpenseHubController::class, 'invoice'])->name('expensehub.invoice');
Route::put('/expense-hub/updateinvoice/{id}', [App\Http\Controllers\ExpenseHubController::class, 'updateInvoice'])->name('expensehub.updateInvoice');
Route::put('/expense-hub/update/{id}', [App\Http\Controllers\ExpenseHubController::class, 'update'])->name('expensehub.update');

Route::prefix('office')->group(function () {
    Route::get('/dashboard', [OfficeController::class, 'dashboard'])->name('office.dashboard');
    Route::get('/data-cuti', [OfficeController::class, 'dataCuti']);
    Route::get('/data-mengajar', [OfficeController::class, 'dataMengajar']);
});

Route::prefix('dashboard-sla/{team}')->group(function () {
    Route::whereIn('team', ['programmer', 'tech-support']);
    Route::get('/tim', [DashboardSLAController::class, 'dashboardTim']);
    Route::get('/user', [DashboardSLAController::class, 'dashboardUser']);
    Route::get('/kritis', [DashboardSLAController::class, 'dashboardKritis']);
});
Route::get('/dashboard-sla/event/{mappingId}', [DashboardSLAController::class, 'dashboardEventSla']);
Route::get('/dashboard-sla/digital', [DashboardSLAController::class, 'dashboardDigital']);

Route::prefix('office')->name('office.')->middleware(['auth'])->group(function () {

    //pickup driver routes
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
    });

    Route::prefix('biaya-transportasi')->name('biayaTransportasi.')->group(function () {
        Route::get('/index', [BiayaTransportasiController::class, 'index'])->name('index');
        Route::post('/create', [BiayaTransportasiController::class, 'create'])->name('create');
        Route::get('/get', [BiayaTransportasiController::class, 'get'])->name('get');
        Route::put('/update/{id}', [BiayaTransportasiController::class, 'update'])->name('office.biayaTransportasi.update');
        Route::delete('/delete/{id}', [BiayaTransportasiController::class, 'destroy'])->name('office.biayaTransportasi.destroy');
    });
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('chartFeedback', [OfficeController::class, 'getNilaiInstruktur'])->name('get');

    });
    Route::get(
        '/feedbackinstrukturpdf',
        [OfficeController::class, 'exportPdf']
    )->name('feedbackinstrukturpdf');


    // Certificate Routes
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
    });

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::resource('/souvenir', vendorOfficeController::class);
        Route::resource('/makansiang', vendorOfficeController::class);
        Route::resource('/coffeebreak', vendorOfficeController::class);
        Route::resource('/bengkel', vendorOfficeController::class);
    });

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::resource('/souvenir', vendorOfficeController::class);
        Route::resource('/makansiang', vendorOfficeController::class);
        Route::resource('/coffeebreak', vendorOfficeController::class);
        Route::resource('/bengkel', vendorOfficeController::class);
    });


    Route::prefix('modul')->group(function () {
        Route::get('/index', [ModulController::class, 'indexNomor'])->name('modul.index');
        Route::post('/', [ModulController::class, 'storeNomor'])->name('modul.store.nomor');
        Route::put('/update/nomor/{id}', [ModulController::class, 'updateNomor'])->name('modul.update.nomor');
        Route::delete('/delete/nomor/{id}', [ModulController::class, 'deleteNomor'])->name('modul.delete.nomor');

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
    });

    Route::prefix('kegiatan')->group(function() {
        Route::get('/index', [KegiatanController::class, 'index'])->name('indexKegiatan');
        Route::post('/store', [KegiatanController::class, 'storeKegiatan'])->name('storeKegiatan');
        Route::put('/update/{id}', [KegiatanController::class, 'updateKegiatan'])->name('updateKegiatan');
        Route::delete('/delete/{id}', [KegiatanController::class, 'deleteKegiatan'])->name('deleteKegiatan');

        Route::get('/show/{id}', [KegiatanController::class, 'show'])->name('showRincian');
        Route::post('/store/rincian/{id}', [KegiatanController::class, 'storeRincian'])->name('storeRincian');
        Route::put('/update/rincian/{id}', [KegiatanController::class, 'updateRincian'])->name('updateRincian');
        Route::delete('/delete/rincian/{id}', [KegiatanController::class, 'deleteRincian'])->name('deleteRincian');

        Route::put('/update/status/gm/{id}', [KegiatanController::class, 'gm'])->name('UpdateStatusGM');
        Route::put('/update/status/finance/{id}', [KegiatanController::class, 'finance'])->name('UpdateStatusFinance');
        Route::put('/update/status/selesai/{id}', [KegiatanController::class, 'selesai'])->name('UpdateStatusSelesai');

        Route::put('/tambah/peserta/{id}', [KegiatanController::class, 'storePeserta'])->name('StorePesertaKegiatan');
        Route::get('/download/pdf/{id}', [KegiatanController::class, 'downloadPDF'])->name('downloadPdfRab');
    });

    Route::prefix('kendaraan')->group(function(){
        Route::get('/index/kondisi', [KendaraanController::class, 'indexKondisi'])->name('indexKondisiKendaraan');
        Route::get('/detail/kondisi/{id}', [KendaraanController::class, 'detailKondisi'])->name('detailKondisiKendaraan');
        Route::post('/store/kondisi', [KendaraanController::class, 'storeKondisi'])->name('storeKondisiKendaraan');
        Route::put('/update/kondisi/{id}', [KendaraanController::class, 'updateKondisi'])->name('updateKondisiKendaraan');
        Route::delete('/delete/kondisi/{id}', [KendaraanController::class, 'deleteKondisi'])->name('deleteKondisiKendaraan');

        //perbaikan
        Route::get('/index/perbaikan', [KendaraanController::class, 'indexPerbaikan'])->name('indexPerbaikanKendaraan');
        Route::get('/detail/perbaikan/{id}', [KendaraanController::class, 'detailPerbaikan'])->name('detailPerbaikanKendaraan');
        Route::post('/store/perbaikan', [KendaraanController::class, 'storePerbaikan'])->name('storePerbaikanKendaraan');
        Route::put('/update/perbaikan/{id}', [KendaraanController::class, 'updatePerbaikan'])->name('updatePerbaikanKendaraan');
        Route::delete('/delete/perbaikan/{id}', [KendaraanController::class, 'deletePerbaikan'])->name('deletePerbaikanKendaraan');
        Route::post('/update/status/perbaikan', [KendaraanController::class, 'updateStatusPerbaikan'])->name('updateStatusPerbaikanKendaraan');
    });
});

Route::prefix('/catering')->name('catering.')->group(function () {
    Route::get('/index', [CateringController::class, 'index'])->name('index');
    Route::get('/get', [CateringController::class, 'get'])->name('get');
    Route::get('/create', [CateringController::class, 'create'])->name('create');
    Route::post('/store', [CateringController::class, 'store'])->name('store');
    Route::get('/show/{id}', [CateringController::class, 'show'])->name('show');
    Route::post('/update/{id}', [CateringController::class, 'update'])->name('update');
    Route::post('/export-pdf', [CateringController::class, 'PDF'])->name('pdf');
    Route::put('/approved', [CateringController::class, 'approved'])->name('approved');
    Route::get('/destroy/{id}', [CateringController::class, 'destroy'])->name('destroy');
    Route::get('/invoice/{id}', [CateringController::class, 'invoice'])->name('invoice');
    Route::put('/updateinvoice/{id}', [CateringController::class, 'updateInvoice'])->name('updateInvoice');
});

Route::prefix('/rekomendasi-lanjutan')->name('rekomendasiLanjutan.')->group(function () {
    Route::get('/index', [RekomendasiLanjutanController::class, 'index'])->name('index');
    Route::get('/get/{year}/{month}', [RekomendasiLanjutanController::class, 'showMonth']);
    Route::post('/store', [RekomendasiLanjutanController::class, 'store'])->name('store');
});

// Penambahan Souvneir
Route::get('/getPenambahanSouvenir/{month}/{year}', [App\Http\Controllers\PenambahanSouvenirController::class, 'getPenambahanSouvenir'])->name('getPenambahanSouvenir');
// Penukaran Souvenir
Route::get('/penukaransouvenir/getRiwayat/{month}/{year}', [PenukaranSouvenirController::class, 'getRiwayat'])->name('getRiwayat');
Route::get('/get-peserta/{rkmId}', [PenukaranSouvenirController::class, 'getPesertaByRKM'])->name('getPeserta');
Route::get('/dashboard/souvenir', [DashboardSouvenirController::class, 'index'])->name('dashboard.souvenir');

// timeline
Route::get('/timeline', [CalendarController::class, 'index'])->name('timeline.index');

Route::get('/api/checklist/{mappingId}', [ChecklistController::class, 'index']);
Route::patch('/api/checklist/{id}/toggle', [ChecklistController::class, 'toggle']);
Route::put('/api/checklist/{id}/detail', [ChecklistController::class, 'updateDetail']);

// API Timeline Item
Route::post('/api/timeline-item', [TimelineItemController::class, 'store']);

// API Event Update
Route::post('/api/event/{id}/update', [CalendarController::class, 'updateEvent']);

Route::get('/activityinstruktur-data', [ActivityInstrukturController::class, 'getActivitiesData'])->name('api.activities');
Route::post('/activityinstruktur-store', [ActivityInstrukturController::class, 'store'])->name('api.activities.store');
Route::post('/activityinstruktur-update', [ActivityInstrukturController::class, 'update'])->name('api.activities.proof_update');
Route::get('/activityinstruktur', [ActivityInstrukturController::class, 'index'])->name('activities.index');

// Pastikan berada di dalam grup middleware auth
Route::get('/activityinstruktur-data/summary', [App\Http\Controllers\ActivityInstrukturController::class, 'getSummaryData'])->name('api.activities.summary');
// content
Route::patch('content-schedules/{contentSchedule}/mark-uploaded', [App\Http\Controllers\ContentScheduleController::class, 'markAsUploaded'])
    ->name('content-schedules.mark-uploaded');

Route::get('/dashboard/feedback', [OfficeController::class, 'index']);
Route::get('/development', [InstructorDevelopmentController::class, 'index'])->name('development.index');
Route::post('/development/sertifikasi', [InstructorDevelopmentController::class, 'storeSertifikasi'])->name('sertifikasi.store');
Route::delete('/development/sertifikasi/{id}', [InstructorDevelopmentController::class, 'destroySertifikasi'])->name('sertifikasi.destroy');
Route::post('/development/sertifikasi/{id}/approve', [InstructorDevelopmentController::class, 'approveSertifikasi'])->name('sertifikasi.approve');
Route::post('/development/pelatihan', [InstructorDevelopmentController::class, 'storePelatihan'])->name('pelatihan.store');
Route::delete('/development/pelatihan/{id}', [InstructorDevelopmentController::class, 'destroyPelatihan'])->name('pelatihan.destroy');
Route::post('/development/pelatihan/{id}/approve', [InstructorDevelopmentController::class, 'approvePelatihan'])->name('pelatihan.approve');
Route::put('/development/sertifikasi/{id}', [InstructorDevelopmentController::class, 'updateSertifikasi'])->name('sertifikasi.update');
Route::put('/development/pelatihan/{id}', [InstructorDevelopmentController::class, 'updatePelatihan'])->name('pelatihan.update');
Route::post('/development/pelatihan/{id}/upload-bukti', [InstructorDevelopmentController::class, 'uploadBukti'])->name('pelatihan.uploadBukti');
Route::post('/development/sertifikasi/{id}/upload-bukti', [InstructorDevelopmentController::class, 'uploadBuktiSertifikasi'])->name('sertifikasi.uploadBukti');
Route::post('/specialization', [InstructorDevelopmentController::class, 'storeSpecialization'])->name('specialization.store');
Route::put('/specialization/{id}', [InstructorDevelopmentController::class, 'updateSpecialization'])->name('specialization.update');
Route::delete('/specialization/{id}', [InstructorDevelopmentController::class, 'destroySpecialization'])->name('specialization.destroy');
Route::post('/development/sertifikasi/{id}/renew', [InstructorDevelopmentController::class, 'storeRenewal'])->name('sertifikasi.renew');

Route::get('/db-klien', [dbklienController::class, 'index'])->name('dbklien.index');
Route::post('import-klien', [dbklienController::class, 'import']);
Route::get('download-template-klien', [dbklienController::class, 'downloadTemplate'])->name('excel.dbklien');

Route::post('/internal/update-tickets', [TicketController::class, 'handleInternalUpdate']);
Route::get('/internal/open-tickets', [TicketController::class, 'getOpenTickets']);

Route::get('/colaborator/data', [colaboratorController::class, 'getData'])->name('colaborator.data');

