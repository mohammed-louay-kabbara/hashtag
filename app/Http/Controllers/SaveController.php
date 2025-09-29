<?php

namespace App\Http\Controllers;

use App\Models\save;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SaveController extends Controller
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
        $save=save::where('user_id',Auth::id())->where('saveable_type', $request->saveable_type)
        ->where('saveable_id',$request->saveable_id)->first();
        if ($save) {
            $save->delete();
            return response()->json(['تم إلغاء الحفظ بنجاح'], 200);
        }
        save::create([
            'user_id' => Auth::id(),
            'saveable_type' => $request->saveable_type,
            'saveable_id' => $request->saveable_id
        ]);
        return response()->json(['تم حفظ الفيديو بنجاح'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(save $save)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(save $save)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, save $save)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(save $save)
    {
        //
    }
}
