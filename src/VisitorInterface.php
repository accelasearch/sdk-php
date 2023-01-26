<?php
namespace AccelaSearch\ProductMapper;

interface VisitorInterface {
    public function visitBanner(Banner $item);
    public function visitPage(Page $item);
    public function visitCategoryPage(CategoryPage $item);
    public function visitSimple(Simple $item);
    public function visitVirtual(Virtual $item);
    public function visitDownloadable(Downloadable $item);
    public function visitConfigurable(Configurable $item);
    public function visitBundle(Bundle $item);
    public function visitGrouped(Grouped $item);
}