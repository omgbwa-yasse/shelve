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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;


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
        $timestamp = date('Y-m-d_H-i-s');
        $filename = 'backup_' . $timestamp . '.sql';
        $backupDir = storage_path('app/backups/' . $timestamp);

        // Création du dossier de sauvegarde s'il n'existe pas
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Commande de sauvegarde de la base de données
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg(env('DB_HOST')),
            escapeshellarg(env('DB_USERNAME')),
            escapeshellarg(env('DB_PASSWORD')),
            escapeshellarg(env('DB_DATABASE')),
            escapeshellarg($backupDir . '/' . $filename)
        );
        exec($command);

        // Si une sauvegarde complète est demandée, sauvegarder les fichiers
        if ($request->input('type') === 'full') {
            $this->copyDirectory(storage_path('app'), $backupDir . '/storage_app');
            $this->copyDirectory(public_path('storage'), $backupDir . '/public_storage');
        }

        // Création d'une archive ZIP du dossier de sauvegarde
        $zipFilename = 'backup_' . $timestamp . '.zip';
        $zipPath = storage_path('app/' . $zipFilename);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addFolderToZip($backupDir, $zip);
            $zip->close();
        }

        $size = filesize($zipPath);
        $hash = hash_file('sha256', $zipPath);

        $this->deleteDirectory($backupDir);


        $backup = Backup::create([
            'date_time' => now(),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'user_id' => auth()->id(),
            'size' => $size,
            'backup_file' => $zipFilename,
            'path' => Storage::disk('public')->putFileAs('backups', new \Illuminate\Http\File($zipPath), $zipFilename),
            'hash' => $hash,
        ]);

        unlink($zipPath);

        return redirect()->route('backups.index')->with('success', 'Backup created successfully');
    }


        private function copyDirectory($source, $destination)
        {
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
                if ($item->isDir()) {
                    if (!file_exists($destPath)) {
                        mkdir($destPath);
                    }
                } else {
                    copy($item, $destPath);
                }
            }
        }



        private function addFolderToZip($folder, &$zipFile)
        {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folder) + 1);
                    $zipFile->addFile($filePath, $relativePath);
                }
            }
        }

        private function deleteDirectory($dir)
        {
            if (!file_exists($dir)) {
                return true;
            }

            if (!is_dir($dir)) {
                return unlink($dir);
            }

            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }

            return rmdir($dir);
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
