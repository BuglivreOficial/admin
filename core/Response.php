<?php
namespace Core;

class Response {
    public function json(array $data, int $status_code = 200) {
        header('Content-Type: application/json');
        http_response_code($status_code);
        echo json_encode($data);
        exit();
    }
}
