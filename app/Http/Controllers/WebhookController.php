<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Svea\Exceptions\SignatureVerificationException;
use Svea\Laravel\WebhookService;

/**
 * Inbound Svea webhook handler.
 *
 * Two flavours of inbound notification land here:
 *
 *   1. Subscription pushes — signed with the shared `webhook_secret`. The bound
 *      WebhookService verifies the HMAC-SHA256 signature header and returns a typed
 *      WebhookEvent.
 *
 *   2. Per-order merchant pushes (`pushUri`) — Svea posts the SveaOrderId so you
 *      can call `Svea::admin()->order($id)->get()` and react. These are NOT signed;
 *      the URL itself acts as a capability token (always include a hard-to-guess
 *      query string in `pushUri`). For brevity this demo logs them.
 *
 * Replace Log calls with queued jobs in production.
 */
final class WebhookController extends Controller
{
    public function __invoke(Request $request, WebhookService $webhooks): JsonResponse
    {
        // Per-order push (no signature) — Svea calls this when checkout enters Final.
        if ($request->isMethod('GET') || $request->query('clientOrderNumber') !== null) {
            Log::info('Svea per-order push received', [
                'query' => $request->query(),
                'body' => $request->all(),
            ]);

            return response()->json(['received' => true]);
        }

        // Subscription push — verify HMAC and dispatch.
        try {
            $event = $webhooks->fromRequest($request);
        } catch (SignatureVerificationException $e) {
            Log::warning('Svea webhook signature verification failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Svea webhook received', [
            'type' => $event->type()?->value,
            'orderId' => $event->orderId(),
            'deliveryId' => $event->deliveryId(),
            'occurredAt' => $event->occurredAt()?->format(DATE_ATOM),
        ]);

        // In a real app: dispatch a queued job per event type.
        // SveaWebhookReceived::dispatch($event);

        return response()->json(['received' => true]);
    }
}

