<?php
/** @noinspection PhpUnused */

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

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
    $openApi = Generator::scan($paths);
    $jsonContent = $openApi->toJson();
    file_put_contents($targetDirectory . '/api-docs.json', $jsonContent);

    echo "\nSwagger documentation generated successfully.\n\n";
    echo "Scanned paths: \n* " . implode("\n* ", $paths) . "\n\n";
    echo 'Number of paths found: ' . count($openApi->paths) . "\n\n";
} catch (Exception $e) {
    echo "\nError generating Swagger documentation: " . $e->getMessage() . "\n\n";
}
