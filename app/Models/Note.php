<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'user_id', 'collection_id'];

    public function images() : HasMany {
        return $this->hasMany(Image::class);
    }

    public function user() : BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function collection() : BelongsTo{
        return $this->belongsTo(Collection::class);
    }

    public function todos() : HasMany{
        return $this->hasMany(Todo::class);
    }

    // Zwischentabelle note_tag
    public function tags() : BelongsToMany{
        return $this->belongsToMany(Tag::class);
    }

}
