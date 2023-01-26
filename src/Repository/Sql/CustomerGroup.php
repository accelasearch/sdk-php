<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\Price\CustomerGroup as Subject;
use \AccelaSearch\ProductMapper\Repository\CustomerGroupInterface;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use \AccelaSearch\ProductMapper\DataMapper\Sql\CustomerGroup as DataMapper;

class CustomerGroup {
    private $mapper;
    private $groups;

    public function __construct(DataMapper $mapper) {
        $this->mapper = $mapper;
        $this->groups = [];
        foreach ($mapper->search() as $group) {
            $this->groups[$group->getIdentifier()] = $group;
        }
    }

    public static function fromConnection(Connection $connection): self {
        return new CustomerGroup(DataMapper::fromConnection($connection));
    }

    public function insert(Subject $group): self {
        $this->mapper->create($group);
        $this->groups[$group->getIdentifier()] = $group;
        return $this;
    }

    public function read(int $identifier): Subject {
        if (!isset($this->groups[$identifier])) {
            $this->groups[$identifier] = $this->mapper->read($identifier);
        }
        return $this->groups[$identifier];
    }

    public function update(Subject $group): self {
        $this->mapper->update($group);
        $this->groups[$group->getIdentifier()] = $group;
        return $this;
    }

    public function delete(Subject $group): self {
        $this->mapper->softDelete($group);
        unset($this->groups[$group->getIdentifier()]);
        return $this;
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->groups, $criterion));
    }
}