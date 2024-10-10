<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Backup;
use App\Models\BackupPlanning;
use Illuminate\Support\Facades\Validator;

class BackupPlanningController extends Controller
{
    public function index($backupId)
    {
        $backup = Backup::findOrFail($backupId);
        $plannings = $backup->backupPlannings;
        return view('backups.plannings.index', compact('backup', 'plannings'));
    }



    public function create($backupId)
    {
        $backup = Backup::findOrFail($backupId);
        return view('backups.plannings.create', compact('backup'));
    }



    public function store(Request $request, $backupId)
    {
        $validator = Validator::make($request->all(), [
            'frequence' => 'required|in:daily,weekly,monthly',
            'week_day' => 'required_if:frequence,weekly|nullable|integer|between:1,7',
            'month_day' => 'required_if:frequence,monthly|nullable|integer|between:1,31',
            'hour' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->route('backups.plannings.create', $backupId)->withErrors($validator)->withInput();
        }

        $backup = Backup::findOrFail($backupId);
        $backup->backupPlannings()->create($request->all());

        return redirect()->route('backups.plannings.index', $backupId)->with('success', 'Backup planning created successfully.');
    }




    public function show($backupId, $planningId)
    {
        $backup = Backup::findOrFail($backupId);
        $planning = $backup->backupPlannings()->findOrFail($planningId);
        return view('backups.plannings.show', compact('backup', 'planning'));
    }



    public function edit($backupId, $planningId)
    {
        $backup = Backup::findOrFail($backupId);
        $planning = $backup->backupPlannings()->findOrFail($planningId);
        return view('backups.plannings.edit', compact('backup', 'planning'));
    }



    public function update(Request $request, $backupId, $planningId)
    {
        $validator = Validator::make($request->all(), [
            'frequence' => 'required|in:daily,weekly,monthly',
            'week_day' => 'required_if:frequence,weekly|nullable|integer|between:1,7',
            'month_day' => 'required_if:frequence,monthly|nullable|integer|between:1,31',
            'hour' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->route('backups.plannings.edit', [$backupId, $planningId])->withErrors($validator)->withInput();
        }

        $backup = Backup::findOrFail($backupId);
        $planning = $backup->backupPlannings()->findOrFail($planningId);
        $planning->update($request->all());

        return redirect()->route('backups.plannings.show', [$backupId, $planningId])->with('success', 'Backup planning updated successfully.');
    }



    public function destroy($backupId, $planningId)
    {
        $backup = Backup::findOrFail($backupId);
        $planning = $backup->backupPlannings()->findOrFail($planningId);
        $planning->delete();

        return redirect()->route('backups.plannings.index', $backupId)->with('success', 'Backup planning deleted successfully.');
    }


}
