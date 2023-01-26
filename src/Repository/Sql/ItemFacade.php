<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;

use AccelaSearch\ProductMapper\Category as CategoryModel;
use AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface;
use AccelaSearch\ProductMapper\Price\CustomerGroup as CustomerGroupModel;
use AccelaSearch\ProductMapper\ItemInterface;
use AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use AccelaSearch\ProductMapper\DataMapper\Sql\Item as ItemMapper;
use AccelaSearch\ProductMapper\ProductInterface;

class ItemFacade {
    private $mapper;
    private $categories;
    private $warehouses;
    private $customer_groups;

    public function __construct(
        ItemMapper $mapper,
        Category $categories,
        Warehouse $warehouses,
        CustomerGroup $customer_groups
    ) {
        $this->mapper = $mapper;
        $this->categories = $categories;
        $this->warehouses = $warehouses;
        $this->customer_groups = $customer_groups;
    }

    public static function fromConnection(Connection $connection): self {
        return new ItemFacade(
            ItemMapper::fromConnection($connection),
            Category::fromConnection($connection),
            Warehouse::fromConnection($connection),
            CustomerGroup::fromConnection($connection)
        );
    }

    public function getMapper(): ItemMapper {
        return $this->mapper;
    }

    public function getCategories(): Category {
        return $this->categories;
    }

    public function getWarehouses(): Warehouse {
        return $this->warehouses;
    }

    public function getCustomerGroups(): CustomerGroup {
        return $this->customer_groups;
    }

    public function save(ItemInterface $item): self {
        if (!$item instanceof ProductInterface) {
            $this->mapper->create($item);
            return $this;
        }

        foreach ($item->getCategoriesAsArray() as $category) {
            $this->ensureCategory($category);
        }
        foreach ($item->getAvailability()->asArray() as $stock) {
            $this->ensureWarehouse($stock->getWarehouse());
        }
        foreach ($item->getPricing()->asArray() as $price) {
            $this->ensureCustomerGroup($price->getCustomerGroup());
        }
        $this->mapper->create($item);
        return $this;
    }

    private function ensureCategory(CategoryModel $category): self {
        if (empty($this->categories->search(function ($c) use($category) { return $c->getFullName() === $category->getFullName(); }))) {
            if (!is_null($category->getParent())) {
                $this->ensureCategory($category->getParent());
            }
            $this->categories->insert($category);
        }
        $category->setIdentifier($this->categories->search(function ($c) use($category) { return $c->getFullName() === $category->getFullName(); })[0]->getIdentifier());
        return $this;
    }

    private function ensureWarehouse(WarehouseInterface $warehouse): self {
        if (empty($this->warehouses->search(function ($w) use($warehouse) { return $w->getLabel() === $warehouse->getLabel(); }))) {
            $this->warehouses->insert($warehouse);
        }
        $warehouse->setIdentifier($this->warehouses->search(function ($w) use($warehouse) { return $w->getLabel() === $warehouse->getLabel(); })[0]->getIdentifier());
        return $this;
    }

    private function ensureCustomerGroup(CustomerGroupModel $customer_group): self {
        if (empty($this->customer_groups->search(function ($c) use($customer_group) { return $c->getLabel() === $customer_group->getLabel(); }))) {
            $this->customer_groups->insert($customer_group);
        }
        $customer_group->setIdentifier($this->customer_groups->search(function ($c) use($customer_group) { return $c->getLabel() === $customer_group->getLabel(); })[0]->getIdentifier());
        return $this;
    }
}
