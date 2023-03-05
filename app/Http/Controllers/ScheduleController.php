<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $check = Schedule::select('schedule_code','schedule_time')
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get();

        function checkSchedule($schedule, $mytime){
            $res = false;
            $parsedMyTime = json_decode($mytime);

            foreach($parsedMyTime as $pmt){
                $myday = $pmt->day;
                $mycategory = $pmt->category;

                foreach($schedule as $sc){
                    $parsedTime = json_decode($sc->schedule_time);
    
                    foreach($parsedTime as $pt){
                        if($pt->day == $myday && $pt->category == $mycategory){
                            $res = true;
                        }
                    }
                }
            }

            return $res;
        }

        function getFirstId($schedule){
            $randChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            foreach($schedule as $sc){
                $before_alph = substr($sc->schedule_code,0,2);
                $before_num = substr($sc->schedule_code,2,1);

                if($before_num < 9){
                    $after_num = (int)$before_num + 1;
                    $after_alph = $before_alph;
                } else {
                    $after_num = 0;
                    $after_alph = substr(str_shuffle(str_repeat($randChar, 5)), 0, 2);
                }
            }            

            return $after_alph.$after_num;
        }

        function getSecondId(){
            $now = date("myd");
            
            return $now;
        }

        function getThirdId($name){
            $id = strtoupper(substr($name, 0,1));

            return $id;
        }

        $getFinalId = getFirstId($check)."-".getSecondId()."-".getThirdId($request->schedule_consume);

        if(!checkSchedule($check, $request->schedule_time)){
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
