<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hashtag_story extends Model
{
    protected $fillable = [
        'user_id', 'name_hashtag', 'story_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function story()
    {
        return $this->belongsTo(Story::class, 'story_id');
    }
}
