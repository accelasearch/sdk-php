<?php
namespace AccelaSearch\ProductMapper\Price;

class CustomerGroup {
    public const DEFAULT_LABEL = 'default';
    private $identifier;
    private $label;

    public function __construct(string $label) {
        $this->identifier = null;
        $this->label = $label;
    }

    public static function fromDefault(): self {
        return new CustomerGroup(self::DEFAULT_LABEL);
    }

    public function getIdentifier(): ?int {
        return $this->identifier;
    }

    public function setIdentifier(int $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function setLabel(string $label): self {
        $this->label = $label;
        return $this;
    }
}