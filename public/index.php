<?php

require dirname(__DIR__) . '/vendor/autoload.php';

try {
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
