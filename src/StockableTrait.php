<?php
namespace AccelaSearch\ProductMapper;
use \AccelaSearch\ProductMapper\Stock\Availability;

trait StockableTrait {
    private $availability;

    public function getAvailability(): Availability {
        return $this->availability;
    }
}