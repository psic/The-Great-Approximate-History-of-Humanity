# Frise chronologique (PHP / HTML / CSS)

Site minimal qui affiche une **frise chronologique** générée à partir d’un fichier JSON.  
Pas de framework PHP, JavaScript limité à l’AJAX pour rafraîchir la frise sans recharger la page.

## Structure

```
greatHistory/
├── apache/
│   ├── greatHistory.conf   # Exemple de VirtualHost Apache
│   └── README.md           # Instructions configuration Apache
├── data/
│   └── timeline.json      # Données de la frise (à éditer)
├── api/
│   └── timeline.php       # Endpoint JSON pour l’AJAX
├── css/
│   └── style.css
├── js/
│   └── ajax.js            # Uniquement appel AJAX
├── index.php
└── README.md
```

## Format du JSON

Dans `data/timeline.json` :

- **pas** : nombre. Échelle d’affichage des années (ex. `50` = graduation de 50 en 50 ans).
- **tableaux** : tableau de **tableaux** de périodes. Chaque sous-tableau = **une ligne** de la frise. Si vous n’avez qu’un seul tableau de périodes, vous pouvez encore utiliser **periodes** (un seul tableau) : il sera affiché sur une ligne.
  - Dans un même sous-tableau, si deux périodes se **chevauchent** (dates qui se recoupent), elles sont affichées sur des **lignes différentes** (lanes) dans cette ligne.
  - Chaque période : **debut**, **fin**, **titre**, **description** (optionnel).
- **evenements** : tableau d’événements (tri par date). Chaque élément : **date**, **titre**, **description** (optionnel).

Chaque période a une **couleur différente**, aléatoire et harmonieuse (palette HSL).

## Lancer le site

### Serveur PHP intégré

À la racine du projet :

```bash
php -S localhost:8000
```

Puis ouvrir : **http://localhost:8000**

### Avec Apache

Voir le dossier **`apache/`** et le fichier **`apache/README.md`** pour la configuration VirtualHost et l’accès au site via Apache.

## Fonctionnalités

- Affichage de la frise à partir de `timeline.json` (pas, periodes, evenements)
- Échelle des années selon **pas** (graduations sur l’axe)
- Périodes affichées en barres (debut → fin), tri automatique par date de début
- Événements affichés comme points sur la frise, tri automatique par date
- Bouton « Rafraîchir la frise (AJAX) » : recharge les données via `api/timeline.php` sans recharger la page
