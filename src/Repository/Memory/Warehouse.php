<?php
namespace AccelaSearch\ProductMapper\Repository\Memory;
use \OutOfBoundsException;
use \BadFunctionCallException;
use \AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface as Subject;
use \AccelaSearch\ProductMapper\Repository\WarehouseInterface;

class Warehouse implements WarehouseInterface {
    private $warehouses;

    public function __construct() {
        $this->warehouses = [];
    }

    public function insert(Subject $warehouse): self {
        if (is_null($warehouse->getIdentifier())) {
            throw new BadFunctionCallException('Missing identifier');
        }
        $this->warehouses[$warehouse->getIdentifier()] = $warehouse;
        return $this;
    }

    public function read(int $identifier): Subject {
        if (!isset($this->warehouses[$identifier])) {
            throw new OutOfBoundsException('No warehouse with identifier ' . $identifier);
        }
        return $this->warehouses[$identifier];
    }

    public function update(Subject $warehouse): self {
        return $this->insert($warehouse);
    }

    public function delete(Subject $warehouse): self {
        unset($this->warehouses[$warehouse->getIdentifier()]);
        return $this;
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->warehouses, $criterion));
    }
}
