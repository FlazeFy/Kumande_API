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
}
