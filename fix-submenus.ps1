$files = @('deposits', 'transferrings', 'tools', 'settings', 'repositories', 'mails', 'dollies')

$cssToAdd = @"

        /* Style pour les sections collapsibles */
        .submenu-content.collapsed {
            display: none;
        }

        .submenu-heading::after {
            content: '';
            margin-left: auto;
            font-family: 'bootstrap-icons';
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .submenu-heading.collapsed::after {
            transform: rotate(-90deg);
        }
"@

$jsToAdd = @"

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonctionnalité de collapse optionnelle pour les sous-menus
    const headings = document.querySelectorAll('.submenu-heading');

    headings.forEach(function(heading) {
        heading.addEventListener('click', function() {
            const content = this.nextElementSibling;

            if (content && content.classList.contains('submenu-content')) {
                // Toggle la classe collapsed
                content.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
            }
        });
    });
});
</script>
"@

foreach ($file in $files) {
    Write-Host "Processing $file.blade.php..."
    $path = "resources/views/submenu/$file.blade.php"

    if (Test-Path $path) {
        $content = Get-Content $path -Raw

        # Ajouter les styles CSS avant la fermeture de </style>
        $content = $content -replace '</style>', "$cssToAdd`n    </style>"

        # Ajouter le JavaScript avant la fermeture de </div> finale
        $content = $content -replace '</div>\s*$', "</div>$jsToAdd"

        Set-Content -Path $path -Value $content
        Write-Host "Done with $file.blade.php"
    } else {
        Write-Host "File $path not found"
    }
}

Write-Host "All files processed successfully!"
