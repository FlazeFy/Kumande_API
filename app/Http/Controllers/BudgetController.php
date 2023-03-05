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

    public function deleteBudgetById($id){
        Budget::where('id', $id)->delete();

        return response()->json([
            "msg"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateBudgetData(Request $request, $id){
        $bdt = Budget::where('id', $id)->update([
            'budget_total' => $request->budget_total,
            'budget_month_year' => $request->budget_month_year,
            'budget_over' => $request->budget_over,
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully updated',
            'result' => $bdt
        ]);
    }

    public function updateBudgetStatus(Request $request, $id){
        $bdt = Budget::where('id', $id)->update([
            'budget_status' => $request->budget_status,
            'updated_at' => date("Y-m-d h:i:s"),
            'achieve_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Status successfully updated',
            'result' => $bdt
        ]);
    }

    public function createBudget(Request $request){
        function getFirstCode(){
            $randChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            $check = Budget::select('budget_code')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get();

            foreach($check as $ck){
                $before_alph = substr($ck->budget_code,0,2);
                $before_num = substr($ck->budget_code,2,1);

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

        function getSecondCode(){
            $now = date("myd");
            
            return $now;
        }

        $getFinalCode = getFirstCode()."-".getSecondCode()."-".$request->budget_over;

        $bdt = Budget::create([
            'budget_code' => $getFinalCode,
            'budget_total' => $request->budget_total,
            'budget_month_year' => $request->budget_month_year,
            'budget_over' => $request->budget_over,
            'budget_status' => $request->budget_status,
            'created_at' => date("Y-m-d h:i:s"),
            'updated_at' => date("Y-m-d h:i:s"),
            'achieve_at' => null
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully created',
            'result' => $bdt
        ]);
    }
}
