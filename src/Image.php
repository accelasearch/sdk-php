<?php
namespace AccelaSearch\ProductMapper;

class Image {
    private $label;
    private $url;
    private $position;

    public function __construct(
        string $label,
        string $url,
        int $position
    ) {
        $this->label = $label;
        $this->url = $url;
        $this->position = $position;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function setLabel(string $label): self {
        $this->label = $label;
        return $this;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }

    public function getPosition(): int {
        return $this->position;
    }

    public function setPosition(int $position): self {
        $this->position = $position;
        return $this;
    }
}