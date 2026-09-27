<?php
namespace Core;

use Core\Exceptions\RoutingException;

class Routing {
    private string $prefix = '';
    private array $routes = [];

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
                throw new RoutingException("Classe da rota {$fullPath} não existe.", 500);
            }
            if (!method_exists($controller, $function)) {
                throw new RoutingException("Método da classe na rota {$fullPath} não existe.", 500);
            }
        }
        $this->routes[$fullPath][$method] = [
            'controller' => $controller,
            'function' => $function,
            'callable' => $callable,
        ];
    }
    public function start(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/faaaaaAAAAAA';
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!isset($this->routes[$uri])) {
            throw new RoutingException(
                'O servidor não consegue encontrar o recurso solicitado.)',
                404,
            );
        }
        if (!isset($this->routes[$uri][$httpMethod])) {
            throw new RoutingException(
                'O método de requisição é conhecido pelo servidor, mas não é suportado pelo recurso de destino.',
                405,
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
