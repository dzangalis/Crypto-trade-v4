<?php
//
//namespace App\Services;
//
//use App\Api\ApiClientInterface;
//use App\Database\SqliteDatabase;
//use App\Models\Wallet;
//use Symfony\Component\Console\Helper\Table;
//use Symfony\Component\Console\Output\ConsoleOutput;
//
//class asdasdasda
//{
//
//    private const STARTING_BALANCE = 1000.0;
//
//    private Wallet $wallet;
//    private SqliteDatabase $database;
//    private ApiClientInterface $apiClient;
//    private string $userId;
//
//    public function __construct(
//        Wallet             $wallet,
//        SqliteDatabase     $database,
//        ApiClientInterface $apiClient,
//        string             $userId
//    )
//    {
//        $this->wallet = $wallet;
//        $this->database = $database;
//        $this->apiClient = $apiClient;
//        $this->userId = $userId;
//    }
//
//    public function createWallet(): void
//    {
//        $initialBalance = self::STARTING_BALANCE;
//
//        $newWallet = new Wallet ($this->userId, $initialBalance);
//
//        $this->database->saveWallet($newWallet);
//    }
//
//    public function calculateWalletState(): array
//    {
//        $state = [];
//
//        $balance = $this->wallet->getBalance();
//        $transactions = $this->database->getAllTransactionsByUserId($this->userId);
//
//        foreach ($transactions as $transaction) {
//            $symbol = strtoupper($transaction->getSymbol());
//            $amount = $transaction->getAmount();
//            $total = $transaction->getPrice() * $amount;
//
//            if ($transaction->getType() === 'buy') {
//                $balance -= $total;
//                if (!isset($state[$symbol])) {
//                    $state[$symbol] = ['amount' => 0, 'totalSpent' => 0];
//                }
//                $state[$symbol]['amount'] += $amount;
//                $state[$symbol]['totalSpent'] += $total;
//            } elseif ($transaction->getType() === 'sell') {
//                $balance += $total;
//                if (!isset($state[$symbol])) {
//                    $state[$symbol] = ['amount' => 0, 'totalSpent' => 0];
//                }
//                $state[$symbol]['amount'] -= $amount;
//                $state[$symbol]['totalSpent'] -= $total;
//            }
//        }
//
//        $state['balance'] = $balance;
//        return $state;
//    }
//
//    public function getBalance(): float
//    {
//        $state = $this->calculateWalletState();
//        return $state['balance'];
//    }
//
//    public function getExistingAmountInWallet(string $symbol): float
//    {
//        $transactions = $this->database->getAllTransactionsByUserId($this->userId);
//        $existingAmount = 0.0;
//
//        foreach ($transactions as $transaction) {
//            if ($transaction->getSymbol() === $symbol && $transaction->getType() === 'buy') {
//                $existingAmount += $transaction->getAmount();
//            } elseif ($transaction->getSymbol() === $symbol && $transaction->getType() === 'sell') {
//                $existingAmount -= $transaction->getAmount();
//            }
//        }
//
//        return $existingAmount;
//    }
//
//    public function showWallet(): void
//    {
//        $state = $this->calculateWalletState();
//        $output = new ConsoleOutput();
//        $table = new Table($output);
//        $table->setHeaders(['Symbol', 'Amount', 'Avg Purchase Price', 'Current Price', 'Profit/Loss (%)']);
//
//        foreach ($state as $symbol => $data) {
//            if ($symbol !== 'balance') {
//                $amount = $data['amount'];
//                $totalSpent = $data['totalSpent'];
//                $avgPurchasePrice = $totalSpent / $amount;
//                $currentPrice = $this->apiClient->getCryptoBySymbol($symbol)->getPrice();
//                $profitLoss = (($currentPrice - $avgPurchasePrice) / $avgPurchasePrice) * 100;
//
//                $table->addRow([
//                    $symbol,
//                    $amount,
//                    number_format($avgPurchasePrice, 8),
//                    number_format($currentPrice, 8),
//                    number_format($profitLoss, 2) . "%",
//                ]);
//            }
//        }
//
//        $table->render();
//        $output->writeln("Balance: $" . number_format($state['balance'], 2));
//        echo "\n";
//    }
//}