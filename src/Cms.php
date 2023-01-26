<?php
namespace AccelaSearch\ProductMapper;

class Cms {
    private $identifier;
    private $name;
    private $version;

    public function __construct(
        int $identifier,
        string $name,
        string $version
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->version = $version;
    }

    public function getIdentifier(): int {
        return $this->identifier;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getVersion(): string {
        return $this->version;
    }
}