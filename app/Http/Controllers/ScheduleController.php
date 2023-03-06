<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use Illuminate\Support\Facades\Validator;

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
        try{
            $validator = Validator::make($request->all(), [
                'schedule_consume' => 'required|max:75|min:1',
                'schedule_desc' => 'nullable|max:255|min:1',
                'schedule_tag' => 'nullable|json',
                'schedule_time' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            } else {        
                $sch = Schedule::where('id', $id)->update([
                    'schedule_consume' => $request->schedule_consume,
                    'schedule_desc' => $request->schedule_desc,
                    'schedule_tag' => $request->schedule_tag,
                    'schedule_time' => $request->schedule_time,
                    'updated_at' => date("Y-m-d h:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Schedule updated',
                    'data' => $sch
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createSchedule(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'schedule_consume' => 'required|max:75|min:1',
                'schedule_desc' => 'nullable|max:255|min:1',
                'schedule_tag' => 'nullable|json',
                'schedule_time' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            } else {        
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
                        'status' => 'success',
                        'message' => 'Schedule created',
                        'data' => $sch
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Schedule failed to create',
                        'data' => "There's a schedule with same day and category"
                    ], Response::HTTP_OK);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
