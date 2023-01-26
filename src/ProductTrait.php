<?php
namespace AccelaSearch\ProductMapper;

trait ProductTrait {
    use ItemTrait;
    use StockableTrait;
    use SellableTrait;

    private $categories;
    private $images;
    private $attributes;

    public function getCategoriesAsArray(): array {
        return array_values($this->categories);
    }

    public function addCategory(Category $category): ProductInterface {
        $this->categories[$category->getExternalIdentifier()] = $category;
        return $this;
    }

    public function removeCategory(Category $category): ProductInterface {
        unset($this->categories[$category->getExternalIdentifier()]);
        return $this;
    }

    public function clearCategories(): ProductInterface {
        $this->categories = [];
        return $this;
    }

    public function getImagesAsArray(): array {
        return array_values($this->images);
    }

    public function getImage(string $label): ?Image {
        return isset($this->images[$label])
            ? $this->images[$label]
            : null;
    }

    public function addImage(Image $image): ProductInterface {
        $this->images[$image->getLabel()] = $image;
        return $this;
    }

    public function removeImage(Image $image): ProductInterface {
        unset($this->images[$image->getLabel()]);
        return $this;
    }

    public function clearImages(): ProductInterface {
        $this->images = [];
        return $this;
    }

    public function getAttributesAsArray(): array {
        return array_values($this->attributes);
    }

    public function getAttribute(string $name): ?Attribute {
        return isset($this->attributes[$name])
            ? $this->attributes[$name]
            : null;
    }

    public function addAttribute(Attribute $attribute): ProductInterface {
        $this->attributes[$attribute->getName()] = $attribute;
        return $this;
    }

    public function removeAttribute(Attribute $attribute): ProductInterface {
        unset($this->attributes[$attribute->getName()]);
        return $this;
    }

    public function clearAttributes(): ProductInterface {
        $this->attributes = [];
        return $this;
    }
}