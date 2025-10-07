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

class NubdhaController extends Controller
{

    public function index()
    {
    $meId = Auth::id();
    $nubdhas = Nubdha::with(['user', 'stories'])->get();

    $storyIds = $nubdhas->pluck('stories.*.id')
        ->flatten()
        ->filter()
        ->unique()
        ->values()
        ->all();

    $topByStory = [];

    if (!empty($storyIds)) {
        $ids = implode(',', array_map('intval', $storyIds));

        try {
            // إذا DB يدعم window functions
            $sql = "
                SELECT story_id, name_hashtag, votes FROM (
                    SELECT story_id, name_hashtag, COUNT(*) as votes,
                           ROW_NUMBER() OVER (PARTITION BY story_id ORDER BY COUNT(*) DESC) AS rn
                    FROM hashtag_stories
                    WHERE story_id IN ($ids)
                    GROUP BY story_id, name_hashtag
                ) t
                WHERE rn = 1
            ";

            $rows = DB::select(DB::raw($sql));

            foreach ($rows as $r) {
                $topByStory[$r->story_id] = [
                    'name_hashtag' => $r->name_hashtag,
                    'votes'        => (int) $r->votes,
                ];
            }
        } catch (\Throwable $ex) {
            // fallback: DB لا يدعم window functions
            foreach ($storyIds as $sId) {
                $top = DB::table('hashtag_stories')
                    ->select('name_hashtag', DB::raw('COUNT(*) as votes'))
                    ->where('story_id', $sId)
                    ->groupBy('name_hashtag')
                    ->orderByDesc('votes')
                    ->limit(1)
                    ->first();

                if ($top) {
                    $topByStory[$sId] = [
                        'name_hashtag' => $top->name_hashtag,
                        'votes'        => (int) $top->votes,
                    ];
                }
            }
        }
    }


    // 5. تعديل البيانات قبل الإرجاع
$nubdhas->transform(function ($nubdha) use ($topByStory, $following) {


    // ربط الهاشتاغ الأعلى لكل ستوري
    $nubdha->stories->transform(function ($story) use ($topByStory) {
        if (isset($topByStory[$story->id])) {
            $story->top_hashtag = $topByStory[$story->id]['name_hashtag'];
            $story->hashtag_votes = $topByStory[$story->id]['votes'];
        } else {
            $story->top_hashtag = null;
            $story->hashtag_votes = 0;
        }
        return $story;
    });

    return $nubdha;
});

    return response()->json($nubdhas, 200);
    }

    public function create()
    {
        
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
                'caption' => $request->captions ?? null
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
            $nubdha->delete();
            }
        return response()->json(['تم الحذف بنجاح'], 200);
    }
}
