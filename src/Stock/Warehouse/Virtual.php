<?php
namespace AccelaSearch\ProductMapper\Stock\Warehouse;

class Virtual implements WarehouseInterface {
    use WarehouseTrait;
    public const DEFAULT_LABEL = 'default';

    public function __construct(string $label) {
        $this->label = $label;
    }

    public static function fromDefault(): self {
        return new Virtual(self::DEFAULT_LABEL);
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitVirtual($this);
    }
}