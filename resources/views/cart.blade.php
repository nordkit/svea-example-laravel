@extends('layout')

@section('content')
    <h2>Catalog</h2>
    <table>
        <thead>
            <tr><th>Product</th><th class="price">Price (SEK)</th><th></th></tr>
        </thead>
        <tbody>
            @foreach ($catalog as $sku => $product)
                <tr>
                    <td>{{ $product['name'] }}</td>
                    <td class="price">{{ number_format($product['price'], 0, ',', ' ') }}</td>
                    <td style="text-align: right">
                        <form method="POST" action="{{ route('cart.add') }}" style="display: inline">
                            @csrf
                            <input type="hidden" name="sku" value="{{ $sku }}">
                            <button type="submit">Add</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Your cart</h2>
    @if (empty($items))
        <div class="empty">Your cart is empty. Add a product above to start.</div>
    @else
        <table>
            <thead>
                <tr><th>Item</th><th>Qty</th><th class="price">Subtotal (SEK)</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($items as $sku => $qty)
                    @php $product = $catalog[$sku] ?? null; @endphp
                    @if ($product)
                        <tr>
                            <td>{{ $product['name'] }}</td>
                            <td>{{ $qty }}</td>
                            <td class="price">{{ number_format($product['price'] * $qty, 0, ',', ' ') }}</td>
                            <td style="text-align: right">
                                <form method="POST" action="{{ route('cart.remove') }}" style="display: inline">
                                    @csrf
                                    <input type="hidden" name="sku" value="{{ $sku }}">
                                    <button type="submit" class="btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="total">Total: {{ number_format($total, 0, ',', ' ') }} SEK</div>

        <div class="actions">
            <form method="POST" action="{{ route('checkout.create') }}">
                @csrf
                <button type="submit">Proceed to Svea Checkout →</button>
            </form>
        </div>
    @endif
@endsection

