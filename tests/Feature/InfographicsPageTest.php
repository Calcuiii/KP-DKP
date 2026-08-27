<?php

namespace Tests\Feature;

use App\Models\Infographic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InfographicsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_infographics_page_renders_the_catalog_in_configured_order(): void
    {
        $response = $this->get(route('infographics'));
        $content = $response->getContent();

        $response->assertOk();

        $previousPosition = -1;

        foreach (Infographic::query()->where('type', '!=', Infographic::TYPE_WOPPS)->ordered()->get() as $infographic) {
            $position = strpos($content, $infographic->caption);

            self::assertNotFalse($position);
            self::assertGreaterThan($previousPosition, $position);

            $previousPosition = $position;
        }

        self::assertSame(7, substr_count($content, 'data-infographic-lightbox-trigger'));
        self::assertStringContainsString('Pusat Informasi Visual', $content);
        self::assertStringContainsString('Akses Cepat', $content);
        self::assertStringNotContainsString('WOPPS · Seri 07/07', $content);
    }

    public function test_the_landing_page_includes_the_infographics_preview(): void
    {
        $response = $this->get(route('landing'));
        $content = $response->getContent();

        $response
            ->assertOk()
            ->assertSee(route('infographics'), false)
            ->assertSee('Magang / KP / PKL · Seri 01/07')
            ->assertSee('WOPPS · Seri 07/07')
            ->assertSee('Surat Edaran Resmi');

        self::assertSame(1, substr_count($content, 'loading="eager"'));
        self::assertSame(7, substr_count($content, 'loading="lazy"'));
    }

    public function test_infographics_are_categorized_by_service(): void
    {
        $magangResponse = $this->get(route('infographics'));
        $woppsResponse = $this->get(route('infographics', ['layanan' => 'wopps']));

        $magangResponse
            ->assertOk()
            ->assertSee('Magang / KP / PKL · Seri 01/07')
            ->assertSee('Magang / KP / PKL · Seri 06/07')
            ->assertDontSee('WOPPS · Seri 07/07');

        $woppsResponse
            ->assertOk()
            ->assertSee('WOPPS · Seri 07/07')
            ->assertDontSee('Magang / KP / PKL · Seri 01/07');

        self::assertSame(1, substr_count($woppsResponse->getContent(), 'data-infographic-lightbox-trigger'));
        self::assertSame(1, substr_count($woppsResponse->getContent(), 'data-lightbox-minimal="true"'));
        $woppsResponse
            ->assertSee('Infografis WOPPS')
            ->assertSee('Buka Infografis')
            ->assertSee('Wawancara, Observasi, Penelitian, Permintaan Data, dan Sampling')
            ->assertDontSee('Peta perjalanan infografis')
            ->assertDontSee('Menampilkan infografis khusus WOPPS');

        $magangResponse->assertDontSee('Menampilkan infografis Magang');

        self::assertSame('infografis_wopps', Infographic::query()->where('series_number', 7)->value('type'));
    }

    public function test_an_admin_can_update_an_infographic_caption_and_image(): void
    {
        $admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'Aktif',
        ]);
        $infographic = Infographic::query()->ordered()->firstOrFail();

        $response = $this->actingAs($admin)->put(
            route('admin.infographics.update', $infographic),
            [
                'caption' => 'Caption infografis terbaru',
                'alt' => 'Deskripsi gambar terbaru',
                'image' => UploadedFile::fake()->image('infografis-baru.jpg', 1200, 1600),
            ],
        );

        $infographic->refresh();

        try {
            $response->assertRedirect(route('admin.infographics'));

            self::assertSame('Caption infografis terbaru', $infographic->caption);
            self::assertSame('Deskripsi gambar terbaru', $infographic->alt);
            self::assertStringStartsWith('images/infografis/uploads/', $infographic->image_path);
            self::assertSame(1200, $infographic->image_width);
            self::assertSame(1600, $infographic->image_height);
        } finally {
            File::delete(public_path($infographic->image_path));
        }
    }
}
