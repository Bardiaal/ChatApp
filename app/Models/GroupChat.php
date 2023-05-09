<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class GroupChat extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'group_chats';
    public $timestamps = false;
    
    protected $fillable = [
        'chat_name',
        'chat_photo',
        'date',
    ];
}