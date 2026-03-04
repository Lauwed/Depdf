<?php
$currentPage  = 'legal';
$scheme       = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host         = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
$canonicalUrl = $scheme . '://' . $host . '/legal.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Notice — DePDF - PDF to Text Converter</title>
    <meta name="description" content="Legal Notice for the DePDF - PDF to Text Converter. Open-source MIT-licensed self-hosted PHP application powered by pdftotext and spatie/pdf-to-text.">
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

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .info-table td {
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .info-table td:first-child {
            color: var(--text-muted);
            width: 40%;
            background: var(--surface-2);
        }

        .info-table td:last-child {
            color: var(--text);
            background: var(--surface);
        }

        .info-table a {
            color: var(--accent);
            text-decoration: none;
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

        code {
            font-family: var(--mono);
            font-size: 0.85em;
            background: var(--surface-2);
            padding: 0.15em 0.4em;
            border-radius: 4px;
            color: var(--accent);
        }
    </style>
</head>

<body>
    <div class="legal-page">
        <a href="index.php" class="back-link">← Back to the app</a>

        <h1>Legal <span>Notice</span></h1>
        <p class="subtitle">In accordance with Belgian legislation applicable to information society services.</p>

        <h2>1. Publisher</h2>
        <table class="info-table">
            <tr>
                <td>Name / Organisation</td>
                <td>[YOUR NAME OR ORGANISATION]</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>[YOUR ADDRESS], Belgium</td>
            </tr>
            <tr>
                <td>Email</td>
                <td><a href="mailto:contact@example.be">contact@example.be</a></td>
            </tr>
            <tr>
                <td>Country</td>
                <td>Belgium</td>
            </tr>
        </table>
        <p>This application is free open-source software distributed under the MIT licence. It is not operated for commercial purposes.</p>

        <h2>2. Hosting</h2>
        <p>This application is designed to be <strong>self-hosted</strong>. The hosting provider is therefore the person or organisation deploying this instance. If you are using a public instance, the specific hosting details for that instance should be provided here.</p>
        <table class="info-table">
            <tr>
                <td>Hosting type</td>
                <td>Self-hosted</td>
            </tr>
            <tr>
                <td>Location</td>
                <td>[SERVER COUNTRY / REGION]</td>
            </tr>
        </table>

        <h2>3. Intellectual property</h2>
        <p>The source code of this application is published under the <strong>MIT licence</strong> and is freely available at:</p>
        <p><a href="https://github.com/[YOUR-ACCOUNT]/pdf-to-text-app" target="_blank" style="color:var(--accent)">[LINK TO GITHUB REPOSITORY]</a></p>
        <p>The MIT licence allows you to use, copy, modify and distribute this software, provided the copyright notice is retained. See the <code>LICENSE</code> file for the full text.</p>
        <p>Third-party dependencies used:</p>
        <ul>
            <li><a href="https://github.com/spatie/pdf-to-text" target="_blank" style="color:var(--accent)">spatie/pdf-to-text</a> — MIT Licence</li>
            <li><a href="https://symfony.com/components/Process" target="_blank" style="color:var(--accent)">symfony/process</a> — MIT Licence</li>
            <li><a href="https://poppler.freedesktop.org" target="_blank" style="color:var(--accent)">poppler-utils (pdftotext)</a> — GPL v2 Licence</li>
        </ul>

        <h2>4. Liability</h2>
        <p>This application is provided <strong>"as is"</strong>, without warranty of any kind. The publisher cannot be held liable for any direct or indirect damages arising from the use of this software.</p>
        <p>Users are responsible for ensuring they hold the necessary rights over any PDF files they submit for conversion. The publisher accepts no liability for the content of processed files.</p>

        <h2>5. Personal data</h2>
        <p>Data processing is detailed in the <a href="privacy.php" style="color:var(--accent)">Privacy Policy</a>. In summary: no personal data is retained. Uploaded PDF files are deleted immediately after conversion.</p>
        <p>In accordance with the <strong>General Data Protection Regulation (GDPR)</strong> applicable in Belgium, and the <strong>Belgian Act of 30 July 2018</strong> on the protection of natural persons with regard to the processing of personal data, you have the right to access, rectify, erase and port your personal data.</p>
        <p>To exercise these rights, contact: <a href="mailto:contact@example.be" style="color:var(--accent)">contact@example.be</a></p>
        <p>In the event of a dispute, you may lodge a complaint with the <strong>Data Protection Authority (DPA)</strong>:<br>
            Rue de la Presse 35, 1000 Brussels — <a href="https://www.dataprotectionauthority.be" target="_blank" style="color:var(--accent)">www.dataprotectionauthority.be</a></p>

        <h2>6. Applicable law</h2>
        <p>This legal notice is governed by <strong>Belgian law</strong>. In the event of a dispute, the competent Belgian courts shall have sole jurisdiction.</p>
        <p>Applicable legal references:</p>
        <ul>
            <li>Belgian Act of 11 March 2003 on certain legal aspects of information society services</li>
            <li>Regulation (EU) 2016/679 (GDPR)</li>
            <li>Belgian Act of 30 July 2018 on the protection of personal data</li>
        </ul>

        <div class="legal-footer">
            <a href="index.php">← App</a>
            <a href="privacy.php">Privacy Policy</a>
            <span>MIT License — <a href="https://github.com" target="_blank">Source code</a></span>
        </div>
    </div>
</body>

</html>