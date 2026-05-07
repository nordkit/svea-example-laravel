<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Svea SDK Demo' }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
            max-width: 880px; margin: 0 auto; padding: 2rem 1.25rem;
            line-height: 1.55; color: #1a2238; background: #f7f9fc;
        }
        header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
        header h1 { font-size: 1.25rem; margin: 0; color: #1a2238; }
        header .badge { font-size: .75rem; padding: .2rem .5rem; border-radius: 999px; background: #1e3a8a; color: #fff; }
        h2 { margin-top: 2rem; color: #1a2238; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        th, td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #eef1f6; }
        th { background: #f0f4fa; font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: .03em; color: #4b5b7a; }
        tr:last-child td { border-bottom: none; }
        .price { font-variant-numeric: tabular-nums; text-align: right; font-weight: 500; }
        button, .btn {
            display: inline-block; padding: .5rem 1rem; border: none; border-radius: 6px;
            background: #1e3a8a; color: #fff; font-size: .9rem; cursor: pointer; text-decoration: none;
        }
        button:hover, .btn:hover { background: #1d4ed8; }
        .btn-secondary { background: #6b7280; }
        .btn-danger { background: transparent; color: #b91c1c; border: 1px solid #fca5a5; padding: .25rem .6rem; }
        .total { font-size: 1.25rem; font-weight: 700; text-align: right; margin: 1rem 0; }
        .alert { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-info { background: #dbeafe; color: #1e40af; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .empty { text-align: center; padding: 2rem; color: #6b7280; background: #fff; border-radius: 8px; }
        .actions { margin-top: 1rem; display: flex; justify-content: flex-end; gap: .5rem; }
        footer { margin-top: 3rem; font-size: .85rem; color: #6b7280; text-align: center; }
        footer a { color: #1d4ed8; text-decoration: none; }
        .iframe-wrap { background: #fff; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    </style>
</head>
<body>
    <header>
        <h1>Svea SDK Laravel Demo</h1>
        <span class="badge">{{ strtoupper(config('svea.environment', 'test')) }}</span>
    </header>

    @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{ $slot ?? '' }}
    @yield('content')

    <footer>
        Powered by <a href="https://github.com/nordkit/svea">nordkit/svea</a> · <a href="{{ route('cart.index') }}">Cart</a>
    </footer>
</body>
</html>

