<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \AccelaSearch\ProductMapper\Simple;
use \AccelaSearch\ProductMapper\Stock\Availability;
use \AccelaSearch\ProductMapper\Price\Pricing;
use \AccelaSearch\ProductMapper\Image;

final class SimpleTest extends TestCase {
    public function testDefaultIsActive() {
        $product = $this->create();
        $this->assertEquals('url', $product->getUrl());
    }

    private function create(): Simple {
        return new Simple('url', 'id', new Availability(), new Pricing());
    }
}
