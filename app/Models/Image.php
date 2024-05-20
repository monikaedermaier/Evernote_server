<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'title', 'note_id', 'todo_id', 'user_id'];

    public function note():BelongsTo {
        return $this->belongsTo(Note::class);
    }

    public function todo():BelongsTo {
        return $this->belongsTo(Todo::class);
    }

    public function user():BelongsTo {
        return $this->belongsTo(User::class);
    }
}
