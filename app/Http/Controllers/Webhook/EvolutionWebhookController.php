<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\WaMessage;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');
        $instance = $request->input('instance');

        if (!$data || !$instance) {
            return response()->json(['status' => 'ignored']);
        }

        $session = WaSession::where('instance_name', $instance)->first();
        if (!$session) {
            Log::warning("Evolution Webhook: Session not found for instance {$instance}");
            return response()->json(['status' => 'session_not_found']);
        }

        switch ($event) {
            case 'messages.upsert':
                $this->handleIncomingMessage($session, $data);
                break;
            
            case 'connection.update':
                $session->update(['status' => $data['state'] ?? 'disconnected']);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleIncomingMessage(WaSession $session, array $data)
    {
        $msg = $data['message'] ?? $data;
        $remoteJid = $msg['key']['remoteJid'] ?? null;
        $fromMe = $msg['key']['fromMe'] ?? false;
        $body = $msg['message']['conversation'] ?? ($msg['message']['extendedTextMessage']['text'] ?? '');

        if (!$remoteJid || $fromMe) return;

        // Find or create conversation
        $conversation = Conversation::updateOrCreate(
            [
                'workspace_id' => $session->workspace_id,
                'wa_contact_phone' => explode('@', $remoteJid)[0],
            ],
            [
                'type' => 'whatsapp',
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );

        // Store message
        WaMessage::create([
            'workspace_id' => $session->workspace_id,
            'session_id' => $session->id,
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'remote_jid' => $remoteJid,
            'body' => $body,
            'type' => 'text',
            'status' => 'delivered',
            'external_msg_id' => $msg['key']['id'],
            'timestamp' => now(),
        ]);
        
        $conversation->update(['last_message_at' => now()]);
    }
}
