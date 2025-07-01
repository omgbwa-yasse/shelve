# Système de Notifications & Workflow pour le Courrier

## 🚀 Fonctionnalités Implémentées

### 1. Système de Notifications & Alertes
- ✅ **Notifications de délais** : Alertes automatiques quand une priorité approche de sa deadline
- ✅ **Notifications de nouveaux courriers** reçus/assignés
- ✅ **Notifications d'échéances d'actions** à effectuer
- ✅ **Notifications de retards** de traitement
- ✅ **Système de priorités** (1-5) avec codes couleur
- ✅ **Notifications en temps réel** via polling

### 2. Système de Workflow & États Avancés
- ✅ **Machine d'états** complète pour le cycle de vie du courrier
- ✅ **États disponibles** : draft, pending_review, in_progress, pending_approval, approved, transmitted, completed, rejected, cancelled, overdue
- ✅ **Workflow d'approbation** pour certains types de courriers
- ✅ **Assignation automatique** et manuelle
- ✅ **Escalade automatique** basée sur les délais
- ✅ **Historique complet** des transitions d'états

### 3. Système de Suivi & Traçabilité
- ✅ **Tracking complet** : Qui a fait quoi, quand
- ✅ **Historique des modifications** (audit trail) avec détails complets
- ✅ **Géolocalisation** du courrier (organisation, session, IP)
- ✅ **Temps de traitement** par étape
- ✅ **Métadonnées** étendues pour chaque action

## 📋 Installation & Configuration

### 1. Exécuter les migrations
```bash
php artisan migrate
```

### 2. Créer des données de test (optionnel)
```bash
php artisan db:seed --class=MailTrackingTestSeeder
```

### 3. Configurer les tâches programmées
Ajoutez dans votre crontab :
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Tester les notifications manuellement
```bash
# Vérifier toutes les notifications
php artisan mail:process-notifications

# Vérifier seulement les échéances (24h par défaut)
php artisan mail:process-notifications --type=deadlines --hours=24

# Vérifier seulement les retards
php artisan mail:process-notifications --type=overdue

# Vérifier les actions à effectuer
php artisan mail:process-notifications --type=actions

# Escalade automatique
php artisan mail:process-notifications --type=escalation
```

## 🎯 Utilisation

### Accéder aux notifications
- **Interface web** : `/mails/notifications/show`
- **API JSON** : `/mails/notifications` 
- **Polling temps réel** : `/mails/notifications/poll`
- **Compteur non lues** : `/mails/notifications/unread-count`

### Workflow de base

#### 1. Créer et assigner un courrier
```php
$mail = Mail::create([
    'code' => 'MAIL-001',
    'name' => 'Nouveau courrier',
    'status' => MailStatusEnum::DRAFT,
    'deadline' => now()->addDays(7),
    // ... autres champs
]);

// Assigner à un utilisateur
$mail->assignTo($userId, 'Assignation initiale');
```

#### 2. Faire évoluer le statut
```php
// Transition d'état avec validation automatique
$mail->updateStatus(MailStatusEnum::IN_PROGRESS, 'Début du traitement');
$mail->updateStatus(MailStatusEnum::PENDING_APPROVAL, 'Demande d\'approbation');
```

#### 3. Approbation/Rejet
```php
$workflow = $mail->workflow;

// Approuver
$workflow->approve($approverId, 'Approuvé après révision');

// Rejeter
$workflow->reject($rejecterId, 'Documents manquants');
```

#### 4. Escalade manuelle
```php
$workflow->escalate($supervisorId, 'Escalade manuelle - cas complexe');
```

### Notifications personnalisées

#### Créer une notification
```php
$notificationService = app(MailNotificationService::class);

$notificationService->createNotification(
    $mail,
    $user,
    NotificationTypeEnum::MAIL_ASSIGNED,
    'Message personnalisé',
    ['custom_data' => 'valeur']
);
```

#### Récupérer les notifications
```php
// Notifications non lues
$unreadNotifications = $notificationService->getUnreadNotifications($user);

// Marquer comme lu
$notificationService->markAsRead($notificationIds);
```

### Historique et audit

#### Consulter l'historique
```php
// Historique complet d'un courrier
$auditTrail = $mail->getAuditTrail();

// Historique récent (7 jours)
$recentHistory = $mail->histories()->recent(7)->get();
```

#### Log personnalisé
```php
$mail->logAction(
    'custom_action',
    'field_name',
    $oldValue,
    $newValue,
    'Description de l\'action'
);
```

## 📊 Automatisation

### Tâches programmées (configurées automatiquement)
- **Toutes les heures** : Vérification des échéances approchantes
- **Toutes les 2 heures** : Vérification des retards
- **Toutes les 4 heures** : Vérification des actions à effectuer
- **Quotidien (9h)** : Escalade automatique 
- **Hebdomadaire (dimanche 2h)** : Nettoyage des anciennes notifications

### Règles métier automatiques
- **Auto-escalade** après X heures sans action
- **Changement de statut** automatique en cas de retard
- **Notifications** automatiques selon les seuils configurés
- **Approbation requise** pour certaines typologies (CONF, LEGAL, EXEC)

## 🔧 API Endpoints

### Notifications
- `GET /mails/notifications` - Liste des notifications
- `GET /mails/notifications/unread-count` - Compteur non lues
- `GET /mails/notifications/poll` - Polling temps réel
- `PATCH /mails/notifications/{id}/read` - Marquer comme lu
- `PATCH /mails/notifications/mark-all-read` - Tout marquer comme lu
- `DELETE /mails/notifications/{id}` - Supprimer

### Workflow (à implémenter selon besoins)
- `POST /mails/{id}/assign` - Assigner
- `PATCH /mails/{id}/status` - Changer statut
- `POST /mails/{id}/approve` - Approuver
- `POST /mails/{id}/reject` - Rejeter
- `POST /mails/{id}/escalate` - Escalader

## 🎨 Interface Utilisateur

### Éléments visuels
- **Badges de priorité** avec codes couleur
- **Icônes** selon le type de notification
- **Indicateurs visuels** pour les notifications non lues
- **Temps relatifs** (il y a 2 heures, etc.)
- **Actions rapides** (marquer lu, supprimer)

### Fonctionnalités interactives
- **Polling automatique** toutes les 30 secondes
- **Actions en masse** (marquer tout comme lu)
- **Filtrage** par statut de lecture
- **Tri** par priorité et date

## ⚙️ Configuration

### Variables d'environnement (optionnelles)
```env
# Intervalles de notification (en heures)
MAIL_DEADLINE_WARNING_HOURS=24
MAIL_OVERDUE_CHECK_HOURS=2
MAIL_ACTION_CHECK_HOURS=4

# Rétention des notifications (en jours)
MAIL_NOTIFICATION_RETENTION_DAYS=30

# Escalade automatique
MAIL_AUTO_ESCALATE_HOURS=48
```

### Personnalisation des règles métier
Modifiez les méthodes dans `MailWorkflow` et `Mail` :
- `requiresApproval()` - Logique d'approbation requise
- `canTransitionTo()` - Règles de transition d'états
- `findSupervisor()` - Logique de recherche de superviseur

## 🐛 Dépannage

### Vérifier le système
```bash
# Tester les notifications
php artisan mail:process-notifications --type=all

# Vérifier les tâches programmées
php artisan schedule:list

# Vérifier les logs
tail -f storage/logs/laravel.log
```

### Problèmes courants
1. **Notifications non générées** : Vérifier les tâches cron
2. **Escalade non fonctionnelle** : Vérifier la logique `findSupervisor()`
3. **États bloqués** : Vérifier les règles de transition dans `MailStatusEnum`

Cette implémentation fournit une base solide pour la gestion avancée du courrier avec notifications, workflow et traçabilité complète !
