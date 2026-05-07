<?php

namespace App\Core;

class Request
{
    private array $data = [];
    private string $method;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($this->method === 'GET') {
            $this->data = $_GET;
        } elseif ($this->method === 'POST') {
            $this->data = $_POST;
        } else {
            $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    public function get(string $key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    public function isPatch(): bool
    {
        return $this->method === 'PATCH';
    }

    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }
}
