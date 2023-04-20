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
            $csm = Payment::selectRaw('MONTH(created_at) as context, SUM(payment_price) as total')
                ->groupBy('context')
                ->whereRaw('YEAR(created_at) = '.$year)
                ->orderBy('context','ASC')
                ->get();

            $obj = [];
            for ($i = 1; $i <= 12; $i++) {
                $spend = 0;
                $timestamp = mktime(0, 0, 0, $i, 1, date('Y'));
                $mon = date('M', $timestamp);
            
                foreach ($csm as $cs) {
                    if ($cs->context == $i) {
                        $spend = $cs->total;
                        break;
                    }
                }
            
                $obj[] = [
                    'context' => $mon,
                    'count' => (int)$spend,
                ];
            }

            $collection = collect($obj);

            return response()->json([
                "msg"=> count($collection)." Data retrived", 
                "status"=> 200,
                "data"=> $collection
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
            $csm = Payment::selectRaw('DAY(created_at) as context, SUM(payment_price) as total')
                ->groupBy('context')
                ->whereRaw('YEAR(created_at) = '.$year)
                ->whereRaw('MONTH(created_at) = '.$month)
                ->orderBy('context','ASC')
                ->get();

            $obj = [];
            $date = $year."-".$month."-01";
            $max = date("t", strtotime($date));

            for ($i = 1; $i <= $max; $i++) {
                $spend = 0;
            
                foreach ($csm as $cs) {
                    if ($cs->context == $i) {
                        $spend = $cs->total;
                        break;
                    }
                }
            
                $obj[] = [
                    'context' => (string)$i,
                    'count' => (int)$spend,
                ];
            }

            $collection = collect($obj);

            return response()->json([
                "msg"=> count($collection)." Data retrived", 
                "status"=> 200,
                "data"=> $collection
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
