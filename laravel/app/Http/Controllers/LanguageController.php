<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        if (! in_array($locale, ['en', 'fr'])) {
            abort(400);
        }

        session()->put('locale', $locale);
        App::setLocale($locale);

        return redirect()->back();
    }

    public function index()
    {
        $languages = Language::all();

        return view('languages.index', compact('languages'));
    }


    public function create()
    {
        return view('languages.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:2|unique:languages',
            'name' => 'required|string|max:50',
        ]);

        Language::create($request->all());

        return redirect()->route('languages.index')
            ->with('success', 'Language created successfully.');
    }



    public function show(Language $language)
    {
        return view('languages.show', compact('language'));
    }



    public function edit(Language $language)
    {
        return view('languages.edit', compact('language'));
    }



    public function update(Request $request, Language $language)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:languages,code,' . $language->id,
            'name' => 'required|string|max:50',
        ]);

        $language->update($request->all());

        return redirect()->route('languages.index')
            ->with('success', 'Language updated successfully');
    }



    public function destroy(Language $language)
    {
        $language->delete();

        return redirect()->route('languages.index')
            ->with('success', 'Language deleted successfully');
    }
}
