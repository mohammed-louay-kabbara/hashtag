<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nubdha extends Model
{
    protected $fillable = [
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function stories(){
      return $this->hasMany(story::class);
    }
    public function nubdha_view(){
      return $this->hasMany(nubdha_view::class);
    }   
}
