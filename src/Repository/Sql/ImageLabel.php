<?php
namespace AccelaSearch\ProductMapper\Repository\Sql;
use \OutOfBoundsException;
use \AccelaSearch\ProductMapper\ImageLabel as Subject;
use \AccelaSearch\ProductMapper\Repository\ImageLabelInterface;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Connection;
use \AccelaSearch\ProductMapper\DataMapper\Sql\ImageLabel as DataMapper;

class ImageLabel {
    private $mapper;
    private $image_labels;

    public function __construct(DataMapper $mapper) {
        $this->mapper = $mapper;
        $this->image_labels = [];
        foreach ($mapper->search() as $image_label) {
            $this->image_labels[$image_label->getIdentifier()] = $image_label;
        }
    }

    public static function fromConnection(Connection $connection): self {
        return new ImageLabel(DataMapper::fromConnection($connection));
    }
    
    public function read(int $identifier): Subject {
        if (!isset($this->image_labels[$identifier])) {
            $this->image_labels[$identifier] = $this->mapper->read($identifier);
        }
        return $this->image_labels[$identifier];
    }

    public function search(callable $criterion): array {
        return array_values(array_filter($this->image_labels, $criterion));
    }
}
