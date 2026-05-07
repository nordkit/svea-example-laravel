@extends('layout')

@section('content')
    <h2>Complete your purchase</h2>
    <p style="color: #4b5b7a">Order ID: <code>{{ $orderId }}</code></p>
    <div class="iframe-wrap">
        {!! $snippet !!}
    </div>
@endsection

