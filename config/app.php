<?php

date_default_timezone_set('America/Sao_Paulo');

define('REQUEST_ID', generateUuidV4());
define('IP', \Core\Request::get_cloudflare_ip());
define('METHOD_HTTP', $_SERVER['REQUEST_METHOD'] ?? 'CLI');
