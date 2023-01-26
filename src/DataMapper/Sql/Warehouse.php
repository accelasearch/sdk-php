<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\Stock\Warehouse\WarehouseInterface as Subject;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Physical;
use \AccelaSearch\ProductMapper\Stock\Warehouse\Virtual;

class Warehouse {
    private $connection;
    private $visitor;

    public function __construct(
        Connection $connection,
        WarehouseVisitor $visitor
    ) {
        $this->connection = $connection;
        $this->visitor = $visitor;
    }

    public static function fromConnection(Connection $connection): self {
        return new Warehouse($connection, new WarehouseVisitor());
    }

    public function create(Subject $warehouse): self {
        $info = $warehouse->accept($this->visitor);
        $query = 'INSERT INTO warehouses(storeviewid, label, latitude, longitude, isvirtual) '
            . 'VALUES(:shop_identifier, :label, :latitude, :longitude, :is_virtual)';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':shop_identifier' => $this->connection->getShopIdentifier(),
            ':label' => $warehouse->getLabel(),
            ':latitude' => $info['latitude'],
            ':longitude' => $info['longitude'],
            ':is_virtual' => $info['is_virtual']
        ]);
        $warehouse->setIdentifier($this->connection->getDbh()->lastInsertId());
        return $this;
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, label, latitude, longitude, isvirtual FROM warehouses WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No warehouse with identifier ' . $identifier);
        }
        return $this->rowToWarehouse($row);
    }

    public function update(Subject $warehouse): self {
        $info = $warehouse->accept($this->visitor);
        $query = 'UPDATE warehouses SET label = :label, latitude = :latitude, longitude = :longitude, deleted = 0 WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':identifier' => $warehouse->getIdentifier(),
            ':label' => $warehouse->getLabel(),
            ':latitude' => $info['latitude'],
            ':longitude' => $info['longitude']
        ]);
        return $this;
    }

    public function delete(Subject $warehouse): self {
        $query = 'DELETE FROM warehouses WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $warehouse->getIdentifier()]);
        return $this;
    }

    public function softDelete(Subject $warehouse): self {
        $query = 'UPDATE warehouses SET deleted = 1 WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $warehouse->getIdentifier()]);
        return $this;
    }

    public function search(): array {
        $query = 'SELECT id, label, latitude, longitude, isvirtual FROM warehouses WHERE storeviewid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        $warehouses = [];
        while ($row = $sth->fetch()) {
            $warehouses[] = $this->rowToWarehouse($row);
        }
        return $warehouses;
    }

    private function rowToWarehouse(array $row): Subject {
        $warehouse = $row['isvirtual'] == 0
            ? new Physical($row['label'], $row['latitude'], $row['longitude'])
            : new Virtual($row['label']);
        $warehouse->setIdentifier($row['id']);
        return $warehouse;
    }
}