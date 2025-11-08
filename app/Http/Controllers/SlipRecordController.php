<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Container;
use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipRecordContainer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SlipRecordController extends Controller
{
    public function create(Slip $slip)
    {
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $users = User::all();
        $levels = RecordLevel::all();
        return view('slips.records.create', compact('slip','levels', 'supports', 'activities', 'users'));
    }




    public function store(Request $request, Slip $slip)
    {

        $dateFormat = $this->getDateFormat($request->date_start, $request->date_end);
        if (strlen($dateFormat) > 1) {
            return back()->withErrors(['date_format' => 'The date format must not be greater than 1 character.'])->withInput();
        }

        $request->merge(['date_format' => $dateFormat]);
        $request->merge(['creator_id' => auth()->id()]);
        $request->merge(['slip_id' => $slip->id]);

        $request->validate([
            'slip_id' => 'required|exists:slips,id',
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_format' => 'required|string|max:1',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable|string',
            'level_id' => 'required|exists:record_levels,id',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|string|max:100',
            'support_id' => 'required|exists:record_supports,id',
            'activity_id' => 'required|exists:activities,id',
            'container_id' => 'nullable|exists:containers,id',
            'creator_id' => 'required|exists:users,id',
        ]);

        $slipRecordData = [
            'slip_id' => $request->input('slip_id'),
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'date_format' => $request->input('date_format'),
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
            'date_exact' => $request->input('date_exact'),
            'content' => $request->input('content'),
            'level_id' => $request->input('level_id'),
            'width' => $request->input('width'),
            'width_description' => $request->input('width_description'),
            'support_id' => $request->input('support_id'),
            'activity_id' => $request->input('activity_id'),
            'creator_id' => $request->input('creator_id'),
        ];

        $slipRecord = SlipRecord::create($slipRecordData);

        // Attacher le container via la relation many-to-many si fourni
        if ($request->filled('container_id')) {
            $slipRecord->containers()->attach($request->input('container_id'), [
                'creator_id' => Auth::id(),
                'description' => $request->input('name') // ou une autre description appropriée
            ]);
        }

        // Traitement des mots-clés
        if ($request->filled('keywords')) {
            $keywords = \App\Models\Keyword::processKeywordsString($request->keywords);
            $slipRecord->keywords()->attach($keywords->pluck('id'));
        }

        $slip = $slipRecord->slip;
        return view('slips.show', compact('slip'));
    }




    private function getDateFormat($dateStart, $dateEnd)
    {
        $start = new DateTime($dateStart);
        $end = new DateTime($dateEnd);

        if ($start->format('Y') !== $end->format('Y')) {
            return 'Y';
        } elseif ($start->format('m') !== $end->format('m')) {
            return 'M';
        } elseif ($start->format('d') !== $end->format('d')) {
            return 'D';
        }
        return 'D';
    }





    public function show(INT $id, INT $slipRecordId)
    {
        $slipRecord = SlipRecord::findOrFail($slipRecordId);
        $slip = Slip::findOrFail($id);

        // Charger les relations nécessaires pour l'affichage
        $slipRecord->load(['containers.shelf', 'containers.status']);

        return view('slips.records.show', compact('slip', 'slipRecord'));
    }



    public function edit(INT $slipId, INT $id)
    {
        $slipRecord = SlipRecord::findOrFail($id);
        $slip = Slip::findOrFail($slipId);
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $users = User::all();
        $levels = RecordLevel::all();
        return view('slips.records.edit', compact('slip', 'levels','slipRecord', 'supports', 'activities', 'containers', 'users'));
    }




    public function update(Request $request, INT $slip_id, INT $record_id)
    {
        $dateFormat = $this->getDateFormat($request->date_start, $request->date_end);
        if (strlen($dateFormat) > 1) {
            return back()->withErrors(['date_format' => 'The date format must not be greater than 1 character.'])->withInput();
        }

        $request->merge(['date_format' => $dateFormat]);
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required',
            'date_format' => 'required|max:1',
            'date_start' => 'nullable|max:10',
            'date_end' => 'nullable|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable',
            'level_id' => 'required',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|max:100',
            'support_id' => 'required|exists:record_supports,id',
            'activity_id' => 'required|exists:activities,id',
            'container_ids' => 'nullable|array',
            'container_ids.*' => 'exists:containers,id',
        ]);

        $slipRecord = slipRecordPhysical::findOrFail($record_id);
        $slipRecord->update($request->except(['container_ids']));

        // Gestion des contenants via la table pivot
        if ($request->has('container_ids') && !empty($request->container_ids)) {
            // Synchroniser les contenants (ajouter les nouveaux, supprimer ceux non sélectionnés)
            $containersData = [];
            foreach ($request->container_ids as $containerId) {
                $containersData[$containerId] = [
                    'creator_id' => Auth::id(),
                    'description' => "Association via édition SlipRecord - " . now()->format('Y-m-d H:i'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $slipRecord->containers()->sync($containersData);
        } else {
            // Aucun contenant sélectionné, supprimer toutes les associations
            $slipRecord->containers()->detach();
        }

        // Traitement des mots-clés
        if ($request->filled('keywords')) {
            $keywords = \App\Models\Keyword::processKeywordsString($request->keywords);
            $slipRecord->keywords()->sync($keywords->pluck('id'));
        } else {
            $slipRecord->keywords()->detach();
        }

        $slip = $slipRecord->slip;
        return view('slips.records.show', compact('slip','slipRecord' ));
    }



    public function destroy(int $slip_id, int $slipRecord_id)
    {
        $slipRecord = SlipRecord::where(['slip_id' => $slip_id, 'id' => $slipRecord_id])->firstOrFail();
        $slip = $slipRecord->slip;

        // Vérifier s'il y a des attachements associés
        $attachmentsCount = $slipRecord->attachments()->count();

        if ($attachmentsCount > 0) {
            // Supprimer d'abord les relations dans la table pivot slip_record_attachments
            $slipRecord->attachments()->detach();

            // Log de l'action pour traçabilité
            Log::info("SlipRecord supprimé avec {$attachmentsCount} attachement(s)", [
                'slip_record_id' => $slipRecord_id,
                'slip_id' => $slip_id,
                'user_id' => Auth::id(),
                'attachments_removed' => $attachmentsCount
            ]);
        }

        // Supprimer le SlipRecord
        $slipRecord->delete();

        return view('slips.show', compact('slip'));
    }


}




