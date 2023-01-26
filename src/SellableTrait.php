<?php
namespace AccelaSearch\ProductMapper;
use \AccelaSearch\ProductMapper\Price\Pricing;

trait SellableTrait {
    private $pricing;

    public function getPricing(): Pricing {
        return $this->pricing;
    }
}