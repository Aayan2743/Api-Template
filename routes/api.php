<?php

use App\Mail\SendOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomApiAuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\buildingController;
use App\Http\Controllers\TwoStepVerification;
use App\Http\Controllers\Mgs91Controller;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware'=>'api'],function($routes){
    Route::post('/register',[CustomApiAuthController::class,'register']);
    Route::post('/login',[CustomApiAuthController::class,'login']);
    Route::post('/reset_password',[CustomApiAuthController::class,'reset_password_link']);
    Route::post('/verify_otp',[CustomApiAuthController::class,'verify_otp_update_password']);

 

});



Route::group(['middleware'=>'jwt.verify'],function($routes){

    // Manager Flow

    Route::post('/add-manager',[ManagerController::class,'store']);
    Route::get('/view-manager/{id?}',[ManagerController::class,'viewmanager']); // use this for to fetch the manager details
    Route::post('/update-manager/{id}',[ManagerController::class,'updatemanager']);
    Route::get('/delete-manager/{id}',[ManagerController::class,'deletemanager']);

    //building Flow
    Route::post('/add-building',[buildingController::class,'store']);


    Route::post('/logout',[CustomApiAuthController::class,'logout']);


    // 2 step factor verify email or update R & D later 
    Route::post('/send_verification_code',[TwoStepVerification::class,'send_verification_code'])->middleware('EmailTwoStepVerification');
    Route::post('/verify_email',[TwoStepVerification::class,'verifyEmail'])->middleware('EmailTwoStepVerification');

    // Message OTP Flow
    Route::post('/Send-Otp',[Mgs91Controller::class,'SendOtp']);
    Route::post('/Verify-Otp',[Mgs91Controller::class,'VerifyOtp']);

});

