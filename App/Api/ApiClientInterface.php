<?php

namespace App\Api;

use App\Models\Currency;

interface ApiClientInterface
{
    public function getTopCryptos(int $limit = 10): array;

    public function getCryptoBySymbol(string $symbol): Currency;
}