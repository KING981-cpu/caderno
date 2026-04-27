<?php

namespace App\Core;

class BaseController
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function response($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function success($data = [], int $statusCode = 200): void
    {
        $this->response(['success' => true, 'data' => $data], $statusCode);
    }

    protected function error(string $message, int $statusCode = 400): void
    {
        $this->response(['success' => false, 'error' => $message], $statusCode);
    }

    protected function json($data, int $statusCode = 200): void
    {
        $this->response($data, $statusCode);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data);
        $templatePath = dirname(__DIR__, 2) . '/resources/views/' . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new \Exception("View not found: {$template}");
        }

        include $templatePath;
    }
}
