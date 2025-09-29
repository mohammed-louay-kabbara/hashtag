<?php

namespace App\Http\Controllers;

use App\Models\hashtag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HashtagController extends Controller
{

    public function index()
    {
        $hashtags=hashtag::with('user')->withcount('love')->get();
        return response()->json($hashtags, 200);
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
        $request->validate([
            'description' => 'required', 
        ]);
        hashtag::create([
            'user_id' => Auth::id(),
            'description' => $request->description
        ]);
        return response()->json(['تم إضافة الهاشتاغ بنجاخ'], 200);
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
    public function update(Request $request, hashtag $hashtag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(hashtag $hashtag)
    {
        //
    }
}
