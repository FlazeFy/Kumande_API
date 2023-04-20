<?php

namespace App\Http\Controllers\PaymentApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Payment;

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

    public function getTotalSpendMonth($year){
        try{
            $csm = Payment::selectRaw('MONTH(created_at) as context, SUM(payment_price) as count')
                ->groupBy('context')
                ->whereRaw('YEAR(created_at) = '.$year)
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

    public function getTotalSpendDay($month, $year){
        try{
            $csm = Payment::selectRaw('DAY(created_at) as context, SUM(payment_price) as count')
                ->groupBy('context')
                ->whereRaw('YEAR(created_at) = '.$year)
                ->whereRaw('MONTH(created_at) = '.$month)
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
