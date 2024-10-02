<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Imagick;

class PDFController extends Controller
{
    public function thumbnail($id)
    {
        if (!extension_loaded('imagick')) {
            Log::error("L'extension Imagick n'est pas installée ou chargée.");
            // Utilisez une image par défaut ou affichez un message d'erreur
        }
        $attachment = Attachment::findOrFail($id);
        $pdfPath = storage_path('app/public/' . $attachment->path);

        $thumbnail = $this->generatePDFThumbnail($pdfPath);

        return response($thumbnail)->header('Content-Type', 'image/jpeg');
    }

    private function generatePDFThumbnail($pdfPath)
    {
        try {
            $imagick = new Imagick();
            $imagick->setResolution(300, 300);
            $imagick->readImage($pdfPath . '[0]'); // Lire seulement la première page
            $imagick->setImageFormat('jpeg');
            $imagick->thumbnailImage(200, 0); // Largeur de 200px, hauteur proportionnelle

            return $imagick->getImageBlob();
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une image par défaut
            return file_get_contents(public_path('images/default-pdf-thumbnail.jpg'));
        }
    }
}
