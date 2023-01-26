<?php
namespace AccelaSearch\ProductMapper;

class Category {
    public const DEFAULT_SEPARATOR = '/';
    private $identifier;
    private $external_identifier;
    private $parent;
    private $name;
    private $full_name;
    private $url;

    public function __construct(
        string $external_identifier,
        string $name,
        ?Category $parent
    ) {
        $this->identifier = null;
        $this->external_identifier = $external_identifier;
        $this->parent = $parent;
        $this->name = $name;
        $this->full_name = !is_null($parent)
            ? $parent->getFullName() . self::DEFAULT_SEPARATOR . $name
            : $name;
        $this->url = null;
    }

    public static function fromName(string $name): self {
        return new Category($name, $name, null);
    }

    public static function fromNameAndParent(string $name, Category $parent): self {
        return new Category($name, $name, $parent);
    }

    public function getIdentifier(): ?int {
        return $this->identifier;
    }

    public function setIdentifier(int $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }

    public function getExternalIdentifier(): string {
        return $this->external_identifier;
    }

    public function setExternalIdentifier(string $identifier): self {
        $this->external_identifier = $identifier;
        return $this;
    }

    public function getParent(): ?Category {
        return $this->parent;
    }

    public function setParent(?Category $parent): self {
        $this->parent = $parent;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getFullName(): string {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self {
        $this->full_name = $full_name;
        return $this;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }
}