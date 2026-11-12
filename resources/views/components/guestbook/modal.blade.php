<dialog data-guestbook-dialog aria-labelledby="checkin-title" aria-describedby="guestbook-modal-description"
        class="guestbook-dialog fixed inset-0 m-auto w-[calc(100%-2rem)] max-w-lg overflow-visible rounded-2xl bg-white p-0 text-navy shadow-2xl">
    <button type="button" data-guestbook-close aria-label="Tutup verifikasi Buku Tamu"
            class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-slate-100 hover:text-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ocean">
        <span aria-hidden="true" class="text-2xl leading-none">×</span>
    </button>
    <div class="max-h-[85dvh] overflow-y-auto rounded-2xl" data-guestbook-content></div>
    <p id="guestbook-modal-description" class="sr-only">Verifikasi Buku Tamu untuk layanan Magang, KP, dan PKL tanpa meninggalkan halaman chatbot.</p>
</dialog>
