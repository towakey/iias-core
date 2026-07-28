# iias-core

IIAS（Integrated Intelligence Archive System）のバックエンド API。

## 概要

Laravel で構築された REST API。個人用の全自動ロギング・マネジメントシステム「IIAS」の中核。

## 技術スタック

- PHP / Laravel
- MySQL
- さくらのレンタルサーバー スタンダードプラン

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
  Http/Controllers  # コントローラー
  Models            # Eloquent モデル
bootstrap/        # ブートストラップ
config/           # 設定ファイル
database/         # マイグレーション・シーダー
routes/           # ルート定義
resources/        # リソース
storage/          # ログ・キャッシュ・セッション
  app/            # アップロードファイル等
  framework/      # フレームワーク用キャッシュ
  logs/           # ログ
tests/            # テスト
```

## 主な機能

- ユーザー認証（パスワード認証）
- 閲覧履歴・メモ・アーカイブの保存・検索
- 買い物リストの管理
- 自動タグ付け（ページタイトルキーワード）
- SQLite クライアントとの双方向同期
- 全文検索 API
