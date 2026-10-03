@extends('layouts.app')

@section('title', $site->about_seo_title ?: 'À propos & contact — ' . $site->site_name)
@section('description', $site->about_seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $site->about_text), 155))

@push('head')
    @php
        $graph = [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => 'À propos & contact',
            'url' => url()->current(),
            'mainEntity' => array_filter([
                '@type' => 'Person',
                'name' => $site->site_name,
                'jobTitle' => $site->tagline,
                'image' => $site->avatar_url,
                'email' => $site->contact_email,
                'sameAs' => array_values($site->socials()),
            ], fn($v) => filled($v)),
        ];
    @endphp
    <script
        type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-sun/30 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto grid max-w-6xl gap-12 px-5 py-16 sm:py-24 lg:grid-cols-5">
            {{-- À propos --}}
            <section class="lg:col-span-3">
                <h1 class="font-display text-4xl font-semibold leading-tight sm:text-5xl">{{ $site->about_title }}</h1>

                @if ($site->avatar_url)
                    <img src="{{ $site->avatar_url }}" alt="Portrait de {{ $site->site_name }}" width="900" height="900"
                        loading="eager" decoding="async"
                        class="mt-8 size-40 rounded-full object-cover ring-8 ring-peach sm:size-48">
                @endif

                <div class="mt-8 space-y-4 text-lg leading-relaxed text-ink/80">
                    {!! nl2br(e($site->about_text)) !!}
                </div>

                @if ($site->city)
                    <p class="mt-6 font-semibold">📍 {{ $site->city }}</p>
                @endif

                @if ($site->socials())
                    <ul class="mt-8 flex flex-wrap gap-3">
                        @foreach ($site->socials() as $label => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer me"
                                    class="inline-block rounded-full bg-white px-5 py-2.5 text-sm font-semibold ring-1 ring-ink/10 transition hover:-translate-y-0.5 hover:bg-sun">
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Contact --}}
            <section id="contact" class="lg:col-span-2">
                <div class="rounded-3xl bg-white p-7 shadow-xl shadow-sun/20 ring-1 ring-ink/5">
                    <h2 class="font-display text-2xl font-semibold">Écrivons-nous 👋</h2>
                    <p class="mt-1 text-sm text-ink/60">Un projet, une question ? Je réponds avec plaisir.</p>

                    @if (session('success'))
                        <div class="mt-5 rounded-2xl bg-sage/30 px-4 py-3 text-sm font-semibold" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-4">
                        @csrf

                        {{-- Anti-spam (honeypot) --}}
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label>Ne pas remplir <input type="text" name="website" tabindex="-1"
                                    autocomplete="off"></label>
                        </div>

                        <div>
                            <label for="name" class="mb-1 block text-sm font-semibold">Nom</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="100"
                                autocomplete="name"
                                class="w-full rounded-xl border-0 bg-cream px-4 py-3 ring-1 ring-ink/10 focus:ring-2 focus:ring-coral">
                            @error('name')
                            <p class="mt-1 text-sm text-coral">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150"
                                autocomplete="email"
                                class="w-full rounded-xl border-0 bg-cream px-4 py-3 ring-1 ring-ink/10 focus:ring-2 focus:ring-coral">
                            @error('email')
                            <p class="mt-1 text-sm text-coral">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="message" class="mb-1 block text-sm font-semibold">Message</label>
                            <textarea id="message" name="message" rows="6" required minlength="10" maxlength="3000"
                                class="w-full rounded-xl border-0 bg-cream px-4 py-3 ring-1 ring-ink/10 focus:ring-2 focus:ring-coral">{{ old('message') }}</textarea>
                            @error('message')
                            <p class="mt-1 text-sm text-coral">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                            class="w-full cursor-pointer rounded-full bg-coral px-6 py-3.5 font-semibold text-white shadow-lg shadow-coral/30 transition hover:-translate-y-0.5">
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection