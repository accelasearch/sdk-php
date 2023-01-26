<?php
namespace AccelaSearch\ProductMapper\Stock\Warehouse;

interface WarehouseInterface {
    public function getIdentifier(): ?int;
    public function setIdentifier(int $identifier): self;
    public function getLabel(): string;
    public function setLabel(string $label): self;
    public function accept(VisitorInterface $visitor);
}