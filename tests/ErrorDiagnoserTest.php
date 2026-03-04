<?php

declare(strict_types=1);

namespace Tests;

use App\ErrorDiagnoser;
use PHPUnit\Framework\TestCase;

class ErrorDiagnoserTest extends TestCase
{
    public function testPasswordError(): void
    {
        $message = ErrorDiagnoser::diagnose('Error: Incorrect password');

        $this->assertStringContainsStringIgnoringCase('password', $message);
    }

    public function testPasswordErrorVariant(): void
    {
        $message = ErrorDiagnoser::diagnose('Command line error: PDF password');

        // "password" triggers the password branch before "command line error"
        $this->assertStringContainsStringIgnoringCase('password', $message);
    }

    public function testSyntaxError(): void
    {
        $message = ErrorDiagnoser::diagnose('Syntax Error (525): ...');

        $this->assertStringContainsStringIgnoringCase('corrupted', $message);
    }

    public function testGenericErrorColon(): void
    {
        $message = ErrorDiagnoser::diagnose('Error: unknown problem');

        $this->assertStringContainsStringIgnoringCase('corrupted', $message);
    }

    public function testErrorOpeningFile(): void
    {
        $message = ErrorDiagnoser::diagnose('Error opening document.pdf');

        $this->assertStringContainsStringIgnoringCase('open', $message);
    }

    public function testCouldNotOpenFile(): void
    {
        $message = ErrorDiagnoser::diagnose("Couldn't open file.");

        $this->assertStringContainsStringIgnoringCase('open', $message);
    }

    public function testInvalidXref(): void
    {
        $message = ErrorDiagnoser::diagnose('invalid xref table');

        $this->assertStringContainsStringIgnoringCase('version', $message);
    }

    public function testPdfVersionKeyword(): void
    {
        $message = ErrorDiagnoser::diagnose('PDF version 2.0 not supported');

        $this->assertStringContainsStringIgnoringCase('version', $message);
    }

    public function testCommandLineError(): void
    {
        $message = ErrorDiagnoser::diagnose('Command line error unrecognized flag');

        $this->assertStringContainsStringIgnoringCase('Internal error', $message);
    }

    public function testInvalidOption(): void
    {
        $message = ErrorDiagnoser::diagnose('Invalid option: -foo');

        $this->assertStringContainsStringIgnoringCase('Internal error', $message);
    }

    public function testUnknownOption(): void
    {
        $message = ErrorDiagnoser::diagnose('Unknown option -bar');

        $this->assertStringContainsStringIgnoringCase('Internal error', $message);
    }

    public function testGenericStderr(): void
    {
        $message = ErrorDiagnoser::diagnose('Some other pdftotext message');

        $this->assertStringStartsWith('pdftotext reported:', $message);
    }

    public function testEmptyStderr(): void
    {
        $message = ErrorDiagnoser::diagnose('');

        $this->assertStringContainsString('no error detail', $message);
    }

    public function testWhitespaceOnlyStderr(): void
    {
        $message = ErrorDiagnoser::diagnose("   \n  ");

        $this->assertStringContainsString('no error detail', $message);
    }
}
