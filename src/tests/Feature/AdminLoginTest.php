<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_error_message_when_email_is_missing(): void
    {
        // 1. テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // 2. 一旦ログインフォームページにアクセス
        //    ※ これがないとリダイレクト先が見つからず 404 エラーになる
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        // 3. メールアドレスを空にして送信
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $formData = [
            'email' => '', // ← 未入力
            'password' => 'password123',
        ];
        $response = $this->post(route('admin.login.post'), $formData);

        // 4. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['email']);

        // 5. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('admin.login.post'), $formData)
            ->assertSee('メールアドレスを入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_password_is_missing(): void
    {
        // 1. テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // 2. 一旦ログインフォームページにアクセス
        //    ※ これがないとリダイレクト先が見つからず 404 エラーになる
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        // 3. メールアドレスを空にして送信
        $formData = [
            'email' => 'taro@example.com',
            'password' => '', // ← 未入力
        ];
        $response = $this->post(route('admin.login.post'), $formData);

        // 4. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['password']);

        // 5. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('admin.login.post'), $formData)
            ->assertSee('パスワードを入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_login_information_is_wrong(): void
    {
        // 1. テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // 2. 一旦ログインフォームページにアクセス
        //    ※ これがないとリダイレクト先が見つからず 404 エラーになる
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        // 3. 存在しないユーザー情報で送信
        $formData = [
            'email' => 'notfound@example.com',
            'password' => 'wrongpassword',
        ];
        $response = $this->post(route('admin.login.post'), $formData);

        // 4. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['email']);

        // 5. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('admin.login.post'), $formData)
            ->assertSee('ログイン情報が登録されていません');
    }
}
