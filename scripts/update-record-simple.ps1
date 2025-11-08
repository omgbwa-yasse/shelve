# Script simplifié de mise à jour: Record → RecordPhysical
param(
    [switch]$DryRun = $false
)

$rootPath = "c:\wamp64_New\www\shelve"
$stats = @{
    FilesScanned = 0
    FilesModified = 0
    ReplacementsMade = 0
}

# Liste des fichiers à traiter
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

# Fichiers à exclure
$excludeFiles = @(
    'Record.php',
    'RecordPhysical.php',
    'SlipRecord.php',
    'PublicRecord.php',
    'MailRecord.php'
)

function Update-File {
    param([string]$FilePath)

    $fileName = [System.IO.Path]::GetFileName($FilePath)
    if ($excludeFiles -contains $fileName) {
        return
    }

    $stats.FilesScanned++
    $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
    $original = $content

    # Remplacement 1: Import statement
    $content = $content -replace 'use App\\Models\\Record;', 'use App\Models\RecordPhysical;'

    # Remplacement 2: Type hints
    $content = $content -replace '\bRecord\s+\$', 'RecordPhysical $'
    $content = $content -replace ':\s*Record\b', ': RecordPhysical'

    # Remplacement 3: Static calls (mais pas SlipRecord::, PublicRecord::, etc.)
    $content = $content -replace '(?<!Slip)(?<!Public)(?<!Mail)Record::', 'RecordPhysical::'

    # Remplacement 4: Class references
    $content = $content -replace '(?<!Slip)(?<!Public)(?<!Mail)Record::class', 'RecordPhysical::class'
    $content = $content -replace '\\App\\Models\\Record::class', '\App\Models\RecordPhysical::class'

    if ($content -ne $original) {
        $stats.FilesModified++
        $changes = ([regex]::Matches($original, 'Record')).Count - ([regex]::Matches($content, 'Record')).Count + ([regex]::Matches($content, 'RecordPhysical')).Count - ([regex]::Matches($original, 'RecordPhysical')).Count
        $stats.ReplacementsMade += [Math]::Abs($changes) / 2

        $relativePath = $FilePath.Replace($rootPath, "").TrimStart('\')
        Write-Host "  [MODIFIE] $relativePath" -ForegroundColor Green

        if (-not $DryRun) {
            Set-Content -Path $FilePath -Value $content -Encoding UTF8 -NoNewline
        }
    }
}

# Début
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  MIGRATION: Record -> RecordPhysical" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($DryRun) {
    Write-Host "`n[MODE SIMULATION - Aucun fichier ne sera modifie]`n" -ForegroundColor Yellow
}

foreach ($targetPath in $targetPaths) {
    $fullPath = Join-Path $rootPath $targetPath
    if (Test-Path $fullPath) {
        Write-Host "`nTraitement: $targetPath" -ForegroundColor Yellow
        $files = Get-ChildItem -Path $fullPath -Filter "*.php" -Recurse -File
        foreach ($file in $files) {
            Update-File -FilePath $file.FullName
        }
    }
}

# Résumé
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  RESUME" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fichiers analyses      : $($stats.FilesScanned)" -ForegroundColor White
Write-Host "Fichiers modifies      : $($stats.FilesModified)" -ForegroundColor Green
Write-Host "Remplacements effectues: $($stats.ReplacementsMade)" -ForegroundColor Green

if ($DryRun) {
    Write-Host "`n[Pour appliquer les changements, executez sans -DryRun]`n" -ForegroundColor Yellow
}
else {
    Write-Host "`n[TERMINE - Executez: composer dump-autoload]`n" -ForegroundColor Green
}
