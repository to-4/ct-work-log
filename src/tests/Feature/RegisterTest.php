<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_error_message_when_name_is_missing(): void
    {
        // 1. 会員登録ページを開く（GETリクエスト）
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 2. 名前を空にして POST 送信
        $formData = [
            'name' => '', // ← 空
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $response = $this->post('/register', $formData);

        // 3. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['name']);

        // 4. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post('/register', $formData)
            ->assertSee('お名前を入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_email_is_missing(): void
    {
        // 1. 登録フォームページにアクセス
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // 2. メールアドレスを空にしてフォーム送信
        $formData = [
            'name' => 'テスト太郎',
            'email' => '', // ← 入力しない
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $response = $this->post('/register', $formData);

        // 3. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['email']);

        // 4. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('register'), $formData)
            ->assertSee('メールアドレスを入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_password_is_missing(): void
    {
        // 1. 登録フォームページにアクセス
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // 2. パスワードを空にして送信
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => '', // ← 入力しない
            'password_confirmation' => 'password123',
        ];
        $response = $this->post('/register', $formData);

        // 3. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['password']);

        // 4. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('register'), $formData)
            ->assertSee('パスワードを入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_password_is_too_short(): void
    {
        // 1. 登録フォームページにアクセス
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // 2. パスワードを7文字以下にして送信
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'pass123', // 7文字
            'password_confirmation' => 'pass123',
        ];
        $response = $this->post('/register', $formData);

        // 3. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['password']);

        // 4. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('register'), $formData)
            ->assertSee('パスワードは8文字以上で入力してください');
    }

    #[Test]
    public function it_displays_error_message_when_password_confirmation_does_not_match(): void
    {
        // 1. 登録フォームページにアクセス
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // 2. パスワードと確認用パスワードを不一致にして送信
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123', // ← 不一致
        ];
        $response = $this->post('/register', $formData);

        // 3. バリデーションエラーをセッションに持つか確認
        $response->assertSessionHasErrors(['password_confirmation']);

        // 4. 実際のBlade上にメッセージが出ることを確認
        $this->followingRedirects()
            ->post(route('register'), $formData)
            ->assertSee('パスワードと一致しません');
    }

    #[Test]
    public function it_registers_user_and_redirects_to_profile(): void
    {
        // 1. 登録フォームページにアクセス
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        // 2. 正しいデータを送信
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $response = $this->post('/register', $formData);

        // 3. DBにユーザーが登録されたか確認
        $this->assertDatabaseHas('users', [
            'email' => 'taro@example.com',
        ]);

    }
}
