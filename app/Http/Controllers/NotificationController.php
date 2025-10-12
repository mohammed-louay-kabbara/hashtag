<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Models\notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function index()
    {
       $notification=notification::where('user_id',Auth::id())->with(['user','sender'])->orderByDesc('created_at')->get();
        return response()->json($notification, 200);
    }
    public function storeDeviceToken(Request $request)
    {
        $request->validate([
        'token' => 'required|string',
        ]);
        $user = Auth::user();
        $isDeviceToken=DeviceToken::where('user_id',$user->id)->first();
        if ($isDeviceToken) {
            $isDeviceToken->update(['token' => $request->token]);
            return response()->json(['message' => 'تم حفظ التوكن بنجاح'], 200);
        }
        // احفظ أو حدث التوكن الحالي للمستخدم
        DeviceToken::updateOrCreate(
        ['user_id' => $user->id],
        ['token' => $request->token]
        );
        return response()->json(['message' => 'تم حفظ التوكن بنجاح'], 200);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    public function read($id)
    {
        notification::where('id',$id)->update(['read_at'=> now()]);
        return response()->json(['تم قراءة الإشعار'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        notification::where('id',$id)->delete();
        return response()->json(['تم حذف الإشعار'], 200);
    }
}
