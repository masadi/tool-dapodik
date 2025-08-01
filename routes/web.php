<?php

use Illuminate\Support\Facades\Route;
/*use App\Http\Controller\AuthController;
Route::group(['prefix' => 'auth'], function () {
//Route::post('sociallogin/{provider}', 'Auth\AuthController@SocialSignup');
Route::get('social/{provider}/callback', [AuthController::class, 'index']);
//Route::get('auth/{provider}/callback', 'OutController@index')->where('provider', '.*');
});*/
Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');
