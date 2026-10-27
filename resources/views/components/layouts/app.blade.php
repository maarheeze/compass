<!DOCTYPE html>
<html lang="{{ $htmlLanguage }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-canvas text-foreground">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
