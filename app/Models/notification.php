<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'body', 'read_at', 'sender_id'
    ];

    public function user(){
      return $this->belongsTo(User::class);
    }
    
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
