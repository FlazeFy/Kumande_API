<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Generator;

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
        $firstCode = Generator::getConsumeFromCode($request->consume_from);
        $secondCode = Generator::getFirstCode("consume");
        $thirdCode = Generator::getConsumeTimeCode().Generator::getDateCode().Generator::getConsumeCode($request->consume_type);

        $getFinalCode = $firstCode."-".$secondCode."-".$thirdCode;

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
