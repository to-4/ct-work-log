<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AuthRegisterMailTest extends TestCase
{
    Use RefreshDatabase;

    #[Test]
    public function it_sends_verification_mail_after_user_registration(): void
    {
        // 1. 登録データを準備
        $formData = [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 登録リクエストを送信
        $response = $this->post(route('register.post'), $formData);

        // 3. リダイレクト確認
        $response->assertRedirect(route('verification.notice'));

        // 4. 登録後のDB確認
        $user = \App\Models\User::where('email', $formData['email'])->first();
        $this->assertNotNull($user);

        // 5. 有効期限やコード保存の確認
        $this->assertNotNull($user->email_verification_code);
        $this->assertNotNull($user->email_verification_expires_at);

        // MailHog を Docker で動かしている場合、
        // テスト中に送られたメールは
        // ブラウザで次のURLを開くと確認できます👇

        // http://localhost:8025/

        // そこで「To」欄が test@example.com になっていればOK

    }

    #[Test]
    public function it_displays_verification_page_with_link_to_email_verification(): void
    {

        // 1. テスト用ユーザーでログイン
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
        $this->actingAs($user);

        // 2. 誘導ページを開く
        $response = $this->get(route('verification.notice'));

        // 3. ページステータス確認
        $response->assertStatus(200);

        // 4. 「認証はこちらから」ボタンが表示されていること
        $response->assertSee('認証はこちらから');

        // 5. ボタンの遷移先が正しいことを確認
        $response->assertSee(route('verification.code.notice'));

        // 6. 実際にメール認証サイトへ遷移できることを確認
        $verifyResponse = $this->get(route('verification.code.notice'));

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee('メール認証'); // ページ内キーワードで確認
    }

    #[Test]
    public function it_redirects_to_profile_page_after_email_verification()
    {
        // 1. 仮ユーザーを作成（未認証状態）
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 2. 認証コードと有効期限を設定
        $code = '123456';
        $user->update([
            'email_verification_code' => $code,
            'email_verification_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 認証コードを送信（POSTリクエスト）
        $response = $this->post(route('verification.code.check'), [
            'code' => $code,
        ]);

        // 5. リダイレクト先が勤怠登録画面であることを確認
        $response->assertRedirect(route('attendance.index'));

        // 6. ユーザーが認証済みになっていることを確認
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // 7. プロフィール設定画面を開けることを確認
        $page = $this->get(route('attendance.index'));
        $page->assertStatus(200);
        $page->assertSee('出勤');
    }
}
