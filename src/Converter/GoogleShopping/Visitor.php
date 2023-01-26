<?php
namespace AccelaSearch\ProductMapper\Converter\GoogleShopping;

use AccelaSearch\ProductMapper\Banner;
use AccelaSearch\ProductMapper\Bundle;
use AccelaSearch\ProductMapper\CategoryPage;
use AccelaSearch\ProductMapper\Configurable;
use AccelaSearch\ProductMapper\Downloadable;
use AccelaSearch\ProductMapper\Grouped;
use AccelaSearch\ProductMapper\Page;
use AccelaSearch\ProductMapper\Simple;
use AccelaSearch\ProductMapper\Virtual;
use AccelaSearch\ProductMapper\VisitorInterface;

// @todo This class is a stub
class Visitor implements VisitorInterface {
    private $warehouse;
    private $quantity;

    public function __construct(
        WarehouseVisitor $warehouse,
        QuantityVisitor $quantity
    ) {
        $this->warehouse = $warehouse;
        $this->quantity = $quantity;
    }

    public static function fromDefault(): self {
        return new Visitor(new WarehouseVisitor(), new QuantityVisitor());
    }

    public function getWarehouse(): WarehouseVisitor {
        return $this->warehouse;
    }

    public function getQuantity(): QuantityVisitor {
        return $this->quantity;
    }

    public function visitBanner(Banner $item) {
    }

    public function visitPage(Page $item) {
    }

    public function visitCategoryPage(CategoryPage $item) {
    }

    public function visitSimple(Simple $item) {
    }

    public function visitVirtual(Virtual $item) {
    }

    public function visitDownloadable(Downloadable $item) {
    }

    public function visitConfigurable(Configurable $item) {
    }

    public function visitBundle(Bundle $item) {
    }

    public function visitGrouped(Grouped $item) {
    }
}