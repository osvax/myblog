<?php

namespace App\Core;

class Response
{
    private $status;
    private $headers;
    private $body;

    public function __construct($body = '', $status = 200, array $headers = [])
    {
        $this->body = (string) $body;
        $this->status = (int) $status;
        $this->headers = $headers;
    }

    public static function json(array $payload, $status = 200)
    {
        return new self(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'no-store']
        );
    }

    public static function redirect($location, $status = 302)
    {
        return new self('', $status, ['Location' => $location, 'Cache-Control' => 'no-store']);
    }

    public function send()
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
