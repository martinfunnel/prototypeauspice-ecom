<?php

/* WhatsAppService — DÉSACTIVÉ, remplacé par TelegramService
namespace App\Services;

use App\Models\Order;
use App\Models\OrderManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiVersion;
    protected string $phoneNumberId;
    protected string $accessToken;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiVersion = config('services.whatsapp.api_version', 'v18.0');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->accessToken = config('services.whatsapp.access_token', '');
        $this->enabled = config('services.whatsapp.enabled', false);
    }

    public function send(string $to, string $message, ?string $templateName = null, array $templateParams = []): bool
    {
        if (!$this->enabled) {
            Log::info('[WhatsApp] Désactivé — message non envoyé', ['to' => $to]);
            return false;
        }

        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            Log::warning('[WhatsApp] Config incomplète (phone_number_id ou access_token manquant)');
            return false;
        }

        $to = $this->normalizePhone($to);
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
        ];

        if ($templateName) {
            $payload['type'] = 'template';
            $payload['template'] = [
                'name' => $templateName,
                'language' => ['code' => 'fr'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $templateParams),
                    ],
                ],
            ];
        } else {
            $payload['type'] = 'text';
            $payload['text'] = ['body' => $message];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('[WhatsApp] Message envoyé', ['to' => $to, 'message_id' => $response->json('messages.0.id')]);
                return true;
            }

            Log::error('[WhatsApp] Échec envoi', [
                'to' => $to,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function notifyClient(Order $order, ?string $templateName = null): bool
    {
        $trackUrl = url('/suivi/' . $order->order_number);
        $message = "Bonjour {$order->customer_name},\n\n" .
            "Votre commande *{$order->order_number}* a bien été reçue.\n" .
            "Total : " . number_format($order->total, 0, ',', ' ') . " FCFA\n\n" .
            "Suivez votre commande ici : {$trackUrl}\n\n" .
            "Merci pour votre confiance !";

        $params = [
            $order->customer_name,
            $order->order_number,
            number_format($order->total, 0, ',', ' ') . ' FCFA',
            $trackUrl,
        ];

        return $this->send($order->customer_phone, $message, $templateName, $params);
    }

    public function notifyManagers(Order $order, ?string $templateName = null): int
    {
        $managers = OrderManager::where('is_active', true)->get();
        if ($managers->isEmpty()) {
            Log::info('[WhatsApp] Aucun gérant de commande configuré');
            return 0;
        }

        $adminUrl = url('/admin/orders');
        $itemsList = $order->items->map(function ($item) {
            return "- {$item->product_name} x {$item->quantity}";
        })->implode("\n");

        $sent = 0;
        foreach ($managers as $manager) {
            $message = "📦 *Nouvelle commande*\n\n" .
                "N° : {$order->order_number}\n" .
                "Client : {$order->customer_name}\n" .
                "Tél : {$order->customer_phone}\n" .
                "Commune : {$order->commune_name}\n" .
                "Adresse : {$order->address}\n\n" .
                "Articles :\n{$itemsList}\n\n" .
                "Total : " . number_format($order->total, 0, ',', ' ') . " FCFA\n\n" .
                "Gérer : {$adminUrl}";

            $params = [
                $order->order_number,
                $order->customer_name,
                $order->customer_phone,
                $order->commune_name,
                number_format($order->total, 0, ',', ' ') . ' FCFA',
                $adminUrl,
            ];

            if ($this->send($manager->phone, $message, $templateName, $params)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
*/
