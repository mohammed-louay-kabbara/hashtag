<?php

namespace App\Http\Controllers;

use App\Models\follower;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FollowerController extends Controller
{

    public function index()
    {
        
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {
        $isfollower=follower::where('user_id',Auth::id())->where('followed_id',$request->followed_id)->first();
        if ($isfollower) {
            $isfollower->delete();
            return response()->json(['تم إلغاء المتابعة '], 200);
        }
        follower::create([
            'user_id' => Auth::id(),
            'followed_id' => $request->followed_id
        ]);
        return response()->json(['تم إضافة متابعة بنجاح'], 200);
    }

    public function show(follower $follower)
    {
        
    }

    public function edit(follower $follower)
    {
        //
    }

    public function update(Request $request, follower $follower)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(follower $follower)
    {
        //
    }
}
