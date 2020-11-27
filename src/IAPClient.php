<?php

namespace IAP\SDK;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use OAuth2ClientCredentials\OAuthClient;

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
     * @param string $productId
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

        return $this->request(function (PendingRequest $request) use ($params) {
            return $request->asJson()
                ->post($this->getUrl('/purchases'), $params);
        })
            ->throw()
            ->json();
    }

    /**
     * @param array $params ['page' => 1, 'limit' => 10, 'sort' => 'id', 'dir' => 'asc']
     * @return array[]
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getProducts($params = [])
    {
        return $this->request(function (PendingRequest $request) use ($params) {
            return $request->get(
                $this->getUrl('/products'),
                $params
            );
        })
            ->throw()
            ->json();
    }

    /**
     * @param int $productId
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getProduct($productId)
    {
        return $this->request(function (PendingRequest $request) use ($productId) {
            return $request->get(
                $this->getUrl('/products/' . $productId)
            );
        })
            ->throw()
            ->json();
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

        return $this->request(function (PendingRequest $request) use ($params) {
            return $request->post(
                $this->getUrl('/products'),
                $params
            );
        })
            ->throw()
            ->json();
    }

    /**
     * @param int $productId
     * @param array $params ['name' => '', 'price' => '', 'value' => '']
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateProduct($productId, $params = [])
    {
        return $this->request(function (PendingRequest $request) use ($productId, $params) {
            return $request->put(
                $this->getUrl('/products/' . $productId),
                $params
            );
        })
            ->throw()
            ->json();
    }
}