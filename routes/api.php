<?php

use App\Http\Controllers\TaskApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(TaskApi::class)->group(function () {
    Route::post('/task/start', 'startTask')->name('startTask');
    Route::post('/task/pause', 'pauseTask')->name('pauseTask');
    Route::post('/task/update-elapsed-time', 'updateElapsedTime')->name('updateElapsedTime');
    Route::get('/task/get-task', 'getTask')->name('getTask');
    Route::get('/task/check-for-started-task', 'checkForStartedTask')->name('checkForStartedTask');
});