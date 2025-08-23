<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RecordAttachment;
use App\Models\Record;
use App\Models\Attachment;
use Intervention\Image\Image;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RecordAttachmentController extends Controller
{
    public function index(Record $record)
    {
        $attachments = $record->attachments;
        return view('records.attachments.index', compact('record', 'attachments'));
    }

    public function create($id)
    {
        $record = Record::findOrFail($id);
        return view('records.attachments.create', compact('record'));
    }

    public function store(Request $request, $id)
    {
        // Vérifier les configurations PHP
        $this->checkUploadConfiguration();
        
        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:102400', // 100MB max
                'thumbnail' => 'nullable|string',
            ]);

            $record = Record::findOrFail($id);
            $file = $request->file('file');
            
            // Vérifier que le fichier est valide
            if (!$file || !$file->isValid()) {
                throw new \Exception('Le fichier du champ file n\'a pu être téléversé.');
            }

            $path = $file->store('attachments');
            $filePath = $file->getRealPath();

            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0];

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($filePath),
                'crypt_sha512' => hash_file('sha512', $filePath),
                'size' => $file->getSize(),
                'creator_id' => auth()->id(),
                'mime_type' => $mimeType,
                'type' => 'record',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_record/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->update(['thumbnail_path' => $thumbnailPath]);
                }
            } else {
                // Generate thumbnail for images and videos if not provided
                if (in_array($fileType, ['image', 'video'])) {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id, $fileType);
                    if ($thumbnailPath) {
                        $attachment->update(['thumbnail_path' => $thumbnailPath]);
                    }
                }
            }

            $record->attachments()->attach($attachment->id);

            // Vérifier si c'est une requête AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attachment créé avec succès',
                    'attachment' => [
                        'id' => $attachment->id,
                        'name' => $attachment->name,
                        'size' => $attachment->size,
                        'path' => $attachment->path
                    ]
                ]);
            }

            return redirect()->route('records.attachments.index', $record->id)->with('success', 'Attachment created successfully.');

        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // Gestion spécifique de l'erreur de taille POST
            $message = 'Le fichier est trop volumineux pour la configuration actuelle du serveur. ';
            $message .= 'Configuration requise : upload_max_filesize=100M, post_max_size=100M';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $message
                ], 413);
            }
            return back()->with('error', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion des erreurs de validation
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log l'erreur
            \Log::error('Erreur dans RecordAttachmentController@store: ' . $e->getMessage(), [
                'record_id' => $id,
                'user_id' => auth()->id(),
                'file_name' => $request->file('file')?->getClientOriginalName(),
                'file_size' => $request->file('file')?->getSize(),
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    private function generateThumbnail($file, $attachmentId, $fileType)
    {
        $thumbnailPath = 'thumbnails_record/' . $attachmentId . '.jpg';

        if ($fileType === 'image') {
            $img = Image::make($file->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));
        } elseif ($fileType === 'video') {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($file->getRealPath());
            $frame = $video->frame(TimeCode::fromSeconds(1));
            $frame->save(storage_path('app/public/' . $thumbnailPath));
        } else {
            return null;
        }

        return $thumbnailPath;
    }

    public function edit(Record $record, Attachment $attachment)
    {
        return view('records.attachments.edit', compact('record', 'attachment'));
    }

    public function update(Request $request, Record $record, Attachment $attachment)
    {
        $request->validate([
            'file_path' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $attachment->update($request->all());

        return redirect()->route('records.attachments.index', $record);
    }

    public function destroy(Record $record, Attachment $attachment)
    {
        $attachment->delete();

        return redirect()->route('records.attachments.index', $record);
    }

    public function show(Record $record, Attachment $attachment)
    {
        return view('records.attachments.show', compact('record', 'attachment'));
    }
    public function download($id)
    {
        $attachment = RecordAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            // Obtenez l'extension du fichier à partir du chemin
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = $attachment->name . '.' . $fileExtension;
//dd( $fileExtension,$filePath);
            return response()->download($filePath, $fileName);
        }

        return abort(404);
    }
    public function preview($id)
    {
        $attachment = RecordAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return abort(404);
    }

    /**
     * Télécharger un attachment temporaire (sans l'associer à un record)
     */
    public function uploadTemp(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,txt,docx,doc,rtf,odt,jpg,jpeg,png,gif,mp4,avi,mov|max:102400', // 100MB max
            ]);

            $file = $request->file('file');
            $path = $file->store('attachments/temp');

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name', $file->getClientOriginalName()),
                'crypt' => md5_file($file->getRealPath()),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => Auth::id(),
                'mime_type' => $file->getMimeType(),
                'type' => 'record',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fichier téléchargé avec succès',
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'path' => $attachment->path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement temporaire:', [
                'error' => $e->getMessage(),
                'file' => $request->file('file')?->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Vérifier la configuration PHP pour les uploads
     */
    private function checkUploadConfiguration()
    {
        $maxFileSize = ini_get('upload_max_filesize');
        $maxPostSize = ini_get('post_max_size');
        $memoryLimit = ini_get('memory_limit');
        
        Log::info('Configuration PHP pour les uploads:', [
            'upload_max_filesize' => $maxFileSize,
            'post_max_size' => $maxPostSize,
            'memory_limit' => $memoryLimit,
            'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled'
        ]);
        
        if (!ini_get('file_uploads')) {
            throw new \Exception('Les uploads de fichiers sont désactivés sur ce serveur.');
        }
    }
    
    /**
     * Endpoint de diagnostic pour les limites d'upload
     */
    public function diagnostics()
    {
        $config = [
            'file_uploads' => ini_get('file_uploads') ? 'Activé' : 'Désactivé',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_file_uploads' => ini_get('max_file_uploads'),
        ];
        
        // Convertir en bytes pour comparaison
        $uploadMaxBytes = $this->parseSize(ini_get('upload_max_filesize'));
        $postMaxBytes = $this->parseSize(ini_get('post_max_size'));
        $memoryLimitBytes = $this->parseSize(ini_get('memory_limit'));
        
        $recommendations = [];
        
        if ($uploadMaxBytes < 100 * 1024 * 1024) {
            $recommendations[] = "upload_max_filesize doit être au moins 100M (actuellement: " . ini_get('upload_max_filesize') . ")";
        }
        
        if ($postMaxBytes < 100 * 1024 * 1024) {
            $recommendations[] = "post_max_size doit être au moins 100M (actuellement: " . ini_get('post_max_size') . ")";
        }
        
        if ($memoryLimitBytes < 150 * 1024 * 1024 && $memoryLimitBytes > 0) {
            $recommendations[] = "memory_limit devrait être au moins 150M (actuellement: " . ini_get('memory_limit') . ")";
        }
        
        return response()->json([
            'config' => $config,
            'config_bytes' => [
                'upload_max_filesize' => $uploadMaxBytes,
                'post_max_size' => $postMaxBytes,
                'memory_limit' => $memoryLimitBytes
            ],
            'recommendations' => $recommendations,
            'php_version' => PHP_VERSION
        ]);
    }
    
    /**
     * Convertir les tailles PHP en bytes
     */
    private function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.\-]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}


