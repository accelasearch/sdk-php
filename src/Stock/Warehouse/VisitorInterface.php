<?php
namespace AccelaSearch\ProductMapper\Stock\Warehouse;

interface VisitorInterface {
    public function visitPhysical(Physical $warehouse);
    public function visitVirtual(Virtual $virtual);
}