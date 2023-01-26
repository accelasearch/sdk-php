<?php
namespace AccelaSearch\ProductMapper\Api;

class Request {
    private $path;
    private $parameters;
    private $headers;

    public function __construct(
        string $path,
        array $parameters,
        array $headers
    ) {
        $this->path = $path;
        $this->parameters = $parameters;
        $this->headers = $headers;
    }

    public static function fromPath(string $path): self {
        return new Request($path, [], []);
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getParametersAsArray(): array {
        return $this->parameters;
    }

    public function addParameter(string $name, $value): self {
        $this->parameters[$name] = $value;
        return $this;
    }

    public function getHeadersAsArray(): array {
        return $this->headers;
    }

    public function addHeader(string $name, string $value): self {
        $this->headers[$name] = $value;
        return $this;
    }
}