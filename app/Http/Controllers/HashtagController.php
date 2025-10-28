<?php

namespace App\Http\Controllers;

use App\Models\hashtag;
use App\Models\save;
use App\Models\love;
use App\Models\follower;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HashtagController extends Controller
{

    public function index()
{
    $meId = auth()->id();

    // جلب الهاشتاغات الأساسية مع علاقاتها وعدد الإعجابات
    $result = Hashtag::with('user')
        ->withCount('loves')
        ->when($meId, function ($query) use ($meId) {
            // في حال كان المستخدم مسجل دخول
            $followedIds = Follower::where('user_id', $meId)->pluck('followed_id');

            // نضيف ترتيبًا يعتمد على المتابعة + الإعجابات + الزمن
            $query->orderByRaw("
                (CASE WHEN user_id IN (" . $followedIds->implode(',') . ") THEN 2 ELSE 0 END)
                + (loves_count * 0.7)
                + (CASE 
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 3
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 72 THEN 1
                    ELSE 0
                  END)
                DESC
            ");
        }, function ($query) {
            // إذا لم يكن مسجل دخول: ترتيب حسب الإعجابات والزمن فقط
            $query->orderByRaw("
                (loves_count * 0.7)
                + (CASE 
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 3
                    WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 72 THEN 1
                    ELSE 0
                  END)
                DESC
            ");
        })
        ->get();

    $ids = $result->pluck('id')->all();

    // 🔹 المتغيرات لتحديد الإعجاب والحفظ
    $likedIds = [];
    $savedIds = [];

    if ($meId && !empty($ids)) {
        // جلب IDs الهاشتاغات التي أحبها المستخدم
        $likedIds = Love::where('user_id', $meId)
            ->whereIn('hashtag_id', $ids)
            ->pluck('hashtag_id')
            ->toArray();

        // جلب IDs الهاشتاغات المحفوظة
        $savedIds = Save::where('user_id', $meId)
            ->where('saveable_type', 'hashtag')
            ->whereIn('saveable_id', $ids)
            ->pluck('saveable_id')
            ->toArray();
    }

    // 🔹 إرفاق حالات الإعجاب والحفظ بالنتيجة
    $hashtags = $result->map(function ($h) use ($likedIds, $savedIds) {
        $h->isLiked = in_array($h->id, $likedIds, true);
        $h->isSaved = in_array($h->id, $savedIds, true);
        return $h;
    });

    return response()->json($hashtags, 200);
}


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required', 
        ]);
        hashtag::create([
            'user_id' => Auth::id(),
            'description' => $request->description
        ]);
        return response()->json(['تم إضافة الهاشتاغ بنجاح'], 200);
    }


    public function show(hashtag $hashtag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(hashtag $hashtag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required', 
        ]);
        hashtag::where('id',$id)->update([
            'description' => $request->description
        ]);
        return response()->json(['تم التعديل بنجاح'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        hashtag::where('id',$id)->delete();
        save::where('saveable_type','hashtag')->where('saveable_id',$id)->delete();
        return response()->json(['تم الحذف بنجاح'], 200);
        
    }
}
