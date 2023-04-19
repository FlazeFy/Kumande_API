<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use Illuminate\Support\Facades\Validator;

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

    public function getAllConsume($page_limit, $order, $favorite, $type){
        if($favorite == "all"){
            if($type != "All"){
                $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                    ->join('payment', 'payment.consume_id', '=', 'consume.id')
                    ->whereNull('deleted_at')
                    ->where('consume_type',$type)
                    ->orderBy('consume.created_at', $order)
                    ->orderBy('slug_name', $order)
                    ->paginate($page_limit);
            } else {
                $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                    ->join('payment', 'payment.consume_id', '=', 'consume.id')
                    ->whereNull('deleted_at')
                    ->orderBy('consume.created_at', $order)
                    ->orderBy('slug_name', $order)
                    ->paginate($page_limit);
            }
        } else {
            if($type != "All"){
                $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                    ->join('payment', 'payment.consume_id', '=', 'consume.id')
                    ->where('is_favorite',$favorite)
                    ->whereNull('deleted_at')
                    ->where('consume_type',$type)
                    ->orderBy('consume.created_at', $order)
                    ->orderBy('slug_name', $order)
                    ->paginate($page_limit);
            } else {
                $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                    ->join('payment', 'payment.consume_id', '=', 'consume.id')
                    ->where('is_favorite',$favorite)
                    ->whereNull('deleted_at')
                    ->orderBy('consume.created_at', $order)
                    ->orderBy('slug_name', $order)
                    ->paginate($page_limit);
            }
        }
    
        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=>200,
            "data"=>$csm
        ]);
    }

    public function getTotalConsumeByFrom(){
        $csm = Consume::selectRaw('consume_from as context, count(1) as total')
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
        $csm = Consume::selectRaw('consume_type as context, count(1) as total')
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
        try{
            $validator = Validator::make($request->all(), [
                'consume_type' => 'required|max:10|min:1',
                'consume_name' => 'required|json',
                'consume_from' => 'required|max:10|min:1',
                'consume_payment' => 'required|json',
                'consume_tag' => 'nullable|json',
                'consume_comment' => 'nullable|max:255|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            } else {        
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
                    'status' => 'success',
                    'message' => 'Consume updated',
                    'data' => $csm
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateConsumeFavorite(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'is_favorite' => 'required|max:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            } else {        
                $csm = Consume::where('id', $id)->update([
                    'is_favorite' => $request->is_favorite,
                    'updated_at' => date("Y-m-d h:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Consume updated',
                    'data' => $csm
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createConsume(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'consume_type' => 'required|max:10|min:1',
                'consume_name' => 'required|json',
                'consume_from' => 'required|max:10|min:1',
                'consume_payment' => 'required|json',
                'consume_tag' => 'nullable|json',
                'is_favorite' => 'required|max:1',
                'consume_comment' => 'nullable|max:255|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            } else {        
                $firstCode = Generator::getConsumeFromCode($request->consume_from);
                $secondCode = Generator::getFirstCode("consume");
                $thirdCode = Generator::getConsumeTimeCode().Generator::getDateCode().Generator::getConsumeCode($request->consume_type);

                $getFinalCode = $firstCode."-".$secondCode."-".$thirdCode;

                $csm = Consume::create([
                    'slug_name' => $getFinalCode,
                    'consume_type' => $request->consume_type,
                    'consume_name' => $request->consume_name,
                    'consume_from' => $request->consume_from,
                    'consume_payment' => $request->consume_payment,
                    'is_favorite' => $request->is_favorite,
                    'consume_tag' => $request->consume_tag,
                    'consume_comment' => $request->consume_comment,
                    'created_at' => date("Y-m-d h:i:s"),
                    'updated_at' => date("Y-m-d h:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Consume created',
                    'data' => $csm
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
