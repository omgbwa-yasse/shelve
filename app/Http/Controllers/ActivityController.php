<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with('parent','communicability')->orderBy('code', 'asc')->get();
        return view('activities.index', compact('activities'));
    }


    public function create()
    {
        $parents = Activity::all();
        return view('activities.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:activities|max:10',
            'name' => 'required|max:100',
            'observation' => 'nullable',
            'parent_id' => 'nullable|exists:activities,id',
        ]);

        Activity::create($request->all());

        return redirect()->route('activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity)
    {
        $activity->load('communicability');
        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity)
    {
        $parents = Activity::all();
        return view('activities.edit', compact('activity', 'parents'));
    }

    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'code' => 'required|unique:activities,code,' . $activity->id . '|max:10',
            'name' => 'required|max:100',
            'observation' => 'nullable',
            'parent_id' => 'nullable|exists:activities,id',
        ]);

        $activity->update($request->all());

        return redirect()->route('activities.index')
            ->with('success', 'Activity updated successfully.');
    }
    private function buildHierarchy($activities)
    {
        $grouped = $activities->groupBy('parent_id');

        // Fonction récursive pour construire l'arbre
        $buildTree = function($parentId, $level = 0) use (&$buildTree, $grouped) {
            if (!isset($grouped[$parentId])) {
                return collect();
            }

            return $grouped[$parentId]->map(function($activity) use ($buildTree, $level) {
                return [
                    'activity' => $activity,
                    'level' => $level,
                    'children' => $buildTree($activity->id, $level + 1)
                ];
            });
        };

        // Commence avec les activités sans parent (parent_id = null)
        return $buildTree(null);
    }

    private function flattenHierarchy($hierarchy)
    {
        $result = collect();

        foreach ($hierarchy as $item) {
            $result->push([
                'activity' => $item['activity'],
                'level' => $item['level']
            ]);

            $result = $result->concat($this->flattenHierarchy($item['children']));
        }

        return $result;
    }

    public function exportExcel()
    {
        $activities = Activity::with('parent', 'communicability')
            ->orderBy('code', 'asc')
            ->get();

        $hierarchy = $this->buildHierarchy($activities);
        $flattenedHierarchy = $this->flattenHierarchy($hierarchy);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'Code');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Observation');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Style pour l'en-tête
        $sheet->getStyle('A1:C1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ]);

        // Données avec indentation
        $row = 2;
        foreach ($flattenedHierarchy as $item) {
            $indentation = str_repeat('    ', $item['level']);
            $activity = $item['activity'];

            $sheet->setCellValue('A' . $row, $activity->code);
            $sheet->setCellValue('B' . $row, $indentation . $activity->name);
            $sheet->setCellValue('C' . $row, $activity->observation);

            // Style conditionnel pour les missions (niveau 0)
            if ($item['level'] === 0) {
                $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
                $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA']
                    ]
                ]);
            }

            $row++;
        }

        // Ajustement automatique des colonnes
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'plan_de_classement_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function exportPdf()
    {
        $activities = Activity::with('parent', 'communicability')
            ->orderBy('code', 'asc')
            ->get();

        $hierarchy = $this->buildHierarchy($activities);

        $pdf = PDF::loadView('activities.pdf', [
            'hierarchy' => $hierarchy
        ]);

        return $pdf->download('plan_de_classement_' . date('Y-m-d') . '.pdf');
    }
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
}


