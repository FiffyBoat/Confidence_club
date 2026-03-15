@props([
    'title' => config('app.name', 'Laravel'),
    'maxWidth' => 'md',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        default => 'sm:max-w-md',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="relative min-h-screen overflow-hidden bg-slate-950">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(244,63,94,0.28),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(250,204,21,0.18),_transparent_35%),linear-gradient(160deg,_#0f172a_0%,_#111827_45%,_#1e293b_100%)]"></div>
            <div class="absolute -top-24 left-10 h-48 w-48 rounded-full bg-rose-500/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 translate-x-20 translate-y-16 rounded-full bg-amber-400/10 blur-3xl"></div>

            <div class="relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full {{ $maxWidthClass }}">
                    <div class="mb-8 text-center">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-white">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                                <img src="{{ asset('images/ccm-logo.png') }}" alt="Confidence Club" class="h-14 w-14 object-contain" />
                            </span>
                            <span class="text-left">
                                <span class="block text-xs font-semibold uppercase tracking-[0.3em] text-rose-200/90">CCM Portal</span>
                                <span class="block text-xl font-bold tracking-tight">{{ config('app.name', 'Laravel') }}</span>
                            </span>
                        </a>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/95 shadow-2xl shadow-black/30 backdrop-blur-xl">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
