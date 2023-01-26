<?php
namespace AccelaSearch\ProductMapper\Converter;

class Generic {
    private $converter_a;
    private $converter_b;

    public function __construct(
        ItemInterface $converter_a,
        ItemInterface $converter_b
    ) {
        $this->converter_a = $converter_a;
        $this->converter_b = $converter_b;
    }

    public function AToB($item) {
        return $this->converter_b->fromObject($this->converter_a->toObject($item));
    }

    public function BToA($item) {
        return $this->converter_a->fromObject($this->converter_b->toObject($item));
    }
}