<?php
namespace AccelaSearch\ProductMapper\Converter\Dictionary;
use \AccelaSearch\ProductMapper\Stock\Quantity\VisitorInterface;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited;
use \AccelaSearch\ProductMapper\Stock\Quantity\Unlimited;

class QuantityVisitor implements VisitorInterface {
    public function visitLimited(Limited $quantity) {
        return [
            'type' => 'limited',
            'quantity' => $quantity->getQuantity()
        ];
    }

    public function visitUnlimited(Unlimited $quantity) {
        return [
            'type' => 'unlimited'
        ];
    }
}