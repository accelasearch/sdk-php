<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\AttributeLabel as Subject;

class AttributeLabel {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function fromConnection(Connection $connection): self {
        return new AttributeLabel($connection);
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, label, deleted FROM products_attr_label WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No attribute label with identifier ' . $identifier);
        }
        return $this->rowToAttributeLabel($row);
    }

    public function search(): array {
        $query = 'SELECT id, label, deleted FROM products_attr_label WHERE storeviewid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        $labels = [];
        while ($row = $sth->fetch()) {
            $labels[] = $this->rowToAttributeLabel($row);
        }
        return $labels;
    }

    private function rowToAttributeLabel(array $row): Subject {
        return new Subject(
            $row['id'],
            $row['label'],
            $row['deleted'] == 0
        );
    }
}
