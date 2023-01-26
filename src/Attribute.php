<?php
namespace AccelaSearch\ProductMapper;

class Attribute {
    public const DEFAULT_IS_CONFIGURABLE = false;
    private $is_configurable;
    private $name;
    private $values;

    public function __construct(string $name) {
        $this->is_configurable = self::DEFAULT_IS_CONFIGURABLE;
        $this->name = $name;
        $this->values = [];
    }

    public static function fromNameAndValue(string $name, $value): self {
        $attribute = new Attribute($name);
        $attribute->addValue($value);
        return $attribute;
    }

    public function isConfigurable(): bool {
        return $this->is_configurable;
    }

    public function setIsConfigurable(bool $is_configurable): self {
        $this->is_configurable = $is_configurable;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getValuesAsArray(): array {
        return array_values($this->values);
    }

    public function addValue($value): self {
        $this->values[] = $value;
        return $this;
    }

    public function removeValue($value): self {
        $key = array_search($value, $this->values);
        if ($key !== false) {
            unset($this->values[$key]);
        }
        return $this;
    }

    public function clearValues(): self {
        $this->values = [];
        return $this;
    }

    public function merge(Attribute $other): self {
        foreach ($other->getValuesAsArray() as $value) {
            $this->addValue($value);
        }
        return $this;
    }
}