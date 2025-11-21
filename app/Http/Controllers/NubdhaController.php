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
    $meId = Auth::id();

    // 1️⃣ جلب النبذات الأساسية
    $nubdhas = Nubdha::with(['user', 'stories'])
        ->withCount('nubdha_view')
        ->get();

    $ids = $nubdhas->pluck('id')->all();

    // 2️⃣ المستخدمين الذين يتابعهم
    $followedIds = collect();
    if ($meId) {
        $followedIds = DB::table('followers')
            ->where('user_id', $meId)
            ->pluck('followed_id');
    }

    // 3️⃣ اهتمامات المستخدم
    $userInterests = [];
    if ($meId) {
        $userInterests = DB::table('hashtag_stories')
            ->where('user_id', $meId)
            ->select('name_hashtag', DB::raw('COUNT(*) as count'))
            ->groupBy('name_hashtag')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('name_hashtag')
            ->toArray();
    }

    // 4️⃣ الهاشتاغ الأعلى لكل ستوري
    $storyIds = $nubdhas->pluck('stories.*.id')->flatten()->filter()->unique()->values()->all();
    $topByStory = [];

    if (!empty($storyIds)) {
        $idsStr = implode(',', array_map('intval', $storyIds));
        $rows = DB::select("
            SELECT story_id, name_hashtag, COUNT(*) as votes
            FROM hashtag_stories
            WHERE story_id IN ($idsStr)
            GROUP BY story_id, name_hashtag
        ");
        foreach ($rows as $r) {
            $topByStory[$r->story_id] = [
                'name_hashtag' => $r->name_hashtag,
                'votes' => (int) $r->votes,
            ];
        }
    }

    // 5️⃣ هاشتاغ المستخدم نفسه
    $userHashtags = [];
    if ($meId && !empty($storyIds)) {
        $rows = DB::table('hashtag_stories')
            ->select('story_id', 'name_hashtag')
            ->where('user_id', $meId)
            ->whereIn('story_id', $storyIds)
            ->get();

        foreach ($rows as $r) {
            $userHashtags[$r->story_id] = $r->name_hashtag;
        }
    }

    // 6️⃣ حساب score وترتيب
    $scored = $nubdhas->map(function ($n) use ($followedIds, $userInterests, $topByStory) {

        // عدد المشاهدات
        $viewsCount = $n->nubdha_view_count ?? 0;

        // هل المستخدم يتابع صاحب النبذة؟
        $isFollowed = $followedIds->contains($n->user_id);

        // هل النبذة جديدة < 24 ساعة؟
        $isRecent = $n->created_at->gt(now()->subHours(24));

        // الوقت بالدقائق منذ الإنشاء → لتقوية الجديد
        $minutesSince = $n->created_at->diffInMinutes(now());
        $timeBoost = 1 / max($minutesSince, 1); // وزن قوي للأحدث

        // حساب أصوات الهاشتاغ
        $votesSum = 0;
        $matchesInterest = 0;

        foreach ($n->stories as $s) {
            if (isset($topByStory[$s->id])) {
                $votesSum += $topByStory[$s->id]['votes'];

                if (in_array($topByStory[$s->id]['name_hashtag'], $userInterests)) {
                    $matchesInterest += 1;
                }
            }
        }

        $avgVotes = count($n->stories) ? $votesSum / count($n->stories) : 0;

        // ⭐ الخوارزمية الجديدة
        $n->score =
            ($viewsCount * 0.5) +
            ($avgVotes * 1) +
            ($isFollowed ? 2 : 0) +
            ($isRecent ? 3 : 0) +
            ($matchesInterest * 2) +
            ($timeBoost * 10);   // وزن إضافي للأحدث

        return $n;
    });

    // 7️⃣ الترتيب النهائي
    $sorted = $scored->sortByDesc('score')->values();

    // 8️⃣ بيانات الحفظ
    $savedIds = [];
    if ($meId && !empty($ids)) {
        $savedIds = Save::where('user_id', $meId)
            ->where('saveable_type', 'nubdha')
            ->whereIn('saveable_id', $ids)
            ->pluck('saveable_id')
            ->toArray();
    }

    // 📌 مهم: لا نغير أي شيء في الـ response
    $sorted->transform(function ($nubdha) use ($savedIds, $topByStory, $userInterests, $userHashtags) {
        $nubdha->isSaved = in_array($nubdha->id, $savedIds, true);

        $nubdha->stories->transform(function ($story) use ($topByStory, $userInterests, $userHashtags) {
            $story->top_hashtag = $topByStory[$story->id]['name_hashtag'] ?? null;
            $story->hashtag_votes = $topByStory[$story->id]['votes'] ?? 0;
            $story->matches_interest = in_array($story->top_hashtag, $userInterests);
            $story->user_hashtag = $userHashtags[$story->id] ?? null;
            return $story;
        });

        return $nubdha;
    });

    // 9️⃣ إرجاع نفس الـ response
    return response()->json($sorted->values(), 200);
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
