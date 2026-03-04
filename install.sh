#!/bin/bash

# ╔══════════════════════════════════════════════╗
# ║   Script d'installation - PDF → Texte App   ║
# ╚══════════════════════════════════════════════╝

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║   Installation - PDF → Texte App             ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo ""

# 1. Vérifier PHP
echo -e "${CYAN}[1/4]${NC} Vérification de PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    echo -e "  ${GREEN}✓${NC} PHP ${PHP_VERSION} trouvé"
else
    echo -e "  ${RED}✗${NC} PHP non trouvé. Installation..."
    sudo apt update -qq
    sudo apt install -y php php-cli php-mbstring php-xml php-curl php-zip
    echo -e "  ${GREEN}✓${NC} PHP installé"
fi

# 2. Installer poppler-utils (fournit pdftotext)
echo -e "${CYAN}[2/4]${NC} Vérification de pdftotext (poppler-utils)..."
if command -v pdftotext &> /dev/null; then
    echo -e "  ${GREEN}✓${NC} pdftotext trouvé : $(which pdftotext)"
else
    echo -e "  ${DIM}Installation de poppler-utils...${NC}"
    sudo apt update -qq
    sudo apt install -y poppler-utils
    echo -e "  ${GREEN}✓${NC} poppler-utils installé"
fi

# 3. Installer Composer si nécessaire
echo -e "${CYAN}[3/4]${NC} Vérification de Composer..."
if command -v composer &> /dev/null; then
    echo -e "  ${GREEN}✓${NC} Composer trouvé"
else
    echo -e "  ${DIM}Installation de Composer...${NC}"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    sudo mv composer.phar /usr/local/bin/composer
    echo -e "  ${GREEN}✓${NC} Composer installé"
fi

# 4. Installer les dépendances PHP
echo -e "${CYAN}[4/4]${NC} Installation des dépendances..."
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
echo -e "    cd $(pwd)"
echo -e "    php -S localhost:8080 -t public"
echo -e "    ${DIM}→ Ouvrir http://localhost:8080 dans votre navigateur${NC}"
echo ""
echo -e "  ${CYAN}Ligne de commande :${NC}"
echo -e "    php convert.php mon-document.pdf"
echo -e "    php convert.php rapport.pdf --layout --output=rapport.txt"
echo ""
