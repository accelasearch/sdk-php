<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \AccelaSearch\ProductMapper\Cms;

final class CmsTest extends TestCase {
    public function testIdentifier() {
        $cms = new Cms(1, 'name', 'version');
        $this->assertEquals(1, $cms->getIdentifier());
    }

    public function testName() {
        $cms = new Cms(1, 'name', 'version');
        $this->assertEquals('name', $cms->getName());
    }

    public function testVersion() {
        $cms = new Cms(1, 'name', 'version');
        $this->assertEquals('version', $cms->getVersion());
    }
}
