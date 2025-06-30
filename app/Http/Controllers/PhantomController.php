<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PhantomController extends Controller
{
    /**
     * Génère un PDF fantôme pour une communication
     */
    public function generatePhantom(Communication $communication)
    {
        // Charger les relations nécessaires
        $communication->load([
            'user',
            'userOrganisation',
            'operator',
            'operatorOrganisation',
            'records' => function($query) {
                $query->withPivot('return_date', 'return_effective', 'created_at', 'updated_at');
            }
        ]);

        // Préparer les données pour le PDF
        $data = [
            'communication' => $communication,
            'generated_at' => Carbon::now(),
            'records' => $communication->records->map(function($record) {
                return [
                    'id' => $record->id,
                    'name' => $record->name,
                    'code' => $record->code ?? 'N/A',
                    'status' => $this->getRecordStatus($record),
                    'return_date' => $record->pivot->return_date,
                    'return_effective' => $record->pivot->return_effective,
                    'last_modified' => $record->pivot->updated_at ?? $record->pivot->created_at,
                    'content' => $record->content ?? '',
                    'width' => $record->width ?? '',
                    'width_description' => $record->width_description ?? ''
                ];
            })
        ];

        // Générer le PDF
        $pdf = Pdf::loadView('communications.phantom', $data);
        $pdf->setPaper('A4', 'portrait');

        // Définir le nom du fichier
        $filename = 'fantome_' . $communication->code . '_' . Carbon::now()->format('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Génère un aperçu HTML du fantôme (pour debug/preview)
     */
    public function previewPhantom(Communication $communication)
    {
        // Charger les relations nécessaires
        $communication->load([
            'user',
            'userOrganisation',
            'operator',
            'operatorOrganisation',
            'records' => function($query) {
                $query->withPivot('return_date', 'return_effective', 'created_at', 'updated_at');
            }
        ]);

        // Préparer les données
        $data = [
            'communication' => $communication,
            'generated_at' => Carbon::now(),
            'records' => $communication->records->map(function($record) {
                return [
                    'id' => $record->id,
                    'name' => $record->name,
                    'code' => $record->code ?? 'N/A',
                    'status' => $this->getRecordStatus($record),
                    'return_date' => $record->pivot->return_date,
                    'return_effective' => $record->pivot->return_effective,
                    'last_modified' => $record->pivot->updated_at ?? $record->pivot->created_at,
                    'content' => $record->content ?? '',
                    'width' => $record->width ?? '',
                    'width_description' => $record->width_description ?? ''
                ];
            })
        ];

        return view('communications.phantom', $data);
    }

    /**
     * Détermine le statut d'un record basé sur les dates de retour
     */
    private function getRecordStatus($record)
    {
        if ($record->pivot->return_effective) {
            return [
                'label' => __('Returned'),
                'class' => 'success',
                'icon' => 'check-circle-fill'
            ];
        }

        if ($record->pivot->return_date) {
            $returnDate = Carbon::parse($record->pivot->return_date);
            $now = Carbon::now();

            if ($returnDate < $now) {
                return [
                    'label' => __('Overdue'),
                    'class' => 'danger',
                    'icon' => 'exclamation-triangle-fill'
                ];
            } elseif ($returnDate->isToday()) {
                return [
                    'label' => __('Due Today'),
                    'class' => 'warning',
                    'icon' => 'clock-fill'
                ];
            } else {
                return [
                    'label' => __('On Loan'),
                    'class' => 'primary',
                    'icon' => 'arrow-right-circle'
                ];
            }
        }

        return [
            'label' => __('Unknown'),
            'class' => 'secondary',
            'icon' => 'question-circle'
        ];
    }
}
