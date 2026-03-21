#!/bin/bash
# Diagnostic 403 : exécute avec bash apache/diagnostic-403.sh
# Affiche pourquoi Apache ne peut pas accéder au projet

CHEMIN="${1:-/home/jpierson@sarastro.xyz/Dev/perso/greatHistory}"
echo "=== Chemin testé : $CHEMIN ==="
echo ""

if [ ! -d "$CHEMIN" ]; then
  echo "ERREUR : le dossier n'existe pas. Adapte CHEMIN en paramètre."
  echo "Exemple : bash apache/diagnostic-403.sh /chemin/vers/greatHistory"
  exit 1
fi

echo "1) Droits sur chaque répertoire (namei) :"
echo "   (si une ligne affiche 'd' ou 'denied', c'est le blocage)"
if command -v namei &>/dev/null; then
  namei -l "$CHEMIN"
else
  echo "   (namei non installé : sudo apt install namei)"
  path="$CHEMIN"
  while [ -n "$path" ]; do
    ls -ld "$path" 2>/dev/null || echo "ACCÈS REFUSÉ: $path"
    [ "$path" = "/" ] && break
    path="$(dirname "$path")"
  done
fi

echo ""
echo "2) Utilisateur Apache :"
if [ -r /etc/apache2/envvars ]; then
  . /etc/apache2/envvars 2>/dev/null
  echo "   APACHE_RUN_USER=${APACHE_RUN_USER:-www-data}"
elif [ -r /etc/httpd/conf/httpd.conf ] || [ -r /etc/apache2/apache2.conf ]; then
  grep -E '^User ' /etc/apache2/apache2.conf /etc/httpd/conf/httpd.conf 2>/dev/null | head -1
fi

echo ""
echo "3) Test d'accès en tant qu'Apache (www-data) :"
sudo -u www-data test -r "$CHEMIN/index.php" 2>/dev/null && echo "   OK : www-data peut lire index.php" || echo "   ÉCHEC : www-data ne peut pas lire le dossier/fichiers"

echo ""
echo "4) SELinux (si actif, peut bloquer même avec bons droits) :"
if command -v getenforce &>/dev/null; then
  getenforce
  [ "$(getenforce 2>/dev/null)" = "Enforcing" ] && echo "   → Si 403 persiste : sudo setsebool -P httpd_read_user_content 1"
else
  echo "   Non utilisé sur ce système."
fi

echo ""
echo "5) Log Apache (dernière erreur) :"
LOG=$(sudo grep -l "greatHistory\|greathistory" /var/log/apache2/*.log 2>/dev/null | head -1)
[ -z "$LOG" ] && LOG="/var/log/apache2/error.log"
[ -r "$LOG" ] && sudo tail -5 "$LOG" || echo "   Fichier log non trouvé ou illisible."

echo ""
echo "=== Recommandation : utiliser la solution 2 (symlink /var/www) ==="
echo "  sudo ln -sf $(cd "$(dirname "$CHEMIN")" && pwd)/greatHistory /var/www/greatHistory"
echo "  Puis utiliser apache/greatHistory-varwww.conf comme config du site."
