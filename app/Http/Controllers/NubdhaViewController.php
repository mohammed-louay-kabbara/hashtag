<?php

namespace App\Http\Controllers;

use App\Models\nubdha_view;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NubdhaViewController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $nubdha_view=nubdha_view::where('user_id',Auth::id())->where('nubdha_id',$request->nubdha_id)->first();
        if ($nubdha_view) {   
            return response()->json(['لقد شاهدت هذا سابقاً'], 200);         
        }
        nubdha_view::create([
            'user_id' => Auth::id(),
            'nubdha_id'=> $request->nubdha_id
        ]);
        return response()->json(['تم إضافة المشاهدة'], 200);
    }


    public function show(nubdha_view $nubdha_view)
    {
        //
    }


    public function edit(nubdha_view $nubdha_view)
    {
        //
    }


    public function update(Request $request, nubdha_view $nubdha_view)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(nubdha_view $nubdha_view)
    {
        //
    }
}
