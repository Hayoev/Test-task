<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteFile extends Model
{
    protected $fillable = [
        'note_id',
        'original_name',
        'path',
        'size',
        'mime_type',
    ];

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
