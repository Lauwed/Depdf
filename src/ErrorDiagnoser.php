<?php

declare(strict_types=1);

namespace App;

final class ErrorDiagnoser
{
    /**
     * Translates raw pdftotext stderr output into a human-readable message.
     */
    public static function diagnose(string $stderr): string
    {
        $s = strtolower($stderr);

        if (str_contains($s, 'incorrect password') || str_contains($s, 'password')) {
            return 'Incorrect or missing password. This PDF is encrypted — open the "Password-protected PDF" section and provide the correct user or owner password.';
        }

        if (str_contains($s, 'syntax error') || str_contains($s, 'error:')) {
            return 'The PDF appears to be corrupted or uses an unsupported internal format. Try re-saving it from its original application, or use "Repair" in Acrobat Reader / Ghostscript.';
        }

        if (str_contains($s, 'error opening') || str_contains($s, "couldn't open")) {
            return 'pdftotext could not open this PDF. The file may be incomplete or damaged during upload.';
        }

        if (str_contains($s, 'invalid xref') || str_contains($s, 'pdf version')) {
            return 'This PDF uses a version or cross-reference format not supported by pdftotext. Try exporting it as PDF 1.4–1.7 from your application.';
        }

        if (str_contains($s, 'command line error') || str_contains($s, 'invalid option') || str_contains($s, 'unknown option')) {
            return 'Internal error: invalid options were passed to pdftotext. Please report this.';
        }

        $clean = trim($stderr);

        return $clean !== ''
            ? 'pdftotext reported: ' . $clean
            : 'Extraction failed with no error detail. The PDF may be encrypted, obfuscated, or in an unsupported format.';
    }
}
