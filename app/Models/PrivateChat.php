<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class PrivateChat extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'private_chats';
    public $timestamps = false;
    
    protected $fillable = [
        'user_1',
        'user_2',
        'username_1',
        'username_2',
        'params',
        'date',
    ];
}
