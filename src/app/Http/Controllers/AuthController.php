<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 登録フォームの表示
     *
     * @return void
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * 登録処理
     *
     * @param StoreUserRequest $request
     * @return void
     */
    public function store(StoreUserRequest $request)
    {
        // 検証はここへ来る前に完了（$request->validated() でOK）
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            // 必要なら管理画面用フラグやロール付与など
            // 'is_admin' => true,
        ]);

        // そのままログインさせたい場合
        Auth::login($user);

        return view("test");

        // == 下記は応用問題時に実装 == //

        // // 認証コードを生成（6桁・ゼロ埋め）
        // $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // // コードと有効期限を保存（例: 10分間）
        // $user->update([
        //     'email_verification_code' => $code,
        //     'email_verification_expires_at' => now()->addMinutes(10),
        // ]);

        // // メール送信（MailHogで確認可）
        // Mail::raw("以下の6桁コードを入力して認証してください：\n\n{$code}\n\n有効期限：10分", function ($message) use ($user) {
        //     $message->from('no-reply@example.com', 'Flea Market 運営');
        //     $message->to($user->email)
        //         ->subject('【Flea Market】メール認証コード');
        // });

        // // メール認証誘導画面
        // return redirect()->route('verification.notice');
    }

    /**
     * ログインフォームの表示
     *
     * @return void
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     *
     * @param LoginRequest $request
     * @return void
     */
    public function send(LoginRequest $request)
    {
        $credentials = $request->validated();

        // remember チェックボックスがあれば第二引数で制御
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('attendance.index')); // 成功 → 管理画面へ
        }

        // ここでは「認証失敗」を email フィールドのエラーとして返す（項目下に出せる）
        throw ValidationException::withMessages([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    /**
     * ログアウト
     *
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request)
    {
        Auth::logout();                         // 認証情報を破棄
        $request->session()->invalidate();      // セッションを完全リセット
        $request->session()->regenerateToken(); // 新しいCSRFトークンを再発行

        return redirect()->route('login');
    }
}
