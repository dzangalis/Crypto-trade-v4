<?php

namespace App\Api;

use App\Models\Currency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class   CoinPaprikaApi implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.coinpaprika.com/v1/',
            'timeout' => 5.0,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getTopCryptos(int $limit = 10): array
    {

        $response = $this->client->request('GET', 'coins');

        $data = json_decode($response->getBody(), true);

        $topCoins = array_slice($data, 0, $limit);
        $result = [];

        foreach ($topCoins as $coin) {
            $coinDetailsResponse = $this->client->request('GET', 'tickers/' . $coin['id']);
            $coinDetails = json_decode($coinDetailsResponse->getBody(), true);

            $currency = new Currency(
                $coin['name'],
                $coin['symbol'],
                $coinDetails['quotes']['USD']['price'],
                $coinDetails['rank'],
            );
            $result[] = $currency;
        }

        return $result;
    }

    /**
     * @throws GuzzleException
     */
    public function getCryptoBySymbol(string $symbol): Currency
    {

        $response = $this->client->request('GET', 'coins');
        $coinsList = json_decode($response->getBody(), true);


        $coinId = null;
        foreach ($coinsList as $coin) {
            if (strtolower($coin['symbol']) === strtolower($symbol)) {
                $coinId = $coin['id'];
                break;
            }
        }


        $response = $this->client->request('GET', 'tickers/' . $coinId);
        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200) {
            return new Currency (
                $data['name'],
                $data['symbol'],
                $data['quotes']['USD']['price'],
                $data['rank'],
            );
        }
    }
}