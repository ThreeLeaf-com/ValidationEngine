<?php

/**
 * Find the first instance of % coverage and return that value.
 * As of this writing, the first instance of % coverage is the number of lines covered, which is the most significant number.
 *
 * @param string $coverageReportFile The coverage report file.
 *
 * @return float|int The percentage of coverage
 * @noinspection SpellCheckingInspection
 */
function getCoveragePercentageFromHtml(string $coverageReportFile): float|int
{
    $htmlContent = file_get_contents($coverageReportFile);
    if (preg_match('/<span class="sr-only">([0-9]+\.[0-9]+)% covered \(success\)<\/span>/', $htmlContent, $matches)) {
        return round(floatval($matches[1]));
    }
    return 0;
}

/**
 * Generate a Coverage badge for use in the README.md file.
 *
 * @param float|int $coveragePercent The coverage percentage
 * @param string    $badgeFileName   The badge file output path
 *
 * @return void
 */
function createBadge(float|int $coveragePercent, string $badgeFileName): void
{
    $color = $coveragePercent >= 80 ? 'brightgreen' : ($coveragePercent >= 50 ? 'yellow' : 'red');
    $badge = "https://img.shields.io/badge/Coverage-$coveragePercent%25-$color";

    file_put_contents($badgeFileName, file_get_contents($badge));
}

/** The PhpUnit coverage report. */
$coverageReportFile = __DIR__ . '/../target/coverage/index.html';
if (file_exists($coverageReportFile)) {
    $badgeFileName = __DIR__ . '/../public/images/coverage-badge.svg';

    $coveragePercent = getCoveragePercentageFromHtml($coverageReportFile);
    createBadge($coveragePercent, $badgeFileName);
}
