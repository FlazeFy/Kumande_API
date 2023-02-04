<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Budget;

class BudgetController extends Controller
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

    public function getAllBudget($page_limit, $order, $over){
        if($over == "all"){
            $bdt = Budget::select('*')
                ->orderBy('created_at', $order)
                ->paginate($page_limit);
        } else {
            $bdt = Budget::select('*')
                ->where('budget_over', $over)
                ->orderBy('created_at', $order)
                ->paginate($page_limit);
        }
    
        return response()->json([
            "msg"=> count($bdt)." Data retrived", 
            "status"=>200,
            "data"=> $bdt
        ]);
    }
}
