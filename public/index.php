<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/app.php';

//dump($_SERVER['SERVER_NAME']);
$logRedis = new \Core\Logger('redis');

// ====== CAPTURA DE IP E GRAVAÇÃO NO REDIS ======
try {
    $redis = \Core\Database\Redi::getInstance()->getClient();
    // Se a conexão foi bem-sucedida
    if ($redis !== null) {
        $redis->sadd('site:visitantes_' . date('d'), IP);
        $redis->expire('site:visitantes_recentes', 86400); // 24 horas
    }
} catch (\Throwable $e) {
    $logRedis->critical('Falha ao registrar IP no Redis', [
        'line' => '9 a 21',
        'file' => '/public/index.php',
        'error' => $e->getMessage(),
        'exception' => $e,
    ]);
}

try {
    //====== SISTEMA DE ROTEAMENTO ======
    $router = new \Core\Routing();
    $router->group('/api', function ($router) {
        require dirname(__DIR__) . '/router/api.php';
    });
    $router->group('/app', function ($router) {
        require dirname(__DIR__) . '/router/web.php';
    });
    $router->start();
} catch (\Exception $e) {
    response(false, 'Error! tente novamente mais tarde!', 500);
}

function response(bool $status, string $message, int $status_code) {
    (new \Core\Response())->json(
        [
            'status' => $status,
            'message' => $message,
        ],
        $status_code,
    );
}

function generateUuidV4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // Define a versão como 4
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // Define a variante
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
