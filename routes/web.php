<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\SignUpController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TasklistController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::controller(SignUpController::class)->group(function () {
    Route::get('/signup', 'signUp')->name('signup');
    Route::post('/signup', 'signUpSubmit')->name('signup.submit');
    Route::post('/signup/send-code', 'sendVerificationCode')->name('signup.send-code');
    Route::get('/signup/verify', 'verifyCodeForm')->name('signup.verify');
    Route::post('/signup/verify', 'verifyCode')->name('signup.verify.submit');
    Route::get('/signup/form', 'signUpForm')->name('signup.form');
    Route::post('/signup/submit', 'signUpSubmit')->name('signup.submit');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('/googleLogin', 'googleLogin')->name('login.google');
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginSubmit')->name('login.submit');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('/logout', 'logoutSubmit')->name('logout.submit');
});

Route::controller(TasklistController::class)->group(function () {
    // get task lists to populate select on edit modal
    Route::get('/tasklist/get/{task_id?}', 'getTasklists')->name('tasklist.get');

    // lists routes
    Route::get('/tasklist', 'showTasklist')->name('tasklist.show'); 
    Route::post('/tasklist/new', 'storeTasklist')->name('tasklist.new');
    Route::post('/tasklist/edit', 'editTasklist')->name('tasklist.edit');
    Route::get('/tasklist/{list_id}/delete', 'deleteTasklist')->name('tasklist.delete');
    Route::get('/tasklist/home', 'index')->name('tasklist.index');
    Route::get('/tasklist/search/{search?}', 'searchTasklist')->name('tasklist.search');
})->middleware('auth');

Route::controller(TaskController::class)->group(function () {
    // homepage of user logged in
    Route::get('/userhome', 'userhome')->name('task.userhome');

    // routes of tasks without list
    Route::get('/task', 'tasks')->name('task.show');
    Route::post('/task/new', 'newTask')->name('task.new'); // check
    Route::post('/task/{task_id}/commentary', 'setCommentaryTask')->name('task.setCommentary'); // check
    Route::post('/task/edit', 'editTask')->name('task.edit');
    Route::get('/task/{task_id}/delete', 'deleteTask')->name('task.delete');
    Route::get('/task/{search?}/search', 'searchTask')->name('task.search');
    Route::get('/task/{filter?}/filter', 'filterTask')->name('task.filter');

    // routes of tasks with list
    Route::get('/tasklist/{list_id}/{search?}', 'searchTask')->name('taskWithList.search');
    Route::get('/tasklist/{list_id}/filter/{filter?}', 'filterTask')->name('taskWithList.filter');
})->middleware('auth');

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/resources', 'resources')->name('resources');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/about_developer', 'developer')->name('developer');
});

Route::get('/download/pdf', [ProjectReportController::class, 'downloadPDF'])->name('downloadPDF');

Route::controller(PasswordResetController::class)->group(function () {
    Route::get('/password/reset', 'resetPasswordForm')->name('password.reset');
    Route::post('/password/reset', 'sendResetPasswordMail')->name('password.reset.submit');
    Route::get('/password/reset/{token}', 'newPasswordForm')->name('password.reset.new');
    Route::post('/password/reset/{token}', 'updatePassword')->name('password.reset.new.submit');
});
