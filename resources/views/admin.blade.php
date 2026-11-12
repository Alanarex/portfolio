<!doctype html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#07101f">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/light/favicon-32x32.png') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/dark/favicon-32x32.png') }}" media="(prefers-color-scheme: dark)">
    <title inertia>Administration — Portfolio</title>
    @vite(['resources/css/app.css', 'resources/js/admin.ts'])
    @inertiaHead
</head>
<body>
@inertia
</body>
</html>
