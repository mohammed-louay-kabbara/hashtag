<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class nubdha_view extends Model
{
    protected $fillable = [
        'user_id' , 'nubdha_id'
    ];
    public function nubdha()
    {
        return $this->belongsTo(nubdha::class);
    }
        public function user()
    {
        return $this->belongsTo(User::class);
    }
}
