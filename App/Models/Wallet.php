<?php

namespace App\Models;

use App\Database\SqliteDatabase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleOutput;

class Wallet
{
    private
    string $id;
    private string $userId;
    private float $balance;

    public function __construct(
        string $userId,
        float  $balance
    )
    {
        $this->id = Uuid::uuid4()->toString();;
        $this->userId = $userId;
        $this->balance = $balance;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }
}