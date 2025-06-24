# Correction des Erreurs de Timeout API - Archives Page

## Problème Initial
- **Erreur** : `AxiosError: timeout of 10000ms exceeded`
- **Cause** : Boucles infinies de re-renders dans RecordsPage.jsx et timeout trop court
- **Impact** : Impossibilité de charger la page archives, multiples appels API redondants

## Solutions Implémentées

### 1. Augmentation du Timeout Axios
**Fichier** : `src/services/api.js`
- Timeout passé de 10s à 30s : `timeout: 30000`
- Ajout de logs de débogage pour le développement
- Amélioration de la gestion d'erreurs avec plus de détails

### 2. Correction des Boucles de Re-renders
**Fichier** : `src/components/pages/RecordsPage.jsx`

#### Problème 1 : Dépendance circulaire dans useEffect
```javascript
// AVANT (problématique)
useEffect(() => {
  if (currentPage > 1) setCurrentPage(1);
}, [apiFilters, currentPage]); // currentPage dans deps = boucle infinie
```

#### Solution : Référence stable et première execution
```javascript
// APRÈS (corrigé)
const isFirstRender = useRef(true);
const prevApiFiltersKey = useRef(JSON.stringify(apiFilters));

useEffect(() => {
  const currentKey = JSON.stringify(apiFilters);
  
  if (!isFirstRender.current && 
      prevApiFiltersKey.current !== currentKey && 
      currentPage > 1) {
    setCurrentPage(1);
  }
  
  prevApiFiltersKey.current = currentKey;
  isFirstRender.current = false;
// eslint-disable-next-line react-hooks/exhaustive-deps
}, [apiFilters]); // currentPage intentionally omitted to avoid loop
```

#### Problème 2 : useApi avec dépendances instables
```javascript
// AVANT (problématique)
useApi(
  () => recordsApi.getRecords({ ...apiFilters, page: currentPage }),
  [apiFilters, currentPage] // objet recrée à chaque render
)
```

#### Solution : Clé stable pour les dépendances
```javascript
// APRÈS (corrigé)
const apiKey = useMemo(() => 
  `${JSON.stringify(apiFilters)}-${currentPage}`, 
  [apiFilters, currentPage]
);

useApi(
  () => recordsApi.getRecords({ ...apiFilters, page: currentPage }),
  null,
  { dependencies: [apiKey] } // clé stable
)
```

### 3. Amélioration des Logs de Débogage
- Logs des requêtes API en mode développement
- Logs des réponses API avec statut et données
- Meilleure traçabilité des erreurs avec contexte complet

## Configuration par Défaut Optimisée

### Filtres par défaut pour la page archives :
```javascript
{
  search: '',
  type: '',
  status: 'published',     // Affiche seulement les records publiés
  date_from: '',
  date_to: '',
  classification: '',
  sort_by: 'published_at', // Tri par date de publication
  sort_order: 'desc'       // Plus récents en premier
}
```

## Tests de Validation

### 1. Backend Laravel (Port 8000)
```powershell
Test-NetConnection -ComputerName localhost -Port 8000
# Résultat : TcpTestSucceeded : True ✅

Invoke-WebRequest -Uri "http://localhost:8000/api/public/records?status=published&sort_by=published_at&sort_order=desc&page=1"
# Résultat : StatusCode 200, données JSON correctes ✅
```

### 2. Frontend React (Port 3001)
```bash
npm start (sur port 3001)
# Résultat : Compilation réussie, logs API propres ✅
```

## Logs API Après Correction
```
API Request: {method: 'GET', url: 'http://localhost:8000/api/public/records', params: {…}}
API Response: {status: 200, url: '/public/records', data: {…}}
```
- ✅ Un seul appel par chargement au lieu de multiples appels
- ✅ Pas de timeout errors
- ✅ Réponse rapide et stable

## Résultats

### Avant
- ❌ Erreurs de timeout répétées
- ❌ 10+ appels API redondants par seconde
- ❌ Page archives inutilisable
- ❌ Boucles infinies de re-renders

### Après
- ✅ Aucune erreur de timeout
- ✅ Appels API optimisés (1 par changement)
- ✅ Page archives fonctionnelle
- ✅ Performance améliorée
- ✅ UX fluide avec recherche en temps réel

## Notes pour le Développement

1. **Backend requis** : S'assurer que WAMP/Laravel tourne sur le port 8000
2. **Variables d'environnement** : Vérifier `.env.local` pour l'URL API
3. **Développement** : Les logs de debug sont activés automatiquement en mode dev
4. **Production** : Les logs de debug seront désactivés automatiquement

## Impact sur l'UX
- Chargement initial rapide des derniers records publiés
- Recherche en temps réel sans délai
- Navigation fluide entre les pages
- Filtres réactifs sans latence
- Messages d'état appropriés (chargement, erreurs, aucun résultat)
