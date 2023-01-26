<?php
namespace AccelaSearch\ProductMapper\Stock;

class Availability {
    private $stocks;

    public function __construct() {
        $this->stocks = [];
    }

    public static function fromQuantity(float $quantity): self {
        $availability = new Availability();
        $availability->add(Stock::fromQuantity($quantity));
        return $availability;
    }

    public function asArray(): array {
        return $this->stocks;
    }

    public function add(Stock $stock): self {
        $this->stocks[] = $stock;
        return $this;
    }

    public function clear(): self {
        $this->stocks = [];
        return $this;
    }
}