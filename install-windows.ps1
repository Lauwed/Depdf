# ╔══════════════════════════════════════════════════╗
# ║  Script d'installation Windows - PDF → Texte App ║
# ╚══════════════════════════════════════════════════╝
# Requires: Windows 10/11, PowerShell 5+ (run as Administrator)
# Uses Chocolatey (https://chocolatey.org) for package management

#Requires -RunAsAdministrator

$ErrorActionPreference = "Stop"

function Write-Step($n, $total, $msg) {
    Write-Host "[$n/$total] $msg" -ForegroundColor Cyan
}
function Write-OK($msg) {
    Write-Host "  [OK] $msg" -ForegroundColor Green
}
function Write-Info($msg) {
    Write-Host "  --> $msg" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "=================================================" -ForegroundColor White
Write-Host "  Installation Windows - PDF to Text App" -ForegroundColor White
Write-Host "=================================================" -ForegroundColor White
Write-Host ""

# ── 1. Chocolatey ────────────────────────────────────────────────────────────
Write-Step 1 4 "Verification de Chocolatey..."
if (Get-Command choco -ErrorAction SilentlyContinue) {
    Write-OK "Chocolatey trouve : $(choco --version)"
} else {
    Write-Info "Installation de Chocolatey..."
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    # Reload PATH
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
    Write-OK "Chocolatey installe"
}

# ── 2. PHP ───────────────────────────────────────────────────────────────────
Write-Step 2 4 "Verification de PHP (8.1+)..."
$phpOk = $false
if (Get-Command php -ErrorAction SilentlyContinue) {
    $phpVer = php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;"
    $parts  = $phpVer -split "\."
    if ([int]$parts[0] -gt 8 -or ([int]$parts[0] -eq 8 -and [int]$parts[1] -ge 1)) {
        Write-OK "PHP $phpVer trouve"
        $phpOk = $true
    }
}
if (-not $phpOk) {
    Write-Info "Installation de PHP via Chocolatey..."
    choco install php --version=8.3.0 -y --no-progress
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
    Write-OK "PHP installe"
}

# ── 3. Poppler (pdftotext) ───────────────────────────────────────────────────
Write-Step 3 4 "Verification de pdftotext (poppler)..."
if (Get-Command pdftotext -ErrorAction SilentlyContinue) {
    Write-OK "pdftotext trouve : $(Get-Command pdftotext | Select-Object -ExpandProperty Source)"
} else {
    Write-Info "Installation de poppler via Chocolatey..."
    choco install poppler -y --no-progress
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
    Write-OK "poppler installe"
}

# ── 4. Composer ──────────────────────────────────────────────────────────────
Write-Step 4 4 "Verification de Composer..."
if (Get-Command composer -ErrorAction SilentlyContinue) {
    Write-OK "Composer trouve"
} else {
    Write-Info "Installation de Composer via Chocolatey..."
    choco install composer -y --no-progress
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
    Write-OK "Composer installe"
}

# ── Dependances PHP ──────────────────────────────────────────────────────────
Write-Host "[+] Installation des dependances PHP..." -ForegroundColor Cyan
composer install --no-interaction --quiet
Write-OK "Dependances installees"

# ── Dossiers ─────────────────────────────────────────────────────────────────
New-Item -ItemType Directory -Force -Path "uploads" | Out-Null
New-Item -ItemType Directory -Force -Path "output"  | Out-Null

Write-Host ""
Write-Host "=================================================" -ForegroundColor Green
Write-Host "  Installation terminee !" -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Interface web :" -ForegroundColor Cyan
Write-Host "  php -S localhost:8080 -t public"
Write-Host "  Ouvrir http://localhost:8080 dans votre navigateur"
Write-Host ""
Write-Host "Ligne de commande :" -ForegroundColor Cyan
Write-Host "  php convert.php mon-document.pdf"
Write-Host "  php convert.php rapport.pdf --layout --output=rapport.txt"
Write-Host ""
