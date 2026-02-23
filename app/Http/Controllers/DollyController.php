<?php

namespace App\Http\Controllers;
use App\Models\Communication;
use App\Models\Container;
use App\Models\DollyCommunication;
use Illuminate\Support\Facades\Auth;
use App\Models\Mail;
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\SlipRecord;
use Illuminate\Http\Request;
use App\Models\Dolly;


class DollyController extends Controller
{
    public function index()
    {
        $dollies = Dolly::where('owner_organisation_id', Auth::user()->current_organisation_id)
            ->paginate(25);
        return view('dollies.index', compact('dollies'));
    }





    public function create()
    {
        $categories = Dolly::categories();
        return view('dollies.create', compact('categories'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|exists:dollies,category',
        ]);
        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::user()->getAuthIdentifier;
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;

        $dolly = Dolly::create($validatedData);
        return redirect()->route('dolly.index')->with('success', 'Dolly created successfully.');
    }





    public function show(Dolly $dolly)
    {
        $records = RecordPhysical::all();
        $mails = Mail::all();
        $communications = Communication::all();
        $rooms = Room::all();
        $containers = Container::all();
        $shelves = Shelf::all();
        $slip_records = SlipRecord::all();

        // Nouvelles entités numériques
        $digitalFolders = RecordDigitalFolder::where('organisation_id', Auth::user()->current_organisation_id)->get();
        $digitalDocuments = RecordDigitalDocument::where('organisation_id', Auth::user()->current_organisation_id)->get();

        $dolly->load('creator','ownerOrganisation');

        return view('dollies.show', compact(
            'dolly', 'records', 'mails', 'communications', 'rooms',
            'containers', 'shelves', 'slip_records',
            'digitalFolders', 'digitalDocuments'
        ));
    }





    public function edit(Dolly $dolly)
    {
        $categories = Dolly::all()->pluck('category');
        return view('dollies.edit', compact('dolly', 'categories'));
    }






    public function update(Request $request, Dolly $dolly)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'category' => 'required|exists:dollies,category',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::user()->getAuthIdentifier;
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;

        $dolly->update($validatedData);
        return redirect()->route('dolly.index')->with('success', 'Dolly updated successfully.');
    }







    public function destroy(Dolly $dolly)
    {

        if ($dolly->mails()->exists()
            || $dolly->records()->exists()
            || $dolly->communications()->exists()
            || $dolly->slips()->exists()
            || $dolly->slipRecords()->exists()
            || $dolly->buildings()->exists()
            || $dolly->rooms()->exists()
            || $dolly->shelve()->exists()
            || $dolly->digitalFolders()->exists()
            || $dolly->digitalDocuments()->exists()
            || $dolly->artifacts()->exists()
        ) {
           return redirect()->route('dolly.index')->with('error', 'Cannot delete Dolly because it has related records in other tables.');
        }
        $dolly->delete();
        return redirect()->route('dolly.index')->with('success', 'Dolly deleted successfully.');
    }






    public function apiList(Request $request)
    {
        $query = Dolly::where('category', 'mail')
            ->where('owner_organisation_id', Auth::user()->current_organisation_id);

        if ($request->has('q') && $request->q) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $dollies = $query->get();
        return response()->json($dollies);
    }




    public function apiCreate(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validatedData['is_public'] = false;
        $validatedData['created_by'] = Auth::id();
        $validatedData['owner_organisation_id'] = Auth::user()->current_organisation_id;
        $validatedData['category'] = 'mail';

        $dolly = Dolly::create($validatedData);

        return response()->json($dolly);
    }


    // ==================== RECORDS ====================

    public function addRecord(Request $request, Dolly $dolly)
    {
        $request->validate([
            'record_id' => 'required|exists:record_physicals,id'
        ]);

        $dolly->records()->syncWithoutDetaching($request->record_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Archive ajoutée au chariot');
    }

    public function removeRecord(Dolly $dolly, RecordPhysical $record)
    {
        $dolly->records()->detach($record->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Archive retirée du chariot');
    }

    // ==================== MAILS ====================

    public function addMail(Request $request, Dolly $dolly)
    {
        $request->validate([
            'mail_id' => 'required|exists:mails,id'
        ]);

        $dolly->mails()->syncWithoutDetaching($request->mail_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Courrier ajouté au chariot');
    }

    public function removeMail(Dolly $dolly, Mail $mail)
    {
        $dolly->mails()->detach($mail->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Courrier retiré du chariot');
    }

    // ==================== COMMUNICATIONS ====================

    public function addCommunication(Request $request, Dolly $dolly)
    {
        $request->validate([
            'communication_id' => 'required|exists:communications,id'
        ]);

        $dolly->communications()->syncWithoutDetaching($request->communication_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Communication ajoutée au chariot');
    }

    public function removeCommunication(Dolly $dolly, Communication $communication)
    {
        $dolly->communications()->detach($communication->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Communication retirée du chariot');
    }

    // ==================== ROOMS ====================

    public function addRoom(Request $request, Dolly $dolly)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id'
        ]);

        $dolly->rooms()->syncWithoutDetaching($request->room_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Salle ajoutée au chariot');
    }

    public function removeRoom(Dolly $dolly, Room $room)
    {
        $dolly->rooms()->detach($room->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Salle retirée du chariot');
    }

    // ==================== CONTAINERS ====================

    public function addContainer(Request $request, Dolly $dolly)
    {
        $request->validate([
            'container_id' => 'required|exists:containers,id'
        ]);

        $dolly->containers()->syncWithoutDetaching($request->container_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Boîte ajoutée au chariot');
    }

    public function removeContainer(Dolly $dolly, Container $container)
    {
        $dolly->containers()->detach($container->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Boîte retirée du chariot');
    }

    // ==================== SHELVES ====================

    public function addShelve(Request $request, Dolly $dolly)
    {
        $request->validate([
            'shelve_id' => 'required|exists:shelves,id'
        ]);

        $dolly->shelve()->syncWithoutDetaching($request->shelve_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Étagère ajoutée au chariot');
    }

    public function removeShelve(Dolly $dolly, Shelf $shelve)
    {
        $dolly->shelve()->detach($shelve->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Étagère retirée du chariot');
    }

    // ==================== SLIP RECORDS ====================

    public function addSlipRecord(Request $request, Dolly $dolly)
    {
        $request->validate([
            'slip_record_id' => 'required|exists:slip_records,id'
        ]);

        $dolly->slipRecords()->syncWithoutDetaching($request->slip_record_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Description de versement ajoutée au chariot');
    }

    public function removeSlipRecord(Dolly $dolly, SlipRecord $slipRecord)
    {
        $dolly->slipRecords()->detach($slipRecord->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Description de versement retirée du chariot');
    }

    // ==================== DIGITAL FOLDERS ====================

    public function addDigitalFolder(Request $request, Dolly $dolly)
    {
        $request->validate([
            'folder_id' => 'required|exists:record_digital_folders,id'
        ]);

        $dolly->digitalFolders()->syncWithoutDetaching($request->folder_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Dossier numérique ajouté au chariot');
    }

    public function removeDigitalFolder(Dolly $dolly, RecordDigitalFolder $folder)
    {
        $dolly->digitalFolders()->detach($folder->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Dossier numérique retiré du chariot');
    }

    // ==================== DIGITAL DOCUMENTS ====================

    public function addDigitalDocument(Request $request, Dolly $dolly)
    {
        $request->validate([
            'document_id' => 'required|exists:record_digital_documents,id'
        ]);

        $dolly->digitalDocuments()->syncWithoutDetaching($request->document_id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Document numérique ajouté au chariot');
    }

    public function removeDigitalDocument(Dolly $dolly, RecordDigitalDocument $document)
    {
        $dolly->digitalDocuments()->detach($document->id);

        return redirect()->route('dolly.show', $dolly)
            ->with('success', 'Document numérique retiré du chariot');
    }

}

