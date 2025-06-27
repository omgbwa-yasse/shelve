# PowerShell script to fix null currentOrganisation access in all policy files

$policyDir = "c:\wamp64\www\shelves\app\Policies"
$policyFiles = Get-ChildItem -Path $policyDir -Name "*.php"

foreach ($file in $policyFiles) {
    $filePath = Join-Path $policyDir $file
    $content = Get-Content $filePath -Raw

    # Skip if file is already fixed (contains null check)
    if ($content -match '\$user->currentOrganisation &&') {
        Write-Host "Skipping $file - already fixed"
        continue
    }

    # Pattern 1: Simple return with hasPermissionTo
    $pattern1 = 'return \$user->hasPermissionTo\(([^,]+), \$user->currentOrganisation\);'
    $replacement1 = 'return $user->currentOrganisation && $user->hasPermissionTo($1, $user->currentOrganisation);'
    $content = $content -replace $pattern1, $replacement1

    # Pattern 2: Multi-line return with hasPermissionTo and checkOrganisationAccess
    $pattern2 = 'return \$user->hasPermissionTo\(([^,]+), \$user->currentOrganisation\) &&\s+\$this->checkOrganisationAccess\(\$user, \$[^)]+\);'
    $replacement2 = 'return $user->currentOrganisation &&' + "`n" + '            $user->hasPermissionTo($1, $user->currentOrganisation) &&' + "`n" + '            $this->checkOrganisationAccess($user, $record);'
    $content = $content -replace $pattern2, $replacement2

    # Write the fixed content back to file
    Set-Content -Path $filePath -Value $content -NoNewline
    Write-Host "Fixed $file"
}

Write-Host "Policy files have been updated to handle null currentOrganisation"
