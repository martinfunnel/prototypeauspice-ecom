<?php

/* WebhookController — DÉSACTIVÉ, WhatsApp remplacé par Telegram
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function verifyWhatsApp(Request $request)
    {
        $verifyToken = config('services.whatsapp.webhook_verify_token');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('[Webhook] WhatsApp vérifié');
            return response($challenge, 200);
        }

        Log::warning('[Webhook] Vérification échouée', ['mode' => $mode, 'token' => $token]);
        return response('Forbidden', 403);
    }

    public function receiveWhatsApp(Request $request)
    {
        $payload = $request->all();
        Log::info('[Webhook] Événement WhatsApp reçu', $payload);

        $entry = $payload['entry'] ?? [];
        foreach ($entry as $e) {
            $changes = $e['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];

                if (!empty($value['statuses'])) {
                    foreach ($value['statuses'] as $status) {
                        Log::info('[Webhook] Statut message', [
                            'message_id' => $status['id'] ?? null,
                            'status' => $status['status'] ?? null,
                            'recipient' => $status['recipient_id'] ?? null,
                        ]);
                    }
                }

                if (!empty($value['messages'])) {
                    foreach ($value['messages'] as $msg) {
                        Log::info('[Webhook] Message reçu', [
                            'from' => $msg['from'] ?? null,
                            'text' => $msg['text']['body'] ?? null,
                            'timestamp' => $msg['timestamp'] ?? null,
                        ]);
                    }
                }
            }
        }

        return response('OK', 200);
    }
}
*/
