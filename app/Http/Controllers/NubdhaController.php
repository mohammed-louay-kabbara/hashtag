<?php

namespace App\Http\Controllers;

use App\Models\nubdha;
use Illuminate\Support\Facades\Auth;
use App\Models\story;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Traits\WithFollowStatus;
use App\Models\User;
use App\Models\Save;


class NubdhaController extends Controller
{
public function index()
{
    $meId = auth()->id();

    // جلب النبضات مع عدد الإعجابات والعلاقات
    $result = Nidha::with(['user'])
        ->withCount('loves')
        ->when($meId, function ($query) use ($meId) {
            // IDs المستخدمين الذين يتابعهم
            $followedIds = Follower::where('user_id', $meId)
                ->pluck('followed_id');

            // خوارزمية الترتيب المحسّنة
            $query->orderByRaw("
                (CASE WHEN user_id IN (" . ($followedIds->isEmpty() ? 0 : $followedIds->implode(',')) . ") THEN 2 ELSE 0 END)
                + (loves_count * 0.7)
                + (
                    CASE
                        WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 5   -- وزن أعلى للأحدث
                        WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 72 THEN 2
                        ELSE 0
                    END
                )
                + (1 / GREATEST(TIMESTAMPDIFF(MINUTE, created_at, NOW()), 1))   -- أولوية قوية للأحدث
                DESC
            ");
        }, function ($query) {
            // إذا لم يكن مسجلاً دخول
            $query->orderByRaw("
                (loves_count * 0.7)
                + (
                    CASE
                        WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 24 THEN 5
                        WHEN TIMESTAMPDIFF(HOUR, created_at, NOW()) <= 72 THEN 2
                        ELSE 0
                    END
                )
                + (1 / GREATEST(TIMESTAMPDIFF(MINUTE, created_at, NOW()), 1))
                DESC
            ");
        })
        ->get();

    $ids = $result->pluck('id')->all();

    // تعريف likedId و savedIds
    $likedIds = [];
    $savedIds = [];

    if ($meId && !empty($ids)) {

        $likedIds = Love::where('user_id', $meId)
            ->whereIn('nidha_id', $ids)
            ->pluck('nidha_id')
            ->toArray();

        $savedIds = Save::where('user_id', $meId)
            ->where('saveable_type', 'nidha')
            ->whereIn('saveable_id', $ids)
            ->pluck('saveable_id')
            ->toArray();
    }

    // إرفاق حالات الإعجاب والحفظ
    $nidhas = $result->map(function ($n) use ($likedIds, $savedIds) {
        $n->isLiked = in_array($n->id, $likedIds, true);
        $n->isSaved = in_array($n->id, $savedIds, true);
        return $n;
    });

    return response()->json($nidhas, 200);
}

    public function create()
    {
        
    }

    public function showPublic($id)
    {
        $nabza = Nabza::with(['user', 'stories'])->find($id);

        if (!$nabza) {
            abort(404);
        }

        // ✅ إذا لم يكن التطبيق مثبّت، أظهر صفحة HTML بسيطة
        return view('public', ['nabza' => $nabza]);
    }


    public function store(Request $request)
    {
        $request->validate([
        'media' => 'required|array|min:1',
    ]);

    // نتحقق أولًا أن الملفات موجودة
    if (!$request->hasFile('media')) {
        return response()->json(['message' => 'لم يتم اختيار أي وسائط.'], 422);
    }

    DB::beginTransaction(); // نبدأ معاملة قاعدة بيانات

    try {
        // إنشاء النبذة فقط بعد التأكد من الملفات
        $nubdha = nubdha::create([
            'user_id' => Auth::id(),
        ]);

        foreach ($request->file('media') as $index => $file) {
            $path = $file->store('stories', 'public');
            $type = strpos($file->getMimeType(), 'video') !== false ? 'video' : 'image';
            story::create([
                'nubdha_id' => $nubdha->id,
                'media' => $path,
                'type' => $type,
                'caption' => $request->captions[$index] ?? null
            ]);
        }

        DB::commit(); // تم كل شيء بنجاح
        return response()->json([
            'message' => 'تمت الإضافة بنجاح',
            'nubdha' => $nubdha->load('stories')
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack(); // إلغاء كل التغييرات إذا حدث خطأ
        return response()->json([
            'message' => 'حدث خطأ أثناء حفظ البيانات',
            'error' => $e->getMessage()
        ], 500);
    }
    }

    




    /**
     * Display the specified resource.
     */
    public function show(nubdha $nubdha)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(nubdha $nubdha)
    {
        //
    }


    public function update(Request $request)
    {
        $story=story::where('id',$request->story_id)->first();
        if ($request->media) {
            if (Storage::disk('public')->exists($story->media)) {
                Storage::disk('public')->delete($story->media);
            }
            $path = $request->file('media')->store('stories', 'public');
            $type = strpos($request->file('media')->getMimeType(), 'video') !== false ? 'video' : 'image';
            $story->update([
                'media' => $path,
                'type' => $type,
                'caption' => $request->caption
            ]);
        }
        else {
           $story->update([
             'caption' => $request->caption ?? null
           ]);
        }
        return response()->json(['تم التعديل بنجاح'], 200);
    }

    public function destroy($id)
    {
        $nubdha = nubdha::with('stories')->where('id', $id)->first();
            if ($nubdha) {
                foreach ($nubdha->stories as $story) {
                    if (Storage::disk('public')->exists($story->media)) {
                        Storage::disk('public')->delete($story->media);
                    }
                    $story->delete();
                }
                save::where('saveable_type','nubdha')->where('saveable_id',$nubdha->id)->delete();
                $nubdha->delete();
                return response()->json(['تم الحذف بنجاح'], 200);
            }
            else{
                return response()->json(['النبزة غير موجودة'], 200);
            }

    }
}
