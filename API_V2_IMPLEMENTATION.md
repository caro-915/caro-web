# API V2 - Routes Implémentées

## ✅ Routes Ajoutées (5 nouvelles routes)

### 🔴 URGENT - Route Vendeur
#### `GET /api/users/{id}/annonces`
**Description:** Récupère toutes les annonces actives d'un vendeur spécifique

**Accès:** Public (pas besoin d'authentification)

**Paramètres:**
- `{id}` - ID de l'utilisateur (path parameter)

**Réponse:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Renault Clio",
      "description": "...",
      "price": 1500000,
      "marque": "Renault",
      "modele": "Clio",
      "year": 2018,
      "km": 50000,
      "fuel": "Essence",
      "gearbox": "Manuelle",
      "wilaya": "Alger",
      "isNew": false,
      "images": ["url1", "url2"],
      "views": 120,
      "createdAt": "2025-12-23T10:00:00Z",
      "isFavorite": false,
      "isActive": true,
      "user": {
        "id": 5,
        "name": "Ahmed",
        "phone": "0555123456",
        "avatar": null
      }
    }
  ]
}
```

---

### 🟡 Statistiques Annonce
#### `GET /api/annonces/{id}/stats`
**Description:** Récupère les statistiques d'une annonce (vues, favoris, messages)

**Accès:** Public

**Paramètres:**
- `{id}` - ID de l'annonce

**Réponse:**
```json
{
  "id": 1,
  "views": 120,
  "favorites": 5,
  "messages": 3,
  "isActive": true,
  "createdAt": "2025-12-23T10:00:00Z"
}
```

---

### 🟡 Incrémenter Vues
#### `POST /api/annonces/{id}/view`
**Description:** Incrémente le compteur de vues d'une annonce (ne compte pas le propriétaire)

**Accès:** Public (peut être appelé sans authentification)

**Paramètres:**
- `{id}` - ID de l'annonce

**Headers (optionnel):**
```
Authorization: Bearer {token}
```

**Logique:**
- Si l'utilisateur est authentifié ET est le propriétaire → Ne compte PAS
- Sinon → Incrémente les vues

**Réponse:**
```json
{
  "views": 121
}
```

---

### 🟡 Marquer Conversation Comme Lue
#### `POST /api/conversations/{id}/read`
**Description:** Marque tous les messages non lus d'une conversation comme lus

**Accès:** Authentifié (Sanctum)

**Paramètres:**
- `{id}` - ID de la conversation

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Validation:**
- L'utilisateur doit être participant de la conversation (buyer OU seller)

**Réponse:**
```json
{
  "message": "Messages marqués comme lus",
  "updated": 3
}
```

**Erreurs:**
- `403` - L'utilisateur n'est pas participant de la conversation
- `404` - Conversation inexistante

---

### 🟡 Modifier Annonce
#### `PUT /api/annonces/{id}`
**Description:** Modifie une annonce existante (seul le propriétaire peut modifier)

**Accès:** Authentifié (Sanctum)

**Paramètres:**
- `{id}` - ID de l'annonce

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (tous les champs sont optionnels):**
```json
{
  "titre": "Nouveau titre",
  "description": "Nouvelle description",
  "prix": 1600000,
  "marque": "Renault",
  "modele": "Clio",
  "annee": 2019,
  "kilometrage": 45000,
  "carburant": "Diesel",
  "boite_vitesse": "Automatique",
  "ville": "Oran",
  "vehicle_type": "car",
  "show_phone": true,
  "couleur": "Noir",
  "document_type": "carte_grise",
  "finition": "Intens",
  "condition": "non",
  "images": [file1, file2, ...]
}
```

**Validation:**
- Seul le propriétaire peut modifier
- Les images sont optionnelles (max 5)
- Format images: jpeg, png, jpg (max 5MB chacune)

**Réponse:**
```json
{
  "message": "Annonce modifiée avec succès",
  "annonce": { /* annonce complète */ }
}
```

**Erreurs:**
- `403` - L'utilisateur n'est pas propriétaire
- `404` - Annonce inexistante
- `422` - Erreurs de validation

---

## 📝 Fichiers Modifiés

### 1. `routes/api.php`
**Lignes ajoutées:**
```php
// Routes publiques
Route::get('/users/{id}/annonces', [AnnonceApiController::class, 'userAnnonces']);
Route::get('/annonces/{id}/stats', [AnnonceApiController::class, 'stats']);
Route::post('/annonces/{id}/view', [AnnonceApiController::class, 'incrementView']);

// Routes protégées
Route::put('/annonces/{id}', [AnnonceApiController::class, 'update']);
Route::post('/conversations/{id}/read', [MessageApiController::class, 'markAsRead']);
```

### 2. `app/Http/Controllers/Api/AnnonceApiController.php`
**Méthodes ajoutées:**
- `userAnnonces($id)` - Annonces d'un vendeur
- `stats($id)` - Statistiques d'une annonce
- `incrementView(Request $request, $id)` - Incrémenter vues
- `update(Request $request, $id)` - Modifier annonce

### 3. `app/Http/Controllers/Api/MessageApiController.php`
**Méthodes ajoutées:**
- `markAsRead(Request $request, $id)` - Marquer comme lu

---

## 🧪 Tests Rapides

### Test 1: Annonces d'un vendeur
```bash
curl -X GET http://127.0.0.1:8000/api/users/1/annonces
```

### Test 2: Statistiques
```bash
curl -X GET http://127.0.0.1:8000/api/annonces/1/stats
```

### Test 3: Incrémenter vues (sans auth)
```bash
curl -X POST http://127.0.0.1:8000/api/annonces/1/view
```

### Test 4: Incrémenter vues (avec auth, propriétaire)
```bash
curl -X POST http://127.0.0.1:8000/api/annonces/1/view \
  -H "Authorization: Bearer {token}"
```

### Test 5: Marquer comme lu
```bash
curl -X POST http://127.0.0.1:8000/api/conversations/1/read \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### Test 6: Modifier annonce
```bash
curl -X PUT http://127.0.0.1:8000/api/annonces/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"titre":"Nouveau titre","prix":1700000}'
```

---

## ✅ Résumé

**Total routes ajoutées:** 5  
**Temps d'implémentation:** ~15 minutes  
**Fichiers modifiés:** 3  
**Lignes de code ajoutées:** ~200  

**État:**
- ✅ Toutes les routes fonctionnent
- ✅ Validation des permissions
- ✅ Syntaxe PHP validée
- ✅ Pas d'erreurs de compilation

**Prêt pour les tests avec l'application Flutter !** 🚀
