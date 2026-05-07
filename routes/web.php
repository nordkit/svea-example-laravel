<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Cart
Route::get('/', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Checkout
Route::post('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::get('/checkout', [CheckoutController::class, 'preview'])->name('checkout.preview');
Route::get('/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

// Static terms page (Svea requires a termsUri)
Route::view('/terms', 'terms')->name('terms');

// Inbound webhook from Svea — bypass CSRF (handled in bootstrap/app.php).
Route::match(['get', 'post'], '/webhooks/svea', WebhookController::class)->name('webhook.svea');
