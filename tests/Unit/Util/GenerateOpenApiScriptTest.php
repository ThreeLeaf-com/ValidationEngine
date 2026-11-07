<?php

namespace Tests\Unit\Util;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Regression tests for util/generate-open-api.php, the Composer post-install-cmd script.
 *
 * A swagger-php major upgrade that removes a class the script uses makes `composer install` fail.
 * These tests check the script's imports and run it in a separate PHP process, so that the
 * failure appears in the test suite, not only in the install step.
 */
class GenerateOpenApiScriptTest extends TestCase
{
    /** Test that every class the script imports exists with the installed swagger-php version. */
    #[Test]
    public function scriptImportsResolveToExistingClasses()
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/util/generate-open-api.php');
        preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+);/m', $source, $matches);

        $this->assertNotEmpty($matches[1], 'The script should import at least one class.');
        foreach ($matches[1] as $className) {
            $this->assertTrue(class_exists($className), "The script imports {$className}, which does not exist.");
        }
    }

    /** Test that the script runs to completion and writes the OpenAPI document. */
    #[Test]
    public function scriptGeneratesOpenApiDocument()
    {
        $root = dirname(__DIR__, 3);
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/util/generate-open-api.php') . ' 2>&1';

        exec($command, $output, $exitCode);

        $outputText = implode("\n", $output);
        $this->assertSame(0, $exitCode, "The script should exit with code 0. Output:\n{$outputText}");
        $this->assertStringContainsString('Swagger documentation generated successfully.', $outputText);
        $this->assertFileExists($root . '/target/api-docs.json');
        $this->assertJson(file_get_contents($root . '/target/api-docs.json'));
    }
}
