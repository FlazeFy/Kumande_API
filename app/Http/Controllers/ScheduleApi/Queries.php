<?php

namespace App\Http\Controllers\ScheduleApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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

    public function getMySchedule(Request $request){
        try{
            $user_id = $request->user()->id;

            $sch = DB::select(DB::raw("SELECT 
                    day, time, GROUP_CONCAT(schedule_consume SEPARATOR ', ') AS schedule_consume
                    FROM (
                    SELECT 
                        REPLACE(JSON_EXTRACT(schedule_time, '$[0].day'), '\"', '') AS day, 
                        REPLACE(JSON_EXTRACT(schedule_time, '$[0].category'), '\"', '') AS time,
                            schedule_consume
                        FROM `schedule`
                        WHERE created_by = '".$user_id."'
                    ) AS q
                    GROUP BY 1, 2
                    ORDER BY DAYNAME(1)
                "));
        
            if (count($sch) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Schedule found", 
                    'data' => $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Schedule not found',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
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
                ->orderByRaw("JSON_EXTRACT(schedule_time, '$[0].time') ASC")
                ->get();

            return response()->json([
                "msg"=> "Data retrived", 
                "status"=> 'success',
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
