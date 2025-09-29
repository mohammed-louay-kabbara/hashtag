<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class story extends Model
{
    protected $fillable = [
        'nubdha_id', 'media', 'type', 'caption'
    ];
    
    public function nubdha()
    {
        return $this->belongsTo(nubdha::class);
    }
     public function hashtags()
    {
        return $this->hasMany(HashtagStory::class, 'story_id');
    }

    // Accessor to get full URL for media
    public function getMediaUrlAttribute()
    {
        return $this->media ? Storage::disk('public')->url($this->media) : null;
    }

}
