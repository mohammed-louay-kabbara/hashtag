<?php

namespace App\Http\Controllers;

use App\Models\hashtag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HashtagController extends Controller
{

    public function index()
    {
    $meId = auth()->id();
    $hashtags = Hashtag::with('user')
        ->withCount('loves')
        ->withCount([
            'loves as isLiked' => function ($query) use ($meId) {
                $query->where('user_id', $meId);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($hashtag) {
            // نحول is_loved من رقم (0 أو 1) إلى true/false لسهولة التعامل في الواجهة
            $hashtag->isLiked = $hashtag->isLiked > 0;
            return $hashtag;
        });
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
