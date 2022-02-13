<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\InvoiceController;

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);
Route::post('register/verify', [ApiController::class, 'verifyOtp']);
Route::post('forgot-password', [ApiController::class, 'sendResetLinkResponse']);
Route::post('forgot-password/check', [ApiController::class, 'sendResetLinkResponseCheck']);
Route::post('reset-password', [ApiController::class, 'sendResetResponse']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [ApiController::class, 'logout']);
    Route::get('get-user', [ApiController::class, 'get_user']);
    Route::post('profile/update', [ApiController::class, 'profile_update']);
    Route::get('get-service', [ServiceController::class, 'get_service']);
    Route::get('get-application-histories', [ServiceController::class, 'get_application_histories']);
    Route::get('get-application-history', [ServiceController::class, 'get_application_history']);
    Route::get('get-staff-assignments', [StaffController::class, 'get_staff_assignments']);

    Route::get('get-invoices', [InvoiceController::class, 'get_invoices']);
    Route::post('init-transaction', [InvoiceController::class, 'init_transaction']);
    Route::post('charge-callback', [InvoiceController::class, 'charge_callback']);
    Route::post('charge-callback-rave', [InvoiceController::class, 'charge_callback_rave']);
    
    Route::get('', [ServiceController::class, 'get_application_history']);

    Route::post('apply-for-service', [ServiceController::class, 'apply_for_service']);
    Route::post('upload-file', [ProfileController::class, 'upload_file']);
    Route::post('update-photo', [ProfileCOntroller::class, 'update_photo']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('create', [ProductController::class, 'store']);
    Route::put('update/{product}',  [ProductController::class, 'update']);
    Route::delete('delete/{product}',  [ProductController::class, 'destroy']);
});