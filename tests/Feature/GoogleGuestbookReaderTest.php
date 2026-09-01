<?php

namespace Tests\Feature;

use App\Services\GoogleGuestbookReader;
use App\Support\GuestbookPhone;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleGuestbookReaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->travelTo(CarbonImmutable::parse('2026-08-31 12:15:00', 'Asia/Jakarta'));
        config(['services.google_guestbook.spreadsheet_id' => 'test-sheet',
            'services.google_guestbook.range' => "'Form Responses 1'!A:Z",
            'services.google_guestbook.phone_column' => 'Nomor Telepon',
            'services.google_guestbook.timestamp_column' => 'Timestamp']);
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

    private function responses(array $phones, array $times, array $headers = ['Timestamp', 'Other', 'Other 2', 'Nomor Telepon']): void
    {
        Http::fake([
            '*values:batchGet*' => Http::response(['valueRanges' => [['values' => $phones], ['values' => $times]]]),
            '*values/*' => Http::response(['values' => [$headers]]),
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

    public function test_matches_recent_response_and_only_reads_phone_and_timestamp_columns(): void
    {
        $epoch = CarbonImmutable::create(1899, 12, 30, 0, 0, 0, 'UTC');
        $serial = $epoch->diffInSeconds(CarbonImmutable::parse('2026-08-31 12:14:00', 'UTC')) / 86400;
        $this->responses([['081111111111'], [], ['+62 812-3456-7890']], [[1], [], [$serial]]);
        $this->assertTrue($this->reader()->hasRecentResponse(GuestbookPhone::fingerprint('6281234567890'), now()->subMinutes(2)->timestamp));
        Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains(urldecode($r->url()), "ranges='Form Responses 1'!D2:D&ranges='Form Responses 1'!A2:A"));
        Http::assertSentCount(2);
    }

    public function test_rejects_old_future_invalid_and_other_phone_responses(): void
    {
        $this->responses([['081234567890'], ['081234567890'], ['081234567890'], ['081111111111']],
            [['30/08/2026 12:00:00'], ['31/08/2026 12:16:00'], ['invalid'], ['31/08/2026 12:14:00']]);
        $this->assertFalse($this->reader()->hasRecentResponse(GuestbookPhone::fingerprint('6281234567890'), now()->subMinutes(2)->timestamp));
    }

    public function test_supports_explicit_text_timestamp_format(): void
    {
        $this->responses([['081234567890']], [['31/08/2026 12:14:00']]);
        $this->assertTrue($this->reader()->hasRecentResponse(GuestbookPhone::fingerprint('6281234567890'), now()->subMinutes(2)->timestamp));
    }

    public function test_different_visitors_are_matched_independently(): void
    {
        $this->responses([['081234567890'], ['081111111111']], [['31/08/2026 12:14:00'], ['31/08/2026 12:14:01']]);
        foreach (['6281234567890', '6281111111111'] as $phone) {
            $this->assertTrue($this->reader()->hasRecentResponse(GuestbookPhone::fingerprint($phone), now()->subMinutes(2)->timestamp));
        }
        $this->assertFalse($this->reader()->hasRecentResponse(GuestbookPhone::fingerprint('6282222222222'), now()->subMinutes(2)->timestamp));
    }

    public function test_missing_headers_fail_closed(): void
    {
        $this->responses([], [], ['Timestamp', 'Name']);
        $this->expectException(\RuntimeException::class);
        $this->reader()->hasRecentResponse('hash', now()->timestamp);
    }

    public function test_google_forbidden_response_is_not_treated_as_verified(): void
    {
        Http::fake(['*' => Http::response([], 403)]);
        $this->expectException(RequestException::class);
        $this->reader()->hasRecentResponse('hash', now()->timestamp);
    }
}
