<?php
namespace AccelaSearch\ProductMapper;

class Shop {
    public const DEFAULT_IS_ACTIVE = false;
    private $is_active;
    private $identifier;
    private $url;
    private $description;
    private $language_iso;
    private $cms;
    private $cms_data;
    private $initialization_timestamp;
    private $last_synchronization_timestamp;
    private $last_update_timestamp;

    public function __construct(
        string $url,
        string $language_iso,
        Cms $cms
    ) {
        $this->is_active = self::DEFAULT_IS_ACTIVE;
        $this->identifier = null;
        $this->url = $url;
        $this->description = null;
        $this->language_iso = $language_iso;
        $this->cms = $cms;
        $this->cms_data = [];
        $this->initialization_timestamp = null;
        $this->last_synchronization_timestamp = null;
        $this->last_update_timestamp = null;
    }

    public function isActive(): bool {
        return $this->is_active;
    }

    public function getIdentifier(): ?int {
        return $this->identifier;
    }

    public function setIdentifier(int $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }

    public function getHash(): string {
        return md5($this->url . $this->language_iso);
    }

    public function setIsActive(bool $is_active): self {
        $this->is_active = $is_active;
        return $this;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }

    public function getLanguageIso(): string {
        return $this->language_iso;
    }

    public function setLanguageIso(string $language_iso): self {
        $this->language_iso = $language_iso;
        return $this;
    }

    public function getCms(): Cms {
        return $this->cms;
    }

    public function setCms(Cms $cms): self {
        $this->cms = $cms;
        return $this;
    }

    public function getCmsData(): array {
        return $this->cms_data;
    }

    public function setCmsData(array $cms_data): self {
        $this->cms_data = $cms_data;
        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getInitializationTimestamp(): ?int {
        return $this->initialization_timestamp;
    }

    public function setInitializationTimestamp(int $timestamp): self {
        $this->initialization_timestamp = $timestamp;
        return $this;
    }

    public function getLastSynchronizationTimestamp(): ?int {
        return $this->last_synchronization_timestamp;
    }

    public function setLastSynchronizationTimestamp(int $timestamp): self {
        $this->last_synchronization_timestamp = $timestamp;
        return $this;
    }

    public function getLastUpdateTimestamp(): ?int {
        return $this->last_update_timestamp;
    }

    public function setLastUpdateTimestamp(int $timestamp): self {
        $this->last_update_timestamp = $timestamp;
        return $this;
    }
}
