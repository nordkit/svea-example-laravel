<?php

declare(strict_types=1);

namespace App\Cart;

/**
 * Tiny in-memory product catalog.
 *
 * Real apps load this from the database — for the demo we keep it simple
 * so the focus stays on the Svea integration, not the e-commerce model.
 *
 * Prices are in MAJOR units (kronor). The Svea SDK's OrderRow builder
 * converts to MINOR units (öre) automatically via × 100.
 */
final class Catalog
{
    /**
     * @return array<string, array{name: string, price: int, vat: int}>
     */
    public static function all(): array
    {
        return [
            'fjord-hoodie' => ['name' => 'Fjord Hoodie',        'price' => 799, 'vat' => 25],
            'aurora-mug'   => ['name' => 'Aurora Ceramic Mug',  'price' => 149, 'vat' => 25],
            'tundra-tee'   => ['name' => 'Tundra T-shirt',      'price' => 299, 'vat' => 25],
            'svea-sticker' => ['name' => 'Svea SDK Sticker',    'price' =>  29, 'vat' => 25],
        ];
    }

    /**
     * @return array{name: string, price: int, vat: int}|null
     */
    public static function find(string $sku): ?array
    {
        return self::all()[$sku] ?? null;
    }
}

