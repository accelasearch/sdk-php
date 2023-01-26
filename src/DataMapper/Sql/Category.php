<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \InvalidArgumentException;
use \AccelaSearch\ProductMapper\Category as Subject;

class Category {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function fromConnection(Connection $connection): self {
        return new Category($connection);
    }

    public function create(Subject $category): self {
        $query = 'INSERT INTO categories(parentid, storeviewid, categoryname, fullcategoryname, externalidstr, url) '
            . 'VALUES(:parent_identifier, :shop_identifier, :name, :full_name, :external_identifier, :url) '
            . 'ON DUPLICATE KEY UPDATE categoryname = :name, fullcategoryname = :full_name, url = :url, deleted = 0';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':parent_identifier' => $category->getParent() ? $category->getParent()->getIdentifier() : null,
            ':shop_identifier' => $this->connection->getShopIdentifier(),
            ':name' => $category->getName(),
            ':full_name' => $category->getFullName(),
            ':external_identifier' => $category->getExternalIdentifier(),
            ':url' => $category->getUrl()
        ]);
        $category->setIdentifier($this->connection->getDbh()->lastInsertId());
        return $this;
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, parentid, categoryname, fullcategoryname, externalid, externalidstr, url FROM categories WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No category with identifier ' . $identifier);
        }
        return $this->rowToCategory($row);
    }

    public function update(Subject $category): self {
        $query = 'UPDATE categories SET parentid = :parent_identifier, categoryname = :name, fullcategoryname = :full_name, externalidstr = :external_identifier, url = :url, deleted = 0 WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':identifier' => $category->getIdentifier(),
            ':parent_identifier' => $category->getParent() ? $category->getParent()->getIdentifier() : null,
            ':name' => $category->getName(),
            ':full_name' => $category->getFullName(),
            ':external_identifier' => $category->getExternalIdentifier(),
            ':url' => $category->getUrl()
        ]);
        return $this;
    }

    public function delete(Subject $category): self {
        $query = 'DELETE FROM categories WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $category->getIdentifier()]);
        return $this;
    }

    public function softDelete(Subject $category): self {
        $query = 'UPDATE categories SET deleted = 1 WHERE id = :identifier AND deleted != 1';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $category->getIdentifier()]);
        return $this;
    }

    public function search(): array {
        $query = 'SELECT id, parentid, categoryname, fullcategoryname, externalid, externalidstr, url FROM categories WHERE storeviewid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        $categories = [];
        while ($row = $sth->fetch()) {
            $categories[] = $this->rowToCategory($row);
        }
        return $categories;
    }

    private function rowToCategory(array $row): Subject {
        $external_identifier = !empty($row['externalid']) ? $row['externalid'] : $row['externalidstr'];
        if (is_null($external_identifier)) {
            throw new InvalidArgumentException('Missing external identifier of category.');
        }
        $category = new Subject(
            $external_identifier,
            $row['categoryname'],
            $row['parentid'] ? $this->read($row['parentid']) : null
        );
        $category->setIdentifier($row['id']);
        $category->setFullName($row['fullcategoryname']);
        if ($row['url']) {
            $category->setUrl($row['url']);
        }
        return $category;
    }
}
