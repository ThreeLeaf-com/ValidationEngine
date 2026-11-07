<?php

namespace Tests\Unit\Util;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for util/generate-open-api.php, the Composer post-install-cmd script.
 *
 * A swagger-php major upgrade that removes a class the script uses makes `composer install` fail.
 * These tests check the script's imports and run it in a separate PHP process, so that the
 * failure appears in the test suite, not only in the install step.
 *
 * The end-to-end test overwrites target/api-docs.json, the same file `composer install` writes.
 */
class GenerateOpenApiScriptTest extends TestCase
{
    /** Absolute path to the repository root. */
    private string $root;

    /** Resolve the repository root before each test. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 3);
    }

    /** Test that every symbol the script imports exists with the installed swagger-php version. */
    #[Test]
    public function scriptImportsResolveToExistingSymbols()
    {
        $source = file_get_contents($this->root . '/util/generate-open-api.php');
        preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+)(?:\s+as\s+\w+)?\s*;/m', $source, $matches);

        $this->assertNotEmpty($matches[1], 'The script should import at least one class.');
        $useLineCount = preg_match_all('/^use\s/m', $source);
        $this->assertSame($useLineCount, count($matches[1]), 'Every use statement should be a single class import this test can check.');
        foreach ($matches[1] as $name) {
            $exists = class_exists($name) || interface_exists($name) || trait_exists($name) || enum_exists($name);
            $this->assertTrue($exists, "The script imports {$name}, which does not exist.");
        }
    }

    /** Test that the script runs to completion and writes a fresh OpenAPI document. */
    #[Test]
    #[Group('integration')]
    public function scriptGeneratesOpenApiDocument()
    {
        $documentPath = $this->root . '/target/api-docs.json';
        if (is_file($documentPath)) {
            unlink($documentPath);
        }
        $command = escapeshellarg(PHP_BINARY) . ' -d xdebug.mode=off '
            . escapeshellarg($this->root . '/util/generate-open-api.php') . ' 2>&1';

        exec($command, $output, $exitCode);

        $outputText = implode("\n", $output);
        $this->assertSame(0, $exitCode, "The script should exit with code 0. Output:\n{$outputText}");
        $this->assertStringContainsString('Swagger documentation generated successfully.', $outputText);
        $this->assertFileExists($documentPath, 'The script should write a new OpenAPI document.');
        $document = json_decode(file_get_contents($documentPath), true);
        $this->assertIsArray($document, 'The OpenAPI document should be a JSON object.');
        $this->assertArrayHasKey('openapi', $document, 'The OpenAPI document should declare its version.');
    }

    /** Test that the script exits with code 1 when generation fails, so the install fails too. */
    #[Test]
    #[Group('integration')]
    public function scriptExitsWithErrorWhenGenerationFails()
    {
        $sandbox = sys_get_temp_dir() . '/generate-open-api-' . bin2hex(random_bytes(6));
        mkdir($sandbox . '/util', 0755, true);
        mkdir($sandbox . '/vendor');
        mkdir($sandbox . '/src');
        file_put_contents(
            $sandbox . '/vendor/autoload.php',
            '<?php require ' . var_export($this->root . '/vendor/autoload.php', true) . ';'
        );
        $script = file_get_contents($this->root . '/util/generate-open-api.php');
        $brokenScript = str_replace('use OpenApi\\Utils\\SourceFinder;', 'use OpenApi\\MissingSourceFinder as SourceFinder;', $script);
        $this->assertNotSame($script, $brokenScript, 'The test should be able to break the SourceFinder import.');
        file_put_contents($sandbox . '/util/generate-open-api.php', $brokenScript);

        try {
            $command = escapeshellarg(PHP_BINARY) . ' -d xdebug.mode=off '
                . escapeshellarg($sandbox . '/util/generate-open-api.php') . ' 2>&1';
            exec($command, $output, $exitCode);
        } finally {
            $this->removeDirectory($sandbox);
        }

        $outputText = implode("\n", $output);
        $this->assertSame(1, $exitCode, "The script should exit with code 1. Output:\n{$outputText}");
        $this->assertStringContainsString('Error generating Swagger documentation', $outputText);
    }

    /**
     * Delete a directory and everything in it.
     *
     * @param string $directory Absolute path of the directory to delete.
     */
    private function removeDirectory(string $directory): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }
}
