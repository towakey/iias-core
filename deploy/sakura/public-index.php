<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

// ソースを www 外 (/home/ユーザー名/iias-core) に配置する場合のエントリーポイント例
// このファイルを /home/ユーザー名/www/iias-core/index.php へ配置してください

require __DIR__ . '/../../iias-core/vendor/autoload.php';

$app = require_once __DIR__ . '/../../iias-core/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request)
    ->send();

$kernel->terminate($request, $response);
