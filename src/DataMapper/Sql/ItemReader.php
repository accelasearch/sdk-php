<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \InvalidArgumentException;
use \AccelaSearch\ProductMapper\ItemInterface;
use \AccelaSearch\ProductMapper\Banner;
use \AccelaSearch\ProductMapper\Page;
use \AccelaSearch\ProductMapper\CategoryPage;
use \AccelaSearch\ProductMapper\StockableInterface;
use \AccelaSearch\ProductMapper\SellableInterface;
use \AccelaSearch\ProductMapper\ProductInterface;
use \AccelaSearch\ProductMapper\ProductFactory;
use \AccelaSearch\ProductMapper\Attribute;
use \AccelaSearch\ProductMapper\Image;
use \AccelaSearch\ProductMapper\Stock\Stock;
use \AccelaSearch\ProductMapper\Stock\Quantity\Limited;
use \AccelaSearch\ProductMapper\Stock\Quantity\Unlimited;
use \AccelaSearch\ProductMapper\Price\Price;

class ItemReader {
    private $connection;
    private $product_factory;
    private $category_mapper;
    private $warehouse_mapper;
    private $group_mapper;
    private $category_lookup;
    private $warehouse_lookup;
    private $group_lookup;

    public function __construct(
        Connection $connection,
        Productfactory $product_factory,
        Category $category_mapper,
        Warehouse $warehouse_mapper,
        CustomerGroup $group_mapper
    ) {
        $this->connection = $connection;
        $this->product_factory = $product_factory;
        $this->category_mapper = $category_mapper;
        $this->warehouse_mapper = $warehouse_mapper;
        $this->group_mapper = $group_mapper;
        $this->category_lookup = [];
        $this->warehouse_lookup = [];
        $this->group_lookup = [];
    }

    public static function fromConnection(Connection $connection): self {
        return new ItemReader(
            $connection,
            new ProductFactory(),
            Category::fromConnection($connection),
            Warehouse::fromConnection($connection),
            CustomerGroup::fromConnection($connection)
        );
    }

    public function read(int $identifier): ItemInterface {
        $query = 'SELECT id, sku, typeid, externalid, externalidstr, url FROM products WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No item with identifier ' . $identifier . '.');
        }
        return $this->rowToItem($row);
    }

    public function search(): array {
        return [];
    }

    public function searchByExternalIdentifier(string $external_identifier): ItemInterface {
        $query = 'SELECT id, sku, typeid, externalid, externalidstr, url FROM products WHERE externalidstr = :external_identifier AND siteid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':external_identifier' => $external_identifier,
            ':shop_identifier' => $this->connection->getShopIdentifier()
        ]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No item with identifier ' . $external_identifier . '.');
        }
        return $this->rowToItem($row);
    }

    private function rowToItem(array $row): ItemInterface {
        $external_identifier = !empty($row['externalid'])
            ? $row['externalid']
            : (!empty($row['externalidstr']) ? $row['externalidstr'] : null)
        ;
        switch ($row['typeid']) {
            case ItemGetTypeVisitor::BUNDLE:
                $item = new Banner($row['url'], '', '');
                $item->setIdentifier($row['id']);
                break;
            case ItemGetTypeVisitor::PAGE:
                $item = new Page($row['url']);
                $item->setIdentifier($row['id']);
                break;
            case ItemGetTypeVisitor::CATEGORY_PAGE:
                $item = new CategoryPage($row['url']);
                $item->setIdentifier($row['id']);
                break;
            case ItemGetTypeVisitor::SIMPLE:
                $item = $this->product_factory->createSimple($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $this->buildProduct($item);
                break;
            case ItemGetTypeVisitor::VIRTUAL:
                $item = $this->product_factory->createVirtual($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $this->buildProduct($item);
                break;
            case ItemGetTypeVisitor::DOWNLOADABLE:
                $item = $this->product_factory->createDownloadable($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $this->buildProduct($item);
                break;
            case ItemGetTypeVisitor::CONFIGURABLE:
                $item = $this->product_factory->createConfigurable($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $children = $this->searchChildrenByIdentifier($row['id']);
                foreach ($children as $child) {
                    $item->addVariant($child);
                }
                $this->buildProduct($item);
                break;
            case ItemGetTypeVisitor::BUNDLE:
                $item = $this->product_factory->createBundle($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $children = $this->searchChildrenByIdentifier($row['id']);
                foreach ($children as $child) {
                    $item->addProduct($child);
                }
                $this->buildProduct($item);
                break;
            case ItemGetTypeVisitor::GROUPED:
                $item = $this->product_factory->createGrouped($row['url'], $external_identifier);
                $item->setIdentifier($row['id']);
                $children = $this->searchChildrenByIdentifier($row['id']);
                foreach ($children as $child) {
                    $item->addProduct($child);
                }
                $this->buildProduct($item);
                break;
            default:
                throw new InvalidArgumentException('Unreckognized product type ' . $row['typeid']);
        }
        if (!empty($row['sku'])) {
            $item->setSku($row['sku']);
        }
        return $item;
    }

    private function searchChildrenByIdentifier(int $identifier): array {
        $query = 'SELECT productid FROM products_children WHERE parentid = :identifier AND deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $children = [];
        while ($row = $sth->fetch()) {
            $children[] = $this->read($row['productid']);
        }
        return $children;
    }

    private function buildProduct(ProductInterface $item): self {
        return $this->buildAttributes($item)
            ->buildCategories($item)
            ->buildImages($item)
            ->buildPricing($item)
            ->buildAvailability($item)
        ;
    }

    private function buildAttributes(ProductInterface $item): self {
        $query = 'SELECT label, value, configurable '
            . 'FROM products_attr_text JOIN products_attr_label ON products_attr_text.labelid = products_attr_label.id '
            . 'WHERE productid = :identifier AND products_attr_text.deleted = 0 '
            . 'UNION '
            . 'SELECT label, value, configurable '
            . 'FROM products_attr_datetime JOIN products_attr_label ON products_attr_datetime.labelid = products_attr_label.id '
            . 'WHERE productid = :identifier AND products_attr_datetime.deleted = 0 '
            . 'UNION '
            . 'SELECT label, value, configurable '
            . 'FROM products_attr_float JOIN products_attr_label ON products_attr_float.labelid = products_attr_label.id '
            . 'WHERE productid = :identifier AND products_attr_float.deleted = 0 '
            . 'UNION '
            . 'SELECT label, value, configurable '
            . 'FROM products_attr_int JOIN products_attr_label ON products_attr_int.labelid = products_attr_label.id '
            . 'WHERE productid = :identifier AND products_attr_int.deleted = 0 '
            . 'UNION '
            . 'SELECT label, value, configurable '
            . 'FROM products_attr_str JOIN products_attr_label ON products_attr_str.labelid = products_attr_label.id '
            . 'WHERE productid = :identifier AND products_attr_str.deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $item->getIdentifier()]);
        while ($row = $sth->fetch()) {
            if (empty($row['value'])) {
                continue;
            }
            if (is_null($item->getAttribute($row['label']))) {
                $attribute = new Attribute($row['label']);
                $attribute->setIsConfigurable($row['configurable'] != 0);
                $item->addAttribute($attribute);
            }
            $item->getAttribute($row['label'])->addValue($row['value']);
        }
        return $this;
    }

    private function buildCategories(ProductInterface $item): self {
        $query = 'SELECT categoryid FROM products_categories WHERE productid = :identifier AND deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $item->getIdentifier()]);
        while ($row = $sth->fetch()) {
            if (!isset($this->category_lookup[$row['categoryid']])) {
                $this->category_lookup[$row['categoryid']] = $this->category_mapper->read($row['categoryid']);
            }
            $item->addCategory($this->category_lookup[$row['categoryid']]);
        }
        return $this;
    }

    private function buildImages(ProductInterface $item): self {
        $query = 'SELECT label, sort, url FROM products_images JOIN products_images_lbl '
            . 'ON products_images.labelid = products_images_lbl.id '
            . 'WHERE productid = :identifier AND products_images.deleted = 0 AND products_images_lbl.deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $item->getIdentifier()]);
        while ($row = $sth->fetch()) {
            $item->addImage(new Image($row['label'], $row['url'], $row['sort']));
        }
        return $this;
    }

    private function buildAvailability(StockableInterface $item): self {
        $query = 'SELECT wharehouseid, quantity, unlimited FROM stocks WHERE productid = :identifier AND deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $item->getIdentifier()]);
        while ($row = $sth->fetch()) {
            if (!isset($this->warehouse_lookup[$row['wharehouseid']])) {
                $this->warehouse_lookup[$row['wharehouseid']] = $this->warehouse_mapper->read($row['wharehouseid']);
            }
            $item->getAvailability()->add(new Stock(
                $this->warehouse_lookup[$row['wharehouseid']],
                $row['unlimited'] ? new Unlimited() : new Limited($row['quantity'])
            ));
        }
        return $this;
    }

    private function buildPricing(SellableInterface $item): self {
        $query = 'SELECT groupid, currency, price, specialprice, quantityfrom FROM prices '
            . 'WHERE productid = :identifier AND deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $item->getIdentifier()]);
        while ($row = $sth->fetch()) {
            if (!isset($this->group_lookup[$row['groupid']])) {
                $this->group_lookup[$row['groupid']] = $this->group_mapper->read($row['groupid']);
            }
            $item->getPricing()->add(new Price(
                $row['price'],
                $row['specialprice'] ? $row['specialprice'] : $row['price'],
                $row['currency'],
                $row['quantityfrom'],
                $this->group_lookup[$row['groupid']]
            ));
        }
        return $this;
    }
}
