<?php

declare(strict_types=1);

namespace Tests\Feature;

use Svea\Checkout\CheckoutResponse;
use Svea\Laravel\Svea;
use Tests\TestCase;

final class CheckoutFlowTest extends TestCase
{
    public function test_cart_page_renders_the_catalog(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Fjord Hoodie')
            ->assertSee('Your cart is empty');
    }

    public function test_a_user_can_add_an_item_to_the_cart(): void
    {
        $this->post('/cart/add', ['sku' => 'fjord-hoodie'])
            ->assertRedirect('/');

        $this->get('/')
            ->assertOk()
            ->assertSee('Total: 799 SEK');
    }

    public function test_proceed_to_checkout_calls_the_svea_sdk_and_renders_the_snippet(): void
    {
        Svea::fake([
            'checkout.create' => CheckoutResponse::make([
                'OrderId' => 12345,
                'Status' => 'Created',
                'Gui' => ['Snippet' => '<div id="svea-checkout">FAKE_SNIPPET</div>'],
            ]),
        ]);

        $this->post('/cart/add', ['sku' => 'aurora-mug']);

        $this->post('/checkout')
            ->assertOk()
            ->assertSee('FAKE_SNIPPET', escape: false)
            ->assertSee('12345');

        Svea::assertCheckoutCreated();
    }

    public function test_checkout_with_an_empty_cart_redirects_back(): void
    {
        $this->post('/checkout')
            ->assertRedirect('/')
            ->assertSessionHas('status', 'Your cart is empty.');
    }

    public function test_confirmation_page_clears_the_cart_when_status_is_final(): void
    {
        Svea::fake([
            'checkout.create' => CheckoutResponse::make([
                'OrderId' => 99,
                'Status' => 'Created',
                'Gui' => ['Snippet' => '<div></div>'],
            ]),
            'checkout.get' => CheckoutResponse::make([
                'OrderId' => 99,
                'Status' => 'Final',
                'Gui' => ['Snippet' => '<div></div>'],
            ]),
        ]);

        $this->post('/cart/add', ['sku' => 'svea-sticker']);
        $this->post('/checkout')->assertOk();

        $this->get('/confirmation')
            ->assertOk()
            ->assertSee('Thank you')
            ->assertSee('Final');

        // Cart should now be empty.
        $this->get('/')->assertSee('Your cart is empty');
    }
}

