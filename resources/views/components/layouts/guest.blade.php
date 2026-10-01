<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-layout.seo :title="$title ?? config('app.name')" />
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans antialiased">
    <x-layout.api-status />
    <x-layout.navbar />

    <div class="flex flex-1 flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <x-flash />
            <x-form-errors />

            {{ $slot }}
        </div>
    </div>

    <x-layout.footer />
</body>
</html>
