<section id="infografis" class="bg-background py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <span class="text-sm font-semibold text-teal">Panduan visual</span>

                <h2 class="mt-2 text-3xl font-bold text-navy sm:text-4xl">
                    Seri Infografis Magang dan PKL
                </h2>

                <p class="mt-3 leading-relaxed text-muted-foreground">
                    Lihat ringkasan informasi resmi untuk membantu memahami proses Magang dan PKL.
                </p>
            </div>

            <a
                href="{{ route('infographics') }}"
                class="inline-flex items-center justify-center rounded-xl border border-ocean px-4 py-2.5 text-sm font-semibold text-ocean transition-colors hover:bg-secondary"
            >
                Lihat Semua Infografis
            </a>
        </div>

        <div class="mt-8" data-infographic-carousel>
            <div
                class="flex snap-x snap-mandatory gap-5 overflow-x-auto pb-4"
                data-infographic-carousel-track
                aria-label="Carousel infografis"
            >
                @foreach ($items as $index => $item)
                    <div class="w-[86%] shrink-0 snap-start sm:w-[calc(50%-0.625rem)] lg:w-[calc(33.333%-0.834rem)]">
                        <x-infographics.card
                            :item="$item"
                            :index="$index"
                            :loading="$index === 0 ? 'eager' : 'lazy'"
                        />
                    </div>
                @endforeach
            </div>

            <div class="mt-2 flex justify-end gap-2">
                <button
                    type="button"
                    data-infographic-carousel-previous
                    class="rounded-lg border border-border px-3 py-2 text-sm font-semibold text-ocean transition-colors hover:bg-secondary"
                    aria-label="Infografis sebelumnya"
                >
                    Sebelumnya
                </button>

                <button
                    type="button"
                    data-infographic-carousel-next
                    class="rounded-lg border border-border px-3 py-2 text-sm font-semibold text-ocean transition-colors hover:bg-secondary"
                    aria-label="Infografis berikutnya"
                >
                    Berikutnya
                </button>
            </div>
        </div>
    </div>
</section>

@include('components.infographics.lightbox')
