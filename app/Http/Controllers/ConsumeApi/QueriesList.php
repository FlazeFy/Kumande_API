<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeList;
use App\Models\Consume;
use App\Models\Payment;
use App\Models\RelConsumeList;

class QueriesList extends Controller
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

    public function getAllList(Request $request, $page_limit, $order){
        try{
            $user_id = $request->user()->id;

            $csl = ConsumeList::select('id','slug_name','list_name','list_desc','list_tag','created_at')
                ->orderBy('created_at', $order)
                ->where('created_by', $user_id)
                ->paginate($page_limit);

            if ($csl->count() > 0) {
                foreach($csl as $idx => $dt){
                    $csm = RelConsumeList::selectRaw("consume.id, consume.slug_name, consume_name, consume_type, CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned) as calorie, REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide, consume_from")
                        ->join('consume','consume.id','=','rel_consume_list.consume_id')
                        ->where('list_id',$dt->id)
                        ->get();
                    
                    foreach($csm as $jdx => $du){
                        $pyt = Payment::selectRaw('CAST(AVG(payment_price) as unsigned) as average_price')
                            ->where('consume_id', $du->id)
                            ->groupby('consume_id')
                            ->first();

                        if($pyt){
                            $csm[$jdx]->average_price = $pyt->average_price;
                        } else {
                            $csm[$jdx]->average_price = null;
                        }
                    }

                    if(count($csm) > 0){
                        $csl[$idx]->consume = $csm;
                    } else {
                        $csl[$idx]->consume = null;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Data retrived", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume List not found',
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
}
