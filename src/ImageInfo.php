<?php
namespace AccelaSearch\ProductMapper;

class ImageInfo {
    private $main;
    private $over;
    private $other;

    public function __construct() {
        $this->main = null;
        $this->over = null;
        $this->other = [];
    }

    public function getMain(): ?string {
        return $this->main;
    }

    public function setMain(string $url): self {
        $this->main = $url;
        return $this;
    }

    public function getOver(): ?string {
        return $this->over;
    }

    public function setOver(string $url): self {
        $this->over = $url;
        return $this;
    }

    public function getOtherAsArray(): array {
        return array_values($this->other);
    }

    public function addOther(string $url): self {
        $this->other[] = $url;
        return $this;
    }

    public function removeOther(string $url): self {
        $key = array_search($url, $this->other);
        if ($key !== false) {
            unset($this->other[$key]);
        }
        return $this;
    }

    public function clearOther(): self {
        return $this;
    }
}