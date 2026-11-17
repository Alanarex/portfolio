<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#faf6f6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b090a" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/light/favicon-32x32.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/dark/favicon-32x32.png') }}" media="(prefers-color-scheme: dark)">
    <title inertia>Administration — Portfolio</title>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            try {
                var theme = localStorage.getItem('portfolio-theme');
                if (theme !== 'dark' && theme !== 'light') {
                    theme = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/admin.css', 'resources/js/admin.ts'])
    @inertiaHead
</head>
<body>
@inertia
</body>
</html>
