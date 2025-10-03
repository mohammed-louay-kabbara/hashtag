<?php

namespace App\Http\Controllers;

use App\Models\nubdha;
use Illuminate\Support\Facades\Auth;
use App\Models\story;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class NubdhaController extends Controller
{

    public function index()
    {
        // $nubdha= nubdha::with('user','stories')->get();
        // return response()->json($nubdha, 200);
         // 1. load nubdhas with user and stories
    $nubdhas = Nubdha::with(['user', 'stories'])->get();

    // 2. collect all story ids
    $storyIds = $nubdhas->pluck('stories.*.id')
                        ->flatten()
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

    $topByStory = [];

    if (!empty($storyIds)) {
        // try window function (MySQL 8+, PostgreSQL)
        try {
            $ids = implode(',', array_map('intval', $storyIds));

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
                    'votes' => (int)$r->votes
                ];
            }
        } catch (\Throwable $ex) {
            // fallback: DB doesn't support window functions => do per-story top query (less optimal)
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
                        'votes' => (int)$top->votes
                    ];
                }
            }
        }
    }

    // 3. attach top hashtag and media_url to each story
    $nubdhas->transform(function ($nubdha) use ($topByStory) {
        $nubdha->stories->transform(function ($story) use ($topByStory) {
            $story->top_hashtag = $topByStory[$story->id] ?? null;
            // add full media url
            $story->media_url = $story->media_url; // uses accessor
            // optionally hide internal fields if you use resources
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
            $type = strpos($file->getMimeType(), 'video') !== false ? 'video' : 'image';
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
