# greatHistory — Vue d'ensemble du site

Site PHP sans framework, pur HTML/CSS/JS vanilla. Aucune dépendance externe, aucun outil de build.

---

## Structure des répertoires

```
/
├── index.php                 # Page d'accueil FR (sélecteur de vue + frise)
├── config.php                # Constante SITE_URL
├── 404.php                   # Page d'erreur 404
├── en/
│   ├── index.php             # Page d'accueil EN (définit $lang='en', inclut ../index.php)
│   └── user/index.php        # Page frise partagée EN
├── user/index.php            # Page frise partagée FR (et pivot EN)
├── creer-ta-frise/index.php  # Éditeur de frise (FR + EN selon $lang)
├── en/creer-ta-frise/        # Entrée EN pour l'éditeur
├── api/
│   ├── timeline.php          # Endpoint AJAX : retourne données normalisées en JSON
│   └── share.php             # Endpoint POST : enregistre frise partagée, retourne URL
├── data/
│   ├── moderne.json          # Frise "Histoire moderne"
│   ├── histoire.json         # Frise "Grande Histoire"
│   ├── humanite.json         # Frise "Humanité"
│   ├── terre.json            # Frise "Terre"
│   └── univers.json          # Frise "Univers"
├── data_user/                # Frises partagées par les utilisateurs (hors git)
│   ├── {id}.json             # Données de la frise
│   └── {id}.png              # Aperçu image (canvas export)
├── js/ajax.js                # Rendu côté client + rechargement dynamique
├── css/style.css             # Thème sombre (#000, accent #7aa2f7)
├── lang/
│   ├── fr.php                # Toutes les chaînes FR
│   └── en.php                # Toutes les chaînes EN
├── includes/
│   └── seo-head.php          # Balises meta OG, canonical, hreflang
├── apache/
│   └── deploy-vers-varwww.sh # Script de déploiement vers /var/www
└── docs/
    ├── site-overview.md       # Ce fichier
    └── nettoyage-data-user.md # Script de nettoyage des frises expirées
```

---

## Pages principales

### `index.php` (accueil)

- Sélecteur de vue : `?vue=moderne|histoire|humanite|terre|univers` (défaut : `moderne`)
- Rendu HTML complet de la frise (PHP)
- Header sticky avec : description de l'échelle, filtres de groupes, slider de zoom, axe des dates
- Rechargement dynamique via `js/ajax.js` (AJAX sur changement de vue)
- Liens vers l'éditeur et le contact

### `creer-ta-frise/index.php` (éditeur)

Interface de création d'une frise personnalisée. Contient tout le HTML, CSS et JS en un seul fichier PHP.

**Fonctionnalités :**
- Import JSON : déposer ou charger un fichier `.json` au format greatHistory
- Éditeur de périodes : groupes nommés, chaque période a titre/début/fin/description
- Éditeur d'événements : groupes nommés, chaque événement a titre/date/description
- Graduation (`pas`) : intervalle des marques de l'axe en années (défaut : 10)
- Aperçu temps réel : rendu de la frise via `window.renderTimeline()`
- Export JSON : télécharge la frise au format standard
- Export image : capture canvas PNG
- Export PDF : impression navigateur
- **Partage** : POST vers `/api/share.php`, obtient une URL permanente `/user/?id=xxx`
  - Cooldown 5 minutes entre deux partages
  - Panel de partage avec : copie d'URL, X/Twitter, LinkedIn, Facebook, WhatsApp, Telegram, Email, Pinterest
  - Bouton ✕ pour fermer le panel

**Données pré-remplies au chargement :**
- Groupe "Expériences" avec 2 périodes (Développeur / Chef de projet, 2005-2026)
- Groupe "Formations" vide
- Groupe "Diplômes" avec 2 événements (Bac, Master)
- Groupe "Projets personnels" vide

### `user/index.php` (frise partagée)

- Lit `data_user/{id}.json`, valide, affiche la frise via `window.renderTimeline()`
- OG image = `data_user/{id}.png` si existe, sinon image par défaut
- Même structure sticky header que la page d'accueil
- Accessible en FR `/user/?id=xxx` et EN `/en/user/?id=xxx`

---

## Format JSON

```json
{
  "pas": 10,
  "tableaux": {
    "Nom du groupe": [
      { "titre": "...", "debut": 2000, "fin": 2010, "description": "..." }
    ]
  },
  "evenements": {
    "Nom du groupe": [
      { "titre": "...", "date": 2005, "description": "..." }
    ]
  }
}
```

- `pas` : intervalle des graduations (en années)
- `tableaux` : dictionnaire `{groupe: [périodes]}` — chaque groupe est une ligne de l'éditeur
- `evenements` : dictionnaire `{groupe: [événements]}`

> Les anciens formats (tableau plat, tableau de tableaux) sont normalisés par `parseTableaux()` côté JS et par `api/timeline.php` côté PHP.

---

## Logique de rendu (dupliquée PHP/JS)

La même logique existe côté serveur (`index.php` + `api/timeline.php`) et côté client (`js/ajax.js`) :

| Fonction | Rôle |
|---|---|
| `palette_harmonieuse()` / couleurs HSL | Génère des couleurs automatiques si `couleur` absent |
| `assigner_lanes()` | Détecte les chevauchements de périodes et attribue des "lanes" (lignes) |
| `assigner_lanes_events()` | Idem pour les événements |

Constante partagée : hauteur de lane = **52px**

---

## Rendu dynamique (`js/ajax.js`)

Fonctions globales exposées :
- `window.renderTimeline(data)` : rendu complet d'une frise à partir d'un objet JSON
- `window.rerenderAxisAndBars(min, max)` : re-rendu de l'axe et des barres sur un sous-intervalle (zoom)

Flux :
1. Changement de vue → `fetch('/api/timeline.php?vue=xxx')` → `renderTimeline(data)`
2. Glissement du zoom → `rerenderAxisAndBars(min, max)`
3. Filtres de groupes → masque/démasque les `.group-row` via CSS

---

## API

### `GET /api/timeline.php?vue={vue}`

Retourne les données normalisées de la vue demandée en JSON.

### `POST /api/share.php`

Champs FormData :
- `data` : JSON de la frise (max 80 Ko)
- `image` : data-URL PNG (max 500 Ko, optionnel)

Réponse : `{ "url": "https://..../user/?id=xxxx", "id": "xxxx" }`

Crée `data_user/{id}.json` + `data_user/{id}.png`. L'ID est 8 caractères hexadécimaux aléatoires.

---

## Internationalisation

Toutes les chaînes sont dans `lang/fr.php` et `lang/en.php` (retournent un tableau associatif).
Les pages EN sont dans `en/` et incluent leur équivalent FR avec `$lang = 'en'` défini.

Clés notables :
- `title`, `home`, `nav_creer_frise`, `nav_user_frise`
- `creer_*` : chaînes de l'éditeur
- `periods`, `events` : labels de l'interface frise
- `scale_label`, `span_label` : description de l'échelle

---

## SEO (`includes/seo-head.php`)

- Balises canonical, hreflang FR/EN
- Open Graph (titre, description, image, locale)
- `$_ogImage` peut être surchargé avant l'inclusion (utilisé par `user/index.php` pour l'aperçu de la frise)

---

## Déploiement

```bash
# Local (développement)
php -S localhost:8000

# Apache local (greathistory.local)
bash apache/deploy-vers-varwww.sh
sudo systemctl reload apache2
```

Apache tourne en `www-data`. Le répertoire `data_user/` doit être accessible en écriture :
```bash
chmod o+w data_user/
```

## Nettoyage des frises partagées

Voir `docs/nettoyage-data-user.md` — script bash avec `--dry-run`, à placer en crontab hebdomadaire.
