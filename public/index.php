<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\ErrorDiagnoser;
use Spatie\PdfToText\Pdf;
use Spatie\PdfToText\Exceptions\BinaryNotFoundException;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Exceptions\CouldNotExtractText;

// ─── Security headers ────────────────────────────────────────────
// Sent on every response (HTML page, JSON API, file downloads)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; img-src 'self' data:; connect-src 'self'");

// ─── Configuration ──────────────────────────────────────────────
$uploadDir = __DIR__ . '/../uploads/';
$outputDir = __DIR__ . '/../output/';
$maxFileSize = 10 * 1024 * 1024; // 10 MB

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

// ─── Handle API requests ────────────────────────────────────────
$response = ['success' => false, 'message' => '', 'text' => '', 'filename' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Handle file upload + conversion
    if (isset($_FILES['pdf_file'])) {
        $file = $_FILES['pdf_file'];

        // Validation — map PHP upload error codes to readable messages
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'The file exceeds the server\'s maximum upload size (php.ini). Try a smaller file or ask the administrator to raise upload_max_filesize.',
                UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the maximum size declared in the form.',
                UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded — the connection may have been interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE    => 'No file was received by the server. Make sure you selected a file before clicking Convert.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error: the temporary upload directory is missing. Contact the administrator.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: unable to write the uploaded file to disk. The server may be out of disk space.',
                UPLOAD_ERR_EXTENSION  => 'The upload was blocked by a PHP extension on the server.',
            ];
            $response['message'] = $uploadErrors[$file['error']]
                ?? 'Upload error (code ' . (int)$file['error'] . '). Please try again.';
            echo json_encode($response);
            exit;
        }

        if ($file['size'] > $maxFileSize) {
            $response['message'] = 'File exceeds the 10 MB size limit.';
            echo json_encode($response);
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            $response['message'] = 'Only PDF files are accepted. Detected MIME type: '
                . htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8') . '.';
            echo json_encode($response);
            exit;
        }

        // Save uploaded file
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $timestamp = date('Ymd_His');
        $pdfPath = $uploadDir . $safeName . '_' . $timestamp . '.pdf';

        if (!move_uploaded_file($file['tmp_name'], $pdfPath)) {
            $response['message'] = 'Unable to save the uploaded file. Check server disk space and directory permissions.';
            echo json_encode($response);
            exit;
        }

        // ─── Build pdftotext options ─────────────────────────────
        $options = [];

        // Output mode: -layout | -raw | -htmlmeta
        $mode = in_array($_POST['mode'] ?? '', ['layout', 'raw', 'htmlmeta']) ? $_POST['mode'] : 'layout';
        $options[] = $mode;

        // Page range: -f <n> / -l <n>
        if (isset($_POST['first_page']) && $_POST['first_page'] !== '' && ctype_digit($_POST['first_page']) && (int)$_POST['first_page'] > 0) {
            $options[] = 'f ' . (int)$_POST['first_page'];
        }
        if (isset($_POST['last_page']) && $_POST['last_page'] !== '' && ctype_digit($_POST['last_page']) && (int)$_POST['last_page'] > 0) {
            $options[] = 'l ' . (int)$_POST['last_page'];
        }

        // Resolution: -r <dpi>
        if (isset($_POST['resolution']) && $_POST['resolution'] !== '' && ctype_digit($_POST['resolution'])) {
            $r = (int)$_POST['resolution'];
            if ($r > 0 && $r !== 72) $options[] = 'r ' . $r;
        }

        // Crop area: -x <n> -y <n> -W <n> -H <n>
        foreach (['crop_x' => 'x', 'crop_y' => 'y', 'crop_w' => 'W', 'crop_h' => 'H'] as $field => $flag) {
            if (isset($_POST[$field]) && $_POST[$field] !== '' && is_numeric($_POST[$field])) {
                $options[] = $flag . ' ' . (int)$_POST[$field];
            }
        }

        // Encoding: -enc <name>
        $allowedEncodings = ['UTF-8', 'Latin1', 'ASCII7', 'Symbol', 'ZapfDingbats', 'UCS-2'];
        $encoding = in_array($_POST['encoding'] ?? '', $allowedEncodings) ? $_POST['encoding'] : 'UTF-8';
        if ($encoding !== 'UTF-8') $options[] = 'enc ' . $encoding;

        // End-of-line: -eol unix|dos|mac
        if (in_array($_POST['eol'] ?? '', ['unix', 'dos', 'mac'])) {
            $options[] = 'eol ' . $_POST['eol'];
        }

        // No page breaks: -nopgbrk
        if (isset($_POST['nopgbrk']) && $_POST['nopgbrk'] === '1') {
            $options[] = 'nopgbrk';
        }

        // Passwords: -upw / -opw
        // Passed as process array arguments — no shell injection risk (Symfony Process uses proc_open).
        // Whitespace stripped: pdftotext does not support spaces in passwords via CLI.
        $userPw  = preg_replace('/\s+/', '', $_POST['user_password']  ?? '');
        $ownerPw = preg_replace('/\s+/', '', $_POST['owner_password'] ?? '');
        if (!empty($userPw))  $options[] = 'upw ' . $userPw;
        if (!empty($ownerPw)) $options[] = 'opw ' . $ownerPw;

        // ─── Extract text ────────────────────────────────────────
        try {
            $text = (new Pdf())
                ->setPdf($pdfPath)
                ->setOptions($options)
                ->text();

            // Delete uploaded PDF immediately after extraction (privacy: no PDF stored)
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if (empty(trim($text))) {
                $response['message'] = 'No extractable text found in this PDF. Common causes: '
                    . '(1) scanned/image-only PDF — text is pixels, not characters; '
                    . '(2) text extraction disabled by encryption; '
                    . '(3) all text rendered as vector paths. '
                    . 'An OCR tool (e.g. Tesseract) is needed for scanned documents.';
                $response['text'] = '';
            } else {
                $ext = ($mode === 'htmlmeta') ? 'html' : 'txt';
                $outPath = $outputDir . $safeName . '_' . $timestamp . '.' . $ext;
                file_put_contents($outPath, $text);

                $response['success']  = true;
                $response['message']  = 'Text extracted successfully!';
                $response['text']     = $text;
                $response['filename'] = $originalName;
                $response['mode']     = $mode;
                $response['download'] = basename($outPath);
            }

        } catch (BinaryNotFoundException $e) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            $response['message'] = 'Server configuration error: pdftotext (poppler-utils) is not installed or not executable. '
                . 'Run the install script or install poppler-utils manually.';

        } catch (PdfNotFound $e) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            $response['message'] = 'Internal error: the uploaded PDF could not be read. '
                . 'This may indicate a permissions issue on the server.';

        } catch (CouldNotExtractText $e) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            // ⚠ Do NOT use $e->getMessage() — ProcessFailedException includes the full command
            //   line with its arguments, which would expose any password supplied by the user.
            $stderr = $e->getProcess()->getErrorOutput();
            $response['message'] = ErrorDiagnoser::diagnose($stderr);

        } catch (\Exception $e) {
            if (file_exists($pdfPath)) unlink($pdfPath);
            $response['message'] = 'Unexpected error during extraction. Please try again.';
        }

        echo json_encode($response);
        exit;
    }

    // Handle download request (POST)
    if (isset($_POST['download'])) {
        $filename = basename($_POST['download']);

        // Whitelist: only serve .txt and .html files produced by this app
        if (!preg_match('/\.(txt|html)$/', $filename)) {
            http_response_code(400);
            exit;
        }

        $filepath = $outputDir . $filename;
        if (file_exists($filepath)) {
            $mime = str_ends_with($filename, '.html') ? 'text/html' : 'text/plain';
            // Strip chars that could inject additional HTTP headers
            $safeDownloadName = preg_replace('/[\r\n"\\\\]/', '', $filename);
            header('Content-Type: ' . $mime . '; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $safeDownloadName . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            unlink($filepath); // Delete output file after serving (privacy)
            exit;
        }
    }
}

// Handle GET download
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    $filename = basename($_GET['download']);

    // Whitelist: only serve .txt and .html files produced by this app
    if (!preg_match('/\.(txt|html)$/', $filename)) {
        http_response_code(400);
        exit;
    }

    $filepath = $outputDir . $filename;
    if (file_exists($filepath)) {
        $mime = str_ends_with($filename, '.html') ? 'text/html' : 'text/plain';
        // Strip chars that could inject additional HTTP headers
        $safeDownloadName = preg_replace('/[\r\n"\\\\]/', '', $filename);
        header('Content-Type: ' . $mime . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeDownloadName . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        unlink($filepath); // Delete output file after serving (privacy)
        exit;
    }
}

$scheme       = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host         = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
$canonicalUrl = $scheme . '://' . $host . '/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PDF to Text Converter — Free Online Tool</title>
    <meta name="description" content="Convert PDF to plain text online for free. Preserve layout, select page ranges, choose encoding, and handle password-protected PDFs. Privacy-first: files are deleted immediately after conversion.">
    <meta name="keywords" content="pdf to text, pdf converter, extract text from pdf, online pdf tool, pdftotext, pdf text extractor, free pdf converter">
    <meta name="author" content="PDF to Text">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#e94560">

    <link rel="canonical" href="<?= $canonicalUrl ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $canonicalUrl ?>">
    <meta property="og:title" content="PDF to Text Converter — Free Online Tool">
    <meta property="og:description" content="Convert PDF to plain text online for free. Preserve layout, select page ranges, choose encoding, and handle password-protected PDFs. Privacy-first: files deleted immediately.">
    <meta property="og:site_name" content="PDF to Text">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="PDF to Text Converter — Free Online Tool">
    <meta name="twitter:description" content="Convert PDF to plain text online for free. Preserve layout, select page ranges, choose encoding, and handle password-protected PDFs.">

    <!-- Structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "PDF to Text Converter",
        "url": <?= json_encode($canonicalUrl) ?>,
        "description": "Convert PDF to plain text online for free. Extract text with layout preservation, page range selection, encoding options, and password support.",
        "applicationCategory": "UtilitiesApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "featureList": [
            "Drag and drop file upload",
            "Layout preservation mode",
            "Page range selection",
            "Multiple encoding support",
            "Password-protected PDF support",
            "Privacy-first: no file storage"
        ]
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>PDF <span>→</span> Text</h1>
            <p>Convert your PDF files to plain text in one click</p>
        </div>

        <div class="drop-zone" id="dropZone">
            <span class="icon">📄</span>
            <div class="label">Drop your PDF here or click to browse</div>
            <div class="sublabel">PDF only · 10 MB max</div>
            <input type="file" id="fileInput" accept=".pdf,application/pdf">
        </div>

        <div class="file-info" id="fileInfo">
            <div class="file-icon">📄</div>
            <div class="file-details">
                <div class="file-name" id="fileName"></div>
                <div class="file-size" id="fileSize"></div>
            </div>
            <button class="remove-btn" id="removeFile" title="Remove">×</button>
        </div>

        <!-- ─── Options panel ───────────────────────────────────── -->
        <div class="options-panel">

            <!-- Output mode -->
            <div class="options">
                <label class="option-chip">
                    <input type="radio" name="mode" value="layout" checked> 📐 Layout
                </label>
                <label class="option-chip">
                    <input type="radio" name="mode" value="raw"> 📝 Raw
                </label>
                <label class="option-chip">
                    <input type="radio" name="mode" value="htmlmeta"> 🌐 HTML + Meta
                </label>
            </div>

            <!-- Page range + Resolution -->
            <div class="field-row">
                <div class="field-group">
                    <label class="field-label">First page</label>
                    <input type="number" id="firstPage" min="1" placeholder="1" class="field-input">
                </div>
                <div class="field-group">
                    <label class="field-label">Last page</label>
                    <input type="number" id="lastPage" min="1" placeholder="last" class="field-input">
                </div>
                <div class="field-group">
                    <label class="field-label">Resolution (DPI)</label>
                    <input type="number" id="resolution" min="1" max="2400" value="72" class="field-input">
                </div>
            </div>

            <!-- Advanced options toggle -->
            <button type="button" class="toggle-btn" id="advancedToggle">
                ⚙ Advanced options <span class="toggle-arrow">▾</span>
            </button>

            <div class="collapsible-panel" id="advancedPanel">
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Encoding</label>
                        <select id="encoding" class="field-select">
                            <option value="UTF-8">UTF-8 (default)</option>
                            <option value="Latin1">Latin-1</option>
                            <option value="ASCII7">ASCII-7</option>
                            <option value="UCS-2">UCS-2</option>
                            <option value="Symbol">Symbol</option>
                            <option value="ZapfDingbats">ZapfDingbats</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">End of line</label>
                        <select id="eol" class="field-select">
                            <option value="">System (default)</option>
                            <option value="unix">Unix · LF</option>
                            <option value="dos">DOS/Windows · CRLF</option>
                            <option value="mac">Mac · CR</option>
                        </select>
                    </div>
                </div>

                <div class="options">
                    <label class="option-chip">
                        <input type="checkbox" id="nopgbrk"> 🚫 No page breaks
                    </label>
                </div>

                <div class="section-subtitle">Crop area (pixels)</div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">X</label>
                        <input type="number" id="cropX" min="0" placeholder="—" class="field-input">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Y</label>
                        <input type="number" id="cropY" min="0" placeholder="—" class="field-input">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Width (W)</label>
                        <input type="number" id="cropW" min="1" placeholder="—" class="field-input">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Height (H)</label>
                        <input type="number" id="cropH" min="1" placeholder="—" class="field-input">
                    </div>
                </div>
            </div>

            <!-- Password toggle -->
            <button type="button" class="toggle-btn" id="passwordToggle">
                🔐 Password-protected PDF <span class="toggle-arrow">▾</span>
            </button>

            <div class="collapsible-panel" id="passwordPanel">
                <div class="field-row">
                    <div class="field-group field-group--wide">
                        <label class="field-label">User password <code>-upw</code></label>
                        <input type="password" id="userPw" class="field-input" placeholder="••••••••" autocomplete="off">
                    </div>
                    <div class="field-group field-group--wide">
                        <label class="field-label">Owner password <code>-opw</code></label>
                        <input type="password" id="ownerPw" class="field-input" placeholder="••••••••" autocomplete="off">
                    </div>
                </div>
            </div>

        </div><!-- end .options-panel -->

        <button class="convert-btn" id="convertBtn" disabled>Convert to text</button>

        <div class="status" id="status"></div>

        <div class="result" id="result">
            <div class="result-header">
                <h2>Result</h2>
                <div class="result-actions">
                    <button class="action-btn" id="copyBtn">📋 Copy</button>
                    <a class="action-btn" id="downloadBtn" href="#" download>💾 Download</a>
                </div>
            </div>
            <div class="text-output" id="textOutput"></div>
            <div class="stats" id="stats"></div>
        </div>

        <div class="footer">
            Powered by <a href="https://github.com/spatie/pdf-to-text" target="_blank">spatie/pdf-to-text</a> &amp; poppler-utils
            &nbsp;·&nbsp;
            <a href="privacy.php">Privacy</a>
            &nbsp;·&nbsp;
            <a href="legal.php">Legal notice</a>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
