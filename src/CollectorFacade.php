<?php
namespace AccelaSearch\ProductMapper;
use \PDO;
use \PDOException;
use \AccelaSearch\ProductMapper\Api\Client;
use \AccelaSearch\ProductMapper\DataMapper\Api\Collector as CollectorMapper;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Item as ItemMapper;
use \AccelaSearch\ProductMapper\Repository\Sql\Factory as RepositoryFactory;
use \AccelaSearch\ProductMapper\DataMapper\Sql\ItemIsProductVisitor;
use \AccelaSearch\ProductMapper\DataMapper\Sql\ItemGetChildrenVisitor;

class CollectorFacade {
    private $dbh;
    private $categories;
    private $warehouses;
    private $groups;
    private $items;

    public function __construct(
        Client $client,
        int $shop_identifier
    ) {
        $collector_mapper = new CollectorMapper($client);
        $collector = $collector_mapper->read();
        $dbh = new PDO(
            'mysql:host=' . $collector->getHostName() . ';dbname=' . $collector->getDatabaseName(),
            $collector->getUsername(),
            $collector->getPassword(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        $connection = new Connection($dbh, $shop_identifier);
        $repository_factory = RepositoryFactory::fromConnection($connection);
        $this->dbh = $dbh;
        $this->categories = $repository_factory->createCategory();
        $this->warehouses = $repository_factory->createWarehouse();
        $this->groups = $repository_factory->createCustomerGroup();
        $this->items = ItemMapper::fromConnection($connection);
    }

    public function save(ProductInterface $item): self {
        $this->dbh->beginTransaction();
        try {
            if ($item->accept(new ItemIsProductVisitor)) {
                $this->ensureCategories($item)->ensureWarehouses($item)->ensureGroups($item);
            }
            $this->items->update($item);
        }
        catch (PDOException $e) {
            $this->dbh->rollBack();
            throw $e;
        }
        $this->dbh->commit();
        return $this;
    }

    public function load(int $identifier): ItemInterface {
        return $this->items->read($identifier);
    }

    public function searchByExternalIdentifier(string $external_identifier): ItemInterface {
        return $this->items->searchByExternalIdentifier($external_identifier);
    }

    public function delete(ItemInterface $item): self {
        $this->items->softDelete($item);
        return $this;
    }

    private function ensureCategories(ProductInterface $item): self {
        foreach ($item->getCategoriesAsArray() as $category) {
            $categories = $this->categories->search(function ($c) use ($category) {
                return $c->getExternalIdentifier() === $category->getExternalIdentifier();
            });
            if (empty($categories)) {
                $this->categories->insert($category);
            }
            else {
                $category->setIdentifier($categories[0]->getIdentifier());
            }
        }
        foreach ($item->accept(new ItemGetChildrenVisitor()) as $child) {
            $this->ensureCategories($child);
        }
        return $this;
    }

    private function ensureWarehouses(StockableInterface $item): self {
        foreach ($item->getAvailability()->asArray() as $stock) {
            $warehouses = $this->warehouses->search(function ($warehouse) use ($stock) {
                return $warehouse->getLabel() === $stock->getWarehouse()->getLabel();
            });
            if (empty($warehouses)) {
                $this->warehouses->insert($stock->getWarehouse());
            }
            else {
                $stock->getWarehouse()->setIdentifier($warehouses[0]->getIdentifier());
            }
        }
        foreach ($item->accept(new ItemGetChildrenVisitor()) as $child) {
            $this->ensureWarehouses($child);
        }
        return $this;
    }

    private function ensureGroups(SellableInterface $item): self {
        foreach ($item->getPricing()->asArray() as $price) {
            $groups = $this->groups->search(function ($group) use ($price) {
                return $group->getLabel() === $price->getCustomerGroup()->getLabel();
            });
            if (empty($groups)) {
                $this->groups->insert($price->getCustomerGroup());
            }
            else {
                $price->getCustomerGroup()->setIdentifier($groups[0]->getIdentifier());
            }
        }
        foreach ($item->accept(new ItemGetChildrenVisitor()) as $child) {
            $this->ensureGroups($child);
        }
        return $this;
    }
}
