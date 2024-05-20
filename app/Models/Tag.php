<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'user_id'];

    public function user() : BelongsTo{
        return $this->belongsTo(User::class);
    }

    // Zwischentabelle note_tag
    public function notes() : BelongsToMany{
        return $this->belongsToMany(Note::class)->withTimestamps();
    }

    // Zwischentabelle tag_todo
    public function todos() : BelongsToMany{
        return $this->belongsToMany(Todo::class)->withTimestamps();
    }
}
