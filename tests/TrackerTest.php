<?php

use PHPUnit\Framework\TestCase;
use Anago\Tools\Tracker;

class TrackerTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = __DIR__ . '/tmp';
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Supprime tous les fichiers JSON de test après chaque test
        array_map('unlink', glob($this->testDir . "/*.json"));
    }

    public function testWriteTrackingToJsonCreatesFile(): void
    {
        $tracker = new Tracker();
        $input = json_encode([
            'visitor' => ['id' => 'visitor123'],
            'account' => ['id' => 208, 'email' => 'test@example.com'],
            'pages' => ['url' => 'https://example.com/home', 'title' => 'Home']
        ]);

        $result = $tracker->writeTrackingToJson($input, $this->testDir);

        $this->assertEquals(200, $result['status']);
        $this->assertFileExists($result['file_path']);

        $data = json_decode(file_get_contents($result['file_path']), true);
        $this->assertArrayHasKey('visitor', $data);
        $this->assertArrayHasKey('pages', $data);
        $this->assertEquals(1, $data['pages']['nbView'] ?? $data['pages'][0]['nbView']);
    }

    public function testWriteTrackingInvalidJson(): void
    {
        $tracker = new Tracker();
        $result = $tracker->writeTrackingToJson('invalid-json', $this->testDir);

        $this->assertEquals(400, $result['status']);
        $this->assertEquals('Invalid JSON', $result['message']);
    }
}
