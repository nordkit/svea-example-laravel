@extends('layout')

@section('content')
    <h2>Thank you! 🎉</h2>

    @if ($status === 'Final')
        <p>Your payment was completed successfully.</p>
    @else
        <p>Your order is in status <strong>{{ $status }}</strong>. We'll update once Svea confirms it.</p>
    @endif

    <table>
        <tr><th>Order ID</th><td><code>{{ $orderId }}</code></td></tr>
        <tr><th>Client order #</th><td><code>{{ $clientOrderNumber }}</code></td></tr>
        <tr><th>Status</th><td>{{ $status }}</td></tr>
    </table>

    <div class="actions">
        <a href="{{ route('cart.index') }}" class="btn">Back to shop</a>
    </div>
@endsection

