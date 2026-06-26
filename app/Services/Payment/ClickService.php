<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickService
{
    private string $merchantId;
    private string $secretKey;
    private string $serviceId;
    private string $merchantUserId;
    private bool $enabled;

    public function __construct()
    {
        $this->merchantId = config("donation.click_merchant_id", "");
        $this->secretKey = config("donation.click_secret_key", "");
        $this->serviceId = config("donation.click_service_id", "");
        $this->merchantUserId = config("donation.click_merchant_user_id", "");
        $this->enabled = config("donation.click_enabled", false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->merchantId !== "";
    }

    /**
     * Click tolov linkini yaratish
     */
    public function createPaymentLink(int $amount, string $orderId): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $params = http_build_query([
            "service_id" => $this->serviceId,
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "transaction_param" => $orderId,
            "return_url" => route("donation.index"),
        ]);

        return "https://my.click.uz/services/pay/?" . $params;
    }

    /**
     * Click webhook / prepare qilish
     */
    public function prepare(array $payload): array
    {
        $clickTransId = $payload["click_trans_id"] ?? 0;
        $merchantTransId = $payload["merchant_trans_id"] ?? "";
        $amount = $payload["amount"] ?? 0;
        $signTime = $payload["sign_time"] ?? "";
        $signString = $payload["sign_string"] ?? "";

        // Click request ni verify qilamiz
        $expectedSign = md5($clickTransId . $this->secretKey . $merchantTransId . $this->serviceId . $amount . $signTime . $this->merchantUserId);

        if ($signString !== $expectedSign) {
            return ["error" => -1, "error_note" => "Sign noto\'g\'ri"];
        }

        return [
            "error" => 0,
            "error_note" => "Success",
            "click_trans_id" => $clickTransId,
            "merchant_trans_id" => $merchantTransId,
        ];
    }

    /**
     * Click webhook / complete qilish
     */
    public function complete(array $payload): array
    {
        $clickTransId = $payload["click_trans_id"] ?? 0;
        $merchantTransId = $payload["merchant_trans_id"] ?? "";
        $amount = $payload["amount"] ?? 0;
        $signTime = $payload["sign_time"] ?? "";
        $signString = $payload["sign_string"] ?? "";
        $error = $payload["error"] ?? 0;

        $expectedSign = md5($clickTransId . $this->secretKey . $merchantTransId . $this->serviceId . $amount . $signTime . $this->merchantUserId);

        if ($signString !== $expectedSign) {
            return ["error" => -1, "error_note" => "Sign noto\'g\'ri"];
        }

        if ((int)$error !== 0) {
            return [
                "error" => (int)$error,
                "error_note" => "To\'lov xatosi",
                "click_trans_id" => $clickTransId,
                "merchant_trans_id" => $merchantTransId,
            ];
        }

        return [
            "error" => 0,
            "error_note" => "Success",
            "click_trans_id" => $clickTransId,
            "merchant_trans_id" => $merchantTransId,
        ];
    }
}