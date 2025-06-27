#!/usr/bin/env php
<?php

// Script temporaire pour ajouter guard_name à toutes les permissions dans le seeder

$file = 'c:\wamp64\www\shelves\database\seeders\PermissionSeeder.php';
$content = file_get_contents($file);

// Remplacer tous les entrées de permissions pour ajouter 'guard_name' => 'web'
$pattern = "/(\[\s*'id'\s*=>\s*\d+,\s*'name'\s*=>\s*'[^']+',)(\s*'description')/";
$replacement = "$1 'guard_name' => 'web',$2";

$updated_content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $updated_content);

echo "Script terminé. Guard_name ajouté à toutes les permissions.\n";
