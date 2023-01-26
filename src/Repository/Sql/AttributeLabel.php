<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\AttributeLabel as Subject;
use \AccelaSearch\ProductMapper\Repository\AttributeLabelInterface;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use \AccelaSearch\ProductMapper\DataMapper\Sql\AttributeLabel as DataMapper;

class AttributeLabel implements AttributeLabelInterface {
    private $mapper;
    private $attribute_labels;

    public function __construct(DataMapper $mapper) {
        $this->mapper = $mapper;
        $this->attribute_labels = [];
        foreach ($mapper->search() as $attribute_label) {
            $this->attribute_labels[$attribute_label->getIdentifier()] = $attribute_label;
        }
    }

    public static function fromConnection(Connection $connection): self {
        return new AttributeLabel(DataMapper::fromConnection($connection));
    }
    
    public function read(int $identifier): Subject {
        if (!isset($this->attribute_labels[$identifier])) {
            $this->attribute_labels[$identifier] = $this->mapper->read($identifier);
        }
        return $this->attribute_labels[$identifier];
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->attribute_labels, $criterion));
    }
}
