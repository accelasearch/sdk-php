<?php
namespace AccelaSearch\ProductMapper;

class AttributeLabel {
    private $identifier;
    private $label;
    private $is_active;
    
    public function __construct(
        int $identifier,
        string $label,
        bool $is_active
    ) {
        $this->identifier = $identifier;
        $this->label = $label;
        $this->is_active = $is_active;
    }

    public function getIdentifier(): int {
        return $this->identifier;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function isActive(): bool {
        return $this->is_active;
    }
}
