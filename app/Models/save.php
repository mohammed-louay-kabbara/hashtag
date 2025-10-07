<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class save extends Model
{
    protected $fillable = [
        'user_id', 'saveable_type', 'saveable_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function saveable()
    {
        return $this->morphTo();
    }
}
