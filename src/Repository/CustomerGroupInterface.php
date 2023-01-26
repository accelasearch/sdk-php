<?php
namespace AccelaSearch\ProductMapper\Repository;
use \AccelaSearch\ProductMapper\Price\CustomerGroup as Subject;

interface CustomerGroupInterface {
    public function insert(Subject $group): self;
    public function read(int $identifier): Subject;
    public function update(Subject $group): self;
    public function delete(Subject $group): self;
    public function search(callable $criterion): array;
}