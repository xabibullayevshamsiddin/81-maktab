<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymeService
{
    private string $merchantId;
    private string $secretKey;
    private bool $enabled;

    public function __construct()
    {
        $this->merchantId = config("donation.payme_merchant_id", "");
        $this->secretKey = config("donation.payme_secret_key", "");
        $this->enabled = config("donation.payme_enabled", false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->merchantId !== "";
    }

    /**
     * Payme tolov linkini yaratish
     */
    public function createPaymentLink(int $amount, string $orderId, string $description = ""): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $params = http_build_query([
            "m" => $this->merchantId,
            "ac.order_id" => $orderId,
            "a" => $amount * 100,
            "l" => "uz",
            "c" => $description,
        ]);

        return "https://checkout.payme.uz/?" . $params;
    }

    /**
     * Payme webhookni tekshirish va tolovni tasdiqlash
     */
    public function verifyWebhook(array $payload): ?array
    {
        Log::info("Payme webhook received", $payload);

        $orderId = $payload["account"]["order_id"] ?? null;
        $transactionId = $payload["transaction"] ?? null;
        $status = $payload["state"] ?? null;

        if (!$orderId || !$transactionId) {
            return null;
        }

        $success = $status === 2;

        return [
            "order_id" => $orderId,
            "transaction_id" => $transactionId,
            "success" => $success,
            "amount" => ($payload["amount"] ?? 0) / 100,
        ];
    }
}