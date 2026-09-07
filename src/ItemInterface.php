<?php
namespace AccelaSearch\ProductMapper;

interface ItemInterface {
    public function getIdentifier(): ?string;
    public function setIdentifier(string $identifier): self;
    public function getExternalIdentifier(): ?string;
    public function setExternalIdentifier(?string $external_identifier): self;
    public function getSku(): ?string;
    public function setSku(string $sku): self;
    public function getUrl(): string;
    public function setUrl(string $url): self;
    public function accept(VisitorInterface $visitor);
}