<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Event;
use App\Models\Attachment;
use App\Models\Organisation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;

class EventController extends Controller
{
    /**
     * Affiche la liste des événements d'un tableau d'affichage
     */
    public function index(BulletinBoard $bulletinBoard)
    {
        $events = Event::with(['bulletinBoard', 'creator', 'attachments'])
            ->where('bulletin_board_id', $bulletinBoard->id)
            ->when(request('period') === 'upcoming', function ($query) {
                return $query->where('start_date', '>=', now());
            })
            ->when(request('organisation'), function ($query, $organisationId) {
                return $query->whereHas('bulletinBoard.organisations', function ($q) use ($organisationId) {
                    $q->where('organisations.id', $organisationId);
                });
            })
            ->orderBy('start_date')
            ->paginate(10);

        $organisations = Organisation::all();

        return view('bulletin-boards.events.index', compact('bulletinBoard', 'events', 'organisations'));
    }

    /**
     * Affiche le formulaire de création d'un événement
     */
    public function create(BulletinBoard $bulletinBoard)
    {
        return view('bulletin-boards.events.create', compact('bulletinBoard'));
    }

    /**
     * Enregistre un nouvel événement
     */
    public function store(Request $request, BulletinBoard $bulletinBoard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published,cancelled',
        ]);

        $event = Event::create([
            'bulletin_board_id' => $bulletinBoard->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $request->status ?? 'published',
            'created_by' => Auth::id()
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('bulletin-boards.events.attachments.create', [$bulletinBoard, $event])
                ->with('success', 'Événement créé avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
            ->with('success', 'Événement créé avec succès.');
    }

    /**
     * Affiche un événement spécifique
     */
    public function show(BulletinBoard $bulletinBoard, Event $event)
    {
        $event->load([
            'bulletinBoard.organisations',
            'attachments',
            'creator',
        ]);

        return view('bulletin-boards.events.show', compact('bulletinBoard', 'event'));
    }

    /**
     * Affiche le formulaire d'édition d'un événement
     */
    public function edit(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $event->load([
            'bulletinBoard.organisations',
            'attachments',
            'creator',
        ]);

        return view('bulletin-boards.events.edit', compact('bulletinBoard', 'event'));
    }

    /**
     * Met à jour un événement existant
     */
    public function update(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published,cancelled',
        ]);

        $event->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location' => $validated['location'],
            'status' => $request->status ?? $event->status,
        ]);

        // Redirection vers la page d'ajout de pièces jointes si "add_attachments" est coché
        if ($request->has('add_attachments')) {
            return redirect()->route('bulletin-boards.events.attachments.create', [$bulletinBoard, $event])
                ->with('success', 'Événement mis à jour avec succès. Vous pouvez maintenant ajouter des pièces jointes.');
        }

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
            ->with('success', 'Événement mis à jour avec succès.');
    }

    /**
     * Supprime un événement
     */
    public function destroy(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Suppression des pièces jointes physiques
        foreach ($event->attachments as $attachment) {
            Storage::delete($attachment->path);
            if ($attachment->thumbnail_path) {
                Storage::disk('public')->delete($attachment->thumbnail_path);
            }
        }

        // Détachement des pièces jointes
        $event->attachments()->detach();

        // Suppression de l'événement
        $event->delete();

        return redirect()->route('bulletin-boards.show', $bulletinBoard)
            ->with('success', 'Événement supprimé avec succès.');
    }

    /**
     * Met à jour le statut d'un événement
     */
    public function updateStatus(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $request->validate([
            'status' => 'required|string|in:draft,published,cancelled'
        ]);

        $event->status = $request->status;
        $event->save();

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
            ->with('success', 'Statut de l\'événement mis à jour avec succès.');
    }

    /**
     * Permet à un utilisateur de s'inscrire à un événement
     */
    public function register(BulletinBoard $bulletinBoard, Event $event)
    {
        // Logique d'inscription à implémenter selon vos besoins

        return back()->with('success', 'Vous êtes inscrit à cet événement.');
    }

    /**
     * Permet à un utilisateur de se désinscrire d'un événement
     */
    public function unregister(BulletinBoard $bulletinBoard, Event $event)
    {
        // Logique de désinscription à implémenter selon vos besoins

        return back()->with('success', 'Vous êtes désinscrit de cet événement.');
    }

    /*
     * GESTION DES PIÈCES JOINTES
     */

    /**
     * Affiche la liste des pièces jointes d'un événement
     */
    public function attachmentsIndex(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $attachments = $event->attachments;

        return view('bulletin-boards.events.attachments.index', compact('bulletinBoard', 'event', 'attachments'));
    }

    /**
     * Affiche le formulaire d'ajout de pièce jointe
     */
    public function attachmentsCreate(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        return view('bulletin-boards.events.attachments.create', compact('bulletinBoard', 'event'));
    }

    /**
     * Enregistre une nouvelle pièce jointe
     */
    public function attachmentsStore(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        try {
            $request->validate([
                'name' => 'required|max:100',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov|max:20480', // 20MB max
                'thumbnail' => 'nullable|string',
            ]);

            $file = $request->file('file');
            $path = $file->store('event_attachments');

            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0];

            $attachment = Attachment::create([
                'path' => $path,
                'name' => $request->input('name'),
                'crypt' => md5_file($file),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'size' => $file->getSize(),
                'creator_id' => Auth::id(),
                'mime_type' => $mimeType,
                'type' => 'bulletinboardevent',
            ]);

            if ($request->filled('thumbnail')) {
                $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
                $thumbnailPath = 'thumbnails_event/' . $attachment->id . '.jpg';
                $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

                if ($stored) {
                    $attachment->thumbnail_path = $thumbnailPath;
                    $attachment->save();
                }
            } else {
                // Génération automatique de vignette pour images
                if ($fileType === 'image') {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }
            }

            $event->attachments()->attach($attachment->id, ['created_by' => Auth::id()]);

            return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
                ->with('success', 'Pièce jointe ajoutée avec succès.');
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'ajout de pièce jointe: ' . $e->getMessage());
            return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
                ->with('error', 'Une erreur est survenue lors de l\'ajout de la pièce jointe.');
        }
    }

    /**
     * Génère une vignette pour une image
     */
    private function generateThumbnail($file, $attachmentId)
    {
        $thumbnailPath = 'thumbnails_event/' . $attachmentId . '.jpg';

        try {
            $img = Image::make($file->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));
            return $thumbnailPath;
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération de la vignette: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Affiche une pièce jointe spécifique
     */
    public function attachmentsShow(BulletinBoard $bulletinBoard, Event $event, Attachment $attachment)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cet événement
        if (!$event->attachments->contains($attachment)) {
            return abort(404);
        }

        return view('bulletin-boards.events.attachments.show', compact('bulletinBoard', 'event', 'attachment'));
    }

    /**
     * Supprime une pièce jointe
     */
    public function attachmentsDestroy(BulletinBoard $bulletinBoard, Event $event, Attachment $attachment)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cet événement
        if (!$event->attachments->contains($attachment)) {
            return abort(404);
        }

        // Détacher la pièce jointe de l'événement
        $event->attachments()->detach($attachment->id);

        // Supprimer le fichier physique
        Storage::delete($attachment->path);

        // Supprimer la vignette si elle existe
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        // Supprimer l'enregistrement
        $attachment->delete();

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard, $event])
            ->with('success', 'Pièce jointe supprimée avec succès.');
    }

    /**
     * Prévisualise une pièce jointe
     */
    public function attachmentsPreview(Attachment $attachment)
    {
        $path = storage_path('app/' . $attachment->path);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

    /**
     * Télécharge une pièce jointe
     */
    public function attachmentsDownload(Attachment $attachment)
    {
        $filePath = storage_path('app/' . $attachment->path);

        if (!File::exists($filePath)) {
            abort(404);
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = $attachment->name . '.' . $fileExtension;

        return response()->download($filePath, $fileName);
    }

    /**
     * Retourne la liste des pièces jointes pour les requêtes AJAX
     */
    public function attachmentsList(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $attachments = $event->attachments;

        if (request()->ajax()) {
            return response()->view('bulletin-boards.events.attachments.partials.list',
                compact('bulletinBoard', 'event', 'attachments'));
        }

        return redirect()->route('bulletin-boards.events.attachments.index', [$bulletinBoard, $event]);
    }

    /**
     * Enregistre des pièces jointes via AJAX
     */
    public function attachmentsAjaxStore(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        $request->validate([
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,doc,docx,xls,xlsx|max:20480',
            'name' => 'nullable|string|max:100',
        ]);

        $results = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('event_attachments');

                $mimeType = $file->getMimeType();
                $fileType = explode('/', $mimeType)[0];

                $name = $request->input('name') ?: $file->getClientOriginalName();

                $attachment = Attachment::create([
                    'path' => $path,
                    'name' => $name,
                    'crypt' => md5_file($file),
                    'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                    'size' => $file->getSize(),
                    'creator_id' => Auth::id(),
                    'mime_type' => $mimeType,
                    'type' => 'event',
                ]);

                // Génération de vignette si nécessaire
                if ($fileType === 'image') {
                    $thumbnailPath = $this->generateThumbnail($file, $attachment->id);
                    if ($thumbnailPath) {
                        $attachment->thumbnail_path = $thumbnailPath;
                        $attachment->save();
                    }
                }

                $event->attachments()->attach($attachment->id, ['created_by' => Auth::id()]);

                $results[] = [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'size' => $attachment->size,
                    'type' => $mimeType,
                    'success' => true
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Fichiers téléversés avec succès',
            'files' => $results
        ]);
    }

    /**
     * Supprime une pièce jointe via AJAX
     */
    public function attachmentsAjaxDestroy(BulletinBoard $bulletinBoard, Event $event, Attachment $attachment)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            return abort(404);
        }

        // Vérifier que la pièce jointe est bien liée à cet événement
        if (!$event->attachments->contains($attachment)) {
            return response()->json([
                'success' => false,
                'message' => 'Pièce jointe non trouvée'
            ], 404);
        }

        // Vérifier les autorisations
        if (!Auth::user()->can('delete', $attachment) && !Auth::user()->can('update', $event)) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée'
            ], 403);
        }

        // Détacher la pièce jointe de l'événement
        $event->attachments()->detach($attachment->id);

        // Supprimer le fichier physique
        Storage::delete($attachment->path);

        // Supprimer la vignette si elle existe
        if ($attachment->thumbnail_path) {
            Storage::disk('public')->delete($attachment->thumbnail_path);
        }

        // Supprimer l'enregistrement
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pièce jointe supprimée avec succès'
        ]);
    }
}
