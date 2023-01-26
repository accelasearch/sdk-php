<?php
namespace AccelaSearch\ProductMapper\Converter\Dictionary;
use \AccelaSearch\ProductMapper\Stock\Warehouse\VisitorInterface;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Physical;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Virtual;

class WarehouseVisitor implements VisitorInterface {
    public function visitPhysical(Physical $warehouse) {
        return [
            'type' => 'physical',
            'id' => $warehouse->getIdentifier(),
            'label' => $warehouse->getLabel(),
            'latitude' => $warehouse->getLatitude(),
            'longitude' => $warehouse->getLongitude()
        ];
    }

    public function visitVirtual(Virtual $warehouse) {
        return [
            'type' => 'virtual',
            'id' => $warehouse->getIdentifier(),
            'label' => $warehouse->getLabel()
        ];
    }
}