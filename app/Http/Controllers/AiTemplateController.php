<?php

namespace App\Http\Controllers;

use App\Models\AiTemplate;
use App\Services\AI\AiTemplateReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiTemplateController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt', 'md', 'csv', 'odt', 'ods', 'html'];

    public function store(Request $request)
    {
        $request->validate([
            'template_file' => 'required|file',
            'name' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('template_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return redirect()->route('ai-search.resources', ['tab' => 'templates'])
                ->with('error', "Format non autorisé : .{$ext}");
        }

        $fileName = $file->getClientOriginalName();
        $dir = 'ai/templates/' . date('Y/m');
        $path = Storage::disk('local')->putFileAs($dir, $file, Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $ext);

        AiTemplate::create([
            'name' => $request->input('name', pathinfo($fileName, PATHINFO_FILENAME)),
            'category' => $request->input('category'),
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'description' => $request->input('description'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('ai-search.resources', ['tab' => 'templates'])
            ->with('success', 'Template ajouté.');
    }

    public function download(AiTemplate $template)
    {
        $path = storage_path('app/' . $template->file_path);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $template->file_name);
    }

    public function preview(AiTemplate $template)
    {
        try {
            $reader = app(AiTemplateReader::class);
            $text = $reader->read($template->absolute_path);

            return response()->json([
                'success' => true,
                'name' => $template->file_name,
                'content' => mb_substr($text, 0, 50000),
                'chars' => mb_strlen($text),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function destroy(AiTemplate $template)
    {
        Storage::disk('local')->delete($template->file_path);
        $template->delete();

        return redirect()->route('ai-search.resources', ['tab' => 'templates'])
            ->with('success', 'Template supprimé.');
    }
}
