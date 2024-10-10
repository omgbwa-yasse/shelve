<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Backup;
use App\Models\BackupFile;
use Illuminate\Support\Facades\Validator;

class BackupFileController extends Controller
{
    public function index($backupId)
    {
        $backup = Backup::findOrFail($backupId);
        $files = $backup->backupFiles;
        return view('backups.files.index', compact('backup', 'files'));
    }




    public function create($backupId)
    {
        $backup = Backup::findOrFail($backupId);
        return view('backups.files.create', compact('backup'));
    }




    public function store(Request $request, $backupId)
    {
        $validator = Validator::make($request->all(), [
            'path_original' => 'required|string',
            'path_storage' => 'required|string',
            'size' => 'required|integer',
            'hash' => 'required|string|size:150',
        ]);

        if ($validator->fails()) {
            return redirect()->route('backups.files.create', $backupId)->withErrors($validator)->withInput();
        }

        $backup = Backup::findOrFail($backupId);
        $backup->backupFiles()->create($request->all());

        return redirect()->route('backups.files.index', $backupId)->with('success', 'Backup file created successfully.');
    }




    public function show($backupId, $fileId)
    {
        $backup = Backup::findOrFail($backupId);
        $file = $backup->backupFiles()->findOrFail($fileId);
        return view('backups.files.show', compact('backup', 'file'));
    }




    public function edit($backupId, $fileId)
    {
        $backup = Backup::findOrFail($backupId);
        $file = $backup->backupFiles()->findOrFail($fileId);
        return view('backups.files.edit', compact('backup', 'file'));
    }




    public function update(Request $request, $backupId, $fileId)
    {
        $validator = Validator::make($request->all(), [
            'path_original' => 'required|string',
            'path_storage' => 'required|string',
            'size' => 'required|integer',
            'hash' => 'required|string|size:150',
        ]);

        if ($validator->fails()) {
            return redirect()->route('backups.files.edit', [$backupId, $fileId])->withErrors($validator)->withInput();
        }

        $backup = Backup::findOrFail($backupId);
        $file = $backup->backupFiles()->findOrFail($fileId);
        $file->update($request->all());

        return redirect()->route('backups.files.show', [$backupId, $fileId])->with('success', 'Backup file updated successfully.');
    }



    public function destroy($backupId, $fileId)
    {
        $backup = Backup::findOrFail($backupId);
        $file = $backup->backupFiles()->findOrFail($fileId);
        $file->delete();

        return redirect()->route('backups.files.index', $backupId)->with('success', 'Backup file deleted successfully.');
    }



}


