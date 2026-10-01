<?php
namespace Core;

use Core\Exceptions\RoutingException;
use Core\Logger;
use Core\Response;

class Routing {
    private string $prefix = '';
    private array $routes = [];
    private Logger $logger;
    private Response $response;

    public function __construct() {
        $this->logger = new Logger('routing');
        $this->response = new Response();
    }
    public function get(string $path, callable|array $callback): void {
        $this->match('GET', $path, $callback);
    }
    public function post(string $path, callable|array $callback): void {
        $this->match('POST', $path, $callback);
    }
    public function put(string $path, callable|array $callback): void {
        $this->match('PUT', $path, $callback);
    }
    public function delete(string $path, callable|array $callback): void {
        $this->match('DELETE', $path, $callback);
    }
    public function group(string $prefix, callable $callback): void {
        $oldPrefix = $this->prefix;
        $this->prefix = '/' . trim($prefix, '/');
        $callback($this);
        $this->prefix = $oldPrefix;
    }
    private function match(string $method, string $path, callable|array $callback): void {
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        if (isset($this->routes[$fullPath][$method])) {
            throw new RoutingException("Rota {$fullPath} duplicada.", 001);
        }
        if (is_callable($callback)) {
            $callable = $callback;
            $controller = null;
            $function = null;
        }
        if (is_array($callback)) {
            $controller = $callback[0];
            $function = $callback[1];
            $callable = null;
            if (!class_exists($controller)) {
                $this->logger->critical(
                    "A classe ```$controller``` da rota ```$fullPath``` não existe ou não foi declarada",
                );
                $this->response->error_interno();
                exit();
            }
            if (!method_exists($controller, $function)) {
                $this->logger->critical(
                    "A classe ```$controller``` da rota ```$fullPath``` existe mais o método ```$function``` não existe",
                );
                $this->response->error_interno();
                exit();
            }
        }
        $this->routes[$fullPath][$method] = [
            'controller' => $controller,
            'function' => $function,
            'callable' => $callable,
        ];
    }
    public function start(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/CLI';
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!isset($this->routes[$uri])) {
            $this->logger->info("A rota ```$uri``` não existe");
            $this->response->json(
                [
                    'status' => false,
                    'message' => 'A rota não existe.',
                ],
                404,
            );
        }
        if (!isset($this->routes[$uri][$httpMethod])) {
            $this->logger->info("Rota ```$uri``` existe mais método ```$httpMethod``` não aceito");
            $this->response->json(
                [
                    'status' => false,
                    'message' => 'Rota existe mais método não aceito.',
                ],
                403,
            );
        }

        extract($this->routes[$uri][$httpMethod]);

        if (!is_null($callable)) {
            call_user_func($callable);
            exit();
        }

        (new $controller())->$function();
    }
}
