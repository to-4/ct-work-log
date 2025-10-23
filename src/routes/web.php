<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| 一般ユーザールート
|--------------------------------------------------------------------------
| - guest: 未ログイン（ログイン画面など）
| - auth:  ログイン済み（ログアウトなど）
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 未ログイン（guest）用ルート
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get ('/register', 'create')->name('register');
        Route::post('/register', 'store') ->name('register.post');
        Route::get ('/login',    'login') ->name('login');
        Route::post('/login',    'send')  ->name('login.post');
    });
});

/*
|--------------------------------------------------------------------------
| ログイン済み（auth）用ルート
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function() {
    // ログイン
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'destroy')->name('logout');
    });

    Route::controller(AttendanceController::class)->group(function () {
        Route::get ('/attendance', 'index')->name('attendance.index');
    });
});

/*
|--------------------------------------------------------------------------
| 管理者ルート
|--------------------------------------------------------------------------
| - URLプレフィックス: /admin
| - ルート名: admin.*
| - guest: 未ログイン（ログイン画面など）
| - admin: ログイン済み（ログアウトなど）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->group(function() {

        /*
        |--------------------------------------------------------------------------
        | 未ログイン（guest）用ルート
        |--------------------------------------------------------------------------
        */
        Route::middleware('guest')->group(function () {
            // 未ログイン
            Route::controller(AdminAuthController::class)->group(function () {
                Route::get ('/login', 'login')->name('login');
                Route::post('/login', 'send') ->name('login.post');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | 管理者ログイン済み（admin）用ルート
        |--------------------------------------------------------------------------
        */
        Route::middleware('admin')->group(function () {
            // 管理者ログイン済み
            Route::controller(AdminAuthController::class)->group(function () {
                Route::post('/logout', 'destroy')->name('logout');
            });
        });
   });

Route::get("/", function() {
    return view("test");
})->name('test');
Route::get("/admin/test", function () {
    return view("test_admin");
})->name('admin.test');
