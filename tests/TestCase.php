<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    private string $uploadedRegistryFixture;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        // The canonical corpus is fixed; local administrator uploads are not test fixtures.
        $this->uploadedRegistryFixture = sys_get_temp_dir().'/simelayur-test-registry-'.bin2hex(random_bytes(12)).'.json';
        file_put_contents($this->uploadedRegistryFixture, '{"schema_version":"1.0","documents":[]}');
        config(['knowledge-base.uploaded_registry_path' => $this->uploadedRegistryFixture]);
    }

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);
        foreach (['', '.lock', '.lifecycle.lock'] as $suffix) {
            @unlink($this->uploadedRegistryFixture.$suffix);
        }
        parent::tearDown();
    }
}
