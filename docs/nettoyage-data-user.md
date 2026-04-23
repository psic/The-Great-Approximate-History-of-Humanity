# Nettoyage des frises partagées (`data_user/`)

Les frises partagées sont stockées dans `data_user/` sous la forme de paires :
- `{id}.json` — données de la frise
- `{id}.png` — aperçu image

Aucune expiration automatique n'est en place. Script de nettoyage par date de fichier (`mtime`) :

```bash
#!/bin/bash
# cleanup-data-user.sh
# Usage : bash cleanup-data-user.sh [--dry-run]

DATA_DIR="/var/www/greathistory/data_user"
MAX_DAYS=90
DRY_RUN=false

[[ "$1" == "--dry-run" ]] && DRY_RUN=true

echo "Nettoyage des frises de plus de $MAX_DAYS jours dans $DATA_DIR"

find "$DATA_DIR" -maxdepth 1 -name "*.json" -mtime +$MAX_DAYS | while read -r f; do
    id=$(basename "$f" .json)

    if $DRY_RUN; then
        echo "[dry-run] Supprimerait : $id.json / $id.png"
    else
        rm -f "$DATA_DIR/$id.json" "$DATA_DIR/$id.png"
        echo "Supprimé : $id"
    fi
done

echo "Terminé."
```

## Ajout en crontab (exécution hebdomadaire)

```cron
0 3 * * 0 bash /var/www/greathistory/docs/cleanup-data-user.sh >> /var/log/greathistory-cleanup.log 2>&1
```
