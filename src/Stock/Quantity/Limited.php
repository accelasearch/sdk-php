<?php
namespace AccelaSearch\ProductMapper\Stock\Quantity;

class Limited implements QuantityInterface {
    private $quantity;

    public function __construct(float $quantity) {
        $this->quantity = $quantity;
    }

    public function getQuantity(): float {
        return $this->quantity;
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitLimited($this);
    }
}