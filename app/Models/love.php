<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class love extends Model
{
    protected $fillable = [
        'user_id', 'hashtag_id' 
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hashtag()
    {
        return $this->belongsTo(hashtag::class);
    }
}
