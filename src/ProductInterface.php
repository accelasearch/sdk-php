<?php
namespace AccelaSearch\ProductMapper;

interface ProductInterface extends ItemInterface, StockableInterface, SellableInterface {
    public function getCategoriesAsArray(): array;
    public function addCategory(Category $category): self;
    public function removeCategory(Category $category): self;
    public function clearCategories(): self;
    public function getImagesAsArray(): array;
    public function getImage(string $label): ?Image;
    public function addImage(Image $image): self;
    public function removeImage(Image $image): self;
    public function clearImages(): self;
    public function getAttributesAsArray(): array;
    public function getAttribute(string $name): ?Attribute;
    public function addAttribute(Attribute $attribute): self;
    public function removeAttribute(Attribute $attribute): self;
    public function clearAttributes(): self;
}