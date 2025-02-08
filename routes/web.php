<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

Route::get('/', [RepositoryController::class, 'index'])->name('repository.index');
Route::get('/file-manager/{type}/{program}', [RepositoryController::class, 'file_manager'])->name('repository.file_manager');
Route::post('/upload-file/{type}/{program}', [RepositoryController::class, 'upload_file'])->name('repository.upload_file');
Route::post('/upload-folder/{type}/{program}', [RepositoryController::class, 'upload_folder'])->name('repository.upload_folder');
Route::post('/add-folder/{type}/{program}', [RepositoryController::class, 'add_folder'])->name('repository.add_folder');
Route::get('/delete-folder/{type}/{program}/{folder}', [RepositoryController::class, 'delete_folder'])->name('repository.delete_folder');
Route::get('/download-file/{type}/{program}/{file}', [RepositoryController::class, 'download_file'])->name('repository.download_file');
Route::get('/delete-file/{type}/{program}/{file}', [RepositoryController::class, 'delete_file'])->name('repository.delete_file');
Route::get('/compress-folder/{type}/{program}/{folder}', [RepositoryController::class, 'compress_folder'])->name('repository.compress_folder');
Route::get('/compress-file/{type}/{program}/{file}', [RepositoryController::class, 'compress_file'])->name('repository.compress_file');
Route::get('/extract-zip/{type}/{program}/{file}', [RepositoryController::class, 'extract_zip'])->name('repository.extract_zip');
Route::get('/login', [AuthController::class, 'ShowLoginForm'])->name('show_login_form');
Route::post('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/logout', [AuthController::class, 'Logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/update-username', [UserController::class, 'updateUsername'])->name('update.username');
    Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::post('/update-download-password', [UserController::class, 'updateDownloadPassword'])->name('update_download_password');
});