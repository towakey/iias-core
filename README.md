# iias-core

IIAS（Integrated Intelligence Archive System）のバックエンド API。

## 概要

Laravel で構築された REST API。個人用の全自動ロギング・マネジメントシステム「IIAS」の中核。

## 技術スタック

- PHP / Laravel
- MySQL（本番：さくらのレンタルサーバー）
- SQLite（ローカル開発用）

## セットアップ

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

## ディレクトリ構成

```
app/              # アプリケーションロジック
  Http/           # コントローラー・ミドルウェア
  Models/         # Eloquent モデル
bootstrap/        # ブートストラップ
config/           # 設定ファイル
database/         # マイグレーション・シーダー・ファクトリー
public/           # 公開ディレクトリ
resources/        # ビュー・JS・CSS
routes/           # ルート定義
storage/          # ログ・キャッシュ・セッション
tests/            # テスト
vendor/           # Composer 依存
```

## 主な機能

- ユーザー認証（パスワード認証）
- 閲覧履歴・メモ・アーカイブの保存・検索
- 購買リストの管理
- 自動タグ付け（ページタイトルキーワード）
- SQLite クライアントとの双方向同期
- 全文検索 API
