@props([
    'item',
    'index',
    'loading' => 'lazy',
])

<article class="overflow-hidden rounded-2xl border border-border bg-white shadow-sm">
    <button
        type="button"
        data-infographic-lightbox-trigger
        data-infographic-index="{{ $index }}"
        data-image-src="{{ $item->image_url }}"
        data-image-alt="{{ $item->alt }}"
        data-image-caption="{{ $item->caption }}"
        data-image-width="{{ $item->image_width }}"
        data-image-height="{{ $item->image_height }}"
        class="block w-full text-left focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-2"
        aria-label="Buka {{ $item->caption }} ukuran penuh"
    >
        <img
            src="{{ $item->image_url }}"
            alt="{{ $item->alt }}"
            width="{{ $item->image_width }}"
            height="{{ $item->image_height }}"
            loading="{{ $loading }}"
            @if ($loading === 'eager') fetchpriority="high" @endif
            class="aspect-[3/4] w-full bg-secondary object-cover transition-transform duration-300 hover:scale-[1.02]"
        >
    </button>

    <div class="p-4">
        <p class="text-sm font-semibold text-navy">
            {{ $item->caption }}
        </p>

        <p class="mt-1 text-xs text-muted-foreground">
            {{ $item->type === 'infografis' ? 'Panduan visual layanan Magang dan PKL' : 'Dokumen resmi Pemerintah Provinsi Jawa Timur' }}
        </p>
    </div>
</article>
