<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;

Route::get('/', [EmailController::class, 'showEmailForm']);
Route::post('/generate-email', [EmailController::class, 'generateEmail']);
Route::get('/emails', [EmailController::class, 'getEmails']);
Route::get('/emails/{id}', [EmailController::class, 'getEmailById']);
Route::put('/emails/{id}', [EmailController::class, 'updateEmail']);
Route::delete('/emails/{id}', [EmailController::class, 'deleteEmail']);