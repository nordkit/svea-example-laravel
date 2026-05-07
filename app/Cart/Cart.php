<?php

declare(strict_types=1);

namespace App\Cart;

use Illuminate\Support\Facades\Session;

/**
 * Session-backed cart — minimal store for the demo.
 *
 * Shape: array<sku, quantity>.
 */
final class Cart
{
    private const SESSION_KEY = 'cart';

    /**
     * @return array<string, int>
     */
    public static function items(): array
    {
        /** @var array<string, int> $items */
        $items = Session::get(self::SESSION_KEY, []);

        return $items;
    }

    public static function add(string $sku, int $quantity = 1): void
    {
        $items = self::items();
        $items[$sku] = ($items[$sku] ?? 0) + $quantity;
        Session::put(self::SESSION_KEY, $items);
    }

    public static function remove(string $sku): void
    {
        $items = self::items();
        unset($items[$sku]);
        Session::put(self::SESSION_KEY, $items);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function isEmpty(): bool
    {
        return self::items() === [];
    }

    /**
     * Total in MAJOR units (SEK).
     */
    public static function total(): int
    {
        $total = 0;
        foreach (self::items() as $sku => $qty) {
            $product = Catalog::find($sku);
            if ($product !== null) {
                $total += $product['price'] * $qty;
            }
        }

        return $total;
    }
}

