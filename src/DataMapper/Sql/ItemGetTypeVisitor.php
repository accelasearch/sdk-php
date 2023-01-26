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

class ItemGetTypeVisitor implements VisitorInterface {
    public const BANNER = 90;
    public const PAGE = 91;
    public const CATEGORY_PAGE = 92;
    public const SIMPLE = 10;
    public const VIRTUAL = 60;
    public const DOWNLOADABLE = 61;
    public const CONFIGURABLE = 30;
    public const BUNDLE = 40;
    public const GROUPED = 20;

    public function visitBanner(Banner $item) {
        return self::BANNER;
    }

    public function visitPage(Page $item) {
        return self::PAGE;
    }

    public function visitCategoryPage(CategoryPage $item) {
        return self::CATEGORY_PAGE;
    }

    public function visitSimple(Simple $item) {
        return self::SIMPLE;
    }

    public function visitVirtual(Virtual $item) {
        return self::VIRTUAL;
    }

    public function visitDownloadable(Downloadable $item) {
        return self::DOWNLOADABLE;
    }

    public function visitConfigurable(Configurable $item) {
        return self::CONFIGURABLE;
    }

    public function visitBundle(Bundle $item) {
        return self::BUNDLE;
    }

    public function visitGrouped(Grouped $item) {
        return self::GROUPED;
    }
}