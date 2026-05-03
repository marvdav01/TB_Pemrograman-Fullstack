<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Smart Travel - Weather-Adaptive Trip Orchestration</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { font-family: 'Outfit', sans-serif; }
            .bg-auth {
                background: radial-gradient(circle at top left, #1e1b4b 0%, #060818 100%);
            }
        </style>
    </head>
    <body class="antialiased text-white bg-auth">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <div class="mb-8">
                <a href="/" wire:navigate class="flex items-center gap-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Smart Travel Logo" class="w-16 h-16 object-contain drop-shadow-2xl">
                    <span class="font-black text-4xl tracking-tight">Smart<span class="text-indigo-400">Travel</span></span>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 bg-white/5 backdrop-blur-xl border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden sm:rounded-[2.5rem]">
                {{ $slot }}
            </div>
            
            <p class="mt-8 text-white/30 text-sm font-medium italic">
                Tugas Besar Pemrograman Fullstack © {{ date('Y') }}
            </p>
        </div>
    </body>
</html>
