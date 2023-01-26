<?php
namespace AccelaSearch\ProductMapper\Api;

class Response {
    private $body;
    private $headers;

    public function __construct(
        array $body,
        array $headers
    ) {
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getBodyAsArray(): array {
        return $this->body;
    }

    public function getHeadersAsArray(): array {
        return $this->headers;
    }
}