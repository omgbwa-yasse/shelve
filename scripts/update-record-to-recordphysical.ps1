# Script de mise à jour: Record → RecordPhysical
# Ce script remplace automatiquement les références au modèle Record par RecordPhysical
# dans tout le codebase Laravel

param(
    [switch]$DryRun = $false,  # Mode simulation (ne modifie pas les fichiers)
    [switch]$Verbose = $false  # Affichage détaillé
)

# Configuration
$rootPath = "c:\wamp64_New\www\shelve"
$backupPath = "$rootPath\backups\phase2-$(Get-Date -Format 'yyyyMMdd-HHmmss')"

# Compteurs
$stats = @{
    FilesScanned = 0
    FilesModified = 0
    ReplacementsMade = 0
    Errors = 0
}

# Patterns à remplacer
$replacements = @(
    # Import statements
    @{
        Pattern = 'use App\\Models\\Record;'
        Replace = 'use App\Models\RecordPhysical;'
        Description = "Import statement"
    },
    # Type hints in function parameters
    @{
        Pattern = '\bRecord\s+\$'
        Replace = 'RecordPhysical $'
        Description = "Type hint in parameter"
        Regex = $true
    },
    # Return type hints
    @{
        Pattern = ':\s*Record\b'
        Replace = ': RecordPhysical'
        Description = "Return type hint"
        Regex = $true
    },
    # Static calls
    @{
        Pattern = '\bRecord::'
        Replace = 'RecordPhysical::'
        Description = "Static method call"
        Regex = $true
    },
    # Class references
    @{
        Pattern = 'Record::class'
        Replace = 'RecordPhysical::class'
        Description = "Class constant"
    },
    @{
        Pattern = '\\App\\Models\\Record::class'
        Replace = '\App\Models\RecordPhysical::class'
        Description = "Fully qualified class constant"
    }
)

# Patterns à EXCLURE (ne pas remplacer)
$exclusions = @(
    'SlipRecord',
    'PublicRecord',
    'MailRecord',
    'RecordStatus',
    'RecordLevel',
    'RecordSupport',
    'RecordContainer',
    'RecordAttachment',
    'RecordKeyword',
    'RecordDocument',
    'RecordLink',
    '\$record',          # Variables
    '\$records',         # Variables
    'record_id',         # Colonnes de base de données
    'records_with_',     # Statistiques
    'total.*Record',     # Compteurs
    '/\*.*Record.*\*/',  # Commentaires
    '//.*Record',        # Commentaires
    "'records'",         # Strings de nom de table
    '"records"'          # Strings de nom de table
)

# Fichiers/dossiers à traiter
$targetPaths = @(
    "app\Http\Controllers",
    "app\Services",
    "app\Policies",
    "app\Observers",
    "app\Jobs",
    "app\Models",
    "app\Imports",
    "app\Exports",
    "app\Console\Commands",
    "app\Providers"
)

function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    Write-Host $Message -ForegroundColor $Color
}

function Should-ExcludeFile {
    param([string]$FilePath)

    # Exclure les fichiers spécifiques
    $excludeFiles = @(
        'Record.php',  # L'ancien modèle (on le garde pour l'instant)
        'RecordPhysical.php',  # Le nouveau modèle (déjà correct)
        'SlipRecord.php',
        'PublicRecord.php',
        'MailRecord.php'
    )

    $fileName = [System.IO.Path]::GetFileName($FilePath)
    return $excludeFiles -contains $fileName
}

function Test-ExclusionPattern {
    param(
        [string]$Content,
        [string]$Match
    )

    foreach ($exclusion in $exclusions) {
        if ($Match -match $exclusion) {
            return $true
        }
    }
    return $false
}

function Update-FileContent {
    param(
        [string]$FilePath
    )

    $stats.FilesScanned++

    if (Should-ExcludeFile -FilePath $FilePath) {
        if ($Verbose) {
            Write-ColorOutput "  ⊘ Exclu: $FilePath" "DarkGray"
        }
        return
    }

    try {
        $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
        $originalContent = $content
        $fileModified = $false
        $replacementsInFile = 0

        foreach ($replacement in $replacements) {
            $pattern = $replacement.Pattern
            $replaceWith = $replacement.Replace
            $description = $replacement.Description

            if ($replacement.Regex) {
                # Utiliser regex
                $matches = [regex]::Matches($content, $pattern)

                foreach ($match in $matches) {
                    # Vérifier si cette correspondance doit être exclue
                    if (-not (Test-ExclusionPattern -Content $content -Match $match.Value)) {
                        $content = $content -replace $pattern, $replaceWith
                        $replacementsInFile++
                        $fileModified = $true

                        if ($Verbose) {
                            Write-ColorOutput "    ✓ $description : $($match.Value) → $replaceWith" "Cyan"
                        }
                    }
                }
            }
            else {
                # Simple remplacement de chaîne
                if ($content -match [regex]::Escape($pattern)) {
                    $occurrences = ([regex]::Matches($content, [regex]::Escape($pattern))).Count
                    $content = $content -replace [regex]::Escape($pattern), $replaceWith
                    $replacementsInFile += $occurrences
                    $fileModified = $true

                    if ($Verbose) {
                        Write-ColorOutput "    ✓ $description : $occurrences remplacement(s)" "Cyan"
                    }
                }
            }
        }

        if ($fileModified) {
            $stats.FilesModified++
            $stats.ReplacementsMade += $replacementsInFile

            $relativePath = $FilePath.Replace($rootPath, "").TrimStart('\')
            Write-ColorOutput "  ✓ Modifié: $relativePath ($replacementsInFile changements)" "Green"

            if (-not $DryRun) {
                # Créer un backup
                $backupFile = Join-Path $backupPath $relativePath
                $backupDir = [System.IO.Path]::GetDirectoryName($backupFile)
                if (-not (Test-Path $backupDir)) {
                    New-Item -Path $backupDir -ItemType Directory -Force | Out-Null
                }
                Copy-Item -Path $FilePath -Destination $backupFile -Force

                # Sauvegarder le fichier modifié
                Set-Content -Path $FilePath -Value $content -Encoding UTF8 -NoNewline
            }
        }
    }
    catch {
        $stats.Errors++
        Write-ColorOutput "  ✗ Erreur: $FilePath - $($_.Exception.Message)" "Red"
    }
}

function Process-Directory {
    param([string]$Path)

    Write-ColorOutput "`n📁 Traitement: $Path" "Yellow"

    $files = Get-ChildItem -Path $Path -Filter "*.php" -Recurse -File

    foreach ($file in $files) {
        Update-FileContent -FilePath $file.FullName
    }
}

# Début du script
Write-ColorOutput "╔════════════════════════════════════════════════════════════╗" "Magenta"
Write-ColorOutput "║   MIGRATION: Record → RecordPhysical                      ║" "Magenta"
Write-ColorOutput "╚════════════════════════════════════════════════════════════╝" "Magenta"

if ($DryRun) {
    Write-ColorOutput "`n⚠️  MODE SIMULATION - Aucun fichier ne sera modifié" "Yellow"
}

Write-ColorOutput "`nRépertoire racine: $rootPath" "Cyan"
Write-ColorOutput "Backup: $backupPath" "Cyan"

# Créer le dossier de backup
if (-not $DryRun) {
    New-Item -Path $backupPath -ItemType Directory -Force | Out-Null
    Write-ColorOutput "✓ Dossier de backup créé" "Green"
}

# Traiter chaque chemin cible
foreach ($targetPath in $targetPaths) {
    $fullPath = Join-Path $rootPath $targetPath
    if (Test-Path $fullPath) {
        Process-Directory -Path $fullPath
    }
    else {
        Write-ColorOutput "⚠️  Chemin introuvable: $fullPath" "Yellow"
    }
}

# Afficher les statistiques
Write-ColorOutput "`n╔════════════════════════════════════════════════════════════╗" "Magenta"
Write-ColorOutput "║   RÉSUMÉ                                                  ║" "Magenta"
Write-ColorOutput "╚════════════════════════════════════════════════════════════╝" "Magenta"
Write-ColorOutput ""
Write-ColorOutput "Fichiers analysés     : $($stats.FilesScanned)" "Cyan"
Write-ColorOutput "Fichiers modifiés     : $($stats.FilesModified)" "Green"
Write-ColorOutput "Remplacements effectués: $($stats.ReplacementsMade)" "Green"
Write-ColorOutput "Erreurs               : $($stats.Errors)" $(if ($stats.Errors -gt 0) { "Red" } else { "Green" })

if ($DryRun) {
    Write-ColorOutput "`n⚠️  Mode simulation - Pour appliquer les changements, exécutez sans -DryRun" "Yellow"
}
else {
    Write-ColorOutput "`n✓ Backup sauvegardé dans: $backupPath" "Green"
    Write-ColorOutput "`nProchaines étapes:" "Yellow"
    Write-ColorOutput "  1. Vérifier les fichiers modifiés avec Git: git status" "White"
    Write-ColorOutput "  2. Exécuter les tests: php artisan test" "White"
    Write-ColorOutput "  3. Vérifier l'autoload: composer dump-autoload" "White"
    Write-ColorOutput "  4. Exécuter la migration: php artisan migrate" "White"
}

Write-ColorOutput "`n════════════════════════════════════════════════════════════" "Magenta"
