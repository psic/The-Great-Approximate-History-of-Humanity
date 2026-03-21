#!/bin/bash
# Copie le projet vers /var/www/greatHistory pour qu'Apache puisse le servir
# (évite "Symbolic link not allowed or link target not accessible")
# À lancer depuis la racine du projet : bash apache/deploy-vers-varwww.sh

set -e
SRC="$(cd "$(dirname "$0")/.." && pwd)"
DEST="/var/www/greatHistory"

echo "Source : $SRC"
echo "Destination : $DEST"
echo ""

# Supprimer un éventuel lien symbolique pour le remplacer par une vraie copie
if [ -L "$DEST" ]; then
  echo "Suppression du lien symbolique existant..."
  sudo rm "$DEST"
fi

echo "Copie des fichiers (rsync)..."
sudo mkdir -p "$DEST"
sudo rsync -a --delete \
  --exclude '.git' \
  --exclude 'apache/diagnostic-403.sh' \
  --exclude 'apache/deploy-vers-varwww.sh' \
  "$SRC/" "$DEST/"

echo "Droits pour www-data..."
sudo chown -R www-data:www-data "$DEST"

echo "Terminé. Recharge Apache si besoin : sudo systemctl reload apache2"
