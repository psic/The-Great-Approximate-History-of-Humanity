# Configuration Apache pour greatHistory

## Prérequis

- Apache installé
- **PHP** installé et chargé dans Apache :  
  `sudo apt install libapache2-mod-php`  
  (ou `php` selon ta distribution)
- Le module `rewrite` activé (souvent utile) :  
  `sudo a2enmod rewrite`

## 1. Activer le site

### Copier la config

```bash
sudo cp apache/greatHistory.conf /etc/apache2/sites-available/
```

### Adapter le chemin (si besoin)

Édite `/etc/apache2/sites-available/greatHistory.conf` et vérifie que **DocumentRoot** et **Directory** pointent bien vers le dossier du projet :

- `DocumentRoot /home/jpierson@sarastro.xyz/Dev/perso/greatHistory`
- `<Directory /home/jpierson@sarastro.xyz/Dev/perso/greatHistory>`

### Activer le virtual host

```bash
sudo a2ensite greatHistory
sudo systemctl reload apache2
```

## 2. Accéder au site

### Option A : avec un nom de domaine local

Ajoute dans `/etc/hosts` :

```
127.0.0.1   greathistory.local
```

Puis ouvre dans le navigateur : **http://greathistory.local**

### Option B : sans modifier hosts

Tu peux utiliser le **DocumentRoot par défaut** d’Apache et y mettre uniquement le projet, ou créer un autre VirtualHost avec `ServerName localhost` et le même `DocumentRoot` que ci‑dessus.  
Tu accèderas alors au site via l’URL indiquée par Apache (souvent **http://localhost** si ce vhost est le seul ou le défaut).

### Option C : sous-dossier du DocumentRoot existant

Si tu ne veux pas de VirtualHost dédié, copie tout le contenu de **greatHistory** dans un sous-dossier de ton DocumentRoot (ex. `/var/www/html/greatHistory`).  
Le site sera alors à : **http://localhost/greatHistory/** (ou l’équivalent selon ta config).

## 3. Vérifier PHP

Crée un fichier `info.php` à la racine du projet avec :

```php
<?php phpinfo(); ?>
```

Ouvre `http://greathistory.local/info.php` (ou l’URL équivalente). Si tu vois la page d’info PHP, Apache exécute bien PHP. Pense à supprimer `info.php` ensuite pour la sécurité.

## 4. Dépannage

### « You don't have permission to access this resource » (403)

Apache tourne sous `www-data` et doit pouvoir traverser tout le chemin jusqu’au projet. En `/home`, ça échoue souvent (droits, SELinux, config globale Apache). **La solution fiable est de servir depuis /var/www.**

**Solution recommandée — Servir depuis /var/www (copie, pas de lien symbolique)** :

Apache sert le site depuis une **copie** dans `/var/www/greatHistory`. Pas de lien symbolique (évite l’erreur « Symbolic link not allowed or link target not accessible »).

**Première fois :**

```bash
# 1. Depuis le dossier du projet, copier les fichiers vers /var/www
cd /home/jpierson@sarastro.xyz/Dev/perso/greatHistory
bash apache/deploy-vers-varwww.sh

# 2. Config Apache et rechargement
sudo cp apache/greatHistory-varwww.conf /etc/apache2/sites-available/greatHistory.conf
sudo a2ensite greatHistory
sudo systemctl reload apache2
```

Ouvre **http://greathistory.local**.

**Quand tu modifies le projet** (fichiers PHP, JSON, CSS, etc.) : relance la copie pour mettre à jour le site servi par Apache :

```bash
cd /home/jpierson@sarastro.xyz/Dev/perso/greatHistory
bash apache/deploy-vers-varwww.sh
```

Tu peux aussi éditer directement les fichiers dans `/var/www/greatHistory` (avec `sudo` ou en te mettant dans le groupe `www-data`) si tu préfères.

---

**Si la solution ci‑dessus ne suffit pas** : lance le diagnostic pour voir la cause exacte (droits, SELinux, log Apache) :

```bash
cd /home/jpierson@sarastro.xyz/Dev/perso/greatHistory
bash apache/diagnostic-403.sh
```

**Solution 1 (droits dans /home)** : souvent insuffisante (SELinux, config Apache). À n’utiliser que si tu ne peux pas passer par /var/www. Voir le script `apache/diagnostic-403.sh` pour vérifier le chemin et les droits.

- **PHP en téléchargement** : le module PHP n’est pas chargé → `sudo a2enmod phpX.X` (remplace par ta version) puis `sudo systemctl restart apache2`.
- **Fichier non trouvé pour /api/timeline.php** : vérifie que le chemin `DocumentRoot` est le bon et que le fichier existe bien dans `api/timeline.php`.
