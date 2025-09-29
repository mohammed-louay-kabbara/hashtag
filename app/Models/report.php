<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class report extends Model
{
    protected $fillable = [
        'user_id', 'report_typeable_type', 'report_typeable_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
