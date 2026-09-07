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

    public function startSynchronization(string $shop_uuid): self {
        $path = '/API/shops/' . $shop_uuid . '/synchronization';
        $this->post(Request::fromPath($path));
        return $this;
    }

    public function endSynchronization(string $shop_uuid): self {
        $path = '/API/shops/' . $shop_uuid . '/synchronization';
        $this->delete(Request::fromPath($path));
        return $this;
    }

    public function index(string $shop_uuid): self {
        $path = '/API/shops/' . $shop_uuid . '/index';
        $this->post(Request::fromPath($path));
        return $this;
    }

    public function convertShopIndentifier(int $collector_shop_identifier): string {
        $path = '/API/shops/' . $collector_shop_identifier . '/convert';
        $response = $this->get(Request::fromPath($path));
        $body = $response->getBodyAsArray();
        return $body['shopUuid'];
    }
}