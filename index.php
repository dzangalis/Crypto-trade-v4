<?php

require_once 'vendor/autoload.php';

use App\Api\CoinmarketcapAPI;
use App\Database\SqliteDatabase;
use App\Models\Wallet;
use App\Services\CurrencyService;
use App\Services\UserService;
use App\Services\WalletService;
use Dotenv\Dotenv;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

//Selection
$apiKey = $_ENV["API_KEY"];
//apiClient = new CoinPaprikaApi();
$apiClient = new CoinmarketcapAPI($apiKey);
$transactions = new SqliteDatabase();


$userService = new UserService($transactions);

$userId = null;

while (true) {

    $output = new ConsoleOutput();
    $table = new Table($output);
    $headers = [
        "<fg=red;options=bold>Command</>",
        "<fg=red;options=bold>Description</>"
    ];
    $table->setHeaders($headers);
    $table->setRows([
        ["1. Register", "Register a brand new user."],
        ["2. Login", "Login to your user."],
        ["3. Exit", "Exit from the application."],
    ]);

    $table->render();

    $input = strtolower(readline("Please enter a command: \n"));

    switch ($input) {
        case 1:
            $username = readline("Enter username: ");
            $password = readline("Enter password: ");
            $userService->createUser($username, $password);
            break;

        case 2:
            $username = readline("Enter username: ");
            $password = readline("Enter password: ");

            $user = $userService->login($username, $password);
            $userId = $user->getId();
            echo "Welcome, " . $user->getUsername() . "!\n";

            break;

        case 3:
            exit;

        default:
            echo "Invalid choice. Please try again.\n";
            break;
    }

    if ($userId !== null) {
        break;
    }
}

$startingBalance = 1000.0;
$userWallet = $transactions->getWallet($userId);
if (!$userWallet) {
    $currentWallet = new Wallet($userId, $startingBalance);
    $transactions->saveWallet($currentWallet);
    $userWallet = $transactions->getWallet($userId);
}
$currentWallet = new Wallet($userWallet['user_id'], $userWallet['balance']);


$walletProcesses = new WalletService($currentWallet, $transactions, $apiClient, $userId);
$tradeService = new CurrencyService($apiClient, $transactions, $walletProcesses, $userId);
$walletProcesses->showWallet();

while (true) {
    $output = new ConsoleOutput();
    $table = new Table($output);
    $headers = [
        "<fg=red;options=bold>Command</>",
        "<fg=red;options=bold>Description</>"
    ];
    $table->setHeaders($headers);
    $table->setRows([
        ["1. Top", "List top cryptocurrencies."],
        ["2. Search", "Search cryptocurrency by it's symbol."],
        ["3. Buy", "Buy cryptocurrency."],
        ["4. Sell", "Sell cryptocurrency."],
        ["5. Wallet", "Display current wallet."],
        ["6. Transactions", "Display total transactions."],
        ["7. Exit", "Exit from the application"]
    ]);
    $table->render();
    $input = strtolower(readline("Please enter a command: \n"));

    switch ($input) {

        case 1:
        case "top":

            $topCryptos = $apiClient->getTopCryptos();
            $output = new ConsoleOutput();
            $table = new Table($output);
            $headers = [
                "<fg=red;options=bold>Rank</>",
                "<fg=red;options=bold>Name</>",
                "<fg=red;options=bold>Symbol</>",
                "<fg=red;options=bold>Price</>"
            ];
            $table->setHeaders($headers);
            foreach ($topCryptos as $crypto) {
                $table->addRow([
                    $crypto->getRank(),
                    $crypto->getName(),
                    $crypto->getSymbol(),
                    number_format($crypto->getPrice(), 8),
                ]);
            }
            $table->render();
            break;

        case 2:
        case "search":

            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $currencyInfo = $apiClient->getCryptoBySymbol($symbol);

            $output = new ConsoleOutput();
            $table = new Table($output);
            $table->setHeaders([
                "<fg=red;options=bold>Symbol</>",
                "<fg=red;options=bold>Name</>",
                "<fg=red;options=bold>Price (USD)</>",
            ]);

            $table->addRow([
                $currencyInfo->getSymbol(),
                $currencyInfo->getName(),
                number_format($currencyInfo->getPrice(), 8),
            ]);

            $table->render();
            break;

        case 3:
        case "buy":

            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $amount = floatval(readline("Enter the amount to buy: \n"));

            $balance = $walletProcesses->getBalance();
            $cryptoData = $apiClient->getCryptoBySymbol($symbol);
            $cryptoPrice = $cryptoData->getPrice();
            $totalCost = $cryptoPrice * $amount;

            if ($balance >= $totalCost) {
                $tradeService->buy($symbol, $amount);

                $output = new ConsoleOutput();
                $table = new Table($output);
                $table->setHeaders([
                    "<fg=red;options=bold>Transaction Type</>",
                    "<fg=red;options=bold>Symbol</>",
                    "<fg=red;options=bold>Amount</>",
                    "<fg=red;options=bold>Price (USD)</>",
                ]);

                $table->addRow(['BUY', $symbol, $amount, number_format($cryptoPrice, 8),]);

                $table->render();

                echo "Thank You for your purchase. \n";
            } else {
                echo "You don't possess the funds to buy this amount of cryptocurrency. \n";
            }
            break;

        case 4:
        case "sell":

            $symbol = strtoupper(readline("Enter cryptocurrency symbol: \n"));
            $amount = floatval(readline("Enter the amount to sell: \n"));

            $cryptoData = $apiClient->getCryptoBySymbol($symbol);
            $cryptoPrice = $cryptoData->getPrice();

            $tradeService->sell($symbol, $amount);

            $output = new ConsoleOutput();
            $table = new Table($output);
            $table->setHeaders([
                "<fg=red;options=bold>Transaction Type</>",
                "<fg=red;options=bold>Symbol</>",
                "<fg=red;options=bold>Amount</>",
                "<fg=red;options=bold>Price (USD)</>",
            ]);

            $table->addRow(['SELL', $symbol, $amount, number_format($cryptoPrice, 8),]);

            $table->render();

            echo "Thank You for your sale. \n";
            break;

        case 5:
        case "wallet":

            $walletProcesses->showWallet();
            break;

        case 6:
        case "transactions":

            $output = new ConsoleOutput();
            $table = new Table($output);

            $headers = [
                "<fg=red;options=bold>Type</>",
                "<fg=red;options=bold>Symbol</>",
                "<fg=red;options=bold>Amount</>",
                "<fg=red;options=bold>Price</>",
            ];
            $table->setHeaders($headers);
            foreach ($transactions->getAllTransactions() as $transaction) {
                $table->addRow([
                    $transaction->getType(),
                    $transaction->getSymbol(),
                    $transaction->getAmount(),
                    number_format($transaction->getPrice(), 8),
                ]);
            }
            $table->render();
            break;


        case 7:
            echo "Have a nice day!\n";
            exit;

        default:
            echo "Invalid command, please try again.\n";
            break;
    }
}