<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\Price\CustomerGroup as Subject;

class CustomerGroup {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function fromConnection(Connection $connection): self {
        return new CustomerGroup($connection);
    }

    public function create(Subject $group): self {
        $query = 'INSERT INTO users_groups(label, storeviewid) '
            . 'VALUES(:label, :shop_identifier)';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':label' => $group->getLabel(),
            ':shop_identifier' => $this->connection->getShopIdentifier()
        ]);
        $group->setIdentifier($this->connection->getDbh()->lastInsertId());
        return $this;
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, label FROM users_groups WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No customer group with identifier ' . $identifier);
        }
        return $this->rowToCustomerGroup($row);
    }

    public function update(Subject $group): self {
        $query = 'UPDATE users_groups SET label = :label, deleted = 0 WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':identifier' => $group->getIdentifier(),
            ':label' => $group->getLabel()
        ]);
        return $this;
    }

    public function delete(Subject $group): self {
        $query = 'DELETE FROM users_groups WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $group->getIdentifier()]);
        return $this;
    }

    public function softDelete(Subject $group): self {
        $query = 'UPDATE users_groups SET deleted = 1 WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $group->getIdentifier()]);
        return $this;
    }

    public function search(): array {
        $query = 'SELECT id, label FROM users_groups WHERE storeviewid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        $groups = [];
        while ($row = $sth->fetch()) {
            $groups[] = $this->rowToCustomerGroup($row);
        }
        return $groups;
    }

    private function rowToCustomerGroup(array $row): Subject {
        $group = new Subject($row['label']);
        $group->setIdentifier($row['id']);
        return $group;
    }
}