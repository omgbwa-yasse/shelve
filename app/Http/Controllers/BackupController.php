<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\BackupFile;
use App\Models\BackupPlanning;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{

    public function index()
    {
        $backups = Backup::with(['backupFiles', 'backupPlannings', 'user'])->get();

        foreach ($backups as $backup) {
            if (!Storage::exists($backup->backup_file)) {
                $backup->status = 'failed';
                $backup->save();
            }
        }

        return view('backups.index', compact('backups'));
    }




    public function create()
    {
        return view('backups.create');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:metadata,full',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,success,failed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('backups.index')->with('error', 'Validation error');
        }

        // Get database credentials
        $databaseName = env('DB_DATABASE');
        $userName = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');

        // Generate filename and command
        $filename = "backup_" . date("Y-m-d_H-i-s") . ".sql";
        $command = "mysqldump -h {$host} -u {$userName} -p'{$password}' {$databaseName} > " . storage_path("app/backups/{$filename}");

        // Execute mysqldump command
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            return redirect()->route('backups.index')->with('error', 'Backup failed');
        }

        // Create ZIP archive
        $zip = new ZipArchive();
        $zipFilename = "backup_" . date("Y-m-d_H-i-s") . ".zip";
        $zip->open($zipFilename, ZipArchive::CREATE);
        $zip->addFile($filename);
        $zip->close();

        // Calculate size and hash
        $size = filesize($zipFilename);
        $hash = hash('sha256', file_get_contents($zipFilename));

        // Delete temporary SQL file
        unlink($filename);

        // Store backup information in database
        $backup = Backup::create([
            'date_time' => now(),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'user_id' => auth()->user()->id,
            'size' => $size,
            'backup_file' => $zipFilename,
            'path' => Storage::disk('public')->putFile('backups', $zipFilename),
            'hash' => $hash,
        ]);

        return redirect()->route('backups.index')->with('success', 'Backup created successfully');
    }



    public function show($id)
    {
        $backup = Backup::with(['backupFiles', 'backupPlannings', 'user'])->find($id);
        if (!$backup) {
            return redirect()->route('backups.index')->with('errors', 'backup failed.');
        }
        return view('backups.show', compact('backup'));
    }



    public function update(Request $request, $id)
    {
        $backup = Backup::find($id);
        if (!$backup) {
            return response()->json(['error' => 'Backup not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'date_time' => 'sometimes|date',
            'type' => 'sometimes|in:metadata,full',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:in_progress,success,failed',
            'user_id' => 'sometimes|exists:users,id',
            'size' => 'sometimes|integer',
            'backup_file' => 'sometimes|string',
            'path' => 'sometimes|string',
        ]);
        if ($validator->fails()) {

            return redirect()->route('backups.index')->with('errors', 'backup failed.');
        }
        $backup->update($request->all());

        return view('backups.show', compact('backups'));
    }




    public function destroy($id)
    {
        $backup = Backup::find($id);

        if (!$backup) {
            return redirect()->route('backups.index')->with('errors', 'Backup non trouvé.');
        }

        if (Storage::exists($backup->backup_file)) {
            Storage::delete($backup->backup_file);
        }

        $backup->delete();

        return redirect()->route('backups.index')->with('success', 'Sauvegarde supprimée avec succès.');
    }



}
