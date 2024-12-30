<?php

namespace JDS\ServiceProvider\Payments;

interface PaymentProviderInterface
{
    /**
     * Charges an amount directly (for one-step payments).
     */
    public function charge(float $amount, string $currency, array $options = []): PaymentResponse;

    /**
     * Creates a payment request (used for preauthorization).
     */
    public function createPayment(array $paymentDetails): PaymentResponse;

    /**
     * Captures a preauthorized payment.
     */
    public function capturePayment(string $transactionId): PaymentResponse;

    /**
     * Refunds an amount for a specific transaction.
     */
    public function refund(string $transactionId, ?float $amount = null): PaymentResponse;

    /**
     * Retrieves the current status of a transaction.
     */
    public function getStatus(string $transactionId): PaymentStatus;

    /**
     * Handles webhook payloads sent by the payment provider.
     */
    public function handleWebhook(array $webhookPayload): void;
}

