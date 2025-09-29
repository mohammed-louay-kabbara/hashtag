<?php

namespace App\Http\Controllers;

use App\Models\love;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoveController extends Controller
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
        $islove=love::where('user_id',Auth::id())->where('hashtag_id',$request->hashtag_id)->first();
        if ($islove) {
            $islove->delete();
            return response()->json(['تم إلغاء تسجيل الإعجاب'], 200);
        }
        love::create([
            'user_id' => Auth::id(),
            'hashtag_id' => $request->hashtag_id
        ]);
        return response()->json(['تم تسجيل الإعجاب بنجاح'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(love $love)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(love $love)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, love $love)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(love $love)
    {
        //
    }
}
