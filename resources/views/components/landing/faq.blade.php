@php
    $faqs = [
        [
            'question' => 'Apa itu DKP Assistant?',
            'answer' => 'DKP Assistant adalah layanan chatbot berbasis AI yang membantu mahasiswa dan siswa SMK menemukan informasi seputar Kerja Praktik dan Magang di Dinas Kelautan dan Perikanan Provinsi Jawa Timur.',
        ],
        [
            'question' => 'Apakah jawaban chatbot merupakan keputusan resmi Dinas?',
            'answer' => 'Tidak. Jawaban yang diberikan DKP Assistant bersifat informatif dan berdasarkan dokumen resmi yang dikelola administrator. Untuk keputusan resmi, silakan hubungi petugas Dinas secara langsung.',
        ],
        [
            'question' => 'Informasi apa saja yang dapat ditanyakan?',
            'answer' => 'Anda dapat menanyakan informasi seputar persyaratan KP & Magang, alur pengajuan, dokumen yang dibutuhkan, pelaksanaan kegiatan, penyelesaian, serta sertifikat dan administrasi akhir.',
        ],
        [
            'question' => 'Dari mana chatbot mendapatkan informasi?',
            'answer' => 'DKP Assistant menggunakan teknologi Retrieval-Augmented Generation (RAG) yang mencari informasi dari knowledge base berisi dokumen resmi yang telah diverifikasi dan dikelola oleh administrator Dinas.',
        ],
        [
            'question' => 'Apa yang terjadi jika chatbot tidak menemukan jawaban?',
            'answer' => 'Jika tidak ditemukan informasi yang relevan, DKP Assistant akan memberitahu Anda dan menyarankan untuk menghubungi petugas Dinas secara langsung. Pertanyaan tersebut juga akan dicatat untuk pembaruan knowledge base.',
        ],
        [
            'question' => 'Bagaimana cara menghubungi petugas?',
            'answer' => 'Anda dapat menghubungi petugas Dinas Kelautan dan Perikanan Provinsi Jawa Timur melalui kontak resmi yang tertera di website resmi Dinas atau mengunjungi kantor langsung pada jam kerja.',
        ],
    ];
@endphp

<section id="faq" class="bg-background py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h2 class="text-3xl font-bold text-navy">
                Pertanyaan Umum
            </h2>
        </div>

        <div class="space-y-3" data-faq-list>
            @foreach ($faqs as $index => $faq)
                <div
                    class="overflow-hidden rounded-2xl border border-border bg-white"
                    data-faq-item
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between px-5 py-4 text-left"
                        data-faq-button
                        aria-expanded="false"
                        aria-controls="faq-answer-{{ $index }}"
                    >
                        <span class="pr-4 text-sm font-semibold text-navy">
                            {{ $faq['question'] }}
                        </span>

                        <i
                            data-lucide="chevron-down"
                            class="h-4 w-4 flex-shrink-0 text-muted-foreground"
                            data-faq-icon
                            aria-hidden="true"
                        ></i>
                    </button>

                    <div
                        id="faq-answer-{{ $index }}"
                        class="hidden border-t border-border px-5 pb-4 pt-3 text-sm leading-relaxed text-muted-foreground"
                        data-faq-answer
                    >
                        {{ $faq['answer'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
