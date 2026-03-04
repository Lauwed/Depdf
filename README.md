# DePDF

Open-source PHP application to convert PDF files to plain text, powered by [spatie/pdf-to-text](https://github.com/spatie/pdf-to-text) and `pdftotext` (poppler-utils).

<img width="1721" height="1016" alt="Screenshot of the DePDF app" src="https://github.com/user-attachments/assets/369491d7-ee0d-45de-b8f1-28dc71e00a40" />

**MIT License** · No data retained · Self-hostable

---

## Features

- Web interface with drag-and-drop
- Extraction in layout, raw, or HTML+meta mode
- Advanced options: page range, encoding, DPI, crop area, passwords
- CLI tool for batch processing
- **Privacy first**: uploaded files are deleted immediately after conversion

---

## Installation

### Docker (all platforms)

The easiest way to run the app on any OS without installing PHP or poppler locally.

```bash
git clone https://github.com/lauwed/pdf-to-text-app.git
cd pdf-to-text-app
docker compose up --build
```

Open **http://localhost:8080** in your browser. The image is built with PHP 8.3 + Apache + poppler-utils.

> **Production tip:** Remove the `public` volume mount in `docker-compose.yml` to serve only the built image without live-reload.

---

### Linux / WSL / Ubuntu

```bash
git clone https://github.com/lauwed/pdf-to-text-app.git
cd pdf-to-text-app
chmod +x install.sh
./install.sh
```

The script automatically installs PHP, poppler-utils, Composer and PHP dependencies.

### macOS

```bash
git clone https://github.com/lauwed/pdf-to-text-app.git
cd pdf-to-text-app
chmod +x install-mac.sh
./install-mac.sh
```

Requires [Homebrew](https://brew.sh). The script installs PHP, poppler and Composer via Homebrew.

### Windows

Open **PowerShell as Administrator**, then:

```powershell
git clone https://github.com/lauwed/pdf-to-text-app.git
cd pdf-to-text-app
.\install-windows.ps1
```

The script installs [Chocolatey](https://chocolatey.org) (if not present), PHP, poppler and Composer.

> **Note:** If script execution is blocked, run first:
>
> ```powershell
> Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
> ```

### Manual install (all OS)

```bash
# 1. Install pdftotext
#    Ubuntu/Debian : sudo apt install poppler-utils
#    macOS         : brew install poppler
#    Windows       : choco install poppler

# 2. Install PHP dependencies
composer install
```

---

## Usage

### Web interface

```bash
php -S localhost:8080 -t public
```

Open **http://localhost:8080** in your browser.

### Command line

```bash
# Basic extraction
php convert.php document.pdf

# Preserve layout
php convert.php document.pdf --layout

# Specific page range
php convert.php book.pdf --pages=10-20

# Save to a file
php convert.php report.pdf --layout --output=report.txt

# Silent mode (with --output)
php convert.php report.pdf --output=report.txt --quiet
```

| Option          | Description                          |
| --------------- | ------------------------------------ |
| `--layout`      | Preserve the PDF layout              |
| `--pages=N-M`   | Extract pages N to M only            |
| `--output=FILE` | Save result to a file                |
| `--quiet`       | No console preview (with `--output`) |

---

## Privacy & file security

Uploaded files are **never** retained on the server. The flow is:

1. The PDF is temporarily saved to `uploads/` for the duration of extraction.
2. **Immediately after** extraction (success or failure), the file is deleted.
3. The extracted text is written to `output/` to enable download.
4. That output file is **deleted as soon as it is served** to the browser.

Excerpt from [public/index.php](public/index.php):

```php
// Extract text
$text = (new Pdf())->setPdf($pdfPath)->setOptions($options)->text();

// Delete uploaded PDF immediately after extraction (privacy)
if (file_exists($pdfPath)) {
    unlink($pdfPath);
}

// ... write output file ...

// Delete output file after serving it for download
readfile($filepath);
unlink($filepath);
```

No cookies, no analytics, no browsing data collected.
See the [Privacy Policy](public/privacy.php) for details.

---

## Project structure

```
pdf-to-text-app/
├── public/
│   ├── index.php       # Web UI (frontend + backend)
│   ├── privacy.php     # Privacy Policy
│   ├── legal.php       # Legal Notice (Belgium)
│   ├── robots.txt      # Crawler directives
│   ├── script.js       # Frontend logic
│   └── styles.css      # Dark theme
├── src/
│   └── ErrorDiagnoser.php  # Translates pdftotext stderr into user messages
├── tests/
│   └── ErrorDiagnoserTest.php  # PHPUnit test suite
├── uploads/            # Temporary — PDFs deleted after conversion
├── output/             # Temporary — text files deleted after download
├── convert.php         # CLI tool
├── phpunit.xml.dist    # PHPUnit configuration
├── install.sh          # Install script — Linux/WSL/Ubuntu
├── install-mac.sh      # Install script — macOS (Homebrew)
├── install-windows.ps1 # Install script — Windows (PowerShell + Chocolatey)
├── Dockerfile          # Docker image (PHP 8.3 + Apache + poppler-utils)
├── docker-compose.yml  # Docker Compose for quick start
├── composer.json       # PHP dependencies (prod + dev)
├── CONTRIBUTING.md     # Contribution guidelines
├── LICENSE             # MIT License
└── README.md
```

---

## Limitations

- Scanned PDFs (image-only) are not supported — an OCR tool like Tesseract would be needed.
- Maximum upload size in web mode: 10 MB.
- `pdftotext` must be installed on the system (handled by install scripts and Docker).

---

## Security — known limitations & best practices

These points cannot be fixed at the application layer alone. They are documented here as guidance for anyone deploying this project publicly.

### Passwords visible in the process list

PDF passwords are passed as command-line arguments to `pdftotext`. On a multi-user system, they are briefly visible in `ps aux` to other users on the same host. `pdftotext` does not support receiving passwords via stdin.

**Mitigation:** Run the app in an isolated single-user container (Docker covers this). Never deploy on a shared host where other users can inspect the process list.

### No rate limiting

The conversion endpoint has no request frequency limit. A malicious client could spam uploads and exhaust server CPU or disk space.

**Mitigation:** Place the app behind a reverse proxy and configure request throttling there. Example with nginx:

```nginx
limit_req_zone $binary_remote_addr zone=pdf:10m rate=5r/m;

server {
    location / {
        limit_req zone=pdf burst=3 nodelay;
        proxy_pass http://localhost:8080;
    }
}
```

### No CSRF protection

The upload form does not use CSRF tokens. Modern browsers block cross-origin `multipart/form-data` submissions, so the practical risk is low for a conversion tool that performs no destructive state changes. If you extend this app with actions that modify server state (delete, admin), add CSRF tokens.

### `uploads/` and `output/` outside the document root

When the document root is set to `public/` (default in the install scripts and Docker image), `uploads/` and `output/` are **not** web-accessible. If you deploy with a custom server configuration, ensure these directories are never served directly.

---

## Testing

The project uses [PHPUnit](https://phpunit.de) for unit tests. Install dev dependencies first, then run the suite:

```bash
composer install
composer test
```

Tests live in `tests/` and cover the `src/` classes (PSR-4, `App\` namespace). The current suite tests `App\ErrorDiagnoser`, which translates raw `pdftotext` stderr output into user-facing messages. Any new class added under `src/` should be accompanied by a matching test file under `tests/`.

---

## SEO & meta tags

The web UI (`public/index.php`) ships with full on-page SEO out of the box:

| Tag / feature               | Details                                                                       |
| --------------------------- | ----------------------------------------------------------------------------- |
| `<title>`                   | "DePDF - PDF to Text Converter — Free Online Tool"                            |
| `<meta name="description">` | Concise, keyword-rich description                                             |
| `<link rel="canonical">`    | Dynamically built from `HTTP_HOST`                                            |
| Open Graph (`og:*`)         | Title, description, URL, type, site name                                      |
| Twitter Card                | `summary` card with title and description                                     |
| JSON-LD (`WebApplication`)  | Schema.org structured data with feature list and free offer                   |
| `<meta name="theme-color">` | Brand accent colour `#e94560`                                                 |
| Font preconnect             | `<link rel="preconnect">` for `fonts.googleapis.com` and `fonts.gstatic.com`  |
| `robots.txt`                | `public/robots.txt` — allows all crawlers, excludes upload/output directories |

Legal subpages (`privacy.php`, `legal.php`) carry `noindex, follow` to avoid indexing thin content.

---

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for the full guidelines.

Quick summary:

1. **Fork** the repository on GitHub
2. Create a branch for your feature:
   ```bash
   git checkout -b feature/my-feature
   ```
3. Add tests for any new or changed behaviour
4. Run the test suite: `composer test`
5. Push your branch and open a **Pull Request**

---

## License

[MIT](LICENSE) — uses [spatie/pdf-to-text](https://github.com/spatie/pdf-to-text) (MIT) and poppler-utils (GPL v2).
