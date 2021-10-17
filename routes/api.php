<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*

AUTH API
*/
Route::post('signup',[UserController::class,'authSignUp']);
Route::post('login',[UserController::class,'authLogin']);

Route::group(['middleware' => ['jwt.verify']], function() {

Route::post('profile-detail',[UserController::class,'profileDetail']);
Route::post('update-profile',[UserController::class,'updateProfile']);
Route::delete('delete-account/{id}',[UserController::class,'deleteAccount']);
Route::post('change-password',[UserController::class,'changePassword']);
Route::post('forgot-password',[UserController::class,'forgotPassword']);
Route::post('fetch-all-user',[UserController::class,'allUser']);

});

/*

POST API

*/

Route::post('create-post',[UserController::class,'createPost']);
Route::post('view-post-details',[UserController::class,'postDetails']);
Route::post('view-all-post',[UserController::class,'viewPost']);
Route::post('like-dislike',[UserController::class,'likeDislike']);
Route::post('comment',[UserController::class,'commentPost']);
Route::post('delete-post',[UserController::class,'deletePost']);
Route::post('update-post',[UserController::class,'updatePost']);


