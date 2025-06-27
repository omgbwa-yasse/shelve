#!/usr/bin/env php
<?php

// Script pour supprimer guard_name de toutes les permissions dans le seeder

$file = 'c:\wamp64\www\shelves\database\seeders\PermissionSeeder.php';
$content = file_get_contents($file);

// Remplacer toutes les occurrences de guard_name
$pattern = "/,\s*'guard_name'\s*=>\s*'web',/";
$replacement = ",";

$updated_content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $updated_content);

echo "Guard_name supprimé de toutes les permissions.\n";
