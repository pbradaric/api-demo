<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    public static function boot()
    {
        parent::boot();
        static::deleting(function(Album $album) {
            $album->pictures()->detach();
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pictures()
    {
        return $this->belongsToMany(Picture::class, 'album_pictures');
    }
}
