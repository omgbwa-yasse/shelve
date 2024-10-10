<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Storage;

class BarcodeController extends Controller
{
    public function create()
    {
        return view('barcodes.create');
    }

    public function generate(Request $request)
    {
        // Validation des paramètres
        $request->validate([
            'debut' => 'required|integer',
            'nombre' => 'required|integer',
            'prefixe' => 'required|string',
            'suffixe' => 'required|string',
            'barcodes_per_line' => 'required|integer',
            'barcodes_per_column' => 'required|integer',
            'margin_left' => 'required|integer',
            'margin_top' => 'required|integer',
            'margin_right' => 'required|integer',
            'margin_bottom' => 'required|integer',
        ]);

        // Récupération des paramètres
        $debut = $request->debut;
        $nombre = $request->nombre;
        $prefixe = $request->prefixe;
        $suffixe = $request->suffixe;

        // Génération des codes-barres
        $barcodes = [];
        for ($i = $debut; $i < $debut + $nombre; $i++) {
            $barcodes[] = $prefixe . $i . $suffixe;
        }

        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        // Génération du HTML pour le PDF
        $html = $this->generateHtml($barcodes, $request);

        $dompdf->loadHtml($html);
        $dompdf->render();

        // Sauvegarde du PDF
        $output = $dompdf->output();

        // Générer un nom de fichier unique pour éviter les conflits

        $fileName = 'barcodes_' . time() . '.pdf';

        // Utilisation de Storage pour sauvegarder le fichier
        Storage::disk('public')->put('barcodes/' . $fileName, $output);

        // Génération de l'URL du fichier
        $fileUrl = Storage::disk('public')->url('barcodes/' . $fileName);

        // Retourner l'URL du fichier PDF en JSON
        return response()->json([
            'pdf_file' => $fileUrl
        ]);
    }




    private function generateHtml($barcodes, $request)
    {
        $barcodesPerLine = $request->barcodes_per_line;
        $barcodesPerColumn = $request->barcodes_per_column;
        $marginLeft = $request->margin_left;
        $marginTop = $request->margin_top;
        $marginRight = $request->margin_right;
        $marginBottom = $request->margin_bottom;

        $html = '
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <style>
                                body {
                                    margin-left: ' . $marginLeft . 'mm;
                                    margin-top: ' . $marginTop . 'mm;
                                    margin-right: ' . $marginRight . 'mm;
                                    margin-bottom: ' . $marginBottom . 'mm;
                                    font-family: Arial, sans-serif;
                                    background-color: #f4f4f4; /* Couleur de fond douce pour le corps */
                                }
                                .table-container {
                                    width: 90%;
                                    margin: 20px auto;
                                    background-color: #ffffff;
                                    padding: 15px;
                                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                    border-radius: 8px;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                }
                                td {
                                    vertical-align: middle;
                                    text-align: center;
                                    padding: 10px;
                                    border: 1px solid #ddd;
                                    background-color: #fafafa;
                                }
                                td div {
                                    margin-top: 5px;
                                    font-size: 14px;
                                    color: #333;
                                }
                                tr:nth-child(even) td {
                                    background-color:
                                }
                            </style>
                        </head>
                        <body>
                            <div class="table-container">
                                <table>
                                    <tbody>';

                    $dns1d = new DNS1D();
                    $barcodeCount = 0;
                    for ($row = 0; $row < $barcodesPerColumn; $row++) {
                        $html .= '<tr>';
                        for ($col = 0; $col < $barcodesPerLine; $col++) {
                            if ($barcodeCount < count($barcodes)) {
                                $barcode = $barcodes[$barcodeCount];
                                $html .= '
                                    <td>
                                        ' . $dns1d->getBarcodeHTML($barcode, 'C128', 1, 25) . '
                                        <div>' . $barcode . '</div>
                                    </td>';
                                $barcodeCount++;
                            } else {
                                $html .= '<td></td>';
                            }
                        }
                        $html .= '</tr>';
                    }

                    $html .= '
                            </tbody>
                            </table>
                        </div>
                        </body>
                        </html>';

                    return $html;



        }
}
