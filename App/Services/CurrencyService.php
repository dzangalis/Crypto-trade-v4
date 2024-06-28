<?php

namespace App\Services;

use App\Api\ApiClientInterface;
use App\Database\SqliteDatabase;
use App\Models\Transaction;
use App\Models\Wallet;

class CurrencyService
{
    private ApiClientInterface $apiClient;
    private SqliteDatabase $database;

    public function __construct(
        ApiClientInterface $apiClient,
        SqliteDatabase     $database,
        WalletService      $walletService,
        string             $userId
    )
    {
        $this->apiClient = $apiClient;
        $this->database = $database;
        $this->walletService = $walletService;
        $this->userId = $userId;
    }

    public function buy(string $symbol, float $amount): void
    {
        $user = $this->database->getUserById($this->userId);

        $currency = $this->apiClient->getCryptoBySymbol($symbol);
        $totalCost = $currency->getPrice() * $amount;

        $wallet = $this->database->getWallet($this->userId);
        $currentBalance = $wallet['balance'];

        $transaction = new Transaction($this->userId, 'buy', $symbol, $amount, $currency->getPrice());
        $this->database->save($transaction);

        $newBalance = $currentBalance - ($currency->getPrice() * $amount);
        $updatedWallet = new Wallet($this->userId, $newBalance);
        $this->database->updateWallet($updatedWallet);
    }

    public function sell(string $symbol, float $amount): void
    {
        $user = $this->database->getUserById($this->userId);

        $currency = $this->apiClient->getCryptoBySymbol($symbol);
        $wallet = $this->database->getWallet($this->userId);

        $currentBalance = $wallet['balance'];

        $transaction = new Transaction($this->userId, 'sell', $symbol, $amount, $currency->getPrice());
        $this->database->save($transaction);

        $newBalance = $currentBalance + ($currency->getPrice() * $amount);
        $updatedWallet = new Wallet($this->userId, $newBalance);
        $this->database->updateWallet($updatedWallet);
    }

    public function getAllTransactions(): array
    {
        return $this->database->getAllTransactions();
    }
}