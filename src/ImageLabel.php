<?php
namespace AccelaSearch\ProductMapper;

class ImageLabel {
    private $identifier;
    private $label;
    private $is_active;

    public function __construct(
        ?int $identifier,
        string $label,
        bool $is_active
    ) {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->is_active = $is_active;
    }

    public function getIdentifier(): ?int {
        return $this->identifier;
    }

    public function setIdentifier(?int $identifier): self {
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

    public function isActive(): bool {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self {
        $this->is_active = $is_active;
        return $this;
    }
}