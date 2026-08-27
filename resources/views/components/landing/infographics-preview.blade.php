<section id="infografis" class="bg-background py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <span class="text-sm font-semibold text-teal">Panduan visual</span>

                <h2 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">
                    Infografis Layanan DKP
                </h2>

                <p class="mt-3 leading-relaxed text-muted-foreground">
                    Kenali panduan Magang, KP, dan PKL serta informasi WOPPS melalui kategori yang jelas.
                </p>
            </div>

            <a
                href="{{ route('infographics') }}"
                class="inline-flex items-center justify-center rounded-xl border border-ocean px-4 py-2.5 text-sm font-semibold text-ocean transition-colors hover:bg-secondary"
            >
                Lihat Semua Infografis
            </a>
        </div>

        <div
            class="mt-8"
            data-infographic-coverflow
            role="region"
            aria-roledescription="carousel"
            aria-label="Carousel infografis"
        >
            <div
                class="cursor-grab overflow-hidden py-8 outline-none focus-visible:ring-2 focus-visible:ring-ocean active:cursor-grabbing sm:py-10"
                data-infographic-coverflow-frame
                tabindex="0"
                style="perspective: 900px; touch-action: pan-y;"
            >
                <div
                    class="relative mx-auto h-[17rem] select-none sm:h-[22rem]"
                    data-infographic-coverflow-stage
                    style="transform-style: preserve-3d;"
                >
                    @foreach ($items as $index => $item)
                        <button
                            type="button"
                            data-infographic-coverflow-card
                            data-infographic-lightbox-trigger
                            data-image-src="{{ $item->image_url }}"
                            data-image-alt="{{ $item->alt }}"
                            data-image-caption="{{ $item->caption }}"
                            data-image-service="{{ $item->display_label }}"
                            data-image-width="{{ $item->image_width }}"
                            data-image-height="{{ $item->image_height }}"
                            class="absolute left-1/2 top-0 aspect-[3/4] w-[clamp(9.25rem,29vw,14rem)] overflow-hidden rounded-2xl border border-white/70 bg-white text-left shadow-xl outline-none ring-ocean transition-shadow focus-visible:ring-4"
                            style="will-change: transform, opacity;"
                            aria-roledescription="slide"
                            aria-label="{{ $index + 1 }} dari {{ $items->count() }}: {{ $item->caption }}"
                        >
                            <img
                                src="{{ $item->image_url }}"
                                alt="{{ $item->alt }}"
                                width="{{ $item->image_width }}"
                                height="{{ $item->image_height }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                @if ($index === 0) fetchpriority="high" @endif
                                draggable="false"
                                class="h-full w-full object-cover"
                            >

                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-navy/90 via-navy/55 to-transparent px-3 pb-3 pt-10 text-xs font-semibold text-white">
                                {{ $item->display_label }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mx-auto mt-1 max-w-xl text-center" aria-live="polite">
                <p class="text-sm font-bold text-navy" data-infographic-coverflow-caption></p>
                <p class="mt-1 text-xs text-muted-foreground" data-infographic-coverflow-detail></p>
            </div>

            <div class="mt-5 flex items-center justify-center gap-3">
                <button
                    type="button"
                    data-infographic-coverflow-previous
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-white text-ocean shadow-sm transition hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-2"
                    aria-label="Infografis sebelumnya"
                >
                    <i data-lucide="chevron-left" class="h-5 w-5" aria-hidden="true"></i>
                </button>

                <div class="flex items-center gap-1.5" data-infographic-coverflow-pagination aria-label="Pilih infografis"></div>

                <button
                    type="button"
                    data-infographic-coverflow-next
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-white text-ocean shadow-sm transition hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-2"
                    aria-label="Infografis berikutnya"
                >
                    <i data-lucide="chevron-right" class="h-5 w-5" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</section>

@include('components.infographics.lightbox')
