#!/usr/bin/env bash
set -e

# さくらレンタルサーバー用セットアップスクリプト
# 実行前に .env を編集してください

APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"

echo "APP_DIR: $APP_DIR"

if [ ! -f "$APP_DIR/.env" ]; then
    echo "ERROR: $APP_DIR/.env が見つかりません。cp .env.example .env を実行してください"
    exit 1
fi

COMPOSER_BIN="$(which composer 2>/dev/null || true)"
if [ -z "$COMPOSER_BIN" ] && [ -f "$HOME/.config/composer/vendor/bin/composer" ]; then
    COMPOSER_BIN="$HOME/.config/composer/vendor/bin/composer"
fi
if [ -z "$COMPOSER_BIN" ] && [ -f "$HOME/.local/bin/composer" ]; then
    COMPOSER_BIN="$HOME/.local/bin/composer"
fi
if [ -z "$COMPOSER_BIN" ]; then
    echo "ERROR: composer が見つかりません"
    exit 1
fi

# storage シンボリックリンク
mkdir -p "$APP_DIR/storage/app/public"
if [ ! -L "$APP_DIR/public/storage" ]; then
    ln -s "$APP_DIR/storage/app/public" "$APP_DIR/public/storage"
fi

# 依存インストール
php "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

# APP_KEY 未設定なら生成
APP_KEY_VALUE="$(grep '^APP_KEY=' "$APP_DIR/.env" | sed 's/APP_KEY=//' | tr -d ' ')"
if [ -z "$APP_KEY_VALUE" ]; then
    php "$APP_DIR/artisan" key:generate
fi

# マイグレーション
php "$APP_DIR/artisan" migrate --force

# キャッシュ作成（本番用）
php "$APP_DIR/artisan" config:cache
php "$APP_DIR/artisan" route:cache

echo "setup done"
