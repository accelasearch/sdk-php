# AccelaSearch PHP Software Development Kit
PHP SDK for easy integration with AccelaSearch.

## Overview
AccelaSearch deploys an intermediate SQL-like database, called *collector*, to store product information, which are then used to configure and populate search engine and results. Data about items must be stored in the proper format (see the [documentation about Custom Integration](https://phase-orchid-049.notion.site/Custom-Integration-and-APIs-6fd930b1082c4b348efbd61dec362a9b) for details). This SDK exposes an *Abstract Data Type* (ADT) to represent all of the different types of product supported by AccelaSearch, as well as a convenient set of data mapper to convert from/to different formats, including JSON, PHP dictionaries and the intermediate SQL format.

This SDK allows to define product data in an abstract, syntax-friendly way and send/retrieve information from the collector database in order to simplify integration with third-parties systems or CMS, without requiring any understanding of the underlying collector database schema. Note, however, that the Product Mapper will operate on products as whole, atomic entities and will be unaware of CMS-specify behaviors which could speed up the synchronization process (see Limitations for an example).

## Requirements
This SDK is self-contained, it requires an up-to-date version of PHP to run.

Provided unit tests can be run with [PHPUnit](https://phpunit.de/) , and code documentation can be generated with [PHPDcoumentor](https://www.phpdoc.org/).

## Installation
Recommended installation is through [Composer](https://getcomposer.org/) :

```bash
composer require accelasearch/sdk-php
```

Manual installation is possible by cloning or downloading this repository:

```bash
git clone https://github.com/accelasearch/sdk-php.git
```

```bash
wget https://github.com/accelasearch/sdk-php/archive/master.zip
```

## Overview
After registering to AccelaSearch, the system will release an **API key**, such as `my-api-key`, which should be kept secret and stored securely. API key may be used to instantiate a `DataMapper\Api\Client` object, which allows to retrieve information about supported CMSs and collector. The former should be used to create an instance of `Shop`, while the latter should be used to establish an SQL connection towards the collector, which may be used to store information about shops and products. In order to facilitate these operations, AccelaSearch - Product Mapper offers a number of utilities and facades, which represent the preferred way to interact with the collector.

## Retrieving Information
Basic information, such as list of supported CMSs and collector credentials, may be accessed through AccelaSearch's API system by using the `DataMapper\Api\Client` along with the relative data mappers.

```php
use \AccelaSearch\ProductMapper\DataMapper\Api\Client;
use \AccelaSearch\ProductMapper\DataMapper\Api\Cms as CmsMapper;
use \AccelaSearch\ProductMapper\DataMapper\Api\Collector as CollectorMapper;

$client = Client::fromApiKey("my-api-key");
$cms_mapper = new CmsMapper($client);
$collector_mapper = new CollectorMapper($client);

$cms_list = $cms_mapper->search();
$collector = $collector_mapper->read();
```

## Connecting to the Collector
Once a collector object is obtained (which is likely to happen through `DataMapper\Api\Collector`), it can be used to establish a connection to the collector database:

```php
use \PDO;

$dbh = new PDO(
    'mysql:host=' . $collector->getHostName() . ';dbname=' . $collector->getDatabaseName(),
    $collector->getUsername(),
    $collector->getPassword(),
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
```

## Managing Shops
An SQL connection may be used to instantiate a shop data mapper, which may in turn be used to insert instances of `Shop` into the collector:

```php
use \AccelaSearch\ProductMapper\Shop;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Shop as ShopMapper;

// $cms_list = ...
// $dbh = ...
$shop_mapper = ShopMapper::fromConnection($dbh);
$shop = new Shop("http://www.shop.com", "en", $cms_list[0]);
$shop_mapper->create($shop);
```

a unique shop identifier will be created upon insertion, and will be assigned to `$shop`. Such identifier may be used to retrieve the shop from the database and update its information or soft-delete it:

```php
// Shop has identifier 2
$shop = $shop_mapper->read(2);
$shop->setUrl("http://www.new-url.com");
$shop_mapper->update($shop);

// Soft-deletion
$shop->setIsActive(false);
$shop_mapper->update($shop);
```

**Note**: shops should not be hard-deleted manually, AccelaSearch will periodically scan the collector and take appropriate actions, eventually removing soft-deleted shops.

## Managing Items
The preferred way to handle item data is through the `CollectorFacade`, which automatically handles SQL transactions and rollbacks, insertion of relational features, implicit handling of insert-versus-update. The `CollectorFacade` requires an instance of `DataMapper\API\Client` and the identifier of the shop on which operate, it will retrieve all of the necessary connection information automatically:

```php
use \AccelaSearch\ProductMapper\DataMapper\Api\Client;
use \AccelaSearch\ProductMapper\CollectorFacade;

$client = Client::fromApiKey("my-api-key");
$collector = new CollectorFacade($client, 2);
```

`CollectorFacade` offers four methods to interact with products: `load`, `searchByExternalIdentifier`, `save` and `delete`. The latter will perform a soft delete, while `save` will either perform an insertion or update depending on the presence of the item in the collector:

```php
// Retrieves item with identifier 42
$item = $collector->load(42);

// Retrieves item having external identifier "ITM0001"
$item = $collector->searchByExternalIdentifier("ITM0001");

// Updates item information
$item->setUrl("http://new-shop-url.com/ITM0001");
$collector->save($item);

// Soft deletion
$collector->delete($item);
```

## Item Hierarchy
AccelaSearch supports nine different type of items:

* **Banner**: a banner with URL and image (different between desktop and mobile)
* **Page**: a generic web page
* **CategoryPage**: web page of a category or collector
* **Simple**: standard product
* **Virtual**: subtype of Simple, usually implies no shipping
* **Downloadable**: subtype of Virtual, an item which can be downloaded
* **Configurable**: an item for which a set of variants exists, for example a shirt available in different colors or sizes; variants are usually represented as Simple items, although it is possible to use any type of item
* **Bundle**: a bundle of items sold together
* **Grouped**: similar to Bundle, used by CMSs which make a distinction such as Magento

every type of item is represented by an homonym class under the main `\AccelaSearch\ProductMapper`. Every class implements the `ItemInterface`, while `Simple`, `Virtual`, `Downloadable`, `Configurable`, `Bundle` and `Grouped` also implements the `ProductInterface`, which adds information about external identifier, belonging to zero or more categories, image information, custom attributes and information about availability and pricing (by extending the `StockableInterface` and `SellableInterface`).

Although items can be created through constructors, products implementing the `ProductInterface` can be instantiated through the `ProductFactory`:

```php
use \AccelaSearch\ProductMapper\ProductFactory;

$factory = new ProductFactory();
$simple = $factory->createSimple("http://myshop.com/SIMPLE0001", "SIMPLE0001");
$virtual = $factory->createVirtual("http://myshop.com/VIRTUAL0001", "VIRTUAL0001");
$downloadable = $factory->createDownloadable("http://myshop.com/DOWNLOADABLE0001", "DOWNLOADABLE0001");
$configurable = $factory->createConfigurable("http://myshop.com/CONF0001", "CONF0001");
$bundle = $factory->createBundle("http://myshop.com/BUNDLE0001", "BUNDLE0001");
$grouped = $factory->createVirtual("http://myshop.com/GROUP0001", "GROUP0001");
```

Every method is named after the type of product it creates, and accepts the URL of the product along with its external identifier. Every product is created with empty availability, pricing and image information, and with no categories.

### Adding Standard Attributes
Every item allows to set sku and URL through accessors, while products also allow for external identifier, categories, image information, stock availability, pricing and custom attributes. The former can be accessed as:

```php
// $item = ...
$item->setSku("ITM-003");
$item->setUrl("http://www.myshop.com/catalogue/itm-003");
echo $item->getSku() . " " . $item->getUrl();

// $product = ...
$product->setExternalIdentifier("56");
echo $product->getExternalIdentifier();
```

Following sections show how to handle more complex standard information.

### Adding Categories
Categories must be created (or read from collector) before being assigned to products:

```php
use \AccelaSearch\ProductMapper\Category;

$parent_category = new Category("cat-0001", "Fashion", null);
$category = new Category("cat-00075", "Woman", $parent_category);
$category->setUrl("http://www.myshop/categories/75");
$item->addCategory($category);
$item->removeCategory($category);
```

Categories are not explicitly handled by the `CollectorFacade`, which will transparently insert of read data when needed. Instead, categories can be persisted by using the `Repository\Sql\Category` repository or the lower level data mapper `DataMapper\Sql\Category` for raw operations.

### Adding Image Information
Information about pictures of a product are handled by the `Image` class which allows to specify, for each image, a label, an URL and a position:

```php
use \AccelaSearch\ProductMapper\Image;

$image_1 = new Image("main", "http://www.myshop.com/storage/images/001.jpeg", 1);
$image_2 = new Image("over", "http://www.myshop.com/storage/images/002.jpeg", 3);
```

Images can be added to items through the `addImage` method:

```php
use \AccelaSearch\ProductMapper\ProductFactory;

$factory = new ProductFactory();
$item = $factory->createSimple("http://myshop.com/SIMPLE0001", "SIMPLE0001");
$item->addImage($image_1)->addImage($image_2);
```

### Adding Availability
Availability is always related to a warehouse, and may be either limited or unlimited. Warehouses must be created (or read from collector) before being used, and can be either virtual or physical (for which latitude and longitude are known). When creating products through the `ProductFactory`, an empty `Stock\Availability` is automatically instantiated upon creation, and may be accessed through its accessors methods:

```php
use \AccelaSearch\ProductMapper\Stock\Warehouse\Virtual as VirtualWarehouse;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Physical as PhysicalWarehouse;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited as LimitedQuantity;
use \AccelaSearch\ProductMapper\Stock\Quantity\Unlimited as UnlimitedQuantity;

$generic_warehouse = new VirtualWarehouse("warehouse-001");
$brick_warehouse = new PhysicalWarehouse("warehouse-002", 45.0, 13.5);
$five_in_stock = new LimitedQuantity(5);
$unlimited = new UnlimitedQuantity();

// $item = ...
$item->getAvailability()->add(new Stock($generic_warehouse, $five_in_stock))
    ->add(new Stock($brick_warehouse, $unlimited));
```

Every combination of warehouses and quantity is allowed, each of which is mediated by the `Stock\Stock` class.

### Adding Pricing
Price information is always related to a customer group, to enable different price systems for different groups of user. Moreover, prices may vary depending on the bought quantity (i.e. multi-tier pricing), support multiple currencies and allow to specify a selling price different from the listing price. Customer groups must be created (or read from collector) before being used, while tier, currency and selling and listing prices are standard attribute of the `Price\Price` class. When creating products through the `ProductFactory`, an empty `Price\Pricing` is automatically instantiated, and should be accessed through its accessors methods:

```php
use \AccelaSearch\ProductMapper\Price\CustomerGroup;
use \AccelaSearch\ProductMapper\Price\Price;

$group_1 = new CustomerGroup("standard-group");
$group_2 = new CustomerGroup("webpos-group");

// $item = ...
// Item normally sold for 19.99 USD, now selling for 15.99, no tiers, only for standard-group
$item->getPricing()->add(new Price(19.99, 15.99, "USD", 0, $group_1));
// Same item normally sold for 16.43 EUR, now selling at 13.14 EUR
$item->getPricing()->add(new Price(19.99, 15.99, "USD", 0, $group_1));

// Item selling at 19.99 USD for quantities between 0 and 9, selling at 9.99 if 100 or more units are bought
$item->getPricing()->add(new Price(19.99, 19.99, "USD", 0, $group_1))
    ->add(new Price(9.99, 9.99, "USD", 100, $group_1));

// Same item, but different prices for different customer group, second group gets a discount
$item->getPricing()->add(new Price(19.99, 19.99, "USD", 0, $group_1))
    ->add(new Price(25.99, 21.50, "USD", 0, $group_1));
```

The same combination of currency, minimum quantity and group must not be inserted more than once, otherwise an undefined behavior occurs.

### Adding Custom Attributes
Attributes other than those explicitly handled (URL, sku, prices, availability, etc.) can be inserted as custom attributes. Every custom attribute has a name and a list of values, that is, every attribute is treated as multi-valued. Single-valued must be inserted as multi-valued attributes having a single value. Attributes should be created before being assigned to products:

```php
use \AccelaSearch\ProductMapper\Attribute;

$name = new Attribute("name");
$name->addValue("T-Shirt");

$tags = new Attribute("tag");
$tags->addValue("fashion")->addValue("summer")->addValue("light");

// $item = ...
$item->addAttribute($name)
    ->addAttribute($tags);
```

Once an attribute is defined for a product, it is possible to retrieve it by its name (and possibly modify its list of values):

```php
$item->getAttribute("tag")->removeValue("light")->addValue("men");
```

Attribute names should be English words, all lowercase, in singular form (when applicable) and must not contain spaces. We recommend using hyphens or underscore in place of spaces.

For single-valued attribute, a shorthand is available in the form of a factory method:

```php
$item->addAttribute(Attribute::fromNameAndValue("name", "T-Shirt"));
```

For products which are part of a configurable, attributes affecting configuration must be flagged as such by setting `isConfigurable` to true:

```php
$color = new Attribute("color");
$color->addValue("red");
$color->setIsConfigurable(true);

// $configurable_item = ...
// $actual_item = ...
$actual_item->addAttribute($color);
```

which is also available in the shorthand:

```php
$actual_item->addAttribute(Attribute::fromNameAndValue("color", "red")->setIsConfigurable(true));
```

### Configurable Products
Configurable products are meta-products logically grouping together a set of actual (usually `Simple`) products which differ among each other by small details, such as color or size. When dealing with configurable products, a set of children products must be created and assigned to the parent configurable. Some attributes of the children should be marked as configurable in order to tell AccelaSearch which attributes makes the configuration, and which one do not (as described in previous section):

```php
use \AccelaSearch\ProductMapper\ProductFactory;
use \AccelaSearch\ProductMapper\Attribute;

$factory = new ProductFactory();
$shirt = $factory->createConfigurable("http://www.myshop.com/shirt", "ID:42");
$shirt->setSku("CONF-001");

$blue_shirt = $factory->createSimple("http://www.myshop.com/shirt/blue", "ID:44");
$blue_shirt->setSku("CONF-001-b");
$blue_shirt->addAttribute(Attribute::fromNameAndValue("color", "blue")->setIsConfigurable(true));
$blue_shirt->addAttribute(Attribute::fromNameAndValue("name", "blue shirt"));

$red_shirt = $factory->createSimple("http://www.myshop.com/shirt/red", "ID:45");
$red_shirt->setSku("CONF-001-r");
$red_shirt->addAttribute(Attribute::fromNameAndValue("color", "red")->setIsConfigurable(true));
$red_shirt->addAttribute(Attribute::fromNameAndValue("name", "red shirt"));

$shirt->addVariant($red_shirt)->addVariant($blue_shirt);
```

Although not shown in the example, it is recommended to set pricing, availability and category information for both parent and children, as the former does not inherit them from the latter, nor vice-versa.

### Bundle and Grouped Products
Bundle and grouped are meta-products grouping together a set of actual (usually `Simple`) products which are sold together. When dealing with bundle or grouped products, a set of children products must be created and assigned to the parent. A product may appear more than once as child of the same parent, for instance when a set of two identical products is sold together:

```php
use \AccelaSearch\ProductMapper\ProductFactory;
use \AccelaSearch\ProductMapper\Attribute;

$factory = new ProductFactory();
$group = $factory->createGrouped("http://www.myshop.com/group", "ID:48");
$group->setSku("GRP-009");

$bottle = $factory->createSimple("http://www.myshop.com/bottle", "ID:94");
$bottle->setSku("BTL-001");

$paper = $factory->createSimple("http://www.myshop.com/paper", "ID:105");
$bottle->setSku("PPR-001");

// Two bottles and one piece of paper
$group->addProduct($bottle)->addProduct($bottle)->addProduct($paper);
```

Although not shown in the example, it is recommended to set pricing, availability and category information for both parent and children, as the former does not inherit them from the latter, nor vice-versa.