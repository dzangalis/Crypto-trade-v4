<?php

namespace App\Models;

class Currency
{
    private string $symbol;
    private string $name;
    private float $price;
    private ?int $rank;

    public function __construct(
        string $symbol,
        string $name,
        float  $price,
        ?int   $rank = null
    )
    {
        $this->symbol = $symbol;
        $this->name = $name;
        $this->price = $price;
        $this->rank = $rank;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }
}