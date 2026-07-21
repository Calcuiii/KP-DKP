<div class="shrink-0 border-t border-border bg-white px-4 py-3 sm:px-6">
    <form data-chat-form class="mx-auto max-w-3xl">
        @csrf

        <div class="flex items-end gap-2 rounded-2xl border border-border bg-input-background px-4 py-3 transition focus-within:border-ocean/40 focus-within:ring-4 focus-within:ring-ocean/5">
            <textarea
                data-chat-input
                rows="1"
                maxlength="500"
                required
                placeholder="Tanyakan informasi tentang KP dan Magang..."
                class="max-h-32 min-h-5 flex-1 resize-none bg-transparent text-sm leading-5 text-navy outline-none placeholder:text-muted-foreground"
            ></textarea>

            <div class="flex shrink-0 items-center gap-2">
                <span data-chat-character-count class="text-[10px] text-muted-foreground">0/500</span>

                <button
                    type="submit"
                    data-chat-send
                    disabled
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean text-white transition hover:bg-navy disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Kirim pertanyaan"
                >
                    <i data-lucide="send" class="h-4 w-4"></i>
                </button>
            </div>
        </div>

        <div data-chat-error class="mt-2 hidden rounded-xl border border-destructive/20 bg-red-50 px-3 py-2 text-xs text-destructive" role="alert"></div>

        <p class="mt-2 text-center text-[10px] leading-4 text-muted-foreground">
            DKP Assistant dapat menghasilkan jawaban yang kurang tepat. Pastikan kembali informasi penting melalui layanan resmi.
        </p>
    </form>
</div>
