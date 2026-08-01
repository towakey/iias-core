<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IIAS Setup</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #050505;
            color: #ff8a1c;
            font-family: 'Segoe UI', 'Noto Sans JP', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            width: 100%;
            max-width: 540px;
            border: 1px solid #ff8a1c;
            background: #0a0a0a;
            padding: 2rem;
        }
        h1 {
            margin: 0 0 1rem;
            font-size: 1.5rem;
            letter-spacing: 0.05em;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
        input, button {
            width: 100%;
            margin-top: 0.25rem;
            padding: 0.6rem;
            border: 1px solid #ff8a1c;
            background: #050505;
            color: #ff8a1c;
            font-size: 1rem;
        }
        input:focus {
            outline: 2px solid #ff8a1c;
        }
        button {
            margin-top: 1.5rem;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #ff8a1c;
            color: #050505;
        }
        .error {
            color: #ff3333;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        .warning {
            color: #ffcc00;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        .success {
            color: #33ff33;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        .note {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>IIAS セットアップ</h1>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if (! $writable['env'] || ! $writable['storage'] || ! $writable['bootstrap_cache'])
            <div class="warning">
                以下のディレクトリ/ファイルが書き込み不可です。パーミッションを 755 または 777 に設定してください。<br>
                .env 配置ディレクトリ: {{ $writable['env'] ? 'OK' : 'NG' }}<br>
                storage/: {{ $writable['storage'] ? 'OK' : 'NG' }}<br>
                bootstrap/cache/: {{ $writable['bootstrap_cache'] ? 'OK' : 'NG' }}
            </div>
        @endif

        <form method="POST" action="/install">
            @csrf

            <h2 style="font-size:1rem; margin-top:1.5rem;">データベース設定</h2>
            <label for="db_host">DB ホスト</label>
            <input type="text" id="db_host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>

            <label for="db_port">DB ポート</label>
            <input type="number" id="db_port" name="db_port" value="{{ old('db_port', '3306') }}" required>

            <label for="db_database">データベース名</label>
            <input type="text" id="db_database" name="db_database" value="{{ old('db_database') }}" required>

            <label for="db_username">DB ユーザー名</label>
            <input type="text" id="db_username" name="db_username" value="{{ old('db_username') }}" required>

            <label for="db_password">DB パスワード</label>
            <input type="password" id="db_password" name="db_password" value="{{ old('db_password') }}">

            <h2 style="font-size:1rem; margin-top:1.5rem;">アプリケーション設定</h2>
            <label for="app_url">アプリ URL（末尾の / は不要）</label>
            <input type="url" id="app_url" name="app_url" value="{{ old('app_url', 'https://kyosserver.sakura.ne.jp/iias-core') }}" required>

            <h2 style="font-size:1rem; margin-top:1.5rem;">管理者アカウント</h2>
            <label for="admin_email">管理者メールアドレス</label>
            <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>

            <label for="admin_password">管理者パスワード（8文字以上）</label>
            <input type="password" id="admin_password" name="admin_password" minlength="8" required>

            <button type="submit">セットアップを実行</button>
        </form>

        <div class="note">
            セットアップが完了すると .env が生成され、データベースマイグレーションが実行されます。
        </div>
    </div>
</body>
</html>
