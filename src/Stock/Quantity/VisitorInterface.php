<?php
namespace AccelaSearch\ProductMapper\Stock\Quantity;

interface VisitorInterface {
    public function visitLimited(Limited $quantity);
    public function visitUnlimited(Unlimited $quantity);
}