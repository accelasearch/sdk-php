<?php
namespace AccelaSearch\ProductMapper\DataMapper\Sql;
use \PDO;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\Shop as Subject;
use \AccelaSearch\ProductMapper\Cms;

class Shop {
    private $dbh;

    public function __construct(PDO $dbh) {
        $this->dbh = $dbh;
    }

    public static function fromConnection(PDO $dbh): self {
        return new Shop($dbh);
    }

    public function create(Subject $shop): self {
        $query = 'INSERT INTO storeviews(url, description, langiso, siteid, storeid, viewid, hash, cmsid, cmsdata) '
            . 'VALUES(:url, :description, :language_iso, 1, 1, 1, :hash, :cms_identifier, :cms_data) '
            . 'ON DUPLICATE KEY UPDATE disabled = 0';
        $sth = $this->dbh->prepare($query);
        $sth->execute([
            ':url' => $shop->getUrl(),
            ':description' => $shop->getDescription(),
            ':language_iso' => $shop->getLanguageIso(),
            ':hash' => $shop->getHash(),
            ':cms_identifier' => $shop->getCms()->getIdentifier(),
            ':cms_data' => json_encode($shop->getCmsData())
        ]);
        $shop->setIdentifier($this->dbh->lastInsertId());
        return $this;
    }

    public function read(int $identifier): Subject {
        $query = 'SELECT id, url, description, langiso, cmsid, cmsdata, disabled, firstinit, lastsync, lastupdate '
            . 'FROM storeviews WHERE id = :identifier';
        $sth = $this->dbh->prepare($query);
        $sth->execute([':identifier' => $identifier]);
        $row = $sth->fetch();
        if (empty($row)) {
            throw new OutOfBoundsException('No shops with identifier ' . $identifier . '.');
        }
        return $this->rowToShop($row);
    }

    public function update(Subject $shop): self {
        $query = 'UPDATE storeviews SET url = :url, description = :description, langiso = :language_iso, hash = :hash, cmsid = :cms_identifier, cmsdata = :cms_data, disabled = :is_disabled, firstinit = :initialization_timestamp, lastsync = :last_synchronization_timestamp, lastupdate = :last_update_timestamp WHERE id = :identifier';
        $sth = $this->dbh->prepare($query);
        $sth->execute([
            ':identifier' => $shop->getIdentifier(),
            ':url' => $shop->getUrl(),
            ':description' => $shop->getDescription(),
            ':language_iso' => $shop->getLanguageIso(),
            ':hash' => $shop->getHash(),
            ':cms_identifier' => $shop->getCms()->getIdentifier(),
            ':cms_data' => json_encode($shop->getCmsData()),
            ':is_disabled' => $shop->isActive() ? 0 : 1,
            ':initialization_timestamp' => $shop->getInitializationTimestamp() ? date('Y-m-d H:i:s', $shop->getInitializationTimestamp()) : null,
            ':last_synchronization_timestamp' => $shop->getLastSynchronizationTimestamp() ? date('Y-m-d H:i:s', $shop->getLastSynchronizationTimestamp()) : null,
            ':last_update_timestamp' => $shop->getLastUpdateTimestamp() ? date('Y-m-d H:i:s', $shop->getLastUpdateTimestamp()) : null
        ]);
        return $this;
    }

    public function delete(Subject $shop): self {
        $query = 'DELETE FROM storeviews WHERE id = :identifier';
        $sth = $this->dbh->prepare($query);
        $sth->execute([':identifier' => $shop->getIdentifier()]);
        return $this;
    }

    public function search(): array {
        $query = 'SELECT id, url, description, langiso, cmsid, cmsdata, disabled, firstinit, lastsync, lastupdate '
            . 'FROM storeviews';
        $sth = $this->dbh->prepare($query);
        $sth->execute();
        $shops = [];
        while ($row = $sth->fetch()) {
            $shops[] = $this->rowToShop($row);
        }
        return $shops;
    }

    private function rowToShop(array $row): Subject {
        $shop = new Subject(
            $row['url'],
            $row['langiso'],
            new Cms($row['cmsid'], 'Stub', '1.0')
        );
        $shop->setIdentifier($row['id']);
        $shop->setIsActive($row['disabled'] == 0);
        if (!is_null($row['cmsdata'])) {
            $shop->setCmsData(json_decode($row['cmsdata'], true));
        }
        if (!empty($row['description'])) {
            $shop->setDescription($row['description']);
        }
        if (!empty($row['firstinit'])) {
            $shop->setInitializationTimestamp(strtotime($row['firstinit']));
        }
        if (!empty($row['lastsync'])) {
            $shop->setLastSynchronizationTimestamp(strtotime($row['lastsync']));
        }
        if (!empty($row['lastupdate'])) {
            $shop->setLastUpdateTimestamp(strtotime($row['lastupdate']));
        }
        return $shop;
    }
}
