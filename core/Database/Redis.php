<?php

namespace Core\Database;

use Predis\Client;
use Predis\Connection\ConnectionException;
use Predis\PredisException;

class Redis {
    // Armazena a instância única da classe
    private static ?Redis $instance = null;

    // Armazena a conexão real do Predis
    private ?Client $client = null;

    // Construtor privado impede a criação de instâncias via "new" fora da classe
    private function __construct() {
        try {
            $this->client = new Client(
                'rediss://default:gQAAAAAABNQoAAIgcDI3ZjQ3MTAxMTZlMjY0M2VjYTMwNjVmNTNlNzY5Njc2OQ@divine-gecko-316456.upstash.io:6379',
            );

            // Força a conexão imediata para validar se o servidor está online
            $this->client->connect();
        } catch (ConnectionException $e) {
            error_log('Redis falhou ao conectar: ' . $e->getMessage());
            $this->client = null; // Garante que fica nulo se falhar
        } catch (PredisException $e) {
            error_log('Erro de configuração do Predis: ' . $e->getMessage());
            $this->client = null;
        }
    }

    // Impede a clonagem da instância
    private function __clone() {}

    // Impede a desserialização da instância
    public function __wakeup() {}

    // Método estático para obter a instância única da classe
    public static function getInstance(): Redis {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Retorna o cliente Predis para você rodar os comandos (sadd, get, set, etc.)
    public function getClient(): ?Client {
        return $this->client;
    }
}
