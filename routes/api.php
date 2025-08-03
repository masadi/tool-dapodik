<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DapodikController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login/{provider}', [AuthController::class, 'login'])->name('login');
});
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('sekolah', [DapodikController::class, 'index']);
    Route::post('sekolah', [DapodikController::class, 'sekolah']);
    Route::get('reset', [DapodikController::class, 'reset']);
    Route::get('cek-sekolah', [DapodikController::class, 'sekolah']);
    Route::post('cari-data', [DapodikController::class, 'cari_data']);
    Route::post('get-jabatan', [DapodikController::class, 'get_jabatan']);
    Route::post('daftar', [DapodikController::class, 'daftar']);
    Route::get('guru', [DapodikController::class, 'guru']);
    Route::post('guru', [DapodikController::class, 'simpan_guru']);
    Route::post('cek-pd', [DapodikController::class, 'cek_pd']);
    Route::get('periodik', [DapodikController::class, 'periodik']);
    Route::post('periodik', [DapodikController::class, 'periodik']);
    Route::get('pip', [DapodikController::class, 'pip']);
    Route::post('pip', [DapodikController::class, 'pip']);
    Route::post('tambah-ptk', [DapodikController::class, 'tambah_ptk']);
    Route::post('wilayah', [DapodikController::class, 'wilayah']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
