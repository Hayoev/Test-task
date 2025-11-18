<?php

namespace App\Http\Controllers;

use App\Models\NoteFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteFileController extends Controller
{
    // DELETE /api/files/{id}
    public function destroy(Request $request, $id)
    {
        $file = NoteFile::with('note')->findOrFail($id);

        if ($file->note->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
