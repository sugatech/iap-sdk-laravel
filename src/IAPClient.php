<?php

namespace IAP\SDK;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use OAuth2ClientCredentials\OAuthClient;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IAPClient
{
    /**
     * @var OAuthClient
     */
    private $oauthClient;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * IAPClient constructor.
     * @param string $apiUrl
     */
    public function __construct($apiUrl)
    {
        $this->oauthClient = new OAuthClient(
            config('iap.oauth.url'),
            config('iap.oauth.client_id'),
            config('iap.oauth.client_secret')
        );
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param callable $handler
     * @return Response
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function request($handler)
    {
        $request = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->oauthClient->getAccessToken(),
            'Accept' => 'application/json',
        ])
            ->withoutVerifying();

        $response = $handler($request);

        if ($response->status() == 401) {
            $this->oauthClient->getAccessToken(true);
        }

        return $response;
    }

    /**
     * @param string $route
     * @return string
     */
    private function getUrl($route)
    {
        return $this->apiUrl . '/api/client/v1' . $route;
    }

    /**
     * @param string $owner
     * @param int $productId
     * @param string $type
     * @param string|array $receipt
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createPurchase($owner, $productId, $type, $receipt)
    {
        $params = [
            'owner' => $owner,
            'product_id' => $productId,
            'type' => $type,
            'receipt' => $receipt,
        ];

        $response = $this->request(function (PendingRequest $request) use ($params) {
            return $request->asJson()
                ->post($this->getUrl('/purchases'), $params);
        });

        if ($response->failed()) {
            throw new HttpException($response->status(), $response->json('message'));
        }

        return $response->json();
    }

    /**
     * @param array $params ['page' => 1, 'limit' => 10, 'sort' => 'id', 'dir' => 'asc']
     * @return array[]
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getProducts($params = [])
    {
        $response = $this->request(function (PendingRequest $request) use ($params) {
            return $request->get(
                $this->getUrl('/products'),
                $params
            );
        });

        if ($response->failed()) {
            throw new HttpException($response->status(), $response->json('message'));
        }

        return $response->json();
    }

    /**
     * @param int $productId
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getProduct($productId)
    {
        $response = $this->request(function (PendingRequest $request) use ($productId) {
            return $request->get(
                $this->getUrl('/products/' . $productId)
            );
        });

        if ($response->failed()) {
            throw new HttpException($response->status(), $response->json('message'));
        }

        return $response->json();
    }

    /**
     * @param string $storeKey
     * @param string $name
     * @param string $imageUrl
     * @param float $price
     * @param float $value
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createProduct($storeKey, $name, $imageUrl, $price, $value)
    {
        $params = [
            'store_key' => $storeKey,
            'name' => $name,
            'image_url' => $imageUrl,
            'price' => $price,
            'value' => $value
        ];

        $response = $this->request(function (PendingRequest $request) use ($params) {
            return $request->asJson()
                ->post($this->getUrl('/products'), $params);
        });

        if ($response->failed()) {
            throw new HttpException($response->status(), $response->json('message'));
        }

        return $response->json();
    }

    /**
     * @param int $productId
     * @param array $params ['name' => '', 'price' => '', 'value' => '']
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateProduct($productId, $params = [])
    {
        $response = $this->request(function (PendingRequest $request) use ($productId, $params) {
            return $request->asJson()
                ->put($this->getUrl('/products/' . $productId), $params);
        });

        if ($response->failed()) {
            throw new HttpException($response->status(), $response->json('message'));
        }

        return $response->json();
    }
}