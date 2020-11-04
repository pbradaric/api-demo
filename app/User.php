<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public static function boot()
    {
        parent::boot();
        static::creating(function(User $user) {
            if (   ($user->api_token && static::where('api_token', $user->api_token)->count())
                || !$user->api_token
            ) {
                do {
                    $user->api_token = Str::random(60);
                } while (static::where('api_token', $user->api_token)->count());
            }
        });
    }

    /**
     * Relationships
     */
    public function pictures()
    {
        return $this->hasMany(Picture::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function posters()
    {
        return $this->hasMany(Poster::class);
    }
}
