<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Cart\Cart;
use App\Cart\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Svea\Checkout\CheckoutOrder;
use Svea\Exceptions\SveaApiException;
use Svea\Laravel\Svea;

/**
 * Drives the Checkout phase of the demo:
 *
 *   1. POST /checkout         → builds a Svea Checkout order, stashes the SveaOrderId in
 *                                the session, and renders the Svea Checkout iframe.
 *   2. GET  /checkout/preview → re-renders the same iframe (e.g. after a page refresh).
 *   3. GET  /confirmation     → fetches the latest order status via the Checkout API and
 *                                clears the cart on Final.
 */
final class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Cart::isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $clientOrderNumber = 'demo-'.Str::lower(Str::random(10));

        $order = CheckoutOrder::make()
            ->currency('SEK')
            ->locale('sv-SE')
            ->countryCode('SE')
            ->clientOrderNumber($clientOrderNumber)
            ->confirmationUri(route('checkout.confirmation'))
            ->checkoutUri(route('cart.index'))
            ->termsUri(url('/terms'))
            ->pushUri(route('webhook.svea').'?clientOrderNumber={checkout.order.uri}');

        foreach (Cart::items() as $sku => $qty) {
            $product = Catalog::find($sku);
            if ($product === null) {
                continue;
            }

            $order->addRow(fn ($row) => $row
                ->sku($sku)
                ->name($product['name'])
                ->quantity($qty * 100)          // minor units: 1 unit = 100
                ->unitPrice($product['price'] * 100)  // minor units: 1 SEK = 100 öre
                ->vatPercent($product['vat'] * 100)   // minor units: 25% = 2500
            );
        }

        try {
            $response = Svea::checkout()->create($order);
        } catch (SveaApiException $e) {
            Log::error('Svea checkout creation failed', [
                'status'  => $e->statusCode,
                'message' => $e->getMessage(),
                'error'   => $e->sveaError,
            ]);

            return redirect()->route('cart.index')->with('error', 'Could not start checkout: '.$e->getMessage());
        }

        Session::put('svea.order_id', $response->id());
        Session::put('svea.client_order_number', $clientOrderNumber);

        return view('checkout', [
            'snippet' => $response->snippet(),
            'orderId' => $response->id(),
        ]);
    }

    public function preview(): View|RedirectResponse
    {
        $orderId = Session::get('svea.order_id');
        if ($orderId === null) {
            return redirect()->route('cart.index');
        }

        $response = Svea::checkout()->get((string) $orderId);

        return view('checkout', [
            'snippet' => $response->snippet(),
            'orderId' => $response->id(),
        ]);
    }

    public function confirmation(): View|RedirectResponse
    {
        $orderId = Session::get('svea.order_id');
        if ($orderId === null) {
            return redirect()->route('cart.index');
        }

        $response = Svea::checkout()->get((string) $orderId);

        // Final = customer completed payment; clear the cart.
        if ($response->status() === 'Final') {
            Cart::clear();
        }

        return view('confirmation', [
            'orderId' => $response->id(),
            'status' => $response->status(),
            'clientOrderNumber' => Session::get('svea.client_order_number'),
        ]);
    }
}

