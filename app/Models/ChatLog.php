<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    use HasFactory;

    protected $table = 'chat_logs';

    protected $fillable = [
        'user_id',
        'session_id',
        'role',
        'message',
        'sources',
    ];

    protected $casts = [
        'sources' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}