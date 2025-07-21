<?php

use App\Http\Controllers\Api\RKMController;
use App\Http\Controllers\approvedNetSalesController;
use App\Http\Controllers\databasekpiContoller;
use App\Http\Controllers\izinTigaJamController;
use App\Http\Controllers\KelasAnalisisController;
use App\Http\Controllers\RKMController as ControllersRKMController;
use App\Http\Controllers\netSalesController;
use App\Http\Controllers\pengajuanKlaimController;
use App\Http\Controllers\DashboardItsmController;
use App\Models\izinTigaJam;
use App\Http\Controllers\InventarisController;
use App\Models\Inventaris;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;


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

Route::redirect('/', '/login');

Auth::routes(['register' => false, 'password.request' => false, 'password.email' =>  false, 'password.reset' =>  false, 'password.update' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/user', [App\Http\Controllers\UserController::class, 'index'])->name('user.index');
    Route::get('/karyawan/{id}/edit', [App\Http\Controllers\KaryawanController::class, 'edit'])->name('karyawan.edit');
    Route::put('/karyawan/{id}', [App\Http\Controllers\KaryawanController::class, 'updateData'])->name('karyawan.update');
    Route::get('/profile/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('user.show');
    Route::get('/user/{id}/password', [App\Http\Controllers\UserController::class, 'editPassword'])->name('user.editPassword');
    Route::put('/user/{id}/password', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('user.updatePassword');
    Route::delete('/user/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy');
    // Route::post('/user/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/gantifoto/{id}', [App\Http\Controllers\KaryawanController::class, 'gantiFoto'])->name('karyawan.gantiFoto');
    Route::put('/gantifoto/{id}', [App\Http\Controllers\KaryawanController::class, 'updateFoto'])->name('karyawan.updateFoto');
    Route::post('/registkaryawan', [App\Http\Controllers\UserController::class, 'regist'])->name('user.registkaryawan');
});
// test
Route::get('/testdata', [App\Http\Controllers\TestController::class, 'index'])->name('testdata');
Route::get('/datas', [App\Http\Controllers\UserController::class, 'datas'])->name('datauser');
Route::get('/datarkm/{tahun}/{bulan}', [App\Http\Controllers\PerusahaanController::class, 'datas'])->name('datarkm');
// Route::post('/change-year', 'HomeController@changeYear')->name('changeYear');
// test

Route::get('paymantAdvance/{year}/{month}', [App\Http\Controllers\netSalesController::class, 'getRkmDataPerBulanPerMinggu']);
Route::resource('/comment', \App\Http\Controllers\CommentController::class);

Route::resource('/perusahaan', \App\Http\Controllers\PerusahaanController::class);
Route::resource('/materi', \App\Http\Controllers\MateriController::class);
Route::resource('/rkm', \App\Http\Controllers\RKMController::class);
Route::resource('/peserta', \App\Http\Controllers\PesertaController::class);
Route::resource('/registrasi', \App\Http\Controllers\RegistrasiController::class);
Route::resource('/feedback', \App\Http\Controllers\feedbackController::class);
Route::resource('/jabatan', \App\Http\Controllers\jabatanController::class);
Route::resource('/nilaifeedback', \App\Http\Controllers\nilaifeedbackController::class);
Route::resource('/notif', \App\Http\Controllers\notifController::class);
Route::resource('/exam', \App\Http\Controllers\examController::class);
Route::resource('/listexams', App\Http\Controllers\ListExamController::class);
Route::resource('/creditcard', \App\Http\Controllers\creditcardController::class);
Route::resource('/registexam', \App\Http\Controllers\registexamController::class);
Route::resource('/souvenir', \App\Http\Controllers\SouvenirController::class);
Route::resource('/pengajuancuti', \App\Http\Controllers\PengajuancutiController::class);
Route::resource('/pengajuanizin', \App\Http\Controllers\izinTigaJamController::class);
Route::resource('/pengajuanbarang', \App\Http\Controllers\PengajuanBarangController::class);
Route::resource('/suratperjalanan', \App\Http\Controllers\SuratPerjalananController::class);
Route::resource('/rekapitulasiabsen', \App\Http\Controllers\RekapitulasiAbsenController::class);
Route::resource('/kelasanalisis', \App\Http\Controllers\KelasAnalisisController::class);
Route::resource('/paymantAdvance', \App\Http\Controllers\netSalesController::class)->except(['show']);
Route::resource('/databasekpi', \App\Http\Controllers\DatabaseKPIController::class);
Route::resource('/target', \App\Http\Controllers\targetController::class);
Route::resource('/outstanding', \App\Http\Controllers\OutstandingController::class);
Route::resource('/tunjangan', \App\Http\Controllers\TunjanganController::class);
Route::resource('/tunjanganEducation', \App\Http\Controllers\tunjanganEducationController::class);
Route::resource('/rekapmengajarinstruktur', \App\Http\Controllers\rekapInstrukturController::class);
Route::resource('/lembur', \App\Http\Controllers\LemburController::class);
Route::resource('/overtime', \App\Http\Controllers\OvertimeController::class);
Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
Route::resource('roles', \App\Http\Controllers\RoleController::class);

Route::get('/rkmEditInstruktur/{id}', [App\Http\Controllers\RKMController::class, 'editInstruktur'])->name('editInstruktur');
Route::put('/rkmUpdateInstruktur', [App\Http\Controllers\RKMController::class, 'updateInstruktur'])->name('updateInstruktur');
Route::get('/rkmEdit', [App\Http\Controllers\RKMController::class, 'editRKM'])->name('rkmEdit');
Route::put('/rkmUpdate', [App\Http\Controllers\RKMController::class, 'updateRKM'])->name('rkmUpdate');

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

Route::get('GetDatabasekpi', [App\Http\Controllers\DatabaseKPIController::class, 'getData'])->name('GetDatabaseKPI');
Route::get('getPerusahaanById', [App\Http\Controllers\PerusahaanController::class, 'getPerusahaanById'])->name('getPerusahaanById');
Route::get('getRegistrasiall', [App\Http\Controllers\RegistrasiController::class, 'getRegistrasiall'])->name('getRegistrasiall');
Route::get('getPesertaall', [App\Http\Controllers\PesertaController::class, 'getPesertaall'])->name('getPesertaall');
Route::get('getExam', [App\Http\Controllers\examController::class, 'getExam'])->name('getExam');
Route::get('getHistoriExam', [App\Http\Controllers\examController::class, 'getHistoriExam'])->name('getHistoriExam');
Route::get('getListExam', [App\Http\Controllers\listexamController::class, 'getListExam'])->name('getListExam');
Route::get('getCC', [App\Http\Controllers\creditcardController::class, 'getCC'])->name('getCC');
Route::get('getRegistrasiexam', [App\Http\Controllers\registexamController::class, 'getRegistrasiexam'])->name('getRegistrasiexam');
Route::get('getRegistrasiexamByIdExam/{id}', [App\Http\Controllers\registexamController::class, 'getRegistrasiexamByIdExam'])->name('getRegistrasiexamByIdExam');
Route::get('getSouvenir', [App\Http\Controllers\SouvenirController::class, 'getSouvenir'])->name('getSouvenir');
Route::get('getSouvenirPeserta', [App\Http\Controllers\SouvenirController::class, 'getSouvenirPeserta'])->name('getSouvenirPeserta');
Route::get('getFeedbacksByMonth/{year}/{month}', [App\Http\Controllers\feedbackController::class, 'getFeedbacksByMonth'])->name('getFeedbacksByMonth');
Route::get('getPengajuanCuti', [App\Http\Controllers\PengajuancutiController::class, 'getPengajuanCuti'])->name('getPengajuanCuti');
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
Route::get('getAbsen', [App\Http\Controllers\RekapitulasiAbsenController::class, 'getAbsen'])->name('getAbsen');
Route::get('getTarget', [App\Http\Controllers\TargetController::class, 'getTarget'])->name('getTarget');
Route::get('getOutstandingLunas', [App\Http\Controllers\OutstandingController::class, 'getOutstandingLunas'])->name('getOutstandingLunas');
Route::get('getOutstandingHutang', [App\Http\Controllers\OutstandingController::class, 'getOutstandingHutang'])->name('getOutstandingHutang');
Route::get('getOutstandingRKM/{year}/{month}', [App\Http\Controllers\OutstandingController::class, 'getOutstandingRKM'])->name('getOutstandingRKM');
Route::get('singkronDataOutstandingRKM', [App\Http\Controllers\OutstandingController::class, 'singkronDataOutstanding'])->name('outstanding.singkronDataOutstanding');
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

Route::get('/rkm/{id}/souvenir', [App\Http\Controllers\SouvenirController::class, 'createSouvenirInhouse'])->name('createSouvenirInhouse');
Route::post('/rkm/storesouvenir', [App\Http\Controllers\SouvenirController::class, 'storeSouvenirInhouse'])->name('storeSouvenirInhouse');
Route::put('/rkm/{id}/updatesouvenir', [App\Http\Controllers\SouvenirController::class, 'updateSouvenirInhouse'])->name('updateSouvenirInhouse');

Route::post('/providers', [App\Http\Controllers\listexamController::class, 'storeProviders'])->name('providers.store');
Route::post('/vendors', [App\Http\Controllers\listexamController::class, 'storeVendor'])->name('vendors.store');

Route::get('/detailfeedbacks', [App\Http\Controllers\feedbackController::class, 'detailfeedbacks'])->name('detailfeedbacks');

// Route::get('nilaifeedback/export', [App\Http\Controllers\feedbackController::class, 'export'])->name('nilaifeedback.export');
Route::get('nilaifeedbackexport/{year}/{month}', [App\Http\Controllers\nilaifeedbackController::class, 'export'])->name('nilaifeedbackexport');
Route::get('RekapitulasiAbsenperKaryawanExport/{year}/{month}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportperKaryawan'])->name('RekapitulasiAbsenperKaryawanExport');
Route::get('RekapitulasiAbsenperBulanExport/{year}/{month}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportperBulan'])->name('RekapitulasiAbsenperBulanExport');
Route::get('RekapitulasiWaktuKeterlambatanExport/{year}', [App\Http\Controllers\RekapitulasiAbsenController::class, 'exportKeterlambatan'])->name('RekapitulasiWaktuKeterlambatanExport');

Route::post('/pengajuan-klaim/excel-download/no-record', [pengajuanKlaimController::class, 'pengajuanKlaimExcel'])->name('pengajuanklaim.excelNoRecord');
Route::post('/pengajuan-klaim/pdf-download/no-record', [pengajuanKlaimController::class, 'pengajuanKlaimPDF'])->name('pengajuanklaim.PDFNoRecord');
Route::get('/pengajuan-klaim/create/no-record', [pengajuanKlaimController::class, 'noRecord'])->name('pengajuanklaim.createNoRecord');
Route::get('/pengajuan-klaim/create/scheme-work', [pengajuanKlaimController::class, 'schemeWork'])->name('pengajuanklaim.createSchemeWork');
Route::get('/pengajuan-klaim/create/cancel-leave', [pengajuanKlaimController::class, 'cancelLeave'])->name('pengajuanklaim.createCancelLeave');
Route::post('/pengajuan-klaim/add/no-record', [pengajuanKlaimController::class, 'createNoRecord'])->name('pengajuanklaim.addNoRecord');
Route::post('/pengajuan-klaim/add/scheme-work', [pengajuanKlaimController::class, 'createCancelLeave'])->name('pengajuanklaim.addCancelLeave');
Route::post('/pengajuan-klaim/add/cancel-leave', [pengajuanKlaimController::class, 'createSchemeWork'])->name('pengajuanklaim.addSchemeWork');
Route::post('/pengajuan-klaim/aprove/no-record', [pengajuanKlaimController::class, 'approveNoRecord'])->name('pengajuanklaim.aproveNoRecord');
Route::post('/pengajuan-klaim/aprove/scheme-work', [pengajuanKlaimController::class, 'approveCancelLeave'])->name('pengajuanklaim.aproveCancelLeave');
Route::post('/pengajuan-klaim/aprove/cancel-leave', [pengajuanKlaimController::class, 'approveSchemeWork'])->name('pengajuanklaim.aproveSchemeWork');
Route::post('/pengajuan-klaim/delete/no-record', [pengajuanKlaimController::class, 'deleteNoRecord'])->name('pengajuanklaim.deleteNoRecord');
Route::post('/pengajuan-klaim/delete/scheme-work', [pengajuanKlaimController::class, 'deleteCancelLeave'])->name('pengajuanklaim.deleteCancelLeave');
Route::post('/pengajuan-klaim/delete/cancel-leave', [pengajuanKlaimController::class, 'deleteSchemeWork'])->name('pengajuanklaim.deleteSchemeWork');
Route::get('/pengajuan-klaim', [pengajuanKlaimController::class, 'index'])->name('pengajuanklaim.index');
Route::put('notifications/{notification}/read', [App\Http\Controllers\CommentController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::put('/notifications/markAllAsRead', [App\Http\Controllers\CommentController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
Route::get('/rkm/{id}/absensi', [App\Http\Controllers\RKMController::class, 'absensiPeserta'])->name('absensiPeserta');
Route::put('/suratperjalanan/{id}/approval', [App\Http\Controllers\SuratPerjalananController::class, 'approval'])->name('suratperjalanan.approval');
Route::get('/fetch-attendance', [\App\Http\Controllers\RKMController::class, 'fetchAttendance'])->name('attendance.fetch');
Route::post('/absensi', [\App\Http\Controllers\AbsensiKaryawanController::class, 'storeAbsensi'])->name('absensi.store');
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
Route::post( '/absensi/create/pengajuan-klaim/no-recorded', [App\Http\Controllers\AbsensiKaryawanController::class, 'createNoRecord'])->name('absensi.createNoRecord');
Route::post( '/absensi/create/pengajuan-klaim/cancel-leave', [App\Http\Controllers\AbsensiKaryawanController::class, 'createCancelLeave'])->name('absensi.createCancelLeave');
Route::post(  '/absensi/create/pengajuan-klaim/scheme_work', [App\Http\Controllers\AbsensiKaryawanController::class, 'createSchemeWork'])->name('absensi.createSchemeWork');
Route::post('/absensi/update', [\App\Http\Controllers\AbsensiKaryawanController::class, 'update'])->name('absensi.update');
Route::get('/absensi/{id}/edit', [App\Http\Controllers\RekapitulasiAbsenController::class, 'edit'])->name('absensi.edit');
Route::get('/absensi/create', [App\Http\Controllers\AbsensiKaryawanController::class, 'create'])->name('absensi.create');
Route::post('/absensi/manual', [\App\Http\Controllers\AbsensiKaryawanController::class, 'absenManual'])->name('absensi.manual');
// Route::put('/absensi/{id}/update', [App\Http\Controllers\RekapitulasiAbsenController::class, 'update'])->name('rekapitulasiabsen.update');
Route::get('/cekip', [App\Http\Controllers\AbsensiKaryawanController::class, 'cekip'])->name('cekip');
Route::get('/rkm/{id}/registform', [App\Http\Controllers\RKMController::class, 'createRegistForm'])->name('createRegistForm');
Route::put('/rkm/{id}/registformupdate', [App\Http\Controllers\RKMController::class, 'uploadRegistForm'])->name('uploadRegistForm');
Route::post('/rkm/download/excel', [App\Http\Controllers\RKMController::class, 'excelDownload'])->name('excel');
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
Route::post('/rkm/store/absensi', [ControllersRKMController::class, 'storeAbsensi'])->name('storeAbsensi');
Route::post('/rkm/delete/absensi', [ControllersRKMController::class, 'deleteAbsensi'])->name('deleteAbsensi');
Route::get('/rkm/uploadSertifikat/{id}', [ControllersRKMController::class, 'uploadSertifikat'])->name('uploadSertifikat');
Route::post('/rkm/store/sertifikat', [ControllersRKMController::class, 'storeSertifikat'])->name('storeSertifikat');
Route::post('/rkm/delete/sertifikat', [ControllersRKMController::class, 'deleteSertifikat'])->name('deleteSertifikat');
Route::get('/paymantAdvance/detail/{id}/view', [netSalesController::class, 'detail'])->name('netsales.detail');
Route::post('/paymantAdvance/detail/data/get', [netSalesController::class, 'dataDetail'])->name('netsales.data.detail.get');
Route::post('/paymantAdvance/approved', [approvedNetSalesController::class, 'approve'])->name('netsales.approved');
Route::get('/paymantAdvance/edit/{id}', [netSalesController::class,'edit'])->name('netSales.edit.index');
Route::post('/paymantAdvance/data/get/', [netSalesController::class, 'dataEdit'])->name('netSales.edit.get');
Route::post('/paymantAdvance/data/update', [netSalesController::class, 'updateNetSales'])->name('netSales.update');

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

Route::get('/ticketing-data', [DashboardItsmController::class, 'getJumlahPermintaan']);
Route::get('/jumlah-pic', [DashboardItsmController::class, 'getJumlahPIC']);
Route::get('/rerata-durasi-data', [DashboardItsmController::class, 'getRerataDurasi']);
Route::get('/rerata-ketepatan-response-data', [DashboardItsmController::class, 'getRerataKetepatanResponse']);
Route::get('/jumlah-permintaan-per-bulan', [DashboardItsmController::class, 'getJumlahPermintaanPerBulan']);
Route::get('/permintaan-sering-diajukan', [DashboardItsmController::class, 'getPermintaanSeringDiajukan']);
Route::get('/list-bulan', [DashboardItsmController::class, 'getListBulan']);

Route::get('/dashboard-crm', function () {
    return view('dashboard');
})->name('dashboard');
