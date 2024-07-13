<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Batch;
use App\Models\documentType;
use App\Models\Author;
use App\Models\User;


class SearchController extends Controller
{
    public function all(Request $request)
    {
        $query = Mail::query();

        if ($request->filled('code')) {
            $query->where('code', 'LIKE', '%' . $request->code . '%');
        }

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->author . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('mail_priority_id')) {
            $query->where('mail_priority_id', $request->mail_priority_id);
        }

        if ($request->filled('mail_type_id')) {
            $query->where('mail_type_id', $request->mail_type_id);
        }

        if ($request->filled('mail_typology_id')) {
            $query->where('mail_typology_id', $request->mail_typology_id);
        }

        if ($request->filled('author_ids')) {
            $authorIds = explode(',', $request->author_ids);
            $query->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('id', $authorIds);
            });
        }

        $mails = $query->with(['priority', 'authors', 'typology', 'type', 'creator', 'updator', 'lastTransaction'])
            ->paginate(15);

        $priorities = MailPriority::all();
        $types = MailType::all();
        $typologies = MailTypology::all();
        $authors = Author::all();

        return view('mails.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }

}
