<?php

use App\Http\Controllers\RepositoryController;

Route::get('/', [RepositoryController::class, 'index'])->name('repository.index');
Route::get('/file-manager/{type}/{program}', action: [RepositoryController::class, 'file_manager'])->name('repository.file_manager');
Route::post('/upload-file/{type}/{program}/{folder?}', [RepositoryController::class, 'upload_file'])->name('repository.upload_file');
Route::post('/add-folder/{type}/{program}/{folder?}', [RepositoryController::class, 'add_folder'])->name('repository.add_folder');
Route::get('/delete-folder/{type}/{program}/{folder?}', [RepositoryController::class, 'delete_folder'])->name('repository.delete_folder');
Route::get('/download-file/{type}/{program}/{file?}', [RepositoryController::class, 'download_file'])->name('repository.download_file');
Route::get('/delete-file/{type}/{program}/{file?}', [RepositoryController::class, 'delete_file'])->name('repository.delete_file');
Route::get('/compress-folder/{type}/{program}/{folder?}', [RepositoryController::class, 'compress_folder'])->name('repository.compress_folder');
Route::get('/compress-file/{type}/{program}/{file?}', [RepositoryController::class, 'compress_file'])->name('repository.compress_file');
Route::get('/extract-zip/{type}/{program}/{file?}', [RepositoryController::class, 'extract_zip'])->name('repository.extract_zip');
Route::get('/file-manager/{type}/{program}/{folder?}', [RepositoryController::class, 'open_folder'])->name('repository.open_folder');