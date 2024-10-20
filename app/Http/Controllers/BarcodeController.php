<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;

class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcodes.create');
    }
    public function create()
    {
        return view('barcodes.create');
    }


    public function generate(Request $request)
    {
        Log::info('Generate method called', $request->all());

        try {
            $validatedData = $request->validate([
                'start' => 'required|integer|min:0',
                'count' => 'required|integer|min:1|max:1000',
                'prefix' => 'nullable|string|max:10',
                'suffix' => 'nullable|string|max:10',
                'per_page' => 'required|integer|min:1|max:100',
                'page_size' => 'required|in:A4,Letter',
            ]);

            Log::info('Data validated successfully', $validatedData);

            $barcodes = $this->generateBarcodes($validatedData);
            $pdf = $this->generatePDF($barcodes, $validatedData);

            Log::info('PDF generated successfully');

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="barcodes.pdf"');
        } catch (\Exception $e) {
            Log::error('Error in generate method', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function generateBarcodes($data)
    {
        $barcodes = [];
        $end = $data['start'] + $data['count'];
        for ($i = $data['start']; $i < $end; $i++) {
            $barcodes[] = ($data['prefix'] ?? '') . $i . ($data['suffix'] ?? '');
        }
        return collect($barcodes);  // Convertir en Collection
    }

    private function generatePDF($barcodes, $data)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper($data['page_size'], 'portrait');

        $perPage = $data['per_page'];
        $columns = min(5, ceil(sqrt($perPage))); // Calculer le nombre de colonnes, max 5
        $rows = ceil($perPage / $columns);

        $barcodeGenerator = new DNS1D();
        $pageSize = $data['page_size'];

        $html = view('barcodes.pdf', compact('barcodes', 'perPage', 'columns', 'rows', 'barcodeGenerator', 'pageSize'))->render();
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }




    public function preview(Request $request)
    {
        $validatedData = $request->validate([
            'start' => 'required|integer|min:0',
            'count' => 'required|integer|min:1|max:10',
            'prefix' => 'nullable|string|max:10',
            'suffix' => 'nullable|string|max:10',
            'per_page' => 'required|integer|min:1|max:100',
            'page_size' => 'required|in:A4,Letter',
        ]);

        $barcodes = $this->generateBarcodes($validatedData);
        $perPage = $validatedData['per_page'];
        $columns = 2; // Commencez avec 2 colonnes
        $rows = ceil($perPage / $columns);

        // Augmentez le nombre de colonnes jusqu'à ce que le nombre de lignes soit raisonnable
        while ($rows > 10 && $columns < 5) {
            $columns++;
            $rows = ceil($perPage / $columns);
        }

        $barcodeGenerator = new DNS1D();
        $pageSize = $validatedData['page_size'];

        $html = view('barcodes.preview', compact('barcodes', 'perPage', 'columns', 'rows', 'barcodeGenerator', 'pageSize'))->render();

        return response()->json(['html' => $html]);
    }





}
