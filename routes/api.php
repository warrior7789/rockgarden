<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\InvoiceController;




Route::group(['middleware' => ['auth:api']], function() {
    /*Route::post('logout', [ApiController::class, 'logout']);
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
    Route::delete('delete/{product}',  [ProductController::class, 'destroy']);*/
});

Route::group([
    'namespace' => 'App\Http\Controllers\Api',
], function (){ 


    Route::post('login', 'ApiController@authenticate');
    Route::post('register','ApiController@register');
    Route::post('register/verify','ApiController@verifyOtp');
    Route::post('forgot-password','ApiController@sendResetLinkResponse');
    Route::post('forgot-password/check','ApiController@sendResetLinkResponseCheck');
    Route::post('reset-password','ApiController@sendResetResponse');
   

    Route::group([
      'middleware' => ['auth:api']
    ], function() {
        Route::post('logout', 'ApiController@logout');
        Route::get('get-user', 'ApiController@get_user');
        Route::post('profile/update', 'ApiController@profile_update');
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

});




Route::group([
    'namespace' => 'App\Http\Controllers\Api\Admin',
    'prefix' => 'admin',
    'as' => 'admin.'
], function (){ 

    Route::post('registration', 'AuthController@register')->name('registration');

    Route::group([
      'middleware' => ['auth:api','admin'],
    ], function() {
        Route::post('roles', 'UserController@login');
            
        //permissions
        Route::Post('get-permission','PermissionController@index'); //{{base_url}}admin/get-permission
        Route::Post('create-permission','PermissionController@store'); //{{base_url}}admin/create-permission // name
        Route::Post('update-permission','PermissionController@update');
        Route::Post('delete-permission','PermissionController@destroy');

        // roles
        Route::Post('get-roles','RoleController@index');
        Route::Post('create-role','RoleController@store');
        Route::post('edit-role','RoleController@edit');
        Route::Post('update-role','RoleController@update');
        Route::Post('delete-role','RoleController@destroy');
        
    });

});