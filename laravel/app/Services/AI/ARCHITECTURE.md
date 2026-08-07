# Architecture des Services AI Search

## Vue d'ensemble

L'architecture actuelle des services AI est déjà bien structurée, mais quelques améliorations peuvent être apportées :

### Services Actuels

1. **QueryAnalyzerService** - Analyse les requêtes utilisateur avec l'IA
2. **QueryExecutorService** - Exécute les requêtes sur la base de données  
3. **ResponseFormatterService** - Formate les réponses pour l'utilisateur
4. **SearchActionService** - Actions de recherche spécialisées
5. **ActionMixerService** - Mélange les actions (semble peu utilisé)

### Améliorations Suggérées

#### 1. Interface Service Pattern
Créer des interfaces pour améliorer la testabilité :

```php
interface QueryAnalyzerInterface 
interface QueryExecutorInterface
interface ResponseFormatterInterface
```

#### 2. Factory Pattern pour les Providers IA
```php
class AIProviderFactory {
    public static function create(string $provider): AIProviderInterface
}
```

#### 3. Cache Layer
Ajouter un service de cache pour les requêtes fréquentes :
```php
class QueryCacheService {
    public function getCachedResult(string $queryHash): ?array
    public function cacheResult(string $queryHash, array $result): void
}
```

#### 4. Logging et Monitoring
```php
class AISearchMonitorService {
    public function logQuery(string $query, string $type): void
    public function logError(\Throwable $e): void
    public function getStatistics(): array
}
```

#### 5. Configuration Centralisée
```php
class AISearchConfigService {
    public function getProviderConfig(string $provider): array
    public function getSearchTypeConfig(string $type): array
}
```

### Structure Proposée

```
app/Services/AI/
├── Contracts/
│   ├── QueryAnalyzerInterface.php
│   ├── QueryExecutorInterface.php
│   └── ResponseFormatterInterface.php
├── Core/
│   ├── QueryAnalyzerService.php
│   ├── QueryExecutorService.php
│   └── ResponseFormatterService.php
├── Providers/
│   ├── AIProviderFactory.php
│   └── ProviderRegistry.php
├── Cache/
│   └── QueryCacheService.php
├── Monitoring/
│   └── AISearchMonitorService.php
└── Config/
    └── AISearchConfigService.php
```

Cette structure offre :
- **Separation of Concerns** claire
- **Testabilité** améliorée avec les interfaces
- **Extensibilité** pour ajouter de nouveaux providers
- **Performance** avec le cache
- **Observabilité** avec le monitoring
- **Configuration** centralisée et flexible
