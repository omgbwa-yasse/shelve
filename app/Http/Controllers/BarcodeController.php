<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            $validatedData = $this->validateBarcodeData($request);

            // Convertir show_text en booléen
            $validatedData['show_text'] = filter_var($validatedData['show_text'], FILTER_VALIDATE_BOOLEAN);

            Log::info('Data validated successfully', $validatedData);

            $barcodes = $this->generateBarcodes($validatedData);
            $pdf = $this->generatePDF($barcodes, $validatedData);

            Log::info('PDF generated successfully');

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="barcodes.pdf"');
        } catch (ValidationException $e) {
            Log::error('Validation error in generate method', ['errors' => $e->errors()]);
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in generate method', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Une erreur est survenue lors de la génération du PDF.'], 500);
        }
    }

    public function preview(Request $request)
    {
        try {
            $validatedData = $this->validateBarcodeData($request, true);

            // Convertir show_text en booléen
            $validatedData['show_text'] = filter_var($validatedData['show_text'], FILTER_VALIDATE_BOOLEAN);

            $barcodes = $this->generateBarcodes($validatedData);
            $perPage = $validatedData['per_page'];
            $columns = 2;
            $rows = ceil($perPage / $columns);

            while ($rows > 10 && $columns < 5) {
                $columns++;
                $rows = ceil($perPage / $columns);
            }

            $barcodeGenerator = new DNS1D();
            $pageSize = $validatedData['page_size'];
            $barcodeType = $validatedData['barcode_type'];
            $barcodeWidth = $validatedData['barcode_width'];
            $barcodeHeight = $validatedData['barcode_height'];
            $showText = $validatedData['show_text'];

            $html = view('barcodes.preview', compact('barcodes', 'perPage', 'columns', 'rows', 'barcodeGenerator', 'pageSize', 'barcodeType', 'barcodeWidth', 'barcodeHeight', 'showText'))->render();

            return response()->json(['html' => $html]);
        } catch (ValidationException $e) {
            Log::error('Validation error in preview method', ['errors' => $e->errors()]);
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in preview method', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Une erreur est survenue lors de la génération de la prévisualisation.'], 500);
        }
    }

    private function validateBarcodeData(Request $request, $isPreview = false)
    {
        $maxCount = $isPreview ? 10 : 1000;

        $rules = [
            'start' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($request->barcode_type, ['UPC', 'EAN13']) && $value < 100000000000) {
                        $fail("Le numéro de début doit être d'au moins 12 chiffres pour les codes UPC et EAN13.");
                    }
                },
            ],
            'count' => "required|integer|min:1|max:{$maxCount}",
            'prefix' => 'nullable|string|max:10',
            'suffix' => 'nullable|string|max:10',
            'per_page' => 'required|integer|min:1|max:100',
            'page_size' => 'required|in:A4,Letter',
            'barcode_type' => ['required', Rule::in(['C128', 'C39', 'EAN13', 'UPC', 'I25'])],
            'barcode_width' => 'required|numeric|min:1|max:5',
            'barcode_height' => 'required|integer|min:10|max:100',
            'show_text' => 'required|in:0,1,true,false',
        ];

        return $request->validate($rules);
    }

    private function generateBarcodes($data)
    {
        $barcodes = [];
        $end = $data['start'] + $data['count'];
        for ($i = $data['start']; $i < $end; $i++) {
            $code = ($data['prefix'] ?? '') . $i . ($data['suffix'] ?? '');
            if (in_array($data['barcode_type'], ['UPC', 'EAN13'])) {
                $code = str_pad($code, 12, '0', STR_PAD_LEFT);
                if ($data['barcode_type'] === 'EAN13') {
                    $code = $this->calculateEAN13CheckDigit($code);
                }
            }
            $barcodes[] = $code;
        }
        return collect($barcodes);
    }

    private function calculateEAN13CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * ($i % 2 ? 3 : 1);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $code . $checkDigit;
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
        $columns = min(5, ceil(sqrt($perPage)));
        $rows = ceil($perPage / $columns);

        $barcodeGenerator = new DNS1D();
        $pageSize = $data['page_size'];
        $barcodeType = $data['barcode_type'];
        $barcodeWidth = $data['barcode_width'];
        $barcodeHeight = $data['barcode_height'];
        $showText = $data['show_text'];

        $html = view('barcodes.pdf', compact('barcodes', 'perPage', 'columns', 'rows', 'barcodeGenerator', 'pageSize', 'barcodeType', 'barcodeWidth', 'barcodeHeight', 'showText'))->render();
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }
}
