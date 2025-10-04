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
    // إذا عندك موديل Love:
    public function loves()
    {
        return $this->hasMany(Love::class, 'hashtag_id');
    }

    // أو: لو تفضّل علاقة many-to-many للمستخدمين:
    public function likers()
    {
        return $this->belongsToMany(User::class, 'loves', 'hashtag_id', 'user_id')->withTimestamps();
    }
}
