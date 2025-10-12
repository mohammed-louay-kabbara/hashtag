<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NubdhaController;
use App\Http\Controllers\SaveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NubdhaViewController;
use App\Http\Controllers\HashtagStoryController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\LoveController;
use App\Http\Controllers\NotificationController;
use App\Services\FirebaseService;
use App\Models\notification;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 'success'
    ]);
});
    Route::post('login', [AuthController::class, 'login']);
    Route::post('editprofile', [AuthController::class, 'editprofile']);
    Route::post('device-token', [AuthController::class, 'fcm_token']);
    Route::get('info', [AuthController::class, 'info']);
    Route::post('editpassword', [AuthController::class, 'editpassword']);
    Route::post('forgotpassword', [AuthController::class, 'forgot_password']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [AuthController::class, 'sendVerificationCode']);
    Route::post('reset-password', [AuthController::class, 'verifyResetCode']);
    Route::post('count_profile', [AuthController::class, 'count_profile']);
    Route::get('info_user/{id}', [AuthController::class, 'info_user']);
    Route::get('nubdha_user/{id}', [AuthController::class, 'nubdha_user']);
    Route::get('hashtag_user/{id}', [AuthController::class, 'hashtag_user']);
    Route::post('pictureupdate', [AuthController::class, 'pictureupdate']);
    Route::post('notify', [NotificationController::class, 'sendTest']);
    Route::get('my_profile', [AuthController::class, 'my_profile']);
    Route::get('my_nubdha', [AuthController::class, 'my_nubdha']);
    Route::get('my_save', [AuthController::class, 'my_save']);
    Route::get('my_hashtag', [AuthController::class, 'my_hashtag']);    
    Route::get('my_reels', [AuthController::class, 'my_reels']);
    Route::get('getuser', [AuthController::class, 'getuser']);
    Route::post('searchUsers', [AuthController::class,'searchUsers']);
    Route::get('Users', [AuthController::class,'users']);
    Route::post('/store-device-token', [AuthController::class, 'storeDeviceToken']);
    Route::resource('Nubdha',NubdhaController::class);
    Route::post('nubdhaupdate',[NubdhaController::class,'update']);
    Route::resource('save',SaveController::class);
    Route::get('notification',[NotificationController::class,'index']);
    Route::delete('notification_delete/{id}',[NotificationController::class,'destroy']);
    Route::put('notification_read/{id}',[NotificationController::class,'read']);
    Route::resource('report',ReportController::class);
    Route::resource('nubdha_views',NubdhaViewController::class);
    Route::resource('hashtagstory',HashtagStoryController::class);
    Route::resource('follower',FollowerController::class);
    Route::resource('hashtag',HashtagController::class);
    Route::post('hashtagupdate/{id}',[HashtagController::class,'update']);
    Route::resource('love',LoveController::class);

    Route::post('/store-device-token', [NotificationController::class, 'storeDeviceToken']);

    
Route::get('/test-notification', function (FirebaseService $firebase) {
    $deviceToken = 'f_mt4PXzR7u2sUWIQciQ5P:APA91bGj49kk3zMl6U-P9dufTY48mXpWPcEckDM8YNCL1qUa2vrz1XS-tG9kD1iNgmGuRyEOVz7EiUZdgBP-WDSNyXhlVjK9OtQVB0phUMqXVWQeMu2JKbA';
    
    notification::create([
        'user_id' =>2,
        'title' => 'إعجاب',
        'body' => 'لقد نال هاشتاغك على إعجاب',
        'sender_id' => 6
    ]);

    return $firebase->sendNotification(
        $deviceToken,
        'إعجاب',
        'لقد نال هاشتاغك على إعجاب'
    );

});
