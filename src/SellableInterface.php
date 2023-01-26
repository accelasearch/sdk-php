<?php
namespace AccelaSearch\ProductMapper;
use \AccelaSearch\ProductMapper\Price\Pricing;

interface SellableInterface extends ItemInterface {
    public function getPricing(): Pricing;
}