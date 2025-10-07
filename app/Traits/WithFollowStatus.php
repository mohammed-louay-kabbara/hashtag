<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WithFollowStatus
{
    public static function withFollowStatus($query = null)
    {
        $meId = Auth::id();
        if (!$meId) return $query ?? static::query();

        $query = $query ?? static::query();

        // جلب جميع المستخدمين الذين يتابعهم المستخدم الحالي
        $followedIds = DB::table('follows')
            ->where('follower_id', $meId)
            ->pluck('followed_id')
            ->toArray();

        // دمج الحالة مع كل مستخدم
        return $query->get()->map(function ($user) use ($followedIds) {
            $user->is_followed = in_array($user->id, $followedIds);
            return $user;
        });
    }
}
