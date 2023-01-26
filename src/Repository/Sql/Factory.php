<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;

class Factory {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function fromConnection(Connection $connection): self {
        return new Factory($connection);
    }

    public function createCategory(): Category {
        return Category::fromConnection($this->connection);
    }

    public function createWarehouse(): Warehouse {
        return Warehouse::fromConnection($this->connection);
    }

    public function createCustomerGroup(): CustomerGroup {
        return CustomerGroup::fromConnection($this->connection);
    }
}