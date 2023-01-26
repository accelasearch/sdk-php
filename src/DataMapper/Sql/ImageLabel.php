<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\ImageLabel as Subject;

class ImageLabel {
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public static function fromConnection(Connection $connection): self {
        return new ImageLabel($connection);
    }

    public function create(Subject $image_label): self {
        $query = 'INSERT INTO products_images_lbl(label, storeviewid, deleted) VALUES(:label, :shop_identifier, 0)';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([
            ':label' => $image_label->getLabel(),
            ':shop_identifier' => $this->connection->getShopIdentifier(),
        ]);
        $image_label->setIdentifier($this->connection->getDbh()->lastInsertId());
        return $this;
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, label, deleted FROM products_images_lbl WHERE id = :identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No image label with identifier ' . $identifier);
        }
        return $this->rowToImageLabel($row);
    }

    public function search(): array {
        $query = 'SELECT id, label, deleted FROM products_images_lbl WHERE storeviewid = :shop_identifier';
        $sth = $this->connection->getDbh()->prepare($query);
        $sth->execute([':shop_identifier' => $this->connection->getShopIdentifier()]);
        $labels = [];
        while ($row = $sth->fetch()) {
            $labels[] = $this->rowToImageLabel($row);
        }
        return $labels;
    }

    private function rowToImageLabel(array $row): Subject {
        return new Subject(
            $row['id'],
            $row['label'],
            $row['deleted'] == 0
        );
    }
}
