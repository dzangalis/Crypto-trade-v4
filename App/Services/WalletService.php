<?php

namespace App\Services;

use App\Api\ApiClientInterface;
use App\Database\SqliteDatabase;
use App\Models\Wallet;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class WalletService
{
    private const INITIAL_BALANCE = 1000.0;

    private Wallet $wallet;
    private SqliteDatabase $database;
    private ApiClientInterface $apiClient;
    private string $userId;

    public function __construct(Wallet $wallet, SqliteDatabase $database, ApiClientInterface $apiClient, string $userId)
    {
        $this->wallet = $wallet;
        $this->database = $database;
        $this->apiClient = $apiClient;
        $this->userId = $userId;
    }

    public function initializeWallet(): void
    {
        $newWallet = new Wallet($this->userId, self::INITIAL_BALANCE);
        $this->database->saveWallet($newWallet);
    }

    private function fetchTransactions(): array
    {
        return $this->database->getAllTransactionsByUserId($this->userId);
    }

    private function calculateWallet(): array
    {
        $wallet = [];
        $balance = $this->wallet->getBalance();
        $transactions = $this->fetchTransactions();

        foreach ($transactions as $transaction) {
            $symbol = strtoupper($transaction->getSymbol());
            $amount = $transaction->getAmount();
            $totalValue = $transaction->getPrice() * $amount;

            if ($transaction->getType() === 'buy') {
                $balance -= $totalValue;
                $wallet[$symbol]['amount'] = ($wallet[$symbol]['amount'] ?? 0) + $amount;
                $wallet[$symbol]['totalSpent'] = ($wallet[$symbol]['totalSpent'] ?? 0) + $totalValue;
            } elseif ($transaction->getType() === 'sell') {
                $balance += $totalValue;
                $wallet[$symbol]['amount'] = ($wallet[$symbol]['amount'] ?? 0) - $amount;
                $wallet[$symbol]['totalSpent'] = ($wallet[$symbol]['totalSpent'] ?? 0) - $totalValue;
            }
        }

        $wallet['balance'] = $balance;
        return $wallet;
    }

    public function getBalance(): float
    {
        return $this->calculateWallet()['balance'];
    }

    public function getCryptoAmount(string $symbol): float
    {
        $transactions = $this->fetchTransactions();
        $totalAmount = 0.0;

        foreach ($transactions as $transaction) {
            if ($transaction->getSymbol() === $symbol) {
                $amount = $transaction->getAmount();
                if ($transaction->getType() === 'buy') {
                    $totalAmount += $amount;
                } elseif ($transaction->getType() === 'sell') {
                    $totalAmount -= $amount;
                }
            }
        }

        return $totalAmount;
    }

    public function showWallet(): void
    {
        $state = $this->calculateWallet();
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Symbol', 'Amount', 'Avg Price', 'Current Price', 'Profit/Loss (%)']);

        foreach ($state as $symbol => $data) {
            if ($symbol !== 'balance') {
                $amount = $data['amount'];
                $totalSpent = $data['totalSpent'];
                $avgPurchasePrice = $totalSpent / $amount;
                $currentPrice = $this->apiClient->getCryptoBySymbol($symbol)->getPrice();
                $profitLoss = (($currentPrice - $avgPurchasePrice) / $avgPurchasePrice) * 100;

                $table->addRow([
                    $symbol,
                    $amount,
                    number_format($avgPurchasePrice, 8),
                    number_format($currentPrice, 8),
                    number_format($profitLoss, 2) . "%",
                ]);
            }
        }

        $table->render();
        $output->writeln("Balance: $" . number_format($state['balance'], 2));
        echo "\n";
    }
}
