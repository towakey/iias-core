# さくらレンタルサーバー サブディレクトリ運用

## 想定 URL

- API: `https://kyosserver.sakura.ne.jp/iias-core/api/...`
- Web: `https://kyosserver.sakura.ne.jp/iias-web/`

## ディレクトリ構成（推奨）

```
/home/kyosserver/
├── www/
│   ├── iias-core/       # iias-core リポジトリを clone
│   │   ├── .htaccess    # public/ へ rewrite
│   │   ├── public/      # Laravel 公開ディレクトリ
│   │   └── ...
│   └── iias-web/        # Nuxt 静的ビルド成果物（GitHub Actions から転送）
│       ├── index.html
│       ├── .htaccess
│       └── _nuxt/
```

## 初回セットアップ

```bash
cd /home/kyosserver/www
git clone https://github.com/towakey/iias-core.git iias-core
cd iias-core
cp .env.example .env
vi .env   # DB・APP_URL 等を本番用に編集
bash deploy/sakura/setup.sh
```

## 自動デプロイ

GitHub Actions を使う場合、以下の Secrets / Vars を設定します。

### Secrets

- `SAKURA_HOST`：サーバー名（例 `kyosserver.sakura.ne.jp`）
- `SAKURA_USER`：SSH ユーザー名
- `SAKURA_SSH_KEY`：SSH 秘密鍵
- `SAKURA_PORT`：SSH ポート（デフォルト 22）
- `SAKURA_APP_DIR`：`/home/ユーザー名/www/iias-core`

### Variables

- `SAKURA_API_BASE_URL`：`https://kyosserver.sakura.ne.jp/iias-core/api`
- `SAKURA_WEB_BASE_URL`：`/iias-web/`
- `SAKURA_WEB_DIR`：`/home/ユーザー名/www/iias-web/`
