<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    // GET /api/notes
    public function index(Request $request)
    {
        $notes = Note::with('files')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $notes;
    }

    // POST /api/notes
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string'],
            'files.*'    => ['file'],
        ]);

        $note = Note::create([
            'user_id' => $request->user()->id,
            'title'   => $data['title'],
            'body'    => $data['body'],
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('notes', 'public');

                NoteFile::create([
                    'note_id'       => $note->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'size'          => $file->getSize(),
                    'mime_type'     => $file->getClientMimeType(),
                ]);
            }
        }

        return $note->load('files');
    }

    // GET /api/notes/{id}
    public function show(Request $request, $id)
    {
        $note = Note::with('files')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $note;
    }

    // PUT/PATCH /api/notes/{id}
    public function update(Request $request, $id)
    {
        $note = Note::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body'  => ['sometimes', 'required', 'string'],
        ]);

        $note->update($data);

        return $note->fresh('files');
    }

    // DELETE /api/notes/{id}
    public function destroy(Request $request, $id)
    {
        $note = Note::with('files')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // удаляем файлы с диска
        foreach ($note->files as $file) {
            Storage::disk('public')->delete($file->path);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted']);
    }
}
