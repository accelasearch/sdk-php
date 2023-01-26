<?php
namespace AccelaSearch\ProductMapper;
use \AccelaSearch\ProductMapper\Stock\Availability;
use \AccelaSearch\ProductMapper\Price\Pricing;

class Grouped extends Bundle {
    public function __construct(
        string $url,
        string $external_identifier,
        Availability $availability,
        Pricing $pricing
    ) {
        parent::__construct($url, $external_identifier, $availability, $pricing);
    }

    public function accept(VisitorInterface $visitor) {
        return $visitor->visitGrouped($this);
    }
}