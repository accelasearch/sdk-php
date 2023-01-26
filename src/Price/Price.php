<?php
namespace AccelaSearch\ProductMapper\Price;

class Price {
    public const DEFAULT_CURRENCY = 'EUR';
    public const DEFAULT_MINIMUM_QUANTITY = 0.0;
    private $listing_price;
    private $selling_price;
    private $currency;
    private $minimum_quantity;
    private $customer_group;

    public function __construct(
        float $listing_price,
        float $selling_price,
        string $currency,
        float $minimum_quantity,
        CustomerGroup $customer_group
    ) {
        $this->listing_price = $listing_price;
        $this->selling_price = $selling_price;
        $this->currency = $currency;
        $this->minimum_quantity = $minimum_quantity;
        $this->customer_group = $customer_group;
    }

    public static function fromListingPrice(float $price): self {
        return new Price(
            $price,
            $price,
            self::DEFAULT_CURRENCY,
            self::DEFAULT_MINIMUM_QUANTITY,
            CustomerGroup::fromDefault()
        );
    }

    public function getListingPrice(): float {
        return $this->listing_price;
    }

    public function setListingPrice(float $listing_price): self {
        $this->listing_price = $listing_price;
        return $this;
    }

    public function getSellingPrice(): float {
        return $this->selling_price;
    }

    public function setSellingPrice(float $selling_price): self {
        $this->selling_price = $selling_price;
        return $this;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function setCurrency(string $currency): self {
        $this->currency = $currency;
        return $this;
    }

    public function getMinimumQuantity(): float {
        return $this->minimum_quantity;
    }

    public function setMinimumQuantity(float $minimum_quantity): self {
        $this->minimum_quantity = $minimum_quantity;
        return $this;
    }

    public function getCustomerGroup(): CustomerGroup {
        return $this->customer_group;
    }

    public function setCustomerGroup(CustomerGroup $customer_group): self {
        $this->customer_group = $customer_group;
        return $this;
    }
}