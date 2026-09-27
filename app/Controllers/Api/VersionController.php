<?php
namespace App\Controllers\Api;

use Core\Request;

class VersionController {
    public function get() {
        dump((new Request())->get_cloudflare_ip());
    }
    public function post() {}
    public function put() {}
    public function delete() {}
}
