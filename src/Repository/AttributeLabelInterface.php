<?php
namespace AccelaSearch\ProductMapper\Repository;
use \AccelaSearch\ProductMapper\AttributeLabel as Subject;

interface AttributeLabelInterface {
    public function read(int $identifier): Subject;
    public function search(callable $criterion): array;
}
