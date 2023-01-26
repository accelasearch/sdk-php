<?php
namespace AccelaSearch\ProductMapper\Price;

class Pricing {
    private $prices;

    public function __construct() {
        $this->prices = [];
    }

    public static function fromListingPrice(float $price): self {
        $pricing = new Pricing();
        $pricing->add(Price::fromListingPrice($price));
        return $pricing;
    }

    public function asArray(): array {
        return $this->prices;
    }

    public function add(Price $price): self {
        $this->prices[] = $price;
        return $this;
    }

    public function clear(): self {
        $this->prices = [];
        return $this;
    }
}