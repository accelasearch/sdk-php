<?php
namespace AccelaSearch\ProductMapper\Converter\Dictionary;
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
        return new Visitor(new WarehouseVisitor(), new QuantityVisitor);
    }

    public function visitBanner(Banner $item) {
        return [
            'header' => [
                'type' => 'banner',
                'id' => $item->getIdentifier(),
                'url' => $item->getUrl(),
                'desktop' => $item->getDesktopImageUrl(),
                'mobile' => $item->getMobileImageUrl(),
                'size' => $item->getSize()
            ]
        ];
    }

    public function visitPage(Page $item) {
        return [
            'header' => [
                'type' => 'page',
                'id' => $item->getIdentifier(),
                'url' => $item->getUrl()
            ]
        ];
    }

    public function visitCategoryPage(CategoryPage $item) {
        return [
            'header' => [
                'type' => 'categoryPage',
                'id' => $item->getIdentifier(),
                'url' => $item->getUrl()
            ]
        ];
    }

    public function visitSimple(Simple $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'simple';
        return $data;
    }

    public function visitVirtual(Virtual $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'virtual';
        return $data;
    }

    public function visitDownloadable(Downloadable $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'downloadable';
        return $data;
    }

    public function visitConfigurable(Configurable $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'configurable';
        $data['variants'] = array_map(function ($child) {
            return $child->accept($this);
        }, $item->getVariantsAsArray());
        return $data;
    }

    public function visitBundle(Bundle $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'bundle';
        $data['products'] = array_map(function ($child) {
            return $child->accept($this);
        }, $item->getProductsAsArray());
        return $data;
    }

    public function visitGrouped(Grouped $item) {
        $data = $this->product($item);
        $data['header']['type'] = 'grouped';
        $data['products'] = array_map(function ($child) {
            return $child->accept($this);
        }, $item->getProductsAsArray());
        return $data;
    }

    private function product($item): array {
        return [
            'header' => [
                'id' => $item->getIdentifier(),
                'externalId' => $item->getExternalIdentifier(),
                'sku' => $item->getSku(),
                'url' => $item->getUrl()
            ],
            'image' => array_map(function ($image) {
                    return [
                        'label' => $image->getLabel(),
                        'url' => $image->getUrl(),
                        'position' => $image->getPosition()
                    ];
                },
                $item->getImagesAsArray()
            ),
            'categories' => $this->categories($item->getCategoriesAsArray()),
            'data' => $this->attributes($item->getAttributesAsArray()),
            'availability' => $this->availability($item->getAvailability()),
            'pricing' => $this->pricing($item->getPricing())
        ];
    }

    private function categories(array $categories): array {
        return array_map(function ($category) { return $category->getName(); }, $categories);
    }

    private function attributes(array $attributes): array {
        $data = [];
        foreach ($attributes as $attribute) {
            $values = $attribute->getValuesAsArray();
            $data[$attribute->getName()] = (count($values) === 1)
                ? $values[0]
                : $values
            ;
        }
        return $data;
    }

    private function availability($availability): array {
        return array_map(function ($stock) {
            return [
                'warehouse' => $stock->getWarehouse()->accept($this->warehouse),
                'quantity' => $stock->getQuantity()->accept($this->quantity)
            ];
        }, $availability->asArray());
    }

    private function pricing($pricing): array {
        return array_map(function ($price) {
            return [
                'listingPrice' => $price->getListingPrice(),
                'sellingPrice' => $price->getSellingPrice(),
                'currency' => $price->getCurrency(),
                'minimumQuantity' => $price->getMinimumQuantity(),
                'customerGroup' => [
                    'id' => $price->getCustomerGroup()->getIdentifier(),
                    'label' => $price->getCustomerGroup()->getLabel()
                ]
            ];
        }, $pricing->asArray());
    }
}