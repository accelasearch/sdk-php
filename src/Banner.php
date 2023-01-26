<?php
namespace AccelaSearch\ProductMapper;

class Banner implements ItemInterface {
    use ItemTrait;
    public const DEFAULT_SIZE = 1;
    private $desktop_image_url;
    private $mobile_image_url;
    private $size;

    public function __construct(
        string $url,
        string $desktop_image_url,
        string $mobile_image_url
    ) {
        $this->identifier = null;
        $this->sku = null;
        $this->url = $url;
        $this->desktop_image_url = $desktop_image_url;
        $this->mobile_image_url = $mobile_image_url;
    }

    public function getDesktopImageUrl(): string {
        return $this->desktop_image_url;
    }

    public function setDesktopImageUrl(string $url): self {
        $this->desktop_image_url = $url;
        return $this;
    }

    public function getMobileImageUrl(): string {
        return $this->mobile_image_url;
    }

    public function setMobileImageUrl(string $url): self {
        $this->mobile_image_url = $url;
        return $this;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function setSize(int $size): self {
        $this->size = $size;
        return $this;
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitBanner($this);
    }
}