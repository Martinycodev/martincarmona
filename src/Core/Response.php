<?php

namespace App\Core;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public function json(mixed $data, int $status = 200): string
    {
        $this->setStatusCode($status);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
