<?php
namespace AccelaSearch\ProductMapper\Converter;
use \AccelaSearch\ProductMapper\ItemInterface as Subject;

interface ItemInterface {
    public function fromObject(Subject $item);
    public function toObject($item): Subject;
}