<?php

require_once 'bootstrap/app.php';

use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\ExternalContact;
use App\Models\ExternalOrganization;

echo "=== DEBUG MAIL INCOMING ERROR ===\n";

// Vérifier les données nécessaires
echo "1. Vérification des typologies de courrier:\n";
$typologies = MailTypology::orderBy('name')->get();
echo "   Nombre de typologies: " . $typologies->count() . "\n";
if ($typologies->count() > 0) {
    echo "   Première typologie: " . $typologies->first()->name . " (ID: " . $typologies->first()->id . ")\n";
}

echo "\n2. Vérification des organisations:\n";
$senderOrganisations = Organisation::orderBy('name')->get();
echo "   Nombre d'organisations: " . $senderOrganisations->count() . "\n";

echo "\n3. Vérification des contacts externes:\n";
$externalContacts = ExternalContact::with('organization')->orderBy('last_name')->get();
echo "   Nombre de contacts externes: " . $externalContacts->count() . "\n";

echo "\n4. Vérification des organisations externes:\n";
$externalOrganizations = ExternalOrganization::orderBy('name')->get();
echo "   Nombre d'organisations externes: " . $externalOrganizations->count() . "\n";

// Test de la génération de code
if ($typologies->count() > 0) {
    echo "\n5. Test de génération de code courrier:\n";
    $typology = $typologies->first();
    $year = date('Y');
    echo "   Typologie utilisée: " . $typology->code . "\n";
    echo "   Année: " . $year . "\n";

    $count = \App\Models\Mail::whereYear('created_at', $year)
            ->where('typology_id', $typology->id)
            ->count();
    echo "   Nombre de courriers existants cette année pour cette typologie: " . $count . "\n";

    $nextNumber = $count + 1;
    $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    $candidateCode = $year . "/" . $typology->code . "/" . $formattedNumber;
    echo "   Code candidat: " . $candidateCode . "\n";
}

echo "\n=== FIN DEBUG ===\n";
