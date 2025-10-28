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

    // 1️⃣ جلب جميع النبذات مع المستخدم والستوريات المرتبطة
    $nubdhas = Nubdha::with(['user', 'stories'])->withcount('nubdha_view')->get();

    // 2️⃣ استخراج كل story_id من النبذات
    $storyIds = $nubdhas->pluck('stories.*.id')
        ->flatten()
        ->filter()
        ->unique()
        ->values()
        ->all();

    $ids = $nubdhas->pluck('id')->all();

    // 3️⃣ جلب الـ saves الخاصة بالمستخدم الحالي
    $savedIds = [];
    if ($meId && !empty($ids)) {
        $savedIds = Save::where('user_id', $meId)
            ->where('saveable_type', 'nubdha')
            ->whereIn('saveable_id', $ids)
            ->pluck('saveable_id')
            ->toArray();
    }

    // 4️⃣ جلب الهاشتاغ الأعلى لكل ستوري
    $topByStory = [];
    if (!empty($storyIds)) {
        $idsStr = implode(',', array_map('intval', $storyIds));

        try {
            // استخدام Window Function إن كانت مدعومة
            $sql = "
                SELECT story_id, name_hashtag, votes FROM (
                    SELECT story_id, name_hashtag, COUNT(*) as votes,
                           ROW_NUMBER() OVER (PARTITION BY story_id ORDER BY COUNT(*) DESC) AS rn
                    FROM hashtag_stories
                    WHERE story_id IN ($idsStr)
                    GROUP BY story_id, name_hashtag
                ) t
                WHERE rn = 1
            ";

            $rows = DB::select(DB::raw($sql));

            foreach ($rows as $r) {
                $topByStory[$r->story_id] = [
                    'name_hashtag' => $r->name_hashtag,
                    'votes' => (int) $r->votes,
                ];
            }
        } catch (\Throwable $ex) {
            // fallback في حال قاعدة البيانات لا تدعم window functions
            $topByStory = DB::table('hashtag_stories')
                ->select('story_id', DB::raw('name_hashtag, COUNT(*) as votes'))
                ->whereIn('story_id', $storyIds)
                ->groupBy('story_id', 'name_hashtag')
                ->get()
                ->groupBy('story_id')
                ->map(function ($group) {
                    $top = $group->sortByDesc('votes')->first();
                    return [
                        'name_hashtag' => $top->name_hashtag,
                        'votes' => (int) $top->votes,
                    ];
                })
                ->toArray();
        }
    }

    // 5️⃣ جلب الهاشتاغ الذي أضافه المستخدم نفسه
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

    // 6️⃣ تجهيز البيانات قبل الإرجاع
    $nubdhas->transform(function ($nubdha) use ($topByStory, $savedIds, $userHashtags) {
        $nubdha->isSaved = in_array($nubdha->id, $savedIds, true);

        $nubdha->stories->transform(function ($story) use ($topByStory, $userHashtags) {
            // الهاشتاغ الأعلى
            if (isset($topByStory[$story->id])) {
                $story->top_hashtag = $topByStory[$story->id]['name_hashtag'];
                $story->hashtag_votes = $topByStory[$story->id]['votes'];
            } else {
                $story->top_hashtag = null;
                $story->hashtag_votes = 0;
            }

            // الهاشتاغ الذي أضافه المستخدم الحالي
            $story->user_hashtag = $userHashtags[$story->id] ?? null;

            return $story;
        });

        return $nubdha;
    });

    // 7️⃣ إرجاع النتيجة النهائية
    return response()->json($nubdhas, 200);
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
            'media' => 'required', 
        ]);
        $nubdha=nubdha::create([
            'user_id' => Auth::id()
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
     return response()->json([
        'message' => 'تمت الإضافة بنجاح',
        'nubdha' => $nubdha->load('stories')
    ], 201);
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
