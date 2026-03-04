#!/bin/bash

# ╔══════════════════════════════════════════════╗
# ║   Script d'installation macOS - PDF → Texte ║
# ╚══════════════════════════════════════════════╝
# Requires: macOS 11+ with Homebrew

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║   Installation macOS - PDF → Texte App       ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo ""

# 1. Vérifier Homebrew
echo -e "${CYAN}[1/4]${NC} Vérification de Homebrew..."
if command -v brew &> /dev/null; then
    echo -e "  ${GREEN}✓${NC} Homebrew trouvé"
else
    echo -e "  ${DIM}Installation de Homebrew...${NC}"
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    echo -e "  ${GREEN}✓${NC} Homebrew installé"
fi

# 2. Vérifier PHP
echo -e "${CYAN}[2/4]${NC} Vérification de PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
    MINOR=$(php -r "echo PHP_MINOR_VERSION;")
    if [ "$MAJOR" -gt 8 ] || ([ "$MAJOR" -eq 8 ] && [ "$MINOR" -ge 1 ]); then
        echo -e "  ${GREEN}✓${NC} PHP ${PHP_VERSION} trouvé"
    else
        echo -e "  ${DIM}PHP ${PHP_VERSION} trop ancien, installation de PHP 8.3...${NC}"
        brew install php
        echo -e "  ${GREEN}✓${NC} PHP installé"
    fi
else
    echo -e "  ${DIM}Installation de PHP...${NC}"
    brew install php
    echo -e "  ${GREEN}✓${NC} PHP installé"
fi

# 3. Installer poppler (fournit pdftotext)
echo -e "${CYAN}[3/4]${NC} Vérification de pdftotext (poppler)..."
if command -v pdftotext &> /dev/null; then
    echo -e "  ${GREEN}✓${NC} pdftotext trouvé : $(which pdftotext)"
else
    echo -e "  ${DIM}Installation de poppler...${NC}"
    brew install poppler
    echo -e "  ${GREEN}✓${NC} poppler installé"
fi

# 4. Installer Composer si nécessaire
echo -e "${CYAN}[4/4]${NC} Vérification de Composer..."
if command -v composer &> /dev/null; then
    echo -e "  ${GREEN}✓${NC} Composer trouvé"
else
    echo -e "  ${DIM}Installation de Composer...${NC}"
    brew install composer
    echo -e "  ${GREEN}✓${NC} Composer installé"
fi

# Installer les dépendances PHP
echo -e "${CYAN}[+]${NC} Installation des dépendances PHP..."
composer install --quiet --no-interaction
echo -e "  ${GREEN}✓${NC} Dépendances installées"

# Créer les dossiers
mkdir -p uploads output

echo ""
echo -e "${GREEN}${BOLD}✓ Installation terminée !${NC}"
echo ""
echo -e "${BOLD}Utilisation :${NC}"
echo ""
echo -e "  ${CYAN}Interface web :${NC}"
echo -e "    php -S localhost:8080 -t public"
echo -e "    ${DIM}→ Ouvrir http://localhost:8080 dans votre navigateur${NC}"
echo ""
echo -e "  ${CYAN}Ligne de commande :${NC}"
echo -e "    php convert.php mon-document.pdf"
echo -e "    php convert.php rapport.pdf --layout --output=rapport.txt"
echo ""
