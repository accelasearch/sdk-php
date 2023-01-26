<?php
namespace AccelaSearch\ProductMapper\Repository\Memory;
use \OutOfBoundsException;
use \BadFunctionCallException;
use \AccelaSearch\ProductMapper\AttributeLabel as Subject;
use \AccelaSearch\ProductMapper\Repository\AttributeLabelInterface;

class AttributeLabel implements AttributeLabelInterface {
    private $attribute_labels;

    public function __construct() {
        $this->attribute_labels = [];
    }

    public function read(int $identifier): Subject {
        if (!isset($this->attribute_labels[$identifier])) {
            throw new OutOfBoundsException('No group with identifier ' . $identifier);
        }
        return $this->attribute_labels[$identifier];
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->attribute_labels, $criterion));
    }
}
