<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Generator;

use App\Models\Schedule;

class ScheduleController extends Controller
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

    public function getAllSchedule($page_limit, $order){
        $sch = Schedule::select('*')
            ->orderBy('created_at', $order)
            ->paginate($page_limit);
    
        return response()->json([
            "msg"=> count($sch)." Data retrived", 
            "status"=> 200,
            "data"=> $sch
        ]);
    }

    public function deleteScheduleById($id){
        Schedule::where('id', $id)->delete();

        return response()->json([
            "msg"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateScheduleData(Request $request, $id){
        $sch = Schedule::where('id', $id)->update([
            'schedule_consume' => $request->schedule_consume,
            'schedule_desc' => $request->schedule_desc,
            'schedule_tag' => $request->schedule_tag,
            'schedule_time' => $request->schedule_time,
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully updated',
            'result' => $sch
        ]);
    }

    public function createSchedule(Request $request){
        $firstCode = Generator::getFirstCode("schedule");
        $secondCode = Generator::getDateCode();
        $thirdCode = Generator::getInitialCode($request->schedule_consume);
        $getFinalId = $firstCode."-".$secondCode."-".$thirdCode;
        $check = Generator::checkSchedule($request->schedule_time);

        if(!$check){
            $sch = Schedule::create([
                'schedule_code' => $getFinalId,
                'schedule_consume' => $request->schedule_consume,
                'schedule_desc' => $request->schedule_desc,
                'schedule_tag' => $request->schedule_tag,
                'schedule_time' => $request->schedule_time,
                'created_at' => date("Y-m-d h:i:s"),
                'updated_at' => date("Y-m-d h:i:s")
            ]);
    
            return response()->json([
                'status' => 200,
                'message' => 'Data successfully created',
                'result' => $sch
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => "Data failed to create",
                'result' => "There's a schedule with same day and category"
            ]);
        }
        
    }
}
