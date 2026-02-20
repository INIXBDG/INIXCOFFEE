<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\Api\apiController;
use App\Http\Controllers\CatatanSalesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PeluangController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebPushController;
use App\Http\Controllers\TelegramController;
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

Route::middleware('auth:api')->group(function () {
    Route::post('/webpush/subscribe', [WebPushController::class, 'subscribe'])->name('webpush.subscribe');
    Route::post('/webpush/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('webpush.unsubscribe');
    Route::get('/webpush/vapid-key', [WebPushController::class, 'getVapidKey'])->name('webpush.vapid-key');
    Route::post('/webpush/test', [WebPushController::class, 'testNotification'])->name('webpush.test');
});

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
Route::get('/dashboard/csat-instruktur', [apiController::class, 'CSATinstruktur'])->name('CSATinstruktur');
Route::get('/dashboard/aktivitas-instruktur', [apiController::class, 'AktivitasInstruktur'])->name('AktivitasInstruktur');
Route::get('/dashboard/rekomendasi-materi', [apiController::class, 'RekomendasiMateri'])->name('RekomendasiMateri');
Route::get('getDBKlien', [App\Http\Controllers\Api\apiController::class, 'getDBKlien'])->name('getDBKlien');



Route::post('/create/ticket', [TicketController::class, 'store']);

Route::match(['get', 'post'], '/webhook/fonnte', [WebhookController::class, 'handle']);

Route::get('/telegram/setwebhook', [TelegramController::class, 'setWebhook'])->name('telegram.setwebhook');
Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->name('telegram.webhook');