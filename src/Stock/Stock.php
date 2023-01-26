<?php
namespace AccelaSearch\ProductMapper\Stock;
use \AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Virtual;
use \AccelaSearch\ProductMapper\Stock\Quantity\QuantityInterface;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited;

class Stock {
    private $warehouse;
    private $quantity;

    public function __construct(
        WarehouseInterface $warehouse,
        QuantityInterface $quantity
    ) {
        $this->warehouse = $warehouse;
        $this->quantity = $quantity;
    }

    public static function fromQuantity(float $quantity): self {
        return new Stock(Virtual::fromDefault(), new Limited($quantity));
    }

    public function getWarehouse(): WarehouseInterface {
        return $this->warehouse;
    }

    public function getQuantity(): QuantityInterface {
        return $this->quantity;
    }
}