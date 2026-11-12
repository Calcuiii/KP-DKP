<form action="{{ route('guestbook.start') }}" method="POST" class="{{ $hasPending ? 'mt-4' : '' }}">
    @csrf
    <label for="guestbook-phone" class="block text-sm font-semibold text-navy">Nomor WhatsApp aktif</label>
    <input id="guestbook-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel"
           required maxlength="32" placeholder="Contoh: 081234567890" aria-describedby="phone-help"
           class="mt-2 w-full rounded-xl border border-border bg-white px-4 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-ocean focus:outline-none focus:ring-2 focus:ring-ocean/20">
    <p id="phone-help" class="mt-2 text-xs leading-relaxed text-muted-foreground">Format 08, 628, atau +628.</p>
    <button type="submit" @disabled(! $guestbookUrl)
            class="mt-5 w-full rounded-xl bg-ocean px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
        Lanjutkan
    </button>
</form>
