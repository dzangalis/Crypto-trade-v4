<?php

namespace App\Models;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Transaction implements JsonSerializable
{
    private string $id;
    private string $userId;
    private string $type;
    private string $symbol;
    private float $amount;
    private float $price;

    public function __construct(string $userId, string $type, string $symbol, float $amount, float $price)
    {
        $this->id = Uuid::uuid4()->toString();;
        $this->userId = $userId;
        $this->setType($type);
        $this->setSymbol($symbol);
        $this->amount = $amount;
        $this->price = $price;
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

    public function getType(): string
    {
        return $this->type;
    }

    private function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    private function setSymbol(string $symbol): void
    {
        $this->symbol = strtoupper($symbol);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'type' => $this->type,
            'symbol' => $this->symbol,
            'amount' => $this->amount,
            'price' => $this->price,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['user_id'],
            $data['type'],
            $data['symbol'],
            $data['amount'],
            $data['price']
        );
    }
}