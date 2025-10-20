# 模擬案件\_勤怠管理アプリ

## 環境構築

### Docker ビルド

1. git clone git@github.com:to-4/ct-flea-market.git
2. cd ct-flea-market
3. Windows(wsl) の場合は、下記を実行
   ```
   printf "UID=%s\n" "$(id -u)" > .env
   ```
   > _※ Mac の場合は省略可_
4. DockerDesktop アプリを立ち上げる
5. docker compose up -d --build

### Laravel 環境構築

1.  docker compose exec php bash
2.  composer install
3.  「.env.example」ファイルをコピーして「.env」ファイルを作成
4.  「.env」ファイルに対して、以下の環境変数を変更

    ```
    DB_CONNECTION=mysql
    - DB_HOST=127.0.0.1
    + DB_HOST=mysql
    DB_PORT=3306
    - DB_DATABASE=laravel
    - DB_USERNAME=root
    - DB_PASSWORD=
    + DB_DATABASE=laravel_db
    + DB_USERNAME=laravel_user
    + DB_PASSWORD=laravel_pass

    > _※ 上記の "-" は削除行、"+"は追加行を表します_

5.  アプリケーションキーの作成
    ```
    php artisan key:generate
    ```
6.  マイグレーションの実行
    ```
    php artisan migrate
    ```
7.  シーディングの実行
    ```
    php artisan db:seed
    ```
8.  シンボリックリンクの作成
    ```
    php artisan storage:link
    ```
## 使用技術

- PHP 8.3
- Laravel 12.28
- MySQL 8.4

## ER 図

![ER図](./images/ER-core_v1.svg)

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- MailHog：http://localhost:8025/

---

## ✉️ メール認証（MailHog 使用）

本アプリでは、MailHog を使用してメール認証を実装しています。
実際には外部メール送信は行われず、MailHog 上で確認できます。

1. 新規登録後、MailHog にメールが届く
2. メール本文にある 6 桁コードを認証画面で入力
3. 認証成功後、ログイン可能になります

MailHog URL: [http://localhost:8025](http://localhost:8025)

## 🧪 テスト手法（PHPUnit）

本アプリでは **PHPUnit** による自動テストを実施しています。  
Docker 上で Laravel + MySQL のテスト環境を再現し、  
`.env.testing` に基づいた専用データベースでテストを行います。

---

### 🔧 環境構成

| コンポーネント | バージョン | 用途 |
|----------------|-------------|------|
| PHP | 8.3 | アプリケーション実行 |
| Laravel | 12.x | フレームワーク |
| MySQL | 8.4 | データベース |
| Docker Compose | 2.27 | コンテナ管理 |
| PHPUnit | 10.x | 自動テスト |

---

### ⚙️ テスト用データベースの作成

```bash
# MySQLコンテナに接続
docker compose exec mysql bash

# MySQLシェルで下記を実行
mysql -u root -p
# パスワードは「root」と入力

# テスト用データベースを作成
CREATE DATABASE test_database;
```

---

### 🧩 マイグレーションとテスト実行

```bash
# PHPコンテナに接続
docker compose exec php bash

# テスト環境（.env.testing）に基づきマイグレーション
php artisan migrate:fresh --env=testing

# PHPUnitによる全テスト実行
./vendor/bin/phpunit
```

---

### 👥 テストユーザー情報

| 区分 | メールアドレス | パスワード |
|------|----------------|------------|
| 管理者 | admin@coachtech.com | pass |
| 一般ユーザー | reina.n@coachtech.com | password |

> ※ 上記アカウントは初期シーディング時に自動作成されます。  
> `database/seeders/UserSeeder.php` 内で定義内容を確認できます。

---

### 🧾 備考

- テスト環境の設定は `.env.testing` に記載されています。  
- `APP_ENV=testing` を指定して実行すると、本番DBに影響を与えません。  
- Feature / Unit テストは `tests/Feature` および `tests/Unit` ディレクトリに配置。  
- CI/CD での自動テストにも対応可能（例：GitHub Actions を利用）。
