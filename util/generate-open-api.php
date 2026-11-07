<?php
/** @noinspection PhpUnused */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;
use OpenApi\Utils\SourceFinder;

$paths = [
    __FILE__,
    __DIR__ . '/../src',
];

/**
 * @OA\Info(title="Generated API", version="1.0")
 */
class OpenApiSpec
{
}

try {
    $targetDirectory = __DIR__ . '/../target';
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0755, true);
    }
    $openApi = (new Generator())->generate(new SourceFinder($paths));
    $jsonContent = $openApi->toJson();
    if (file_put_contents($targetDirectory . '/api-docs.json', $jsonContent) === false) {
        throw new RuntimeException('Cannot write ' . $targetDirectory . '/api-docs.json');
    }

    echo "\nSwagger documentation generated successfully.\n\n";
    echo "Scanned paths: \n* " . implode("\n* ", $paths) . "\n\n";
    $pathCount = is_countable($openApi->paths) ? count($openApi->paths) : 0;
    echo 'Number of paths found: ' . $pathCount . "\n\n";
} catch (Throwable $e) {
    echo "\nError generating Swagger documentation: " . $e->getMessage() . "\n\n";
    exit(1);
}
