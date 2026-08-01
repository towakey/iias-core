<?php

namespace App\Http\Controllers;

use Dotenv\Dotenv;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $writable = $this->checkWritable();

        return view('install', compact('writable'));
    }

    public function setup(Request $request)
    {
        if ($this->isInstalled()) {
            return response()->json(['message' => 'Already installed.'], 403);
        }

        $validated = $request->validate([
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer',
            'db_database' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
            'app_url' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8|max:255',
        ]);

        try {
            $pdo = new PDO(
                "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}",
                $validated['db_username'],
                $validated['db_password'] ?? ''
            );
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['db' => 'DB 接続に失敗しました: '.$e->getMessage()]);
        }

        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                file_put_contents($envPath, $this->envTemplate());
            }
        }

        $this->writeEnv($envPath, [
            'APP_NAME' => 'IIAS',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($validated['app_url'], '/'),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $validated['db_host'],
            'DB_PORT' => $validated['db_port'],
            'DB_DATABASE' => $validated['db_database'],
            'DB_USERNAME' => $validated['db_username'],
            'DB_PASSWORD' => $validated['db_password'] ?? '',
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
        ]);

        // 変更後の .env を読み込み直す
        Dotenv::createImmutable(base_path())->load();

        try {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['setup' => 'セットアップコマンドに失敗しました: '.$e->getMessage()]);
        }

        // 管理者ユーザーを作成
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/')->with('success', 'インストールが完了しました。ログイン画面からログインしてください。');
    }

    private function isInstalled(): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return false;
        }

        $key = env('APP_KEY');

        return ! empty($key);
    }

    private function checkWritable(): array
    {
        return [
            'env' => is_writable(base_path()),
            'storage' => is_writable(storage_path()),
            'bootstrap_cache' => is_writable(base_path('bootstrap/cache')),
        ];
    }

    private function writeEnv(string $path, array $values): void
    {
        $content = file_exists($path) ? file_get_contents($path) : $this->envTemplate();

        foreach ($values as $key => $value) {
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';
            $line = $key.'='.$this->escapeEnvValue($value);

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content .= PHP_EOL.$line;
            }
        }

        file_put_contents($path, $content);
    }

    private function escapeEnvValue(string $value): string
    {
        if (str_contains($value, ' ') || str_contains($value, '#')) {
            return '"'.$value.'"';
        }

        return $value;
    }

    private function envTemplate(): string
    {
        return <<<'ENV'
APP_NAME=IIAS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
ENV;
    }
}
