<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Cart\Cart;
use App\Cart\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function index(): View
    {
        return view('cart', [
            'catalog' => Catalog::all(),
            'items' => Cart::items(),
            'total' => Cart::total(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string',
        ]);

        if (Catalog::find($validated['sku']) !== null) {
            Cart::add($validated['sku']);
        }

        return redirect()->route('cart.index')->with('status', 'Added to cart.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string',
        ]);

        Cart::remove($validated['sku']);

        return redirect()->route('cart.index');
    }
}

