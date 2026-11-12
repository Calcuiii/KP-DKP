<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->environment('local')) {
    throw new RuntimeException('Local only.');
}
$secrets = parse_ini_file(base_path('.codex-artifacts/attendance-local/integration.env'), false, INI_SCANNER_RAW);
$fixtures = json_decode(file_get_contents(base_path('.codex-artifacts/attendance-local/fixtures.json')), true);
$url = $secrets['SI_MELAYUR_URL'];
if ($url !== 'http://127.0.0.1:8000/api/participants/verify') {
    throw new RuntimeException('Unexpected destination.');
}
$snapshot = fn () => hash('sha256', json_encode([
    DB::table('participants')->orderBy('id')->get(),
    DB::table('participant_applications')->orderBy('id')->get(),
]));
$before = $snapshot();
$cases = [
    ['no_token', $fixtures['active']['email'], null, 401],
    ['wrong_token', $fixtures['active']['email'], 'invalid-local-smoke-token-not-a-real-credential', 401],
    ['invalid_email', 'invalid', $secrets['SI_MELAYUR_API_KEY'], 422],
    ['not_found', 'attendance.local.missing@example.test', $secrets['SI_MELAYUR_API_KEY'], 404],
    ['active', $fixtures['active']['email'], $secrets['SI_MELAYUR_API_KEY'], 200],
    ['inactive', $fixtures['inactive']['email'], $secrets['SI_MELAYUR_API_KEY'], 200],
    ['ambiguous', $fixtures['ambiguous']['email'], $secrets['SI_MELAYUR_API_KEY'], 409],
    ['incomplete', $fixtures['incomplete']['email'], $secrets['SI_MELAYUR_API_KEY'], 409],
];
if (isset($fixtures['google_test'])) {
    $cases[] = ['google_test', $fixtures['google_test']['email'], $secrets['SI_MELAYUR_API_KEY'], 200];
}
$report = [];
foreach ($cases as [$label, $email, $token, $expected]) {
    $request = Http::acceptJson()->asJson()->timeout(15);
    if ($token !== null) {
        $request = $request->withToken($token);
    }
    try {
        $response = $request->post($url, ['email' => $email]);
    } catch (Throwable $e) {
        echo $label.': transport failure ('.get_class($e).')'.PHP_EOL;
        exit(1);
    }
    $body = $response->json();
    $ok = $response->status() === $expected && is_array($body);
    if ($expected === 200 && $ok) {
        $p = $body['participant'] ?? [];
        $keys = array_keys($p);
        sort($keys);
        $expectedKeys = ['id', 'email', 'full_name', 'status', 'is_active', 'start_date', 'end_date'];
        sort($expectedKeys);
        $ok = $keys === $expectedKeys && is_string($p['id']) && is_bool($p['is_active'])
            && $p['is_active'] === ($label !== 'inactive') && $p['status'] === 'accepted'
            && $p['start_date'] === '2026-11-01' && $p['end_date'] === '2026-11-30'
            && in_array((int) $p['id'], $fixtures[$label]['application_ids'], true);
        if ($label === 'google_test') {
            $body['participant']['email'] = 's***@gmail.com';
            $body['participant']['full_name'] = '[akun uji disetujui pengguna]';
        }
    }
    $report[] = ['case' => $label, 'status' => $response->status(), 'pass' => $ok, 'response' => $body];
    echo json_encode(end($report), JSON_UNESCAPED_SLASHES).PHP_EOL;
}
$unchanged = hash_equals($before, $snapshot());
file_put_contents(base_path('.codex-artifacts/attendance-local/http-results.json'), json_encode(['checked_at' => now('Asia/Jakarta')->toIso8601String(), 'data_unchanged' => $unchanged, 'cases' => $report], JSON_PRETTY_PRINT));
echo 'Participant/application data unchanged: '.($unchanged ? 'yes' : 'NO').PHP_EOL;
exit($unchanged && ! in_array(false, array_column($report, 'pass'), true) ? 0 : 1);
