<?php
namespace AccelaSearch\ProductMapper\Api;

class Shop {
    use ApiTrait;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public static function fromClient(Client $client): self {
        return new Shop($client);
    }

    public function notify(): self {
        $path = '/API/shops/notify';
        $this->post(Request::fromPath($path));
        return $this;
    }

    public function startSynchronization(int $shop_identifier): self {
        $path = '/API/shops/' . $shop_identifier . '/synchronization';
        $this->post(Request::fromPath($path));
        return $this;
    }

    public function endSynchronization(int $shop_identifier): self {
        $path = '/API/shops/' . $shop_identifier . '/synchronization';
        $this->delete(Request::fromPath($path));
        return $this;
    }

    public function index(int $shop_identifier): self {
        $path = '/API/shops/' . $shop_identifier . '/index';
        $this->post(Request::fromPath($path));
        return $this;
    }

    public function convertShopIndentifier(int $collector_shop_identifier): int {
        $path = '/API/shops/' . $collector_shop_identifier . '/convert';
        $response = $this->get(Request::fromPath($path));
        $body = $response->getBodyAsArray();
        return $body['shopIdentifier'];
    }
}