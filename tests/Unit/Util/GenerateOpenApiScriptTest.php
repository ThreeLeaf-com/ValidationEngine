<?php

namespace Tests\Unit\Util;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Regression tests for util/generate-open-api.php, the Composer post-install-cmd script.
 *
 * A swagger-php major upgrade that removes a class the script uses makes `composer install` fail.
 * These tests check the script's imports and run it in a separate PHP process, so that the
 * failure appears in the test suite, not only in the install step.
 *
 * The end-to-end test overwrites target/api-docs.json, the same file `composer install` writes.
 * The other process tests run a copy of the script in a temporary sandbox directory.
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

        [$exitCode, $output] = $this->runScript($this->root . '/util/generate-open-api.php');

        $this->assertSame(0, $exitCode, "The script should exit with code 0. Output:\n{$output}");
        $this->assertStringContainsString('Swagger documentation generated successfully.', $output);
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
        $script = file_get_contents($this->root . '/util/generate-open-api.php');
        $brokenScript = str_replace('use OpenApi\\Utils\\SourceFinder;', 'use OpenApi\\MissingSourceFinder as SourceFinder;', $script);
        $this->assertNotSame($script, $brokenScript, 'The test should be able to break the SourceFinder import.');
        $autoloader = '<?php require ' . var_export($this->root . '/vendor/autoload.php', true) . ';';

        [$exitCode, $output] = $this->runInSandbox($brokenScript, $autoloader);

        $this->assertSame(1, $exitCode, "The script should exit with code 1. Output:\n{$output}");
        $this->assertStringContainsString('Error generating Swagger documentation', $output);
    }

    /** Test that a development install without swagger-php fails instead of skipping. */
    #[Test]
    #[Group('integration')]
    public function scriptFailsWhenSwaggerPhpIsMissingInDevMode()
    {
        $script = file_get_contents($this->root . '/util/generate-open-api.php');

        [$exitCode, $output] = $this->runInSandbox($script, '<?php');

        $this->assertSame(1, $exitCode, "The script should exit with code 1. Output:\n{$output}");
        $this->assertStringContainsString('Error generating Swagger documentation', $output);
    }

    /** Test that `composer install --no-dev` (COMPOSER_DEV_MODE=0) skips generation and exits with code 0. */
    #[Test]
    #[Group('integration')]
    public function scriptSkipsGenerationWithoutDevDependencies()
    {
        $script = file_get_contents($this->root . '/util/generate-open-api.php');

        [$exitCode, $output, $wroteTarget] = $this->runInSandbox($script, '<?php', ['COMPOSER_DEV_MODE' => '0']);

        $this->assertSame(0, $exitCode, "The script should exit with code 0. Output:\n{$output}");
        $this->assertStringContainsString('skipping OpenAPI generation', $output);
        $this->assertFalse($wroteTarget, 'The script should not write output when it skips.');
    }

    /**
     * Run a copy of the script in a temporary directory with its own vendor/autoload.php.
     *
     * @param string               $script     PHP source to write as util/generate-open-api.php.
     * @param string               $autoloader PHP source to write as vendor/autoload.php.
     * @param array<string,string> $env        Extra environment variables for the PHP process.
     *
     * @return array{0: int, 1: string, 2: bool} Exit code, combined output, and whether target/ was created.
     */
    private function runInSandbox(string $script, string $autoloader, array $env = []): array
    {
        $sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'generate-open-api-' . bin2hex(random_bytes(6));
        try {
            mkdir($sandbox . '/util', 0755, true);
            mkdir($sandbox . '/vendor');
            mkdir($sandbox . '/src');
            file_put_contents($sandbox . '/vendor/autoload.php', $autoloader);
            file_put_contents($sandbox . '/util/generate-open-api.php', $script);

            [$exitCode, $output] = $this->runScript($sandbox . '/util/generate-open-api.php', $env);

            return [$exitCode, $output, is_dir($sandbox . '/target')];
        } finally {
            $this->removeDirectory($sandbox);
        }
    }

    /**
     * Run a PHP script in a separate process with Xdebug off.
     *
     * @param string               $scriptPath Absolute path of the script to run.
     * @param array<string,string> $env        Extra environment variables for the process.
     *
     * @return array{0: int, 1: string} Exit code and combined standard output and error.
     */
    private function runScript(string $scriptPath, array $env = []): array
    {
        $command = [PHP_BINARY, '-d', 'xdebug.mode=off', $scriptPath];
        $environment = array_merge(getenv(), ['COMPOSER_DEV_MODE' => '1'], $env);
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, null, $environment);
        $this->assertIsResource($process, 'The PHP process should start.');
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        return [proc_close($process), (string)$output];
    }

    /**
     * Delete a directory and everything in it, if it exists.
     *
     * @param string $directory Absolute path of the directory to delete.
     */
    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }
}
