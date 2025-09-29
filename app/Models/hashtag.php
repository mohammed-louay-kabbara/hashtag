<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hashtag extends Model
{
    protected $fillable = [
        'user_id', 'description'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function love(){
      return $this->hasMany(love::class);
    }   
}
