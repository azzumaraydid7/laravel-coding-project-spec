<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="mx-auto pt-10">
        {{ $header }}
    </div>
    <div class="max-w-6xl mx-auto py-8">
        {{ $slot }}
    </div>
</body>
</html>
