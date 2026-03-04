<?php
$currentPage  = 'privacy';
$scheme       = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host         = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
$canonicalUrl = $scheme . '://' . $host . '/privacy.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — DePDF - PDF to Text Converter</title>
    <meta name="description" content="Privacy Policy for the DePDF - PDF to Text Converter. No data retained, no cookies, no tracking. Uploaded files are deleted immediately after conversion.">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="<?= $canonicalUrl ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .legal-page {
            max-width: 760px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        .legal-page h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .legal-page h1 span {
            color: var(--accent);
        }

        .legal-page .subtitle {
            color: var(--text-muted);
            margin-bottom: 3rem;
            font-size: 0.95rem;
        }

        .legal-page h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin: 2rem 0 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .legal-page p {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .legal-page ul {
            color: var(--text-muted);
            line-height: 1.8;
            margin: 0.5rem 0 1rem 1.5rem;
            font-size: 0.95rem;
        }

        .legal-page ul li {
            margin-bottom: 0.4rem;
        }

        .highlight-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--accent);
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 1px 4px rgba(26, 26, 46, 0.05);
        }

        .highlight-box p {
            margin: 0;
            color: var(--text);
        }

        code {
            font-family: var(--mono);
            font-size: 0.85em;
            background: var(--surface-2);
            padding: 0.15em 0.4em;
            border-radius: 4px;
            color: var(--accent);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--accent);
        }

        .legal-footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .legal-footer a {
            color: var(--text-muted);
            text-decoration: none;
        }

        .legal-footer a:hover {
            color: var(--accent);
        }
    </style>
</head>

<body>
    <div class="legal-page">
        <a href="index.php" class="back-link">← Back to the app</a>

        <h1>Privacy <span>Policy</span></h1>
        <p class="subtitle">Last updated: March 2026</p>

        <div class="highlight-box">
            <p><strong>In short:</strong> Your PDF files are processed on the server and deleted immediately after conversion. No personal data is collected. No cookies. No tracking.</p>
        </div>

        <h2>1. Data controller</h2>
        <p>This application is open-source, self-hosted software. If you are using a third-party instance, the data controller is the person or organisation hosting that instance. For the reference instance, contact details are listed in the <a href="legal.php" style="color:var(--accent)">legal notice</a>.</p>

        <h2>2. Data processed</h2>
        <p>The only data processed is the <strong>PDF file</strong> you voluntarily upload. This file is:</p>
        <ul>
            <li>Temporarily saved on the server in an <code>uploads/</code> directory that is not publicly accessible.</li>
            <li><strong>Deleted immediately</strong> after text extraction, whether successful or not.</li>
            <li>Never transmitted to any third party, cloud service, or external API.</li>
        </ul>
        <p>The extracted text is temporarily written to an <code>output/</code> file to enable download. This file is <strong>deleted as soon as it is served</strong> to your browser.</p>

        <h2>3. Browsing data</h2>
        <p>This application collects <strong>no browsing data</strong>:</p>
        <ul>
            <li>No cookies are set.</li>
            <li>No analytics or tracking tools are used (no Google Analytics, Matomo, etc.).</li>
            <li>No IP addresses are logged by the application.</li>
            <li>No browser fingerprinting is performed.</li>
        </ul>
        <p>Web server logs (e.g. Apache, Nginx) may exist depending on the host configuration. These logs are managed by the instance administrator.</p>

        <h2>4. Legal basis (GDPR)</h2>
        <p>Processing of your PDF file is based on your <strong>explicit consent</strong> (Art. 6.1.a GDPR): you voluntarily initiate the upload. The retention period is minimal — a few seconds for extraction — followed by immediate deletion.</p>

        <h2>5. Data transfers</h2>
        <p>No data is transferred outside the server hosting this application. Extraction is performed entirely locally via <code>pdftotext</code> (poppler-utils), a system utility with no network connectivity.</p>

        <h2>6. Security</h2>
        <ul>
            <li>Uploaded files are validated (MIME type, 10 MB size limit) before any processing.</li>
            <li>File names are sanitized to prevent injection attacks.</li>
            <li>The <code>uploads/</code> directory is not accessible via the browser.</li>
        </ul>

        <h2>7. Your rights</h2>
        <p>Under the GDPR, you have the right to access, rectify, erase, and port your data. Since no data is retained beyond a few seconds, these rights are satisfied by design (<em>privacy by design</em>).</p>
        <p>For any questions, contact the instance administrator via the <a href="legal.php" style="color:var(--accent)">legal notice</a>.</p>

        <h2>8. Changes</h2>
        <p>This policy may be updated. The date at the top of the page indicates the current version. Since no user data is collected, no prior notice is required for updates.</p>

        <div class="legal-footer">
            <a href="index.php">← App</a>
            <a href="legal.php">Legal notice</a>
            <span>MIT License — <a href="https://github.com" target="_blank">Source code</a></span>
        </div>
    </div>
</body>

</html>