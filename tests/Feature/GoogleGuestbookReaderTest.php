<?php

namespace Tests\Feature;

use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleGuestbookReaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.google_guestbook.spreadsheet_id' => 'test-sheet',
            'services.google_guestbook.range' => "'Form Responses 1'!A:Z",
            'services.google_guestbook.phone_column' => 'Nomor Telepon']);
    }

    private function reader(): GoogleGuestbookReader
    {
        return new class extends GoogleGuestbookReader
        {
            protected function accessToken(): string
            {
                return 'fake-token';
            }
        };
    }

    private function responses(array $phones, array $headers = ['Timestamp', 'Other', 'Other 2', 'Nomor Telepon']): void
    {
        Http::fake([
            '*values/%27Form%20Responses%201%27%21A1%3AZ1*' => Http::response(['values' => [$headers]]),
            '*' => Http::response(['values' => $phones]),
        ]);
    }

    public function test_phone_variations_normalize_and_invalid_values_are_rejected(): void
    {
        foreach (['081234567890', '+62 812-3456-7890', '6281234567890', '81234567890'] as $phone) {
            $this->assertSame('6281234567890', GuestbookPhone::normalize($phone));
        }
        foreach (['', '123', '081234567890abc', '081234567890 / 081111111111', '+14155552671'] as $phone) {
            $this->assertNull(GuestbookPhone::normalize($phone));
        }
    }

    public function test_matches_an_existing_response_and_only_reads_the_phone_column(): void
    {
        $this->responses([['081111111111'], [], ['+62 812-3456-7890']]);
        $this->assertTrue($this->reader()->hasResponse(GuestbookPhone::fingerprint('6281234567890')));
        Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains(urldecode($r->url()), "'Form Responses 1'!D2:D"));
        Http::assertSentCount(2);
    }

    public function test_an_old_response_remains_valid_on_a_later_verification(): void
    {
        $this->responses([['081231987217']]);
        $this->assertTrue($this->reader()->hasResponse(GuestbookPhone::fingerprint('6281231987217')));
    }

    public function test_different_visitors_are_matched_independently(): void
    {
        $this->responses([['081234567890'], ['081111111111']]);
        foreach (['6281234567890', '6281111111111'] as $phone) {
            $this->assertTrue($this->reader()->hasResponse(GuestbookPhone::fingerprint($phone)));
        }
        $this->assertFalse($this->reader()->hasResponse(GuestbookPhone::fingerprint('6282222222222')));
    }

    public function test_missing_headers_fail_closed(): void
    {
        $this->responses([], ['Timestamp', 'Name']);
        $this->expectException(\RuntimeException::class);
        $this->reader()->hasResponse('hash');
    }

    public function test_google_forbidden_response_is_not_treated_as_verified(): void
    {
        Http::fake(['*' => Http::response([], 403)]);
        $this->expectException(RequestException::class);
        $this->reader()->hasResponse('hash');
    }
}
