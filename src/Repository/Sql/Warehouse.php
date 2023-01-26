<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface as Subject;
use \AccelaSearch\ProductMapper\Repository\WarehouseInterface;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Warehouse as DataMapper;

class Warehouse {
    private $mapper;
    private $warehouses;

    public function __construct(DataMapper $mapper) {
        $this->mapper = $mapper;
        $this->warehouses = [];
        foreach ($mapper->search() as $warehouse) {
            $this->warehouses[$warehouse->getIdentifier()] = $warehouse;
        }
    }

    public static function fromConnection(Connection $connection): self {
        return new Warehouse(DataMapper::fromConnection($connection));
    }

    public function insert(Subject $warehouse): self {
        $this->mapper->create($warehouse);
        $this->warehouses[$warehouse->getIdentifier()] = $warehouse;
        return $this;
    }

    public function read(int $identifier): Subject {
        if (!isset($this->warehouses[$identifier])) {
            $this->warehouses[$identifier] = $this->mapper->read($identifier);
        }
        return $this->warehouses[$identifier];
    }

    public function update(Subject $warehouse): self {
        $this->mapper->update($warehouse);
        $this->warehouses[$warehouse->getIdentifier()] = $warehouse;
        return $this;
    }

    public function delete(Subject $warehouse): self {
        $this->mapper->softDelete($warehouse);
        unset($this->warehouses[$warehouse->getIdentifier()]);
        return $this;
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->warehouses, $criterion));
    }
}