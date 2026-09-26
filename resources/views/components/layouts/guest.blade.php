<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    <x-layout.api-status />

    <div class="flex min-h-screen flex-col items-center justify-center px-4">
        <div class="w-full max-w-sm">
            <h1 class="mb-8 text-center text-xl font-semibold text-gray-900">{{ config('app.name') }}</h1>

            <x-flash />
            <x-form-errors />

            {{ $slot }}
        </div>
    </div>
</body>
</html>
