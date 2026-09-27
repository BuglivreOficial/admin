<?php

use App\Controllers\Api\VersionController;

$router->get('/version', [VersionController::class, 'get']);
$router->post('/version', [VersionController::class, 'post']);
$router->put('/version', [VersionController::class, 'put']);
$router->delete('/version', [VersionController::class, 'delete']);
