@php
    $isPhoto = $item->type === 'photo';
    $photo = $isPhoto ? $item->getFirstMedia('photo') : null;
    $w = (int) ($photo?->getCustomProperty('width') ?: 4);
    $h = (int) ($photo?->getCustomProperty('height') ?: 3);
@endphp

<article x-data="mediaCard" x-show="show(@js($item->category?->slug))" x-transition.opacity.duration.300ms
    class="mb-6 break-inside-avoid overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-ink/5 transition duration-300 hover:shadow-xl hover:shadow-sun/25">

    @if ($isPhoto)
        <button type="button" @click="open = !open" class="group block w-full cursor-pointer overflow-hidden"
            aria-label="Afficher la démarche créative : {{ $item->title }}">
            @if ($item->thumb_url)
            <img src="{{ $item->thumb_url }}" srcset="{{ $item->thumb_url }} 640w, {{ $item->large_url }} 1600w"
                sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" alt="{{ $item->display_alt }}"
                width="{{ $w }}" height="{{ $h }}" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                class="h-auto w-full transition duration-700 group-hover:scale-105">
            @else
                <div class="grid aspect-[4/3] place-items-center bg-peach text-sm font-semibold text-ink/55">Photo à ajouter</div>
            @endif
        </button>
    @else
        <div class="relative aspect-video overflow-hidden bg-gradient-to-br from-sun/70 to-coral/60">
            <template x-if="!playing">
                <button type="button" @click="playing = true; open = true" class="group absolute inset-0 cursor-pointer"
                    aria-label="Lire la vidéo : {{ $item->title }}">
                    @if ($item->thumb_url)
                        <img src="{{ $item->thumb_url }}" alt="{{ $item->display_alt }}"
                            loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
                            class="size-full object-cover transition duration-700 group-hover:scale-105">
                    @endif
                    <span class="absolute inset-0 grid place-items-center">
                        <span
                            class="grid size-16 place-items-center rounded-full bg-white/90 text-coral shadow-lg transition group-hover:scale-110">
                            <svg class="ml-1 size-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </span>
                    </span>
                </button>
            </template>

            <template x-if="playing">
                @if ($item->type === 'embed')
                    <iframe src="{{ $item->embed_url }}" title="{{ $item->title }}" class="absolute inset-0 size-full"
                        loading="lazy" allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                        allowfullscreen></iframe>
                @else
                    <video src="{{ $item->video_file_url }}" poster="{{ $item->thumb_url }}"
                        class="absolute inset-0 size-full bg-black" controls autoplay playsinline preload="metadata"></video>
                @endif
            </template>
        </div>
    @endif

    <div class="px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                @if ($item->category)
                    <p class="text-xs font-bold uppercase tracking-wider text-coral">{{ $item->category->name }}</p>
                @endif
                <h3 class="font-display text-lg font-semibold leading-snug">{{ $item->title }}</h3>
            </div>

            @if ($item->caption)
                <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="legende-{{ $item->id }}"
                    class="mt-1 grid size-9 shrink-0 cursor-pointer place-items-center rounded-full bg-peach transition hover:bg-sun"
                    aria-label="Afficher ou masquer la légende">
                    <svg class="size-4 transition-transform duration-300" :class="open && 'rotate-180'" fill="none"
                        stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                </button>
            @endif
        </div>

        @if ($item->caption)
            <div id="legende-{{ $item->id }}" class="grid transition-[grid-template-rows] duration-500 ease-out"
                :class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                <div class="overflow-hidden">
                    <p class="pt-3 text-[15px] leading-relaxed text-ink/75">{!! nl2br(e($item->caption)) !!}</p>
                </div>
            </div>
        @endif
    </div>
</article>
