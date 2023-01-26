<?php
namespace AccelaSearch\ProductMapper\Stock\Quantity;

class Unlimited implements QuantityInterface {
    public function accept(VisitorInterface $visitor) {
        return $visitor->visitUnlimited($this);
    }
}