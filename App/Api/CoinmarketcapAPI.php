<?php

namespace App\Api;

use App\Models\Currency;
use GuzzleHttp\Client;

class CoinmarketcapAPI implements ApiClientInterface
{

    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'timeout' => 5.0,
        ]);
        $this->apiKey = $apiKey;
    }

    public function getTopCryptos(int $limit = 10): array
    {
        $response = $this->client->request('GET', 'cryptocurrency/listings/latest', [
            'query' => [
                'start' => '1',
                'limit' => $limit,
                'convert' => 'USD'
            ],
            'headers' => [
                'X-CMC_PRO_API_KEY' => $this->apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);


        $topCoins = $data['data'];
        $result = [];

        foreach ($topCoins as $coin) {
            $coinDetailsResponse = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
                'query' => [
                    'symbol' => $coin['symbol'],
                    'convert' => 'USD'
                ],
                'headers' => [
                    'X-CMC_PRO_API_KEY' => $this->apiKey,
                ],
            ]);

            $coinDetails = json_decode($coinDetailsResponse->getBody(), true);
            $coinDetail = $coinDetails['data'][$coin['symbol']];

            $currency = new Currency(
                $coin['name'],
                $coin['symbol'],
                $coinDetail['quote']['USD']['price'],
                $coin['cmc_rank'],
            );
            $result[] = $currency;
        }

        return $result;


    }

    public function getCryptoBySymbol(string $symbol): Currency
    {

        $response = $this->client->request('GET', 'cryptocurrency/quotes/latest', [
            'query' => [
                'symbol' => $symbol,
                'convert' => 'USD'
            ],
            'headers' => [
                'X-CMC_PRO_API_KEY' => $this->apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);


        $coinDetail = $data['data'][$symbol];
        return new Currency(
            $coinDetail['name'],
            $coinDetail['symbol'],
            $coinDetail['quote']['USD']['price'],
            $coinDetail ['cmc_rank'] ?? null,
        );

    }
}