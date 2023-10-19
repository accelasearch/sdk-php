<?php
/**
 * @todo This class is not fully implemented
 */
namespace AccelaSearch\ProductMapper\Converter\Dictionary;
use \BadFunctionCallException;
use \InvalidArgumentException;
use \AccelaSearch\ProductMapper\Converter\ItemInterface;
use \AccelaSearch\ProductMapper\ItemInterface as Subject;
use \AccelaSearch\ProductMapper\Stock\Availability;
use \AccelaSearch\ProductMapper\Stock\Stock;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited as LimitedQuantity;
use \AccelaSearch\ProductMapper\Stock\Quantity\Unlimited as UnlimitedQuantity;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Physical as PhysicalWarehouse;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Virtual as VirtualWarehouse;
use \AccelaSearch\ProductMapper\Price\Pricing;
use \AccelaSearch\ProductMapper\Price\CustomerGroup;
use \AccelaSearch\ProductMapper\Price\Price;
use \AccelaSearch\ProductMapper\Category;
use \AccelaSearch\ProductMapper\Image;
use \AccelaSearch\ProductMapper\Attribute;
use \AccelaSearch\ProductMapper\Banner;
use \AccelaSearch\ProductMapper\Page;
use \AccelaSearch\ProductMapper\CategoryPage;
use \AccelaSearch\ProductMapper\Simple;
use \AccelaSearch\ProductMapper\Virtual;
use \AccelaSearch\ProductMapper\Downloadable;
use \AccelaSearch\ProductMapper\Configurable;
use \AccelaSearch\ProductMapper\Grouped;
use \AccelaSearch\ProductMapper\Bundle;

class Item implements ItemInterface {
    private $visitor;

    public function __construct(Visitor $visitor) {
        $this->visitor = $visitor;
    }

    public static function fromDefault(): self {
        return new Item(Visitor::fromDefault());
    }

    public function fromObject(Subject $item) {
        return $item->accept($this->visitor);
    }

    public function toObject($item): Subject {
        if (!isset($item['header']['type'])) {
            throw new BadFunctionCallException('Missing mandatory key "type".');
        }
        switch ($item['header']['type']) {
            case 'banner': return $this->banner($item);
            //case 'page': return $this->page($item);
            //case 'categoryPage': return $this->categoryPage($item);
            case 'simple': return $this->simple($item);
            case 'virtual': return $this->virtual($item);
            case 'downloadable': return $this->downloadable($item);
            case 'configurable': return $this->configurable($item);
            case 'bundle': return $this->bundle($item);
            case 'grouped': return $this->grouped($item);
            default: throw new InvalidArgumentException('Unknown type "' . $item['header']['type'] . '".');
        }
    }

    private function item($item, $data) {
        $item->setIdentifier($data["header"]["id"]);
        if (array_key_exists("sku", $data["header"])) {
            $item->setSku($data["header"]["sku"]);
        }
        return $item;
    }

    private function stockable($item, $data) {
        $availability_data = array_key_exists("availability", $data)
            ? $data["availability"]
            : []
        ;
        foreach ($availability_data as $stock_data) {
            $warehouse = $stock_data["warehouse"]["type"] === "physical"
                ? new PhysicalWarehouse($stock_data["warehouse"]["label"], $stock_data["warehouse"]["latitude"], $stock_data["warehouse"]["longitude"])
                : new VirtualWarehouse($stock_data["warehouse"]["label"])
            ;
            $warehouse->setIdentifier($stock_data["warehouse"]["id"]);
            $quantity = $stock_data["quantity"]["type"] === "limited"
                ? new LimitedQuantity($stock_data["quantity"]["quantity"])
                : new UnlimitedQuantity()
            ;
            $item->getAvailability()->add(new Stock($warehouse, $quantity));
        }
        return $item;
    }

    private function sellable($item, $data) {
        $pricing_data = array_key_exists("pricing", $data) ? $data["pricing"] : [];
        foreach ($pricing_data as $price_data) {
            $item->getPricing()->add(new Price(
                $price_data["listingPrice"],
                $price_data["sellingPrice"],
                $price_data["currency"],
                $price_data["minimumQuantity"],
                new CustomerGroup($price_data["customerGroup"]["label"])
            ));
        }
        return $item;
    }

    private function product($item, $data) {
        $item = $this->item($item, $data);
        $item = $this->stockable($item, $data);
        $item = $this->sellable($item, $data);
        
        // Categories
        $categories_data = array_key_exists("categories", $data) ? $data["categories"] : [];
        foreach ($categories_data as $category_data) {
            $item->addCategory(new Category("", $category_data, null));
        }

        // Images
        $images_data = array_key_exists("image", $data) ? $data["image"] : [];
        $i = 0;
        foreach ($images_data as $image_data) {
            $item->addImage(new Image("main", $image_data, $i++));
        }

        // Attributes
        foreach ($data["data"] as $name => $value) {
            $attribute = new Attribute($name);
            $attribute->addValue($value);
            $item->addAttribute($attribute);
        }

        return $item;
    }

    private function banner($data) {
        $item = new Banner($data["header"]["url"], $data["header"]["desktop"], $data["header"]["mobile"]);
        $item->setIdentifier($data["header"]["id"]);
        $item->setSize($data["header"]["size"]);
        return $item;
    }

    private function simple($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Simple($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        return $item;
    }

    private function virtual($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Virtual($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        return $item;
    }

    private function downloadable($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Downloadable($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        return $item;
    }

    private function configurable($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Configurable($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        foreach ($data["variants"] as $variant_data) {
            $item->addVariant($this->toObject($variant_data));
        }
        return $item;
    }

    private function bundle($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Configurable($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        foreach ($data["products"] as $variant_data) {
            $item->addProduct($this->toObject($variant_data));
        }
        return $item;
    }

    private function grouped($data) {
        $availability = new Availability();
        $pricing = new Pricing();
        $item = new Configurable($data["data"]["url"], $data["header"]["externalId"], $availability, $pricing);
        $item = $this->product($item, $data);
        foreach ($data["products"] as $variant_data) {
            $item->addProduct($this->toObject($variant_data));
        }
        return $item;
    }
}
