<?php

namespace App\Http\Controllers;
use App\Models\Attachment;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipRecordAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class slipRecordAttachmentController extends Controller
{
    // Fonction pour l'upload
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:2048', // validation
        ]);

        // Enregistrer le fichier dans le système
        $path = $request->file('file')->store('attachments');
        $attachment = Attachment::create([
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
            'crypt' => md5_file($request->file('file')),
            'crypt_sha512' => hash_file('sha512', $request->file('file')->getRealPath()),
            'size' => $request->file('file')->getSize(),
            'creator_id' => auth()->id(),
            'type' => 'transferring',
        ]);

        // Enregistrer la relation entre SlipRecord et Attachment
        SlipRecordAttachment::create([
            'slip_record_id' => $request->r_id,
            'attachment_id' => $attachment->id,
        ]);

        $slipRecord = slipRecord::findOrFail($request->r_id);
        $slip =  slip::findOrFail($request->s_id);

        return view('transferrings.records.show', compact('slip', 'slipRecord'));
    }

    // Fonction pour la suppression
    public function delete(Slip $slip, SlipRecord $slipRecord, $id)
    {
        $attachment = Attachment::findOrFail($id);

        // Ensure the attachment belongs to the correct slip and record
//        if ($attachment->slipRecord->id !== $record->id || $record->slip_id !== $slip->id) {
//            return abort(404);
//        }

        Storage::delete($attachment->path);
        $attachment->delete();
//        return view('transferrings.records.show', compact('slip', 'slipRecord'));

        return view('transferrings.slips.show', compact('slip', 'record'));


//        return redirect()->route('transferrings.slips.show', [$slip, $record])
//            ->with('success', 'Pièce jointe supprimée avec succès.');
    }



    public function download($id)
    {
        $attachment = SlipRecordAttachment::findOrFail($id);
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



    public function show(Request $request)
    {
        $request->validate([
            'a_id' => 'required|exists:attachments,id',
            'r_id' => 'required|exists:slip_records,id',
        ]);

        $attachment = Attachment::findOrFail($request->a_id);
        $slipRecord = SlipRecord::findOrFail($request->r_id);

        return view('transferrings.records.attachments.show', compact('slipRecord', 'attachment'));
    }


    public function preview($id)
    {
        $attachment = SlipRecordAttachment::findOrFail($id);
        $filePath = storage_path('app/' . $attachment->path);

        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return abort(404);
    }



}
