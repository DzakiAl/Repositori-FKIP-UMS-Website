<?php

use App\Http\Controllers\RepositoryController;

Route::get('/', [RepositoryController::class, 'index'])->name('repository.index');
Route::get('/file-manager/{type}/{program}', [RepositoryController::class, 'file_manager'])->name('repository.file_manager');
Route::post('/upload-file/{type}/{program}', [RepositoryController::class, 'upload_file'])->name('repository.upload_file');
Route::post('/add-folder/{type}/{program}', [RepositoryController::class, 'add_folder'])->name('repository.add_folder');