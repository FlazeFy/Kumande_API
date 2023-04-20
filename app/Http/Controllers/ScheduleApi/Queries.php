<?php

namespace App\Http\Controllers\ScheduleApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Models\Schedule;

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

    public function getAllSchedule(Request $request, $page_limit, $order){
        try{
            $user_id = $request->user()->id;

            $sch = Schedule::select('*')
                ->where('created_by', $user_id)
                ->orderBy('created_at', $order)
                ->paginate($page_limit);
        
            return response()->json([
                "msg"=> count($sch)." Data retrived", 
                "status"=> 200,
                "data"=> $sch
            ]);

        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTodaySchedule(Request $request, $day){
        try{
            $user_id = $request->user()->id;

            $sch = Schedule::select('*')
                ->where('created_by', $user_id)
                ->whereRaw("schedule_time LIKE '%".'"'."day".'"'.":".'"'.$day.'"'."%'")
                ->orderByRaw("JSON_EXTRACT(schedule_time, '$.time') ASC")
                ->get();

            return response()->json([
                "msg"=> count($sch)." Data retrived", 
                "status"=> 200,
                "data"=> $sch
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
