<?php

namespace JDS\ServiceProvider\Payments;

class PaymentResponse
{
    public function __construct(
        private readonly string $transactionId,
        private string          $paymentStatus,  // e.g., "succeeded", "pending", "failed"
        private readonly string $provider,       // e.g., "Stripe", "PayPal"
        private array           $details = []     // Additional metadata or API response
    )
    {
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function setPaymentStatus(string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

}