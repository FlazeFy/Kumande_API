<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Consume;

class ConsumeController extends Controller
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

    public function getAllConsume($page_limit, $order, $favorite){
        if($favorite == "all"){
            $csm = Consume::select('*')
                ->orderBy('created_at', $order)
                ->orderBy('consume_code', $order)
                ->paginate($page_limit);
        } else {
            $csm = Consume::select('*')
                ->where('consume_favorite',$favorite)
                ->orderBy('created_at', $order)
                ->orderBy('consume_code', $order)
                ->paginate($page_limit);
        }
    
        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=>200,
            "data"=>$csm
        ]);
    }

    public function getTotalConsumeByFrom(){
        $csm = Consume::selectRaw('consume_from, count(1) as total')
            ->groupBy('consume_from')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=> 200,
            "data"=> $csm
        ]);
    }

    public function getTotalConsumeByType(){
        $csm = Consume::selectRaw('consume_type, count(1) as total')
            ->groupBy('consume_type')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=> 200,
            "data"=> $csm
        ]);
    }

    public function deleteConsumeById($id){
        Consume::where('id', $id)->delete();

        return response()->json([
            "msg"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateConsumeData(Request $request, $id){
        $csm = Consume::where('id', $id)->update([
            'consume_type' => $request->consume_type,
            'consume_name' => $request->consume_name,
            'consume_from' => $request->consume_from,
            'consume_payment' => $request->consume_payment,
            'consume_tag' => $request->consume_tag,
            'consume_comment' => $request->consume_comment,
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully updated',
            'result' => $csm
        ]);
    }

    public function updateConsumeFavorite(Request $request, $id){
        $csm = Consume::where('id', $id)->update([
            'consume_favorite' => $request->consume_favorite,
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully updated',
            'result' => $csm
        ]);
    }

    public function createConsume(Request $request){

        function getFirstCode($from){
            if($from == "GoFood"){
                return "GFD";
            } else if($from == "GrabFood"){
                return "GBF";
            } else if($from == "ShopeeFood"){
                return "SPF";
            } else if($from == "Others"){
                return "OTH";
            } else if($from == "Home"){
                return "HOM";
            }
        }

        function getSecondCode(){
            $randChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            $check = Consume::select('consume_code')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get();

            foreach($check as $ck){
                $before_alph = substr($ck->consume_code,4,2);
                $before_num = substr($ck->consume_code,6,1);

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

        function getConsumeCode($type){
            if($type == "Food"){
                return "FD";
            } else { //Drink
                return "DR";
            }
        }

        function getConsumeTimeCode(){
            $now = date("Y-m-d h:i:s");
            $hour = date("h", strtotime($now));

            if($hour > 5 && $hour <= 10){
                $time = "B"; //Breakfast
            } else if($hour > 10 && $hour <= 15){
                $time = "L"; //Lunch
            } else if($hour > 15 && $hour <= 22){
                $time = "D"; //Dinner
            } else {
                $time = "S"; //Snack
            }
            return $time;
        }

        function getThirdCode(){
            $timeStamp = date('dmy');

            return getConsumeTimeCode().$timeStamp;
        }

        $getFinalCode = getFirstCode($request->consume_from)."-".getSecondCode()."-".getThirdCode();

        $csm = Consume::create([
            'consume_code' => $getFinalCode,
            'consume_type' => $request->consume_type,
            'consume_name' => $request->consume_name,
            'consume_from' => $request->consume_from,
            'consume_payment' => $request->consume_payment,
            'consume_favorite' => $request->consume_favorite,
            'consume_tag' => $request->consume_tag,
            'consume_comment' => $request->consume_comment,
            'created_at' => date("Y-m-d h:i:s"),
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully created',
            'result' => $csm
        ]);
    }
}
