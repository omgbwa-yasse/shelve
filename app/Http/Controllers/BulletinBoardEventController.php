<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BulletinBoardEventController extends Controller
{

    public function index(BulletinBoard $bulletinBoard)
    {
        $this->authorize('view', $bulletinBoard);

        $events = $bulletinBoard->events()
            ->with('creator')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('bulletin-boards.events.index', compact('bulletinBoard', 'events'));
    }


    public function create(BulletinBoard $bulletinBoard)
    {
        $this->authorize('createEvent', $bulletinBoard);

        return view('bulletin-boards.events.create', compact('bulletinBoard'));
    }


    public function store(Request $request, BulletinBoard $bulletinBoard)
    {

        $this->authorize('createEvent', $bulletinBoard);

        $event = new Event();
        $event->bulletin_board_id = $bulletinBoard->id;
        $event->name = $request->name;
        $event->description = $request->description;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->location = $request->location;
        $event->status = $request->status ?? 'draft';
        $event->created_by = Auth::id();
        $event->save();


        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('event-attachments');


                $attachment = new \App\Models\Attachment([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'created_by' => Auth::id(),
                ]);
                $attachment->save();

                $event->attachments()->attach($attachment->id, [
                    'created_by' => Auth::id()
                ]);
            }
        }

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id])
            ->with('success', 'Event created successfully.');
    }


    public function show(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('view', $bulletinBoard);

        $event->load(['creator', 'attachments']);

        return view('bulletin-boards.events.show', compact('bulletinBoard', 'event'));
    }


    public function edit(BulletinBoard $bulletinBoard, Event $event)
    {

        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }


        $this->authorize('update', $event);

        $event->load('attachments');

        return view('bulletin-boards.events.edit', compact('bulletinBoard', 'event'));
    }


    public function update(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('update', $event);

        $event->name = $request->name;
        $event->description = $request->description;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->location = $request->location;
        $event->status = $request->status;
        $event->save();

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('event-attachments');

                $attachment = new \App\Models\Attachment([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'created_by' => Auth::id(),
                ]);
                $attachment->save();

                $event->attachments()->attach($attachment->id, [
                    'created_by' => Auth::id()
                ]);
            }
        }


        if ($request->has('remove_attachments')) {
            foreach ($request->remove_attachments as $attachmentId) {
                $attachment = \App\Models\Attachment::find($attachmentId);

                if ($attachment) {

                    $eventAttachment = $event->attachments()
                        ->where('attachment_id', $attachmentId)
                        ->first();

                    if ($eventAttachment) {
                        $event->attachments()->detach($attachmentId);

                        // Delete file if not used elsewhere
                        if ($attachment->events()->count() == 0 && $attachment->posts()->count() == 0) {
                            Storage::delete($attachment->file_path);
                            $attachment->delete();
                        }
                    }
                }
            }
        }

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id])
            ->with('success', 'Event updated successfully.');
    }



    public function destroy(BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('bulletin-boards.events.index', $bulletinBoard->id)
            ->with('success', 'Event deleted successfully.');
    }



    public function changeStatus(Request $request, BulletinBoard $bulletinBoard, Event $event)
    {
        if ($event->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('update', $event);

        $request->validate([
            'status' => 'required|in:draft,published,cancelled'
        ]);

        $event->status = $request->status;
        $event->save();

        return redirect()->route('bulletin-boards.events.show', [$bulletinBoard->id, $event->id])
            ->with('success', 'Event status changed successfully.');
    }
}
