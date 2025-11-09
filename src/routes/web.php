<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

    Route::prefix('/attendance')
        ->name('attendance.')
        ->controller(AttendanceController::class)->group(function () {
            Route::get ('/',            'index')      ->name('index');
            Route::get ('/list',        'list')       ->name('list');
            Route::get ('/detail/{id}', 'detail')     ->name('detail');
            Route::post('/start',       'start')      ->name('start');
            Route::post('/end',         'end')        ->name('end');
            Route::post('/break/start', 'break_start')->name('break.start');
            Route::post('/break/end',   'break_end')  ->name('break.end');
            Route::put ('/detail/{id}', 'update')     ->name('detail.update');
    });

    Route::prefix('/stamp_correction_request')
        ->name('request.')
        ->controller(RequestController::class)->group(function () {
            Route::get('/list', 'list')->name('list');
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

            Route::prefix('/attendance')
                ->name('attendance.')
                ->controller(AdminAttendanceController::class)->group(function () {
                    Route::get('/list',            'list')      ->name('list');
                    Route::get('/detail/{id}',     'detail')    ->name('detail');
                    Route::get('/staff/list/{id}', 'staff_list')->name('staff_list');
                    Route::put('/detail/{id}',     'update')    ->name('detail.update');
                    Route::get('/export',          'export')    ->name('export');
            });

            Route::prefix('/staff')
                ->name('staff.')
                ->controller(AdminStaffController::class)->group(function () {
                    Route::get('/list', 'list')->name('list');
            });
        });
    });

/*
|--------------------------------------------------------------------------
| 申請一覧（管理者ログイン済み（admin））用ルート
|--------------------------------------------------------------------------
*/
Route::middleware('admin')
    ->prefix('stamp_correction_request')
    ->name('admin.request.')
    ->controller(AdminRequestController::class)->group(function () {
        Route::get('/approve/{attendance_correct_request_id}', 'approve')->name('approve');
        Route::put('/approve/{attendance_correct_request_id}', 'update') ->name('approve.update');
    });

/*
|--------------------------------------------------------------------------
| メール認証用ルート
|--------------------------------------------------------------------------
*/

// 認証メール送信後の画面（例：認証待ち）
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メールのリンクをクリックしたとき
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('mypage.edit')
        ->with('success', 'メール認証が完了しました！');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 再送用
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// メール認証画面
Route::get('/email/verify-code', function () {
    return view('auth.verify-code');
})->name('verification.code.notice');

// 認証コード検証
Route::post('/email/verify-code', [AuthController::class, 'verifyCode'])
    ->name('verification.code.check');

// 認証コード再送用
Route::post('/email/verification-code/resend', function () {
    $user = Auth::user();

    if (! $user) {
        abort(403);
    }

    // 6桁の新しい認証コードを生成
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // DBに保存（期限は10分）
    $user->update([
        'email_verification_code' => $code,
        'email_verification_expires_at' => now()->addMinutes(10),
    ]);

    // メール送信（MailHog で確認可能）
    Mail::raw("新しい認証コード: {$code}\n\n有効期限: 10分", function ($message) use ($user) {
        $message->to($user->email)
            ->subject('【Flea Market】メール認証コード再送');
    });

    return back()->with('success', '新しい認証コードを送信しました。');
})->middleware(['auth', 'throttle:3,10'])->name('verification.code.resend');
