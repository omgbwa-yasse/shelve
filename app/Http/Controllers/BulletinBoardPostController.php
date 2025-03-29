<?php

namespace App\Http\Controllers;

use App\Models\BulletinBoard;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BulletinBoardPostController extends Controller
{



    public function index(BulletinBoard $bulletinBoard)
    {
        $this->authorize('view', $bulletinBoard);

        $posts = $bulletinBoard->posts()
            ->with('creator')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('bulletin-boards.posts.index', compact('bulletinBoard', 'posts'));
    }




    public function create(BulletinBoard $bulletinBoard)
    {
        $this->authorize('createPost', $bulletinBoard);

        return view('bulletin-boards.posts.create', compact('bulletinBoard'));
    }



    public function store(Request $request, BulletinBoard $bulletinBoard)
    {
        $this->authorize('createPost', $bulletinBoard);

        $post = new Post();
        $post->bulletin_board_id = $bulletinBoard->id;
        $post->name = $request->name;
        $post->description = $request->description;
        $post->start_date = $request->start_date;
        $post->end_date = $request->end_date;
        $post->status = $request->status ?? 'draft';
        $post->created_by = Auth::id();
        $post->save();

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('post-attachments');

                $attachment = new \App\Models\Attachment([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'created_by' => Auth::id(),
                ]);
                $attachment->save();

                $post->attachments()->attach($attachment->id, [
                    'created_by' => Auth::id()
                ]);
            }
        }

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id])
            ->with('success', 'Post created successfully.');
    }




    public function show(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('view', $bulletinBoard);

        $post->load(['creator', 'attachments']);

        return view('bulletin-boards.posts.show', compact('bulletinBoard', 'post'));
    }




    public function edit(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('update', $post);

        $post->load('attachments');

        return view('bulletin-boards.posts.edit', compact('bulletinBoard', 'post'));
    }




    public function update(Request $request, BulletinBoard $bulletinBoard, Post $post)
    {

        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('update', $post);

        $post->name = $request->name;
        $post->description = $request->description;
        $post->start_date = $request->start_date;
        $post->end_date = $request->end_date;
        $post->status = $request->status;
        $post->save();


        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('post-attachments');

                $attachment = new \App\Models\Attachment([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'created_by' => Auth::id(),
                ]);
                $attachment->save();

                $post->attachments()->attach($attachment->id, [
                    'created_by' => Auth::id()
                ]);
            }
        }

        if ($request->has('remove_attachments')) {
            foreach ($request->remove_attachments as $attachmentId) {
                $attachment = \App\Models\Attachment::find($attachmentId);

                if ($attachment) {
                    $postAttachment = $post->attachments()
                        ->where('attachment_id', $attachmentId)
                        ->first();

                    if ($postAttachment) {
                        $post->attachments()->detach($attachmentId);

                        if ($attachment->events()->count() == 0 && $attachment->posts()->count() == 0) {
                            Storage::delete($attachment->file_path);
                            $attachment->delete();
                        }
                    }
                }
            }
        }

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id])
            ->with('success', 'Post updated successfully.');
    }




    public function destroy(BulletinBoard $bulletinBoard, Post $post)
    {
        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('bulletin-boards.posts.index', $bulletinBoard->id)
            ->with('success', 'Post deleted successfully.');
    }




    public function changeStatus(Request $request, BulletinBoard $bulletinBoard, Post $post)
    {

        if ($post->bulletin_board_id !== $bulletinBoard->id) {
            abort(404);
        }

        $this->authorize('update', $post);

        $request->validate([
            'status' => 'required|in:draft,published,cancelled'
        ]);

        $post->status = $request->status;
        $post->save();

        return redirect()->route('bulletin-boards.posts.show', [$bulletinBoard->id, $post->id])
            ->with('success', 'Post status changed successfully.');
    }
}
