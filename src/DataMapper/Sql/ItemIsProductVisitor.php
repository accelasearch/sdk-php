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

class ItemIsProductVisitor implements VisitorInterface {
    public function visitBanner(Banner $item) {
        return false;
    }

    public function visitPage(Page $item) {
        return false;
    }

    public function visitCategoryPage(CategoryPage $item) {
        return false;
    }

    public function visitSimple(Simple $item) {
        return true;
    }

    public function visitVirtual(Virtual $item) {
        return true;
    }

    public function visitDownloadable(Downloadable $item) {
        return true;
    }

    public function visitConfigurable(Configurable $item) {
        return true;
    }

    public function visitBundle(Bundle $item) {
        return true;
    }

    public function visitGrouped(Grouped $item) {
        return true;
    }
}