<?php

use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\CatatanSalesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PeluangController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::apiResource('/rkmAPI', \App\Http\Controllers\Api\RKMController::class);
// Route::get('rkmAPI', [App\Http\Controllers\UserController::class, 'index']);
Route::get('rkmAPI/{year}/{month}', [App\Http\Controllers\Api\RKMController::class, 'showMonth'])->name('rkmAPI');
Route::get('AbsensiRKMAPI/{year}/{month}', [App\Http\Controllers\Api\RKMController::class, 'RKMAPIabsensi'])->name('RKMAPIabsensi');
Route::get('perusahaan', [App\Http\Controllers\Api\PerusahaanController::class, 'getPerusahaan'])->name('getPerusahaan');
Route::get('getRKMRegist', [App\Http\Controllers\Api\RKMController::class, 'getRKMRegist'])->name('getRKMRegist');
Route::get('cek-peserta', [App\Http\Controllers\Api\PesertaController::class, 'cekPeserta'])->name('cekPeserta');
Route::get('getRKMDetail', [App\Http\Controllers\Api\RKMController::class, 'getRKMDetail'])->name('getRKMDetail');
Route::get('getRKMDetailGroup', [App\Http\Controllers\Api\RKMController::class, 'getRKMDetailGroup'])->name('getRKMDetailGroup');
Route::get('registrasi/list/{id_peserta}', [App\Http\Controllers\Api\PesertaController::class, 'listMateri'])->name('listMateri');
// Route::get('/cek-peserta', 'PesertaController@cekPeserta')->name('cekPeserta');
Route::get('getRKMSouvenir', [App\Http\Controllers\Api\RKMController::class, 'getRKMSouvenir'])->name('getRKMSouvenir');
Route::get('getFeedbacks', [App\Http\Controllers\Api\apiController::class, 'getFeedbacks'])->name('getFeedbacks');
Route::get('getMateri', [App\Http\Controllers\Api\apiController::class, 'getMateri'])->name('getMateri');
Route::get('getPerusahaanall', [App\Http\Controllers\Api\apiController::class, 'getPerusahaanall'])->name('getPerusahaanall');
Route::get('getUserall', [App\Http\Controllers\Api\apiController::class, 'getUserall'])->name('getUserall');
Route::get('getJabatan', [App\Http\Controllers\Api\apiController::class, 'getJabatan'])->name('getJabatan');
Route::get('getMateris', [App\Http\Controllers\Api\apiController::class, 'getMateris'])->name('getMateris');
Route::get('getRegistrasi', [App\Http\Controllers\Api\apiController::class, 'getRegistrasi'])->name('getRegistrasi');
Route::get('upcomingRKM', [App\Http\Controllers\Api\apiController::class, 'UpcomingRKM'])->name('UpcomingRKM');
Route::get('jadwalRKM', [App\Http\Controllers\Api\apiController::class, 'jadwalRKM'])->name('jadwalRKM');
Route::get('materiinix', [App\Http\Controllers\Api\apiController::class, 'getMateriInix'])->name('materiinix');
Route::get('materiinix/{id}', [App\Http\Controllers\Api\apiController::class, 'getMateriInixByID'])->name('materiinixID');
// Route::get('getPerusahaanById', [App\Http\Controllers\Api\PerusahaanController::class, 'getPerusahaanById'])->name('getPerusahaanById');
Route::get('getInventaris', [App\Http\Controllers\Api\apiController::class, 'getInventaris'])->name('getInventaris');



// Contact Test
Route::post('/contact/store', [ContactController::class, 'store'])->name('store.contact');
Route::delete('/contact/delete/{id}', [ContactController::class, 'delete'])->name('delete.contact');
Route::put('/contact/update/{id}', [ContactController::class, 'update'])->name('update.contact');

// Peluang Test
Route::post('/peluang/store', [PeluangController::class, 'store'])->name('store.peluang');
Route::delete('/peluang/delete/{id}', [PeluangController::class, 'delete'])->name('delete.peluang');
Route::put('/peluang/update/{id}', [PeluangController::class, 'updateTahap'])->name('update.tahap');

// Aktivitas Test
Route::post('/aktivitas/store', [AktivitasController::class, 'store'])->name('store.aktivitas');
Route::delete('/aktivitas/delete/{id}', [AktivitasController::class, 'delete'])->name('delete.aktivitas');
Route::put('/aktivitas/update/{id}', [AktivitasController::class, 'update'])->name('update.aktivitas');

// Catatan Sales Test
Route::post('/catatan/sales/store', [CatatanSalesController::class, 'store'])->name('store.catatan.sales');
Route::delete('/catatan/sales/delete/{id}', [CatatanSalesController::class, 'delete'])->name('delete.catatan.sales');
Route::put('/catatan/sales/update/{id}', [CatatanSalesController::class, 'update'])->name('update.catatan.sales');
