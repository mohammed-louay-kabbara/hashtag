<?php

namespace App\Http\Controllers;

use App\Models\hashtag_story;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HashtagStoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $hashtag_story=hashtag_story::where('user_id',Auth::id())->where('story_id',$request->story_id)->first();
        if ($hashtag_story) {
            $hashtag_story->update(['name_hashtag'=> $request->name_hashtag]);
            return response()->json(['تم تعديل الهاشتاغ'], 200);
        }
        hashtag_story::create([
            'user_id' => Auth::id(),
            'name_hashtag' => $request->name_hashtag,
            'story_id' => $request->story_id
        ]);
        return response()->json(['تمت إضافة الهاشتاغ'], 200);
    }


    public function show(hashtag_story $hashtag_story)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(hashtag_story $hashtag_story)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, hashtag_story $hashtag_story)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(hashtag_story $hashtag_story)
    {
        //
    }
}
