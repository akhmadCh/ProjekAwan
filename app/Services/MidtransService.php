<?php

namespace App\Services;

use App\Models\SubscriptionOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    private function ensureServerKeyIsConfigured(): string
    {
        $serverKey = config('services.midtrans.server_key');

        if (! is_string($serverKey) || trim($serverKey) === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diset di .env. Isi dulu kredensial Midtrans sebelum checkout.');
        }

        return $serverKey;
    }

    public function createSnapTransaction(object $order): array
    {
        $serverKey = $this->ensureServerKeyIsConfigured();

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post(config('services.midtrans.snap_url') . '/transactions', [
                'transaction_details' => [
                    'order_id' => $order->order_id,
                    'gross_amount' => (int) round($order->gross_amount),
                ],
                'item_details' => [[
                    'id' => 'subscription-' . $order->subscription_package_id,
                    'price' => (int) round($order->gross_amount),
                    'quantity' => 1,
                    'name' => $order->package->name,
                ]],
                'customer_details' => [
                    'first_name' => $order->user->name,
                    'email' => $order->user->email,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.');
        }

        return $response->json();
    }

    public function getTransactionStatus(string $orderId): array
    {
        $serverKey = $this->ensureServerKeyIsConfigured();

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->get(rtrim(config('services.midtrans.api_url'), '/') . '/v2/' . $orderId . '/status');

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error_messages.0') ?? 'Gagal mengambil status transaksi Midtrans.');
        }

        return $response->json();
    }

    public function isValidNotificationSignature(array $payload): bool
    {
        $serverKey = $this->ensureServerKeyIsConfigured();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    public function generateOrderId(int $userId, int $packageId): string
    {
        return 'SUB-' . $userId . '-' . $packageId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }
}