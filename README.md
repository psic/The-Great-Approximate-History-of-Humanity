# Frise chronologique (PHP / HTML / CSS)

Site qui affiche des **frises chronologiques** interactives générées à partir de fichiers JSON, bilingue FR/EN, sans framework ni dépendance externe.

**URL de prod** : http://taghoh.web-en-royans.fr/

## Structure

```
greatHistory/
├── apache/                  # Config VirtualHost + scripts de déploiement
├── api/
│   ├── timeline.php         # Endpoint AJAX (données normalisées JSON)
│   └── share.php            # Endpoint partage de frise
├── contact/                 # Page contact (FR)
├── creer-ta-frise/          # Éditeur de frise personnalisée (FR)
├── ma-frise/                # Frise personnelle (FR)
├── user/                    # Espace utilisateur (FR)
├── en/                      # Miroir EN de toutes les pages
├── fr/                      # Redirection langue FR
├── data_fr/                 # Données des frises en français
├── data_en/                 # Données des frises en anglais
├── data_user/               # Frises créées par les utilisateurs
├── css/style.css            # Thème sombre
├── js/ajax.js               # Rechargement dynamique
├── lang/fr.php              # Traductions FR
├── lang/en.php              # Traductions EN
├── includes/seo-head.php    # SEO commun
├── config.php               # Configuration globale (SITE_URL, etc.)
├── docs/                    # Documentation interne
└── index.php                # Page principale
```

## Vues disponibles

Sélection via `?vue=` : `moderne` (défaut), `histoire`, `humanite`, `terre`, `vie`, `univers`.  
Chaque vue correspond à un fichier `data_fr/<vue>.json` ou `data_en/<vue>.json`.

## Format du JSON

```json
{
  "pas": 100,
  "tableaux": [[ { "nom": "...", "debut": -500, "fin": 200, "couleur": "..." } ]],
  "evenements": [ { "nom": "...", "date": 1066 } ]
}
```

- **pas** : intervalle des graduations (en années)
- **tableaux** : groupes de périodes — chaque sous-tableau est une ligne de la frise ; les chevauchements sont gérés automatiquement en lanes
- **evenements** : marqueurs ponctuels sur la frise

## Lancer le site

```bash
# Serveur PHP intégré
php -S localhost:8000

# Déploiement Apache
bash apache/deploy-vers-varwww.sh && sudo systemctl reload apache2
```

## Fonctionnalités

- **Multilingue FR/EN** : toutes les pages et données disponibles dans les deux langues
- **6 frises thématiques** : Monde moderne, Histoire, Humanité, Terre, Vie, Univers
- **Header sticky** : navigation par onglets, filtres par groupe, axe des années et slider de zoom toujours visibles
- **Zoom sur plage de dates** : slider interactif pour restreindre la période affichée
- **Filtres par groupe** : afficher/masquer des groupes de périodes indépendamment
- **Rechargement AJAX** : changement de vue sans rechargement de page
- **Couleurs harmonieuses** : palette HSL automatique si aucune couleur définie
- **Gestion des lanes** : détection des chevauchements, affichage sur plusieurs lignes
- **Partage** : bouton de partage avec cooldown 5 min, boutons réseaux sociaux
- **"Créer ta frise"** : éditeur pour créer et exporter une frise personnalisée
- **"Ma frise"** : frise personnelle configurable
- **SEO complet** : balises Open Graph, canonical, sitemap, robots.txt

## Temps de travail

4 à 5 jours (~2.5M tokens Claude)
