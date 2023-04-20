<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Consume;

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

    public function getAllConsume(Request $request, $page_limit, $order, $favorite, $type){
        try{
            $user_id = $request->user()->id;

            if($favorite == "all"){
                if($type != "All"){
                    $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                        ->join('payment', 'payment.consume_id', '=', 'consume.id')
                        ->whereNull('deleted_at')
                        ->where('consume.created_by', $user_id)
                        ->where('consume_type',$type)
                        ->orderBy('consume.created_at', $order)
                        ->orderBy('slug_name', $order)
                        ->paginate($page_limit);
                } else {
                    $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                        ->join('payment', 'payment.consume_id', '=', 'consume.id')
                        ->whereNull('deleted_at')
                        ->where('consume.created_by', $user_id)
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
                        ->where('consume.created_by', $user_id)
                        ->where('consume_type',$type)
                        ->orderBy('consume.created_at', $order)
                        ->orderBy('slug_name', $order)
                        ->paginate($page_limit);
                } else {
                    $csm = Consume::selectRaw('consume.id, slug_name, consume_type, consume_name, consume_detail, consume_from, is_favorite, consume_tag, consume_comment, consume.created_at, payment_method, payment_price, is_payment')
                        ->join('payment', 'payment.consume_id', '=', 'consume.id')
                        ->where('is_favorite',$favorite)
                        ->whereNull('deleted_at')
                        ->where('consume.created_by', $user_id)
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
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTotalConsumeByFrom(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume_from as context, count(1) as total')
                ->where('created_by', $user_id)
                ->groupBy('consume_from')
                ->orderBy('total', 'DESC')
                ->get();

            return response()->json([
                "msg"=> count($csm)." Data retrived", 
                "status"=> 200,
                "data"=> $csm
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTotalConsumeByType(Request $request){
        try{
            $user_id = $request->user()->id;

            $csm = Consume::selectRaw('consume_type as context, count(1) as total')
                ->where('created_by', $user_id)
                ->groupBy('consume_type')
                ->orderBy('total', 'DESC')
                ->get();

            return response()->json([
                "msg"=> count($csm)." Data retrived", 
                "status"=> 200,
                "data"=> $csm
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
