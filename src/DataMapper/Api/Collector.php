<?php
namespace AccelaSearch\ProductMapper\DataMapper\Api;
use \RuntimeException;
use \AccelaSearch\ProductMapper\Collector as Subject;
use \AccelaSearch\ProductMapper\Api\Client;
use \AccelaSearch\ProductMapper\Api\Request;

class Collector {
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public static function fromClient(Client $client): self {
        return new Collector($client);
    }

    public function getClient(): Client {
        return $this->client;
    }

    public function read(): Subject {
        $response = $this->client->get(Request::fromPath('/API/collector'));
        $body = $response->getBodyAsArray();
        if (isset($body['status']) && $body['status'] === 'ERROR') {
            throw new RuntimeException($body['message']);
        }
        return new Subject(
            $body['hostname'],
            $body['name'],
            $body['username'],
            $body['password']
        );
    }
}