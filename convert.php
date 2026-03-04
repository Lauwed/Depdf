#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Spatie\PdfToText\Pdf;
use Spatie\PdfToText\Exceptions\BinaryNotFoundException;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Exceptions\CouldNotExtractText;

// ─── Terminal colours ────────────────────────────────────────────
function bold($text)  { return "\033[1m{$text}\033[0m"; }
function red($text)   { return "\033[31m{$text}\033[0m"; }
function green($text) { return "\033[32m{$text}\033[0m"; }
function cyan($text)  { return "\033[36m{$text}\033[0m"; }
function dim($text)   { return "\033[2m{$text}\033[0m"; }

// ─── Banner ─────────────────────────────────────────────────────
echo "\n";
echo bold("  ╔══════════════════════════════════╗\n");
echo bold("  ║       PDF → Text Converter       ║\n");
echo bold("  ╚══════════════════════════════════╝\n");
echo "\n";

// ─── Arguments ──────────────────────────────────────────────────
if ($argc < 2) {
    echo "  " . bold("Usage:") . "\n";
    echo "    php convert.php <file.pdf> [options]\n\n";
    echo "  " . bold("Options:") . "\n";
    echo "    --layout       Preserve layout\n";
    echo "    --pages=1-5    Extract pages 1 to 5\n";
    echo "    --output=FILE  Save output to a file\n";
    echo "    --quiet        No console output (with --output)\n";
    echo "\n";
    echo "  " . bold("Examples:") . "\n";
    echo "    php convert.php document.pdf\n";
    echo "    php convert.php report.pdf --layout --output=report.txt\n";
    echo "    php convert.php book.pdf --pages=10-20 --layout\n";
    echo "\n";
    exit(0);
}

$pdfPath = $argv[1];
$options = [];
$outputFile = null;
$quiet = false;

// Parse arguments
for ($i = 2; $i < $argc; $i++) {
    $arg = $argv[$i];

    if ($arg === '--layout') {
        $options[] = 'layout';
    } elseif (str_starts_with($arg, '--pages=')) {
        $pages = substr($arg, 8);
        if (preg_match('/^(\d+)-(\d+)$/', $pages, $m)) {
            $options[] = 'f ' . $m[1];
            $options[] = 'l ' . $m[2];
        } elseif (is_numeric($pages)) {
            $options[] = 'f ' . $pages;
            $options[] = 'l ' . $pages;
        }
    } elseif (str_starts_with($arg, '--output=')) {
        $outputFile = substr($arg, 9);
    } elseif ($arg === '--quiet') {
        $quiet = true;
    }
}

// ─── Validation ─────────────────────────────────────────────────
if (!file_exists($pdfPath)) {
    echo "  " . red("✗ File not found: {$pdfPath}") . "\n\n";
    exit(1);
}

if (strtolower(pathinfo($pdfPath, PATHINFO_EXTENSION)) !== 'pdf') {
    echo "  " . red("✗ Not a PDF file.") . "\n\n";
    exit(1);
}

$fileSize = filesize($pdfPath);
$fileSizeFormatted = $fileSize < 1024 * 1024
    ? round($fileSize / 1024, 1) . ' KB'
    : round($fileSize / (1024 * 1024), 2) . ' MB';

echo "  " . dim("File:") . " " . bold(basename($pdfPath)) . " ({$fileSizeFormatted})\n";
if (!empty($options)) {
    echo "  " . dim("Options:") . " " . implode(', ', $options) . "\n";
}
echo "\n";

// ─── Extraction ─────────────────────────────────────────────────
echo "  " . cyan("⟳ Extracting…") . "\n";
$startTime = microtime(true);

try {
    $pdf = new Pdf();
    $pdf->setPdf($pdfPath);

    if (!empty($options)) {
        $pdf->setOptions($options);
    }

    $text = $pdf->text();
    $elapsed = round(microtime(true) - $startTime, 2);

    if (empty(trim($text))) {
        echo "  " . red("⚠ No extractable text found.") . "\n";
        echo "  " . dim("Possible causes:") . "\n";
        echo "  " . dim("  · Scanned PDF (image-only) — use an OCR tool like Tesseract") . "\n";
        echo "  " . dim("  · Text extraction disabled by encryption") . "\n";
        echo "  " . dim("  · Text rendered as vector paths instead of characters") . "\n\n";
        exit(1);
    }

    // Stats
    $chars = strlen($text);
    $words = count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY));
    $lines = substr_count($text, "\n") + 1;

    echo "  " . green("✓ Extracted successfully in {$elapsed}s") . "\n";
    echo "  " . dim("{$chars} characters · {$words} words · {$lines} lines") . "\n\n";

    // Output
    if ($outputFile) {
        file_put_contents($outputFile, $text);
        echo "  " . green("✓ Saved to: {$outputFile}") . "\n\n";

        if (!$quiet) {
            echo dim("─── Preview (first 500 characters) ──────────────────\n");
            echo substr($text, 0, 500);
            if (strlen($text) > 500) echo "\n" . dim("[…truncated]");
            echo "\n" . dim("─────────────────────────────────────────────────────\n");
        }
    } else {
        echo dim("─── Extracted text ──────────────────────────────────\n");
        echo $text . "\n";
        echo dim("─────────────────────────────────────────────────────\n");
    }

    echo "\n";
} catch (BinaryNotFoundException $e) {
    echo "  " . red("✗ pdftotext not found.") . "\n";
    echo "  " . dim("Install poppler-utils: sudo apt install poppler-utils  |  brew install poppler") . "\n\n";
    exit(1);
} catch (PdfNotFound $e) {
    echo "  " . red("✗ PDF could not be read: " . $pdfPath) . "\n";
    echo "  " . dim("Check file permissions.") . "\n\n";
    exit(1);
} catch (CouldNotExtractText $e) {
    // ⚠ Do NOT use $e->getMessage() — it includes the full pdftotext command with passwords.
    $stderr = trim($e->getProcess()->getErrorOutput());
    $s = strtolower($stderr);
    echo "  " . red("✗ Extraction failed.") . "\n";
    if (str_contains($s, 'incorrect password') || str_contains($s, 'password')) {
        echo "  " . dim("The PDF is password-protected. Use --upw=PASSWORD to provide the user password.") . "\n\n";
    } elseif (str_contains($s, 'syntax error') || str_contains($s, 'error:')) {
        echo "  " . dim("The PDF appears corrupted or uses an unsupported format. Try re-saving from the original app.") . "\n\n";
    } elseif ($stderr !== '') {
        echo "  " . dim("pdftotext: " . $stderr) . "\n\n";
    } else {
        echo "  " . dim("Unknown error. The PDF may be encrypted or in an unsupported format.") . "\n\n";
    }
    exit(1);
} catch (\Exception $e) {
    echo "  " . red("✗ Unexpected error.") . "\n";
    echo "  " . dim($e->getMessage()) . "\n\n";
    exit(1);
}
