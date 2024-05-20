<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dateOfCreation', 'open'];

    public function scopePublic($query){
        return $query->where('public', '=', true);
    }

    public function notes() : HasMany {
        return $this->hasMany(Note::class);
    }

    // Zwischentabelle collection_user
    // writePersmission = an additional column in table collection_user
    public function users() : BelongsToMany{
        return $this->belongsToMany(User::class)->withPivot('writePermission');
    }
}
