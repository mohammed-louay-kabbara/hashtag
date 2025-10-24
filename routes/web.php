<?php

use Illuminate\Support\Facades\Route;
use App\Models\Nabza;
use App\Http\Controllers\NubdhaController;


Route::get('/', function () {
    return "scksckscmsk";
});
Route::get('/te',function(){
    return "sssssss";
});
Route::get('/nabza/{id}', [NubdhaController::class, 'showPublic']);

Route::get('/nabza/{id}', function ($id) {
    $nabza = Nabza::with(['user', 'stories'])->find($id);

    if (!$nabza) {
        return response()->view('nabza_not_found', [], 404);
    }

    // ✅ إذا الفتح من متصفح عادي، اعرض صفحة HTML
    return view('nabza_share', ['nabza' => $nabza]);
});