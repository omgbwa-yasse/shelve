<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrganisationController extends Controller
{

    public function index()
    {
        $organisations = Organisation::with('parent')->orderBy('code', 'asc')->get();
        $hierarchy = $this->buildHierarchy($organisations);
        return view('organisations.index', compact('organisations', 'hierarchy'));
    }
    public function switchOrganisation(Request $request)
    {
        $organisationId = $request->input('organisation_id');
        $user = Auth::user();

        if ($user->organisations->contains($organisationId)) {
            $user->current_organisation_id = $organisationId;
            $user->save();

            return redirect()->back()->with('success', 'Organisation changée avec succès');
        }

        return redirect()->back()->with('error', 'Impossible de changer d\'organisation');
    }
    public function create()
    {
        $organisations = Organisation::all();

        return view('organisations.create', compact('organisations'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:organisations|max:10',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        Organisation::create($request->all());

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation created successfully.');
    }




    public function show(Organisation $organisation)
    {
        return view('organisations.show', compact('organisation'));
    }




    public function edit(Organisation $organisation)
    {
        $organisations = Organisation::where('id', '<>', $organisation->id)->get();

        return view('organisations.edit', compact('organisation', 'organisations'));
    }



    public function update(Request $request, Organisation $organisation)
    {
        $request->validate([
            'code' => 'required|unique:organisations,code,'.$organisation->id.'|max:10',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'parent_id' => 'nullable|exists:organisations,id',
        ]);

        $organisation->update($request->all());

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation updated successfully.');
    }


    private function buildHierarchy($organisations)
    {
        $grouped = $organisations->groupBy('parent_id');

        $buildTree = function($parentId, $level = 0) use (&$buildTree, $grouped) {
            if (!isset($grouped[$parentId])) {
                return collect();
            }

            return $grouped[$parentId]->map(function($organisation) use ($buildTree, $level) {
                return [
                    'organisation' => $organisation,
                    'level' => $level,
                    'children' => $buildTree($organisation->id, $level + 1)
                ];
            });
        };

        return $buildTree(null);
    }

    private function flattenHierarchy($hierarchy)
    {
        $result = collect();

        foreach ($hierarchy as $item) {
            $result->push([
                'organisation' => $item['organisation'],
                'level' => $item['level']
            ]);

            $result = $result->concat($this->flattenHierarchy($item['children']));
        }

        return $result;
    }



    public function exportExcel()
    {
        $organisations = Organisation::with('parent')
            ->orderBy('code', 'asc')
            ->get();

        $hierarchy = $this->buildHierarchy($organisations);
        $flattenedHierarchy = $this->flattenHierarchy($hierarchy);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'Code');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Description');
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
            $organisation = $item['organisation'];

            $sheet->setCellValue('A' . $row, $organisation->code);
            $sheet->setCellValue('B' . $row, $indentation . $organisation->name);
            $sheet->setCellValue('C' . $row, $organisation->description);

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

        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'organigramme_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function exportPdf()
    {
        $organisations = Organisation::with('parent')
            ->orderBy('code', 'asc')
            ->get();

        $hierarchy = $this->buildHierarchy($organisations);

        $pdf = PDF::loadView('organisations.pdf', [
            'hierarchy' => $hierarchy
        ]);

        return $pdf->download('organigramme_' . date('Y-m-d') . '.pdf');
    }
    public function destroy(Organisation $organisation)
    {
        $organisation->delete();

        return redirect()->route('organisations.index')
                        ->with('success', 'Organisation deleted successfully.');
    }
}
