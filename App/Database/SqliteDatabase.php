<?php

namespace App\Database;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;

use Medoo\Medoo;

class SqliteDatabase
{
    private Medoo $database;

    public function __construct(string $databaseFile = 'storage/database.sqlite')
    {
        $this->database = new Medoo([
            'database_type' => 'sqlite',
            'database_name' => $databaseFile,
        ]);
        $this->createTable();
    }

    private function createTable(): void
    {
        $this->database->exec('CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL
            )');


        $this->database->exec('CREATE TABLE IF NOT EXISTS wallets (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                balance REAL NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users (id)
            )');


        $this->database->exec('CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,
            symbol TEXT NOT NULL,
            amount REAL NOT NULL,
            price REAL NOT NULL
            )');
    }

    public function save(Transaction $transaction): void
    {

        $this->database->insert('transactions', [
            'id' => $transaction->getId(),
            'user_id' => $transaction->getUserId(),
            'type' => $transaction->getType(),
            'symbol' => strtoupper($transaction->getSymbol()),
            'amount' => $transaction->getAmount(),
            'price' => $transaction->getPrice(),
        ]);

    }

    public function getAllTransactions(): array
    {
        $transactions = [];

        $databaseTransaction = $this->database->select('transactions',
            ['id', 'type', 'symbol', 'amount', 'price']
        );

        foreach ($databaseTransaction as $data) {
            $transactions[] = Transaction::fromArray($data);
        }

        return $transactions;
    }


    public function getAllTransactionsByUserId(string $userId): array
    {
        $transactions = [];

        $databaseTransaction = $this->database->select('transactions',
            ['id', 'user_id', 'type', 'symbol', 'amount', 'price'],
            ['user_id' => $userId]
        );

        foreach ($databaseTransaction as $data) {
            $transactions[] = Transaction::fromArray($data);
        }

        return $transactions;
    }


    public function saveWallet(Wallet $wallet): void
    {

        $this->database->insert('wallets', [
            'id' => $wallet->getId(),
            'user_id' => $wallet->getUserId(),
            'balance' => $wallet->getBalance(),
        ]);
    }


    public function getWallet(string $userId): ?array
    {

        $walletDatabase = $this->database->get('wallets', '*', ['user_id' => $userId]);
        return $walletDatabase ?? null;

    }

    public function updateWallet(Wallet $wallet): void
    {

        $this->database->update('wallets', [
            'balance' => $wallet->getBalance(),
        ], [
            'id' => $wallet->getId(),
        ]);

    }

    public function saveUser(User $user): void
    {

        $this->database->insert('users', [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'password' => $user->getPassword(),
        ]);

    }

    public function getUserById(string $userId): ?array
    {

        $userDatabase = $this->database->get('users', '*', ['id' => $userId]);
        return $userDatabase ?? null;

    }

    public function getUserByUsernameAndPassword(string $username, string $password): ?array
    {
        $userPassword = md5($password);

        $userDatabase = $this->database->get('users', '*', [
            'username' => $username,
            'password' => $userPassword,
        ]);

        return $userDatabase;
    }


}