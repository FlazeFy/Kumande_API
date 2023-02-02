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
            $csm = Consume::select('consume_id', 'consume_type', 'consume_name', 'consume_from', 'consume_payment', 'consume_favorite', 'consume_comment', 'consume_createdAt', 'consume_updatedAt')
                ->orderBy('consume_createdAt', $order)
                ->orderBy('consume_id', $order)
                ->paginate($page_limit);
        } else {
            $csm = Consume::select('consume_id', 'consume_type', 'consume_name', 'consume_from', 'consume_payment', 'consume_favorite', 'consume_comment', 'consume_createdAt', 'consume_updatedAt')
                ->where('consume_favorite',$favorite)
                ->orderBy('consume_createdAt', $order)
                ->orderBy('consume_id', $order)
                ->paginate($page_limit);
        }
    
        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=>200,
            "data"=>$csm
        ]);
    }

    public function getTotalConsumeByFrom(){
        $csm = Consume::selectRaw('consume_from, count(*) as total')
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
        $csm = Consume::selectRaw('consume_type, count(*) as total')
            ->groupBy('consume_type')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json([
            "msg"=> count($csm)." Data retrived", 
            "status"=> 200,
            "data"=> $csm
        ]);
    }
}
