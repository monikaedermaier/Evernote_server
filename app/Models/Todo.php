<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'dueDate', 'open', 'note_id'];

    public function images() : HasMany {
        return $this->hasMany(Image::class);
    }

    public function note() : BelongsTo {
        return $this->belongsTo(Note::class);
    }

    // Zwischentabelle todo_user
    // withTimestamps() = an additional column in table todo_user
    public function users() : BelongsToMany{
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    // Zwischentabelle tag_todo
    // withTimestamps() = an additional column in table tag_todo
    public function tags() : BelongsToMany{
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
