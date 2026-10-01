<?php
namespace Core;

class Response {
    public function json(array $data, int $status_code = 200) {
        header('Content-Type: application/json');
        header('X-Request-Id: ' . REQUEST_ID);
        $data['metadata'] = [
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'), // Padrão ISO 8601 em UTC,
            'request_id' => REQUEST_ID,
        ];
        http_response_code($status_code);
        echo json_encode($data);
        exit();
    }
    public function error_interno() {
        $this->json(
            [
                'status' => false,
                'message' =>
                    'Ops! Ocorreu um erro interno no nosso servidor. Por favor, tente novamente daqui a pouco ou volte mais tarde. Obrigado pela paciência!',
            ],
            404,
        );
    }
}
