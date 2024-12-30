<?php

namespace JDS\ServiceProvider\Payments;

class PaymentStatus
{

    public function __construct(
        private readonly string $transactionId,
        private readonly string $status,
        private readonly float  $amount,
        private readonly string $currency,
        private readonly string $provider,
        private readonly array $metadata = []
    )
    {
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getNumberFormatedAmount(): float
    {
        return number_format($this->amount,2);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

}