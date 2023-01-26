<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\ItemInterface;
use \AccelaSearch\ProductMapper\ProductInterface;
use \AccelaSearch\ProductMapper\StockableInterface;
use \AccelaSearch\ProductMapper\SellableInterface;
use \AccelaSearch\ProductMapper\ImageLabel as ImageLabelSubject;
use \AccelaSearch\ProductMapper\DataMapper\ItemInterface as ItemMapperInterface;

class Item implements ItemMapperInterface {
    private $connection;
    private $attribute_lookup;
    private $image_label_lookup;
    private $image_label_mapper;
    private $item_reader;
    private $item_get_type;
    private $item_is_product;
    private $item_get_children;
    private $quantity_converter;

    public function __construct(
        Connection $connection,
        ImageLabel $image_label_mapper,
        ItemReader $item_reader,
        ItemGetTypeVisitor $item_get_type,
        ItemIsProductVisitor $item_is_product,
        ItemGetChildrenVisitor $item_get_children,
        QuantityConverterVisitor $quantity_converter
    ) {
        $this->connection = $connection;
        $this->attribute_lookup = [];
        $this->image_label_lookup = [];
        $this->image_label_mapper = $image_label_mapper;
        $this->item_reader = $item_reader;
        $this->item_get_type = $item_get_type;
        $this->item_is_product = $item_is_product;
        $this->item_get_children = $item_get_children;
        $this->quantity_converter = $quantity_converter;

        $this->initializeAttributeLookup();
        $this->initializeImageLabelLookup();
    }

    public static function fromConnection(Connection $connection): self {
        return new Item(
            $connection,
            ImageLabel::fromConnection($connection),
            ItemReader::fromConnection($connection),
            new ItemGetTypeVisitor(),
            new ItemIsProductVisitor(),
            new ItemGetChildrenVisitor(),
            new QuantityConverterVisitor()
        );
    }

    public function getConnection(): Connection {
        return $this->connection;
    }

    public function create(ItemInterface $item): ItemMapperInterface {
        // Tries to insert base information
        $query = 'INSERT IGNORE INTO products(sku, siteid, typeid, externalidstr, url) '
            . 'VALUES(:sku, :shop_identifier, :type_identifier, :external_identifier, :url)';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':sku' => $item->getSku(),
            ':shop_identifier' => $this->connection->getShopIdentifier(),
            ':type_identifier' => $item->accept($this->item_get_type),
            ':external_identifier' => $item->accept($this->item_is_product) ? $item->getExternalIdentifier() : null,
            ':url' => $item->getUrl()
        ]);
        $id = $this->connection->getDbh()->lastInsertId();
        if (!empty($id)) {
            $item->setIdentifier($id);
        }

        // Updates item if item already present
        $is_update = $sth->rowCount() === 0;
        if ($is_update) {
            $query = 'SELECT id FROM products WHERE siteid = :shop_identifier AND (sku = :sku OR externalidstr = :external_identifier)';
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute([
                ':shop_identifier' => $this->connection->getShopIdentifier(),
                ':sku' => $item->getSku(),
                ':external_identifier' => $item->getExternalIdentifier()
            ]);
            $row = $sth->fetch();
            if (empty($row)) {
                throw new OutOfBoundsException("Cannot update product with SKU \"" . $item->getSku() . "\".");
            }
            $item->setIdentifier($row['id']);
            $query = 'UPDATE products SET typeid = :type_identifier, externalidstr = :external_identifier, url = :url, deleted = 0 '
                . ' WHERE id = :identifier';
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute([
                ':identifier' => $item->getIdentifier(),
                ':type_identifier' => $item->accept($this->item_get_type),
                ':external_identifier' => $item->accept($this->item_is_product) ? $item->getExternalIdentifier() : null,
                ':url' => $item->getUrl()
            ]);
        }

        // Inserts additional information
        if ($item->accept($this->item_is_product)) {
            if ($is_update) {
                $this->clear($item);
            }
            $this->insertAttributes($item);
            $this->insertCategories($item);
            $this->insertImages($item);
            $this->insertAvailability($item);
            $this->insertPricing($item);
        }

        // Inserts children
        if ($is_update) {
            $query = 'UPDATE products_children SET deleted = 1 WHERE parentid = :parent_identifier';
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute([':parent_identifier' => $item->getIdentifier()]);
        }
        $query = 'INSERT INTO products_children(productid, parentid) VALUES(:child_identifier, :parent_identifier)';
        $sth = $this->connection->getDbh()->prepare($query);
        foreach ($item->accept($this->item_get_children) as $child) {
            $this->create($child);
            $sth->execute([
                ':child_identifier' => $child->getIdentifier(),
                ':parent_identifier' => $item->getIdentifier()
            ]);
        }
        return $this;
    }

    public function read(int $identifier): ItemInterface {
        return $this->item_reader->read($identifier);
    }

    public function update(ItemInterface $item): ItemMapperInterface {
        return $this->create($item);
    }

    public function delete(ItemInterface $item): ItemMapperInterface {
        $sth = $this->connection->getDbh()->prepare('DELETE FROM products_attr_text WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM products_categories WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM products_images WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM stocks WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM prices WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM products_children WHERE productid = :identifier OR parentid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('DELETE FROM products WHERE id = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        return $this;
    }

    public function softDelete(ItemInterface $item): self {
        $sth = $this->connection->getDbh()->prepare('UPDATE products SET deleted = 1 WHERE id = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $this->clear($item);
        $sth = $this->connection->getDbh()->prepare('UPDATE products_children SET deleted = 1 WHERE productid = :identifier OR parentid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        return $this;
    }

    public function search(): array {
        return $this->item_reader->search();
    }

    public function searchByExternalIdentifier(string $external_identifier): ItemInterface {
        return $this->item_reader->searchByExternalIdentifier($external_identifier);
    }

    private function initializeAttributeLookup(): self {
        $query = 'SELECT id, label FROM products_attr_label WHERE storeviewid = :shop_identifier AND deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        while ($row = $sth->fetch()) {
            $this->attribute_lookup[$row['label']] = $row['id'];
        }
        return $this;
    }

    private function initializeImageLabelLookup(): self {
        foreach ($this->image_label_mapper->search() as $image_label) {
            $this->image_label_lookup[$image_label->getLabel()] = $image_label->getIdentifier();
        }
        return $this;
    }

    private function insertAttributes(ProductInterface $item): self {
        $clauses = [];
        $binders = [];
        foreach ($item->getAttributesAsArray() as $attribute) {
            $name = strtolower(trim(str_replace(' ', '_', $attribute->getName())));
            if (!isset($this->attribute_lookup[$name])) {
                $query = 'INSERT INTO products_attr_label(label, storeviewid) '
                    . 'VALUES(:label, :shop_identifier) '
                    . 'ON DUPLICATE KEY UPDATE deleted = 0';
                $sth = $this->connection->getDbh()->prepare($query);
                $sth->execute([
                    ':label' => $attribute->getName(),
                    ':shop_identifier' => $this->connection->getShopIdentifier()
                ]);
                $this->attribute_lookup[$name] = $this->connection->getDbh()->lastInsertId();
            }
            foreach ($attribute->getValuesAsArray() as $value) {
                $progressive_identifier = count($binders) / 4 + 1;
                $clauses[] = '(:label_identifier_'  . $progressive_identifier
                    . ', :product_identifier_' . $progressive_identifier
                    . ', :value_' . $progressive_identifier
                    . ', :is_configurable_' . $progressive_identifier . ')'
                ;
                $binders[':label_identifier_' . $progressive_identifier] = $this->attribute_lookup[$name];
                $binders[':product_identifier_' . $progressive_identifier] = $item->getIdentifier();
                $binders[':value_' . $progressive_identifier] = $value;
                $binders[':is_configurable_' . $progressive_identifier] = $attribute->isConfigurable() ? 1 : 0;
            }
        }
        if (!empty($clauses)) {
            $query = 'INSERT INTO products_attr_text(labelid, productid, value, configurable) VALUES '
                . implode(', ', $clauses);
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute($binders);
        }
        return $this;
    }

    private function insertCategories(ProductInterface $item): self {
        $clauses = [];
        $binders = [];
        foreach ($item->getCategoriesAsArray() as $category) {
            $progressive_identifier = count($clauses) + 1;
            $clauses[] = '(:category_identifier_' . $progressive_identifier
                . ', :product_identifier_' . $progressive_identifier . ')'
            ;
            $binders[':category_identifier_' . $progressive_identifier] = $category->getIdentifier();
            $binders[':product_identifier_' . $progressive_identifier] = $item->getIdentifier();
        }
        if (!empty($clauses)) {
            $query = 'INSERT INTO products_categories(categoryid, productid) VALUES '
                . implode(', ', $clauses);
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute($binders);
        }
        return $this;
    }

    private function insertImages(ProductInterface $item): self {
        $clauses = [];
        $binders = [];
        $push = function (string $url, int $label_identifier, int $position) use($item, &$clauses, &$binders) {
            $progressive_identifier = count($clauses) + 1;
            $clauses[] = '(:product_identifier_' . $progressive_identifier
                . ', :product_external_identifier_' . $progressive_identifier
                . ', :label_identifier_' . $progressive_identifier
                . ', :sort_' . $progressive_identifier
                . ', :url_' . $progressive_identifier . ')'
            ;
            $binders[':product_identifier_' . $progressive_identifier] = $item->getIdentifier();
            $binders[':product_external_identifier_' . $progressive_identifier] = $item->getExternalIdentifier();
            $binders[':label_identifier_' . $progressive_identifier] = $label_identifier;
            $binders[':sort_' . $progressive_identifier] = $position;
            $binders[':url_' . $progressive_identifier] = $url;
        };
        foreach ($item->getImagesAsArray() as $image) {
            if (!isset($this->image_label_lookup[$image->getLabel()])) {
                $image_label = new ImageLabelSubject(null, $image->getLabel(), true);
                $this->image_label_mapper->create($image_label);
                $this->image_label_lookup[$image->getLabel()] = $image_label->getIdentifier();
            }
            $push($image->getUrl(), $this->image_label_lookup[$image->getLabel()], $image->getPosition());
        }
        if (!empty($clauses)) {
            $query = 'INSERT INTO products_images(productid, externalproductidstr, labelid, sort, url) VALUES '
                . implode(', ', $clauses);
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute($binders);
        }
        return $this;
    }

    private function insertAvailability(StockableInterface $item): self {
        $clauses = [];
        $binders = [];
        foreach ($item->getAvailability()->asArray() as $stock) {
            $progressive_identifier = count($clauses) + 1;
            $clauses[] = '(:warehouse_identifier_' . $progressive_identifier
                . ', :product_identifier_' . $progressive_identifier
                . ', :quantity_' . $progressive_identifier
                . ', :is_unlimited_' . $progressive_identifier . ')'
            ;
            $quantity = $stock->getQuantity()->accept($this->quantity_converter);
            $binders[':warehouse_identifier_' . $progressive_identifier] = $stock->getWarehouse()->getIdentifier();
            $binders[':product_identifier_' . $progressive_identifier] = $item->getIdentifier();
            $binders[':quantity_' . $progressive_identifier] = $quantity['quantity'];
            $binders[':is_unlimited_' . $progressive_identifier] = $quantity['is_unlimited'] ? 1 : 0;
        }
        if (!empty($clauses)) {
            $query = 'INSERT INTO stocks(wharehouseid, productid, quantity, unlimited) VALUES '
                . implode(', ', $clauses);
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute($binders);
        }
        return $this;
    }

    private function insertPricing(SellableInterface $item): self {
        $clauses = [];
        $binders = [];
        foreach ($item->getPricing()->asArray() as $price) {
            $progressive_identifier = count($clauses) + 1;
            $clauses[] = '(:group_identifier_' . $progressive_identifier
                . ', :currency_' . $progressive_identifier
                . ', :listing_price_' . $progressive_identifier
                . ', :selling_price_' . $progressive_identifier
                . ', :product_identifier_' . $progressive_identifier
                . ', :product_external_identifier_' . $progressive_identifier
                . ', :minimum_quantity_' . $progressive_identifier . ')'
            ;
            $binders[':group_identifier_' . $progressive_identifier] = $price->getCustomerGroup()->getIdentifier();
            $binders[':currency_' . $progressive_identifier] = $price->getCurrency();
            $binders[':listing_price_' . $progressive_identifier] = $price->getListingPrice();
            $binders[':selling_price_' . $progressive_identifier] = $price->getSellingPrice();
            $binders[':product_identifier_' . $progressive_identifier] = $item->getIdentifier();
            $binders[':product_external_identifier_' . $progressive_identifier] = $item->getExternalIdentifier();
            $binders[':minimum_quantity_' . $progressive_identifier] = $price->getMinimumQuantity();
        }
        if (!empty($clauses)) {
            $query = 'INSERT INTO prices(groupid, currency, price, specialprice, productid, externalproductidstr, quantityfrom) VALUES '
                . implode(', ', $clauses);
            $sth = $this->connection->getDbh()->prepare($query);
            $sth->execute($binders);
        }
        return $this;
    }

    private function clear(ItemInterface $item): self {
        $sth = $this->connection->getDbh()->prepare('UPDATE products_attr_text SET deleted = 1 WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('UPDATE products_categories SET deleted = 1 WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('UPDATE products_images SET deleted = 1 WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('UPDATE stocks SET deleted = 1 WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        $sth = $this->connection->getDbh()->prepare('UPDATE prices SET deleted = 1 WHERE productid = :identifier');
        $sth->execute([':identifier' => $item->getIdentifier()]);
        return $this;
    }
}
