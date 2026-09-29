<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/config/app.php';

// ====== CAPTURA DE IP E GRAVAÇÃO NO REDIS ======
try {
    $ip = \Core\Request::get_cloudflare_ip();
    $redis = \Core\Database\Redis::getInstance()->getClient();
    // Se a conexão foi bem-sucedida
    if ($redis !== null) {
        $redis->sadd('site:visitantes_' . date('d'), $ip);
        $redis->expire('site:visitantes_recentes', 86400); // 24 horas
    }
} catch (\Throwable $e) {
    error_log('Falha silenciosa ao registrar IP no Redis: ' . $e->getMessage());
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
} catch (\Core\Exceptions\RoutingException $e) {
    switch ($e->getCode()) {
        case 404:
            response(false, 'Rota não existe', $e->getCode());
            break;
        case 405:
            response(false, 'Rota não existe', $e->getCode());
            break;
        default:
            response(false, 'Error! tente novamente mais tarde!', 500);
            break;
    }
} catch (\Exception $e) {
    response(false, 'Error! tente novamente mais tarde!', 500);
}

function response(bool $status, string $message, int $status_code) {
    (new \Core\Response())->json(
        [
            'status' => $status,
            'message' => $message,
            'created_at' => date('d/m/Y H:i:s'),
        ],
        $status_code,
    );
}
