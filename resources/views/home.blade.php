@extends('layouts.app')

@section('title', $site->seo_title ?: $site->site_name . ' — ' . $site->tagline)
@section('description', $site->seo_description)

@push('head')
    @php
        $graph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '#website',
                    'url' => url('/'),
                    'name' => $site->site_name,
                    'inLanguage' => 'fr-FR',
                ],
                array_filter([
                    '@type' => 'Person',
                    '@id' => url('/') . '#person',
                    'name' => $site->site_name,
                    'jobTitle' => $site->tagline,
                    'url' => url('/'),
                    'image' => $site->avatar_url,
                    'sameAs' => array_values($site->socials()),
                ], fn($v) => filled($v)),
                [
                    '@type' => 'CollectionPage',
                    'name' => $site->seo_title ?: $site->site_name,
                    'url' => url('/'),
                    'about' => ['@id' => url('/') . '#person'],
                    'mainEntity' => [
                        '@type' => 'ItemList',
                        'itemListElement' => $items->values()->map(fn($item, $i) => [
                            '@type' => 'ListItem',
                            'position' => $i + 1,
                            'item' => $item->toSchema(),
                        ])->all(),
                    ],
                ],
            ],
        ];
    @endphp
    <script
        type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    {{-- HERO --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-24 -top-24 size-96 rounded-full bg-sun/40 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-20 top-20 size-80 rounded-full bg-coral/20 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 size-64 rounded-full bg-sage/30 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-5 py-16 sm:py-24 lg:py-32">
            @if ($site->tagline)
                <p class="mb-5 inline-block rounded-full bg-peach px-4 py-1.5 text-sm font-semibold">{{ $site->tagline }}</p>
            @endif
            <h1 class="max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl">
                {{ $site->hero_title }}
            </h1>
            <p class="mt-6 max-w-xl text-lg text-ink/70">{{ $site->hero_subtitle }}</p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="#galerie"
                    class="rounded-full bg-coral px-7 py-3.5 font-semibold text-white shadow-lg shadow-coral/30 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Découvrir les réalisations
                </a>
                <a href="{{ route('about') }}#contact"
                    class="rounded-full bg-white px-7 py-3.5 font-semibold ring-1 ring-ink/10 transition hover:-translate-y-0.5 hover:bg-peach">
                    Me contacter
                </a>
            </div>
        </div>
    </section>

    {{-- GALERIE --}}
    <section id="galerie" class="mx-auto max-w-7xl px-5 pt-8" x-data="gallery(@js($categories->pluck('slug')))">
        <h2 class="mb-8 text-center font-display text-3xl font-semibold sm:text-4xl">Mes réalisations</h2>

        @if ($categories->isNotEmpty())
            <div class="mb-10 flex flex-wrap items-center justify-center gap-2" role="group" aria-label="Filtrer par catégorie">
                <button type="button" @click="set('all')" :aria-pressed="active === 'all'"
                    :class="active === 'all' ? 'bg-ink text-cream' : 'bg-white hover:bg-peach'"
                    class="cursor-pointer rounded-full px-5 py-2 text-sm font-semibold ring-1 ring-ink/10 transition">
                    Tout
                </button>
                @foreach ($categories as $category)
                    <button type="button" @click="set(@js($category->slug))" :aria-pressed="active === @js($category->slug)"
                        :class="active === @js($category->slug) ? 'bg-ink text-cream' : 'bg-white hover:bg-peach'"
                        class="cursor-pointer rounded-full px-5 py-2 text-sm font-semibold ring-1 ring-ink/10 transition">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        @endif

        @if ($items->isEmpty())
            <p class="rounded-3xl bg-white p-10 text-center text-ink/60 ring-1 ring-ink/5">
                Les premières réalisations arrivent très bientôt ✨
            </p>
        @else
            <div class="columns-1 gap-6 sm:columns-2 lg:columns-3">
                @foreach ($items as $item)
                    @include('partials.media-card', ['item' => $item, 'eager' => $loop->index < 3])
                @endforeach
            </div>
        @endif
    </section>
@endsection