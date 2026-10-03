<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $metaTitle = trim($__env->yieldContent('title')) ?: ($site->seo_title ?: $site->site_name);
        $metaDescription = trim($__env->yieldContent('description')) ?: $site->seo_description;
        $ogImage = $site->og_image_url;
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#fff8ee">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="{{ $site->site_name }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    <link rel="icon"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E📸%3C/text%3E%3C/svg%3E">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="min-h-screen bg-cream font-sans text-ink antialiased">
    <a href="#contenu"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-cream">
        Aller au contenu
    </a>

    <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-ink/5 bg-cream/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
            <a href="{{ route('home') }}" class="font-display text-xl font-semibold tracking-tight">
                {{ $site->site_name }}<span class="text-coral">.</span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold md:flex" aria-label="Navigation principale">
                <a href="{{ route('home') }}#galerie" class="transition hover:text-coral">Galerie</a>
                <a href="{{ route('about') }}"
                    class="rounded-full px-5 py-2 transition {{ request()->routeIs('about') ? 'bg-coral text-white' : 'bg-ink text-cream hover:bg-coral' }}">
                    À propos &amp; contact
                </a>
            </nav>

            <button type="button" class="grid size-10 place-items-center rounded-full bg-peach md:hidden"
                @click="open = !open" :aria-expanded="open" aria-label="Ouvrir le menu">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path x-show="!open" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    <path x-show="open" x-cloak stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <nav x-show="open" x-cloak x-transition class="border-t border-ink/5 px-5 pb-5 pt-3 md:hidden"
            aria-label="Navigation mobile">
            <a href="{{ route('home') }}#galerie" @click="open = false"
                class="block py-3 text-lg font-semibold">Galerie</a>
            <a href="{{ route('about') }}" class="block py-3 text-lg font-semibold">À propos &amp; contact</a>
        </nav>
    </header>

    <main id="contenu">
        @yield('content')
    </main>

    <footer class="mt-24 border-t border-ink/5 bg-peach/50">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-10 text-sm sm:flex-row">
            <p>© {{ date('Y') }} {{ $site->site_name }}. Tous droits réservés.</p>
            <ul class="flex flex-wrap items-center justify-center gap-4 font-semibold">
                @foreach ($site->socials() as $label => $url)
                    <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer me"
                            class="transition hover:text-coral">{{ $label }}</a></li>
                @endforeach
            </ul>
        </div>
    </footer>
</body>

</html>