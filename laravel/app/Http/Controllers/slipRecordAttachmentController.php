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

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
            'thumbnail' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('attachments');
        $file = $request->file('file');

        $attachment = Attachment::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'crypt' => md5_file($file),
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'size' => $file->getSize(),
            'creator_id' => auth()->id(),
            'type' => 'transferring',
        ]);

        if ($request->filled('thumbnail')) {
            $thumbnailData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->thumbnail));
            $thumbnailPath = 'thumbnails/' . $attachment->id . '.jpg';

            $stored = Storage::disk('public')->put($thumbnailPath, $thumbnailData);

            if ($stored) {
                $attachment->update(['thumbnail_path' => $thumbnailPath]);
            }
        }
        SlipRecordAttachment::create([
            'slip_record_id' => $request->r_id,
            'attachment_id' => $attachment->id,
        ]);
        return response()->json(['success' => true]);
    }


    public function delete(Slip $slip, SlipRecord $slipRecord, $id)
    {
        $attachment = Attachment::findOrFail($id);
        Storage::delete($attachment->path);
        $attachment->delete();
        return redirect()->back();
//        return view('slips.show', compact('slip', 'slipRecord'));

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

        return view('slips.records.attachments.show', compact('slipRecord', 'attachment'));
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
