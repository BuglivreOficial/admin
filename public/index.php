<?php

require dirname(__DIR__) . '/vendor/autoload.php';

// ====== CAPTURA DE IP E GRAVAÇÃO NO REDIS (BLINDADO) ======
try {
    $ip = (new Request())->get_cloudflare_ip();

    if (filter_var($ip, FILTER_VALIDATE_IP) && $ip !== '0.0.0.0') {
        // Recupera a instância Singleton do Redis
        $redisInstance = \Core\Database\Redis::getInstance();
        $redis = $redisInstance->getClient();

        // Se a conexão foi bem-sucedida, envia os comandos
        if ($redis !== null) {
            $redis->sadd('site:visitantes_recentes', $ip);
            $redis->expire('site:visitantes_recentes', 86400); // 24 horas
        }
    }
} catch (\Throwable $e) {
    // Captura qualquer erro do Redis/Predis e evita o crash da API
    // Escreve apenas no arquivo de log padrão do PHP (ex: error_log do Nginx/Apache)
    error_log('Falha silenciosa ao registrar IP no Redis: ' . $e->getMessage());
}

try {
    //------ Sistema de roteamento ------//
    $router = new \Core\Routing();
    $router->group('/api', function ($router) {
        require dirname(__DIR__) . '/router/api.php';
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
