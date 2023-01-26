<?php
namespace AccelaSearch\ProductMapper\Repository;
use \AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface as Subject;

interface WarehouseInterface {
    public function insert(Subject $warehouse): self;
    public function read(int $identifier): Subject;
    public function update(Subject $warehouse): self;
    public function delete(Subject $warehouse): self;
    public function search(callable $criterion): array;
}