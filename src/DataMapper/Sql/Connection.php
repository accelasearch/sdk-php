<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \PDO;
use \AccelaSearch\ProductMapper\Shop;
use \AccelaSearch\ProductMapper\Collector;

class Connection {
    private $dbh;
    private $shop_identifier;

    public function __construct(PDO $dbh, int $shop_identifier) {
        $this->dbh = $dbh;
        $this->shop_identifier = $shop_identifier;
    }

    public static function fromShopAndCollector(Shop $shop, Collector $collector): self {
        $dbh = new PDO(
            'mysql:host=' . $collector->getHostName() . ';dbname=' . $collector->getDatabaseName(),
            $collector->getUsername(),
            $collector->getPassword(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return new Connection($dbh, $shop->getIdentifier());
    }

    public function getDbh(): PDO {
        return $this->dbh;
    }

    public function getShopIdentifier(): int {
        return $this->shop_identifier;
    }
}