<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \AccelaSearch\ProductMapper\VisitorInterface;
use \AccelaSearch\ProductMapper\Banner;
use \AccelaSearch\ProductMapper\Page;
use \AccelaSearch\ProductMapper\CategoryPage;
use \AccelaSearch\ProductMapper\Simple;
use \AccelaSearch\ProductMapper\Virtual;
use \AccelaSearch\ProductMapper\Downloadable;
use \AccelaSearch\ProductMapper\Configurable;
use \AccelaSearch\ProductMapper\Bundle;
use \AccelaSearch\ProductMapper\Grouped;

class ItemGetChildrenVisitor implements VisitorInterface {
    public function visitBanner(Banner $item) {
        return [];
    }

    public function visitPage(Page $item) {
        return [];
    }

    public function visitCategoryPage(CategoryPage $item) {
        return [];
    }

    public function visitSimple(Simple $item) {
        return [];
    }

    public function visitVirtual(Virtual $item) {
        return [];
    }

    public function visitDownloadable(Downloadable $item) {
        return [];
    }

    public function visitConfigurable(Configurable $item) {
        return $item->getVariantsAsArray();
    }

    public function visitBundle(Bundle $item) {
        return $item->getProductsAsArray();
    }

    public function visitGrouped(Grouped $item) {
        return $item->getProductsAsArray();
    }
}