<?php

namespace App\Http\Controllers;

use App\Models\hashtag;
use App\Models\save;
use App\Models\love;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HashtagController extends Controller
{

    public function index()
    {
        $meId = auth()->id(); // أو null إن لم يسجل الدخول
            // 1. جلب الهاشتاغات + loves_count (اختياري)
            $result = Hashtag::with('user')
                ->withCount('loves')
                ->orderByDesc('created_at')
                ->get();
            // 2. مصفوفة الـ ids
            $ids = $result->pluck('id')->all();

            $likedIds = [];
            $savedIds = [];

            if ($meId && !empty($ids)) {
                // جلب كل الهاشتاغات التي أحبها المستخدم مرة واحدة
                $likedIds = Love::where('user_id', $meId)
                    ->whereIn('hashtag_id', $ids)
                    ->pluck('hashtag_id')
                    ->toArray();

                // جلب كل الـ saves للموديل Hashtag مرة واحدة
                $savedIds = Save::where('user_id', $meId)
                    ->where('saveable_type', Hashtag::class) // تأكد أن هذا ما تحفظه في قاعدة البيانات
                    ->whereIn('saveable_id', $ids)
                    ->pluck('saveable_id')
                    ->toArray();
            }

            // 3. ربط النتائج
            $hashtags = $result->map(function ($h) use ($likedIds, $savedIds) {
                $h->isLiked = in_array($h->id, $likedIds, true);   // boolean
                $h->isSaved = in_array($h->id, $savedIds, true);   // boolean
                // إن أردت حذف العلاقات الثقيلة قبل الإرسال:
                // unset($h->loves);
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
        return response()->json(['تم الحذف بنجاح'], 200);
        
    }
}
