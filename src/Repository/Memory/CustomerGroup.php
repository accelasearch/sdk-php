<?php
namespace AccelaSearch\ProductMapper\Repository\Memory;
use \OutOfBoundsException;
use \BadFunctionCallException;
use \AccelaSearch\ProductMapper\Price\CustomerGroup as Subject;
use \AccelaSearch\ProductMapper\Repository\CustomerGroupInterface;

class CustomerGroup implements CustomerGroupInterface {
    private $groups;

    public function __construct() {
        $this->groups = [];
    }

    public function insert(Subject $group): self {
        if (is_null($group->getIdentifier())) {
            throw new BadFunctionCallException('Missing identifier');
        }
        $this->groups[$group->getIdentifier()] = $group;
        return $this;
    }

    public function read(int $identifier): Subject {
        if (!isset($this->groups[$identifier])) {
            throw new OutOfBoundsException('No group with identifier ' . $identifier);
        }
        return $this->groups[$identifier];
    }

    public function update(Subject $group): self {
        return $this->insert($group);
    }

    public function delete(Subject $group): self {
        unset($this->groups[$group->getIdentifier()]);
        return $this;
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->groups, $criterion));
    }
}
