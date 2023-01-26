<?php
namespace AccelaSearch\ProductMapper\DataMapper\Api;
use \AccelaSearch\ProductMapper\Cms as Subject;
use \AccelaSearch\ProductMapper\Api\Client;
use \AccelaSearch\ProductMapper\Api\Request;

class Cms {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public static function fromClient(Client $client): self {
        return new Cms($client);
    }

    public function getClient(): Client {
        return $this->client;
    }

    public function search(): array {
        $response = $this->client->get(Request::fromPath('/API/cms'));
        $cms = [];
        foreach ($response->getBodyAsArray() as $cms_data) {
            $cms[] = new Subject($cms_data['id'], $cms_data['label'], $cms_data['version']);
        }
        return $cms;
    }
}