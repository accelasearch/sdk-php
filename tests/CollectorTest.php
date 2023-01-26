<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \AccelaSearch\ProductMapper\Collector;

final class CollectorTest extends TestCase {
    public function testHostName() {
        $collector = new Collector('host', 'db', 'user', 'pass');
        $this->assertEquals('host', $collector->getHostName());
    }

    public function testDatabaseName() {
        $collector = new Collector('host', 'db', 'user', 'pass');
        $this->assertEquals('db', $collector->getDatabaseName());
    }

    public function testUsername() {
        $collector = new Collector('host', 'db', 'user', 'pass');
        $this->assertEquals('user', $collector->getUsername());
    }

    public function testPassword() {
        $collector = new Collector('host', 'db', 'user', 'pass');
        $this->assertEquals('pass', $collector->getPassword());
    }
}
