<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    // Affiche la page principale du module scan pour la numérisation
    public function index()
    {
        return view('scan.session');
    }

    // Affiche la liste des documents numérisés
    public function list()
    {
        return view('scan.list');
    }

    // Reçoit une page scannée (upload AJAX)
    public function uploadPage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,tiff',
            'session' => 'required|string',
        ]);
        $session = $request->input('session');
        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "scans/{$session}/pages/{$filename}";
        Storage::disk('local')->put($path, file_get_contents($file));
        return response()->json(['success' => true, 'filename' => $filename]);
    }

    // Liste les pages d'une session de scan
    public function listPages($session)
    {
        $files = Storage::disk('local')->files("scans/{$session}/pages");
        return response()->json(['pages' => $files]);
    }

    // Détecte les scanners connectés
    public function detectScanners()
    {
        // Dans un environnement réel, vous utiliseriez une bibliothèque ou un service
        // pour détecter les scanners comme TWAIN, SANE, WIA ou autres selon le système d'exploitation

        // Pour la démonstration, nous renverrons une liste statique
        // mais dans un vrai cas d'utilisation, ce serait dynamique

        $scanners = [];

        // Simulation de détection de scanners
        // Dans un cas réel, il faudrait ici implémenter la détection via une bibliothèque adaptée
        $possibleScanners = [
            ['id' => 'scanner1', 'name' => 'HP ScanJet Pro 2000 s2'],
            ['id' => 'scanner2', 'name' => 'Epson WorkForce ES-500W'],
            ['id' => 'scanner3', 'name' => 'Brother ADS-2700W'],
            ['id' => 'scanner4', 'name' => 'Canon imageFormula R40'],
            ['id' => 'scanner5', 'name' => 'Fujitsu ScanSnap iX1600'],
        ];

        // Ajouter aléatoirement des scanners à la liste pour simuler une détection
        // Dans un cas réel, cette liste serait remplie avec les vrais périphériques détectés
        foreach ($possibleScanners as $scanner) {
            if (rand(0, 1)) {
                $scanners[] = $scanner;
            }
        }

        return response()->json([
            'success' => true,
            'scanners' => $scanners
        ]);
    }

    // Supprime une page d'une session
    public function deletePage(Request $request)
    {
        $request->validate([
            'session' => 'required|string',
            'filename' => 'required|string',
        ]);
        $path = "scans/{$request->session}/pages/{$request->filename}";
        Storage::disk('local')->delete($path);
        return response()->json(['success' => true]);
    }

    // Enregistre le scan en PDF ou image (JPEG/TIFF)
    public function save(Request $request)
    {
        $request->validate([
            'session' => 'required|string',
            'format' => 'required|in:pdf,jpeg,tiff',
        ]);
        // TODO: Générer le PDF ou l'image multi-page à partir des images uploadées
        // Stocker dans scans/{session}/output.{format}
        return response()->json(['success' => true, 'message' => 'Fonction à implémenter']);
    }

    // Liste les dossiers de numérisation
    public function listSessions()
    {
        $dirs = Storage::disk('local')->directories('scans');
        return response()->json(['sessions' => $dirs]);
    }

    // Permet de transférer un scan comme pièce jointe (à implémenter)
    public function attachToRecord(Request $request)
    {
        $request->validate([
            'session' => 'required|string',
            'target_type' => 'required|in:record,sliprecord,mail',
            'target_id' => 'required|integer',
        ]);
        // TODO: Associer le fichier à la cible
        return response()->json(['success' => true, 'message' => 'Fonction à implémenter']);
    }
}
