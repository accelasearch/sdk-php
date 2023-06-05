<?php
namespace AccelaSearch\ProductMapper\Converter\GoogleShopping;

use \Exception;
use AccelaSearch\ProductMapper\Attribute;
use AccelaSearch\ProductMapper\Category;
use AccelaSearch\ProductMapper\Converter\ItemInterface;
use AccelaSearch\ProductMapper\Image;
use AccelaSearch\ProductMapper\ItemInterface as ProductMapperItemInterface;
use AccelaSearch\ProductMapper\Price\CustomerGroup;
use AccelaSearch\ProductMapper\Price\Price;
use AccelaSearch\ProductMapper\Simple;
use AccelaSearch\ProductMapper\Price\Pricing;
use AccelaSearch\ProductMapper\Stock\Availability;
use AccelaSearch\ProductMapper\Stock\Quantity\Unlimited;
use AccelaSearch\ProductMapper\Stock\Stock;
use AccelaSearch\ProductMapper\Stock\Warehouse\Virtual;

class Item implements ItemInterface {
    private $visitor;

    public function __construct(Visitor $visitor) {
        $this->visitor = $visitor;
    }

    public static function fromDefault(): self {
        return new Item(Visitor::fromDefault());
    }

    public function getVisitor(): Visitor {
        return $this->visitor;
    }

    // @todo Implement me
    public function fromObject(ProductMapperItemInterface $item) {
    }

    public function toObject($data): ProductMapperItemInterface
    {
        $special_attributes = [
            'link', 'image_link', 'additional_image_link', 
            'price', 'sale_price', 'product_type', 'product_detail', 'shipping'
        ];
        $configurable_attributes = ['age_group', 'color', 'gender', 'material', 'pattern', 'size'];
        $warehouse = Virtual::fromDefault();
        $customer_group = CustomerGroup::fromDefault();

        // Reads base information
        $external_identifier = (string) $this->readValue($data, 'id');
        $url = (string) $this->readValue($data, 'link');

        // Reads availability
        $availability = new Availability();
        if (str_replace('_', ' ', (string) $this->readValue($data, 'availability')) === 'in stock') {
            $availability->add(new Stock($warehouse, new Unlimited()));
        }

        // Reads pricing
        $pricing = new Pricing();
        list($listing_price, $currency) = explode(' ', (string) $this->readValue($data, 'price'), 2);
        if (is_null($currency)) {
            throw new Exception('Missing currency on price of item ' . $external_identifier);
        }
        $listing_price = str_replace(',', '', $listing_price);
        $price = new Price($listing_price, $listing_price, $currency, 0.0, $customer_group);
        if (!empty($data->xpath('g:sale_price'))) {
            list($selling_price, $currency) = explode(' ', (string) $data->xpath('g:sale_price')[0], 2);
            if (is_null($currency)) {
                throw new Exception('Missing currency on selling price of item ' . $external_identifier);
            }
            $selling_price = str_replace(',', '', $selling_price);
            $price->setSellingPrice(floatval($selling_price));
        }
        $pricing->add($price);

        // Builds base item
        $item = new Simple($url, $external_identifier, $availability, $pricing);

        // Reads images
        $item->addImage(new Image('main', (string) $this->readValue($data, 'image_link'), 0));
        foreach ($data->xpath('g:additional_image_link') as $image_url) {
            $item->addImage(new Image('additional', $image_url, count($item->getImagesAsArray())));
        }

        // Reads category
        if (!empty($data->xpath('g:product_type')) || !empty($data->xpath('product_type'))) {
            $category_path = explode(' &gt; ', (string) $this->readValue($data, 'product_type'));
            if (count($category_path) == 1) {
                $category_path = explode(' > ', (string) $this->readValue($data, 'product_type'));
            }
            if (!empty($category_path)) {
                $previous = Category::fromName(array_shift($category_path));
                foreach ($category_path as $category) {
                    $previous = new Category($category, $category, $previous);
                }
                $item->addCategory($previous);
	    }
        }

        // Reads custom attributes
        $attributes = [];
        foreach ($data->xpath('*') as $field) {
            $name = $field->getName();
            $value = (string) $field;
            if (in_array($name, $special_attributes)) {
                continue;
            }
            if (!array_key_exists($name, $attributes)) {
                $attribute = new Attribute($name);
                $attribute->setIsConfigurable(in_array($name, $configurable_attributes));
                $attributes[$name] = $attribute;
            }
            $attributes[$name]->addValue($value);
        }

        // Reads shipping
        foreach ($data->xpath('g:shipping') as $shipping_field) {
            foreach ($shipping_field->xpath('*') as $field) {
                $name = 'shipping_' . $field->getName();
                $value = (string) $field;
                if (!array_key_exists($name, $attributes)) {
                    $attribute = new Attribute($name);
                    $attribute->setIsConfigurable(false);
                    $attributes[$name] = $attribute;
                }
                $attributes[$name]->addValue($value);
            }
        }

        // Reads additional data
        foreach ($data->xpath('g:product_detail') as $detail) {
            $name = $this->readValue($detail, 'attribute_name');
            $value = $this->readValue($detail, 'attribute_value');
            if (!array_key_exists($name, $attributes)) {
                $attribute = new Attribute($name);
                $attribute->setIsConfigurable(in_array($name, $configurable_attributes));
                $attributes[$name] = $attribute;
            }
            $attributes[$name]->addValue($value);
        }

        // Injects attributes
        foreach ($attributes as $attribute) {
            $item->addAttribute($attribute);
        }

        return $item;
    }

    private function readValue($data, string $key) {
        $values = $data->xpath('g:' . $key);
        if (empty($values)) {
            $values = $data->xpath($key);
        }
        if (empty($values)) {
            throw new Exception('Missing mandatory element "' . $key . '".');
        }
        return trim($values[0]);
    }
}
