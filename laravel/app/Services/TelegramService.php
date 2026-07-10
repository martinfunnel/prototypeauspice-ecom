<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $botUsername;
    protected bool $enabled;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->botUsername = config('services.telegram.bot_username', '');
        $this->enabled = config('services.telegram.enabled', false);
    }

    /**
     * Envoie un message Telegram via l'API Bot.
     * Supporte un clavier inline (reply_markup) pour ajouter des boutons.
     */
    public function send(string $chatId, string $message, array $keyboard = []): bool
    {
        if (!$this->enabled) {
            Log::info('[Telegram] Désactivé — message non envoyé', ['chat_id' => $chatId]);
            return false;
        }

        if (empty($this->botToken)) {
            Log::warning('[Telegram] Config incomplète (bot_token manquant)');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($keyboard)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }

        try {
            $response = Http::asForm()
                ->post($url, $payload);

            if ($response->successful() && $response->json('ok')) {
                Log::info('[Telegram] Message envoyé', [
                    'chat_id' => $chatId,
                    'message_id' => $response->json('result.message_id'),
                ]);
                return true;
            }

            Log::error('[Telegram] Échec envoi', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('[Telegram] Exception', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Notifie le client quand sa commande est passée.
     * Le message contient un lien vers le suivi de commande.
     */
    public function notifyClient(Order $order): bool
    {
        $trackUrl = url('/suivi/' . $order->order_number);
        $message = "Bonjour *{$order->customer_name}*,\n\n" .
            "Votre commande *{$order->order_number}* a bien été reçue.\n" .
            "Total : " . number_format($order->total, 0, ',', ' ') . " FCFA\n\n" .
            "Merci pour votre confiance !";

        $keyboard = [
            [
                ['text' => 'Suivre ma commande', 'url' => $trackUrl],
            ],
        ];

        return $this->send($this->normalizeChatId($order->customer_phone), $message, $keyboard);
    }

    /**
     * Notifie tous les gérants de commandes actifs quand une nouvelle commande arrive.
     * Chaque gérant reçoit un message avec un bouton redirigeant vers la commande.
     */
    public function notifyManagers(Order $order): int
    {
        $managers = OrderManager::where('is_active', true)->get();
        if ($managers->isEmpty()) {
            Log::info('[Telegram] Aucun gérant de commande configuré');
            return 0;
        }

        $adminUrl = url('/admin/orders') . '#order-' . $order->id;
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
                "Total : " . number_format($order->total, 0, ',', ' ') . " FCFA";

            $keyboard = [
                [
                    ['text' => 'Voir la commande', 'url' => $adminUrl],
                ],
            ];

            if ($this->send($this->normalizeChatId($manager->phone), $message, $keyboard)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Normalise l'identifiant de chat Telegram.
     * Supprime les espaces et s'assure qu'il s'agit d'une chaîne numérique.
     */
    private function normalizeChatId(string $chatId): string
    {
        $chatId = preg_replace('/\s+/', '', $chatId);
        return $chatId;
    }
}
