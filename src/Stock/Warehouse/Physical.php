<?php
namespace AccelaSearch\ProductMapper\Stock\Warehouse;

class Physical implements WarehouseInterface {
    use WarehouseTrait;
    private $latitude;
    private $longitude;

    public function __construct(
        string $label,
        float $latitude,
        float $longitude
    ) {
        $this->label = $label;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitPhysical($this);
    }
}