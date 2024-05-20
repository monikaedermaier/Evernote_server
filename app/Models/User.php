<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function images() : HasMany {
        return $this->hasMany(Image::class);
    }

    public function notes() : HasMany {
        return $this->hasMany(Note::class);
    }

    public function tags() : HasMany {
        return $this->hasMany(Tag::class);
    }

    // Zwischentabelle todo_user
    // withTimestamps() = an additional column in table todo_user
    public function todos() : BelongsToMany{
        return $this->belongsToMany(Todo::class)->withTimestamps();
    }

    // Zwischentabelle collection_user
    // writePersmission = an additional column in table collection_user
    public function collections() : BelongsToMany{
        return $this->belongsToMany(Collection::class);
    }

    // Key für JWT zurückgeben
    public function getJWTIdentifier(){
        return $this->getKey();
    }

    // Benutzerspezifische Daten zurückgeben
    public function getJWTCustomClaims(){
        return ['user'=>['id'=>$this->id]];
    }
}
