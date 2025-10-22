<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * ログインフォームの表示
     *
     * @return void
     */
    public function login()
    {
        return view('admin.login');
    }

    /**
     * ログイン処理
     * ※ FormRequest は、一般ユーザと共通とする
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

            // 管理者権限チェック
            if (Auth::user()->is_admin) {
                return redirect()->intended(route('admin.test')); // 管理者 → 管理画面へ
            } else {
                Auth::logout(); // 一般ユーザーならログアウトさせる
                throw ValidationException::withMessages([
                    'email' => '管理者権限がありません。',
                ]);
            }
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

        return redirect()->route('admin.login');
    }
}
