<?php

namespace App\Http\Controllers\UserApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class Queries extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getMyProfile(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = User::select('fullname','password','email','gender','born_at','created_at','updated_at')
                ->where('id', $user_id)
                ->limit(1)
                ->get();

            return response()->json([
                "message"=> "User Data retrived", 
                "status"=> 'success',
                "data"=> $usr
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
