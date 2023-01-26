<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \AccelaSearch\ProductMapper\Cms;
use \AccelaSearch\ProductMapper\Shop;

final class ShopTest extends TestCase {
    public function testDefaultIsActive() {
        $shop = $this->create();
        $this->assertEquals(false, $shop->isActive());
    }

    public function testIdentifier() {
        $shop = $this->create()->setIdentifier(1);
        $this->assertEquals(1, $shop->getIdentifier());
    }

    public function testUrl() {
        $data = 'http://www.shop.net';
        $shop = $this->create()->setUrl($data);
        $this->assertEquals($data, $shop->getUrl());
    }

    public function testLanguageIso() {
        $data = 'fr';
        $shop = $this->create()->setLanguageIso($data);
        $this->assertEquals($data, $shop->getLanguageIso());
    }

    public function testCms() {
        $data = new Cms(2, 'cms2', '2.0');
        $shop = $this->create()->setCms($data);
        $this->assertEquals($data, $shop->getCms());
    }

    public function testDescription() {
        $data = 'description';
        $shop = $this->create()->setDescription($data);
        $this->assertEquals($data, $shop->getDescription());
    }

    public function testInitializationTimestamp() {
        $data = 42;
        $shop = $this->create()->setInitializationTimestamp($data);
        $this->assertEquals($data, $shop->getInitializationTimestamp());
    }

    public function testLastSynchronizationTimestamp() {
        $data = 42;
        $shop = $this->create()->setLastSynchronizationTimestamp($data);
        $this->assertEquals($data, $shop->getLastSynchronizationTimestamp());
    }

    private function create(): Shop {
        return new Shop('http://www.shop.com', 'en', new Cms(1, 'cms', '1.0'));
    }
}
