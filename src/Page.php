<?php
namespace AccelaSearch\ProductMapper;

class Page implements ItemInterface {
    use ItemTrait;

    public function __construct(string $url) {
        $this->identifier = null;
        $this->sku = null;
        $this->url = $url;
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitPage($this);
    }
}