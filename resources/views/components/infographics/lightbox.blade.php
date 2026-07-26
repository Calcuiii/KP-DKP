<div
    data-infographic-lightbox
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy/85 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="infographic-lightbox-caption"
    aria-hidden="true"
>
    <button
        type="button"
        data-infographic-lightbox-close
        class="absolute inset-0 cursor-default"
        aria-label="Tutup gambar"
    ></button>

    <div class="relative z-10 flex max-h-full w-full max-w-5xl flex-col items-center">
        <img
            data-infographic-lightbox-image
            src=""
            alt=""
            width="1200"
            height="1600"
            class="max-h-[calc(100vh-12rem)] max-w-full rounded-xl bg-white object-contain shadow-2xl"
        >

        <p
            id="infographic-lightbox-caption"
            data-infographic-lightbox-caption
            class="mt-3 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-navy"
        ></p>

        <div class="mt-3 flex flex-wrap justify-center gap-2">
            <button
                type="button"
                data-infographic-lightbox-previous
                class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-navy transition-colors hover:bg-secondary"
                aria-label="Gambar sebelumnya"
            >
                Sebelumnya
            </button>

            <button
                type="button"
                data-infographic-lightbox-next
                class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-navy transition-colors hover:bg-secondary"
                aria-label="Gambar berikutnya"
            >
                Berikutnya
            </button>

            <button
                type="button"
                data-infographic-lightbox-close
                class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-navy transition-colors hover:bg-secondary"
                aria-label="Tutup gambar"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
