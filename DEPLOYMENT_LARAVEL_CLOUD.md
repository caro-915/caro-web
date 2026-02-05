# Guide de Déploiement - Laravel Cloud

## Problème : Images non visibles en production

### Cause
Les images stockées dans `storage/app/public` ne sont pas accessibles publiquement sur Laravel Cloud.

---

## ✅ Solution 1 : Lien Symbolique (Rapide)

### Via SSH
Si vous avez accès SSH à votre serveur :

```bash
php artisan storage:link
```

### Via Script de Déploiement
Ajoutez cette commande à votre script de déploiement Laravel Cloud :

```bash
php artisan storage:link --force
```

**⚠️ Limitation :** Cette solution ne fonctionne pas bien avec plusieurs serveurs (load balancing).

---

## 🚀 Solution 2 : Stockage Cloud (Recommandé pour Production)

### Étape 1 : Installer le package AWS S3

```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```

### Étape 2 : Configurer les Variables d'Environnement

Dans le tableau de bord **Laravel Cloud**, ajoutez ces variables :

#### Option A : AWS S3
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=votre-access-key
AWS_SECRET_ACCESS_KEY=votre-secret-key
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=autodz-images
```

#### Option B : DigitalOcean Spaces
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=votre-spaces-key
AWS_SECRET_ACCESS_KEY=votre-spaces-secret
AWS_DEFAULT_REGION=fra1
AWS_BUCKET=autodz
AWS_ENDPOINT=https://fra1.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

#### Option C : Cloudflare R2
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=votre-r2-key
AWS_SECRET_ACCESS_KEY=votre-r2-secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=autodz
AWS_ENDPOINT=https://votre-account-id.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Étape 3 : Redéployer l'Application

Le code a déjà été modifié pour supporter automatiquement S3 en fonction de `FILESYSTEM_DISK`.

---

## 🔍 Vérification

### Test Local (Storage Public)
```env
FILESYSTEM_DISK=public
```

### Test Production (S3)
```env
FILESYSTEM_DISK=s3
```

### Vérifier les URLs générées

Ouvrez une annonce et inspectez le HTML. Les URLs des images doivent être :

- **Local :** `https://votre-domaine.com/storage/annonces/image.jpg`
- **S3 :** `https://votre-bucket.s3.amazonaws.com/annonces/image.jpg`

---

## 💰 Coûts Estimés

### AWS S3
- Stockage : ~$0.023/GB/mois
- Transfert : Premiers 100 GB gratuits/mois
- Exemple : 10 GB d'images = ~$0.23/mois

### DigitalOcean Spaces
- $5/mois (250 GB inclus + 1 TB de transfert)

### Cloudflare R2
- $0.015/GB/mois
- **Transfert gratuit illimité** ⭐

---

## 🐛 Dépannage

### Les images ne s'affichent toujours pas

1. **Vérifier les permissions S3 :**
   - Le bucket doit avoir une politique publique pour les images

2. **Vider le cache :**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. **Vérifier les logs :**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Tester l'upload :**
   ```bash
   php artisan tinker
   Storage::disk('s3')->put('test.txt', 'Hello World');
   Storage::disk('s3')->url('test.txt');
   ```

### CORS Errors (si vous utilisez S3)

Ajoutez cette politique CORS sur votre bucket S3 :

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["GET", "HEAD"],
        "AllowedOrigins": ["https://votre-domaine.com"],
        "ExposeHeaders": ["ETag"]
    }
]
```

---

## 📝 Récapitulatif

✅ **Modifications effectuées dans le code :**
- `AnnonceController.php` : Utilise `config('filesystems.default')` au lieu de 'public' en dur
- `ProcessAnnonceImages.php` : Supporte S3 et autres disques cloud
- Les URLs d'images sont générées automatiquement selon le type de stockage

✅ **Configuration flexible :**
- En local : `FILESYSTEM_DISK=public` (pas de frais)
- En production : `FILESYSTEM_DISK=s3` (fiable et scalable)

✅ **Prêt pour le déploiement !**
