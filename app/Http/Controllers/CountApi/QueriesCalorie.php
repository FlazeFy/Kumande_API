<?php

namespace App\Http\Controllers\CountApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\CountCalorie;

class QueriesCalorie extends Controller
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

    public function getLastCountCalorie(Request $request){
        try{
            $user_id = $request->user()->id;

            $cal = CountCalorie::select('weight', 'height', 'result','created_at')
                ->where('created_by', $user_id)
                ->orderBy('created_at', 'DESC')
                ->limit(1)->get();

            foreach ($cal as $c) {
                $c->weight = intval($c->weight);
                $c->height = intval($c->height);
                $c->result = intval($c->result);
                $c->created_at = $c->created_at;
            }

            return response()->json([
                "message"=> "Count data retrived", 
                "status"=> 'success',
                "data"=> $cal
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
