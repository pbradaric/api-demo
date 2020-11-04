<?php

namespace App\Classes;


use App\User;

class PictureHelper
{
    static function getUserPictureStoragePath(User $user)
    {
        return "public/pictures/{$user->id}";
    }
}
