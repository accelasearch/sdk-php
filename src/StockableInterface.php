<?php
namespace AccelaSearch\ProductMapper;
use \AccelaSearch\ProductMapper\Stock\Availability;

interface StockableInterface extends ItemInterface {
    public function getAvailability(): Availability;
}