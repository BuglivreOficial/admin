<?php

namespace Core;

class Logger {
    private string $service;

    public function __construct(string $service) {
        $this->service = $service;
    }
    public function debug(string $message, array $context = []) {
        $this->create('debug', $message, $context);
    }
    public function info(string $message, array $context = []) {
        $this->create('infor', $message, $context);
    }
    public function notice(string $message, array $context = []) {
        $this->create('notice', $message, $context);
    }
    public function warning(string $message, array $context = []) {
        $this->create('warning', $message, $context);
    }
    public function error(string $message, array $context = []) {
        $this->create('error', $message, $context);
    }
    public function critical(string $message, array $context = []) {
        $this->create('critical', $message, $context);
    }
    public function alert(string $message, array $context = []) {
        $this->create('alert', $message, $context);
    }
    public function emergency(string $message, array $context = []) {
        $this->create('emergency', $message, $context);
    }
    private function create(string $level, string $message, array $context) {
        $traceId = $_SERVER['HTTP_X_TRACE_ID'] ?? uniqid('trace_', true);
        $ip = \Core\Request::get_cloudflare_ip();
        $logData = [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'), // Padrão ISO 8601 em UTC
            'level' => strtoupper($level),
            'service' => $this->service,
            'trace_id' => $traceId,
            'ip' => $ip,
            'message' => $message,
        ];
        // Adiciona metadados se existirem
        if (!empty($context)) {
            $logData['metadata'] = $context;
        }
        dump($logData);
    }
}
