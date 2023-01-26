<?php
namespace AccelaSearch\ProductMapper;

trait ItemTrait {
    private $identifier;
    private $external_identifier;
    private $sku;
    private $url;

    public function getIdentifier(): ?int {
        return $this->identifier;
    }

    public function setIdentifier(int $identifier): ItemInterface {
        $this->identifier = $identifier;
        return $this;
    }

    public function getExternalIdentifier(): ?string {
        return $this->external_identifier;
    }

    public function setExternalIdentifier(?string $identifier): ItemInterface {
        $this->external_identifier = $identifier;
        return $this;
    }

    public function getSku(): ?string {
        return $this->sku;
    }

    public function setSku(string $sku): ItemInterface {
        $this->sku = $sku;
        return $this;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): ItemInterface {
        $this->url = $url;
        return $this;
    }
}