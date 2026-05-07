# `nordkit/svea-example-laravel`

A minimal **Laravel 13** example app that demonstrates the [`nordkit/svea`](https://github.com/nordkit/svea) SDK end-to-end:

> 🛒 **Cart** → 💳 **Svea Checkout** → ✅ **Confirmation** → 🔔 **Webhook**

It's intentionally small — one cart, one catalog, one webhook handler — so the focus stays on **how the SDK is wired into Laravel**, not on building an e-commerce app.

[![SDK](https://img.shields.io/badge/SDK-nordkit%2Fsvea-1e3a8a?style=flat-square)](https://github.com/nordkit/svea)
[![Laravel](https://img.shields.io/badge/Laravel-13-red?style=flat-square)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777bb4?style=flat-square)](https://www.php.net/)

---

## What you'll see

| File | Role |
|---|---|
| `app/Cart/Cart.php`, `app/Cart/Catalog.php` | Tiny session-backed cart + in-memory product list |
| `app/Http/Controllers/CartController.php` | List, add, remove cart items |
| `app/Http/Controllers/CheckoutController.php` | **The interesting bit** — builds a `CheckoutOrder`, calls `Svea::checkout()->create()`, renders the iframe snippet, polls status on confirmation |
| `app/Http/Controllers/WebhookController.php` | Verifies HMAC signatures via the bound `WebhookService`, logs incoming events |
| `routes/web.php` | All routes in one file |
| `config/svea.php` | Published from the SDK; reads env vars |
| `.env.example` | All `SVEA_*` keys documented |

---

## Quick start

### 1. Clone alongside the SDK

This example uses a Composer **path repository** that points at `../svea`, so you can hack on the SDK and the demo together with no publishing dance.

```bash
git clone https://github.com/nordkit/svea.git
git clone https://github.com/nordkit/svea-example-laravel.git
cd svea-example-laravel
```

Final layout:

```
your-projects/
├── svea/                   ← the SDK
└── svea-example-laravel/   ← this repo
```

### 2. Install + configure

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Fill in **at minimum** these in `.env`:

```env
SVEA_MERCHANT_ID=your-test-merchant-id
SVEA_SHARED_SECRET=your-test-shared-secret
SVEA_ENVIRONMENT=test
SVEA_WEBHOOK_SECRET=any-random-string-for-now
```

> Need test credentials? See [Svea's "Become a customer"](https://www.svea.com/become-a-customer) page or contact your Svea integration manager.

### 3. Run

```bash
php artisan serve
```

Open http://localhost:8000, add a few items to the cart, then **Proceed to Svea Checkout**. The Svea iframe will load using your test credentials.

---

## The full flow

```
 User browser ──add──▶ CartController ──put──▶ Session (cart[sku] = n)
       │
       │ POST /checkout
       ▼
 CheckoutController
   CheckoutOrder::make()
     ->currency('SEK')
     ->addRow(...)
     ->confirmationUri()      ┌────────────────────┐
                          ──▶ │ Svea::checkout()   │ ──▶ POST /checkout/orders ──▶ Svea
   $resp->snippet()           │   ->create()       │
                              └────────────────────┘
       │
       │ HTML+JS iframe
       ▼
 checkout view ── user pays via Svea iframe ── Svea redirects ──▶ /confirmation

 Svea push   ──POST /webhooks/svea──▶  WebhookController
                                         $webhooks->fromRequest($req)
                                           → verifies HMAC-SHA256
                                           → logs typed WebhookEvent
```

---

## Receiving webhooks locally

Svea needs a public HTTPS URL to deliver subscription pushes. Use [ngrok](https://ngrok.com) or [Expose](https://expose.dev):

```bash
# Terminal 1
php artisan serve

# Terminal 2
ngrok http 8000
# → https://abc123.ngrok.app
```

Then register a subscription pointing at your tunnel:

```php
use Svea\Laravel\Svea;
use Svea\Subscriptions\EventType;

Svea::subscriptions()->add(fn ($s) => $s
    ->callbackUri('https://abc123.ngrok.app/webhooks/svea')
    ->on(EventType::CheckoutOrderCreated)
    ->on(EventType::OrderDelivered)
);
```

Trigger a test event by completing a checkout in the browser — your Laravel log will show the verified payload.

---

## Code highlights

### Building the checkout (the SDK in 12 lines)

From `app/Http/Controllers/CheckoutController.php`:

```php
$order = CheckoutOrder::make()
    ->currency('SEK')
    ->locale('sv-SE')
    ->countryCode('SE')
    ->clientOrderNumber($clientOrderNumber)
    ->confirmationUri(route('checkout.confirmation'))
    ->checkoutUri(route('cart.index'))
    ->termsUri(url('/terms'));

foreach (Cart::items() as $sku => $qty) {
    $product = Catalog::find($sku);
    $order->addRow(fn ($row) => $row
        ->sku($sku)->name($product['name'])
        ->quantity($qty)->unitPrice($product['price'])->vatPercent(25)
    );
}

$response = Svea::checkout()->create($order);
```

### Verifying webhook signatures (the SDK does it for you)

From `app/Http/Controllers/WebhookController.php`:

```php
public function __invoke(Request $request, WebhookService $webhooks): JsonResponse
{
    try {
        $event = $webhooks->fromRequest($request);  // ← HMAC-SHA256 verify
    } catch (SignatureVerificationException) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    Log::info('Svea webhook', [
        'type' => $event->type()?->value,
        'orderId' => $event->orderId(),
    ]);

    return response()->json(['received' => true]);
}
```

### Testing without hitting Svea (`Svea::fake()`)

See `tests/Feature/CheckoutFlowTest.php` — drives the full controller flow with a faked SDK, no network calls.

```php
Svea::fake([
    'checkout.create' => CheckoutResponse::make([
        'OrderId' => 12345,
        'Status' => 'Created',
        'Gui' => ['Snippet' => '<div>fake snippet</div>'],
    ]),
]);

$this->post('/checkout')->assertOk();
Svea::assertCheckoutCreated();
```

---

## Project structure (only the demo-specific bits)

```
app/
├── Cart/
│   ├── Cart.php            ← session cart store
│   └── Catalog.php         ← in-memory product list
└── Http/Controllers/
    ├── CartController.php
    ├── CheckoutController.php
    └── WebhookController.php
config/
└── svea.php                ← published from the SDK
resources/views/
├── layout.blade.php        ← shared layout + minimal styles
├── cart.blade.php
├── checkout.blade.php      ← renders the Svea iframe snippet
├── confirmation.blade.php
└── terms.blade.php
routes/
└── web.php                 ← all 8 routes
tests/Feature/
└── CheckoutFlowTest.php    ← end-to-end test using Svea::fake()
```

Everything else is a stock Laravel 13 install.

---

## Going further

- 📖 [SDK README](https://github.com/nordkit/svea#readme)
- 📚 [Documentation site](https://nordkit.github.io/svea/) — full API reference
- 🧪 [Testing guide](https://nordkit.github.io/svea/guide/testing) — `Svea::fake()` patterns
- 🔁 [Retries & idempotency](https://nordkit.github.io/svea/guide/retries-idempotency)
- 🔔 [Subscriptions API](https://nordkit.github.io/svea/api/subscriptions)

---

## License

MIT — same as the SDK.

