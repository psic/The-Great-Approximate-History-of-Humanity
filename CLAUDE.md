# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the project

```bash
# Serveur intégré PHP (développement)
php -S localhost:8000

# Déploiement Apache (copie vers /var/www)
bash apache/deploy-vers-varwww.sh
sudo systemctl reload apache2
# Accès : http://greathistory.local
```

## Architecture

Site PHP sans framework, pur HTML/CSS/JS vanilla. Aucune dépendance externe, aucun gestionnaire de paquets, aucun outil de build.

### Flux de données

```
data/*.json  →  index.php (rendu initial)
             →  api/timeline.php (rechargement AJAX)  →  js/ajax.js (DOM)
```

### Vues disponibles

Sélection via `?vue=` : `moderne` (défaut), `histoire`, `humanite`, `terre`, `univers`. Chaque vue correspond à un fichier `data/<vue>.json`.

### Format JSON

```json
{
  "pas": 100,
  "tableaux": [[ { "nom": "...", "debut": -500, "fin": 200, "couleur": "..." } ]],
  "evenements": [ { "nom": "...", "date": 1066 } ]
}
```

- `pas` : intervalle des graduations (en années)
- `tableaux` : tableaux de périodes (chaque sous-tableau est un groupe de périodes)
- `evenements` : marqueurs ponctuels sur la frise

### Logique d'affichage (dupliquée PHP/JS)

La même logique existe côté serveur (`index.php`) et côté client (`js/ajax.js`) :
- **`palette_harmonieuse()` / couleurs** : génération de couleurs HSL harmoniques automatiques si `couleur` absent
- **`assigner_lanes()`** : détection des chevauchements de périodes et attribution de "lanes" (lignes) pour éviter les superpositions
- **`assigner_lanes_events()`** : idem pour les événements
- Hauteur de lane : 52px (constante dans les deux fichiers)

### Rendu

- `index.php` : rendu HTML complet + logique PHP (calcul d'échelle, lanes, couleurs)
- `api/timeline.php` : endpoint JSON pour les requêtes AJAX, retourne les mêmes données normalisées
- `js/ajax.js` : rechargement dynamique et re-rendu DOM côté client
- `css/style.css` : thème sombre (`#000000`, accent `#7aa2f7`)
