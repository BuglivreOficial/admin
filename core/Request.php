<?php
namespace Core;

class Request {
    public function body(?string $key = null) {}
    public function get_cloudflare_ip() {
        // 1. Verifica se o cabeçalho oficial da Cloudflare está presente
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // 2. Backup caso a requisição não passe pela Cloudflare (ambiente local, por exemplo)
        else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        // 3. Valida se o IP é válido (IPv4 ou IPv6)
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return '0.0.0.0';
    }
}
