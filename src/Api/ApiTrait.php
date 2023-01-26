<?php
namespace AccelaSearch\ProductMapper\Api;
use \RuntimeException;

trait ApiTrait {
    private $client;

    public function getClient(): Client {
        return $this->client;
    }

    private function get(Request $request): Response {
        $response = $this->client->get($request);
        $this->checkResponse($response);
        return $response;
    }

    private function post(Request $request): Response {
        $response = $this->client->post($request);
        $this->checkResponse($response);
        return $response;
    }

    private function put(Request $request): Response {
        $response = $this->client->put($request);
        $this->checkResponse($response);
        return $response;
    }

    private function delete(Request $request): Response {
        $response = $this->client->delete($request);
        $this->checkResponse($response);
        return $response;
    }

    private function checkResponse(Response $response): self {
        $body = $response->getBodyAsArray();
        if (isset($body['status']) && strtolower($body['status']) === 'error') {
            throw new RuntimeException(
                !empty($body['message']) ? $body['message'] : 'Unknown error'
            );
        }
        return $this;
    }
}