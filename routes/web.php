<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

Route::get('/', [RepositoryController::class, 'index'])->name('repository.index');
Route::get('/file-manager/{type}/{program}/{subfolder?}', [RepositoryController::class, 'file_manager'])->where('subfolder', '.*')->name('repository.file_manager');
Route::post('/upload-file/{type}/{program}/{subfolder?}', [RepositoryController::class, 'upload_file'])->where('subfolder', '.*')->name('repository.upload_file');
Route::post('/upload-folder/{type}/{program}/{subfolder?}', [RepositoryController::class, 'upload_folder'])->where('subfolder', '.*')->name('repository.upload_folder');
Route::post('/add-folder/{type}/{program}/{subfolder?}', [RepositoryController::class, 'add_folder'])->where('subfolder', '.*')->name('repository.add_folder');
Route::get('/delete-folder/{type}/{program}/{subfolder?}/{folder}', [RepositoryController::class, 'delete_folder'])->where('subfolder', '.*')->name('repository.delete_folder');
Route::get('/download-file/{type}/{program}/{subfolder?}/{file}', [RepositoryController::class, 'download_file'])->where('subfolder', '.*')->name('repository.download_file');
Route::post('/download-folder/{type}/{program}/{subfolder?}', [RepositoryController::class, 'download_folder'])->where('subfolder', '.*')->name('repository.download_folder');
Route::get('/delete-file/{type}/{program}/{subfolder?}/{file}', [RepositoryController::class, 'delete_file'])->where('subfolder', '.*')->name('repository.delete_file');
Route::get('/open-file/{type}/{program}/{subfolder?}/{file}', [RepositoryController::class, 'open_file'])->where('subfolder', '.*')->name('repository.open_file');
Route::post('/rename/{type}/{program}/{subfolder?}', [RepositoryController::class, 'rename'])->where('subfolder', '.*')->name('repository.rename');
Route::get('/repository/{type}/{program}/download-all/{subfolder?}', [RepositoryController::class, 'download_current_directory'])->where('subfolder', '.*')->name('repository.download_all');
Route::get('/login', [AuthController::class, 'ShowLoginForm'])->name('show_login_form');
Route::post('/login', [AuthController::class, 'Login'])->name('login');
Route::post('/logout', [AuthController::class, 'Logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/update-username', [UserController::class, 'updateUsername'])->name('update.username');
    Route::post('/update-password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::post('/update-download-password', [UserController::class, 'updateDownloadPassword'])->name('update_download_password');
});