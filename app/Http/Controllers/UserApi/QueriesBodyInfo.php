<?php

namespace App\Http\Controllers\UserApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\BodyInfo;
use App\Models\CountCalorie;

class QueriesBodyInfo extends Controller
{
    public function getMyLatestBodyInfo(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = BodyInfo::select('blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'body_info.created_at','gender')
                ->join('user','user.id','=','body_info.created_by')
                ->where('body_info.created_by', $user_id)
                ->orderby('body_info.created_at','desc')
                ->first();

            $cal = CountCalorie::selectRaw('weight,height,result,count_calorie.created_at as calorie_updated,gender,born_at,TIMESTAMPDIFF(YEAR, born_at, CURDATE()) AS age')
                ->join('user','user.id','=','count_calorie.created_by')
                ->where('count_calorie.created_by',$user_id)
                ->orderby('count_calorie.created_at','desc')
                ->first();

            if($cal){
                if($cal->gender == 'male'){
                    $bmi = $cal->weight / (($cal->height / 100) * ($cal->height / 100));
                } else if($cal->gender == 'female'){
                    $bmi = $cal->weight / (($cal->height / 100) * ($cal->height / 100));
                }

                $cal->bmi = (double) number_format($bmi, 2);
            }

            if ($usr && $cal) {
                $usrArray = $usr->toArray();
                $calArray = $cal->toArray();
                $bodyInfo = array_merge($usrArray, $calArray);
            
                return response()->json([
                    "message" => "User body info retrieved",
                    "status" => 'success',
                    "data" => $bodyInfo
                ], Response::HTTP_OK);
            } elseif ($usr) {
                return response()->json([
                    "message" => "User body info retrieved",
                    "status" => 'success',
                    "data" => $usr
                ], Response::HTTP_OK);
            } elseif ($cal) {
                return response()->json([
                    "message" => "User body info retrieved",
                    "status" => 'success',
                    "data" => $cal
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => "No user body info found",
                    "status" => 'failed'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public static function getMyBodyHistory(Request $request){
        try{
            $user_id = $request->user()->id;

            $usr = BodyInfo::select('body_info.id','blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'body_info.created_at')
                ->join('user','user.id','=','body_info.created_by')
                ->where('body_info.created_by', $user_id)
                ->orderby('body_info.created_at','desc')
                ->get();

            $cal = CountCalorie::select('count_calorie.id','weight','height','result','count_calorie.created_at')
                ->join('user','user.id','=','count_calorie.created_by')
                ->where('count_calorie.created_by',$user_id)
                ->orderby('count_calorie.created_at','desc')
                ->get();

            $dashboard = BodyInfo::selectRaw('MAX(blood_glucose) as max_blood_glucose, MIN(blood_glucose) as min_blood_glucose, MAX(gout) as max_gout, 
                MIN(gout) as min_gout, MAX(cholesterol) as max_cholesterol, MIN(cholesterol) as min_cholesterol,
                MAX(weight) as max_weight, MIN(weight) as min_weight, MAX(height) as max_height, MIN(height) as min_height')
                ->leftjoin('count_calorie','count_calorie.created_by','=','body_info.created_by')
                ->where('count_calorie.created_by', $user_id)
                ->orwhere('body_info.created_by', $user_id)
                ->first();

            if ($usr || $cal) {
                return response()->json([
                    "data" => (object)[
                        "body_info" => $usr,
                        "calorie" => $cal,
                        "dashboard" => $dashboard
                    ],
                    "message" => "No user body info found",
                    "status" => 'failed'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => "No user body history found",
                    "status" => 'failed'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
