<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \AccelaSearch\ProductMapper\Stock\Quantity\VisitorInterface;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited;
use \AccelaSearch\ProductMapper\Stock\Quantity\Unlimited;

class QuantityConverterVisitor implements VisitorInterface {
    public function visitLimited(Limited $quantity) {
        return [
            'is_unlimited' => false,
            'quantity' => $quantity->getQuantity()
        ];
    }

    public function visitUnlimited(Unlimited $quantity) {
        return [
            'is_unlimited' => true,
            'quantity' => 0
        ];
    }
}