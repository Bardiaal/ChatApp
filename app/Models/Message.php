<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Message extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'messages';
    public $timestamps = false;
    
    protected $fillable = [
        'chat_id',
        'user_id',
        'user_name',
        'text',
        'date',
        'who_has_to_read_it'
    ];
}