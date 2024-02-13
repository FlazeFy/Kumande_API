<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use Illuminate\Support\Facades\Validator;

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

    public function deleteBudgetById($id){
        Budget::where('id', $id)->delete();

        return response()->json([
            "message"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateBudgetData(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'budget_total' => 'required|max:10|min:4',
                'budget_month_year' => 'required|json',
                'budget_over' => 'required|max:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $bdt = Budget::where('id', $id)->update([
                    'budget_total' => $request->budget_total,
                    'budget_month_year' => $request->budget_month_year,
                    'budget_over' => $request->budget_over,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Budget updated',
                    'data' => $bdt
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateBudgetStatus(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'budget_status' => 'nullable|json'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $bdt = Budget::where('id', $id)->update([
                    'budget_status' => $request->budget_status,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'achieve_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Budget updated',
                    'data' => $bdt
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createBudget(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'budget_total' => 'required|max:10|min:4',
                'budget_month_year' => 'required|json',
                'budget_over' => 'required|max:1',
                'budget_status' => 'nullable|json'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $firstCode = Generator::getFirstCode("budget");
                $secondCode = Generator::getDateCode();

                $getFinalCode = $firstCode."-".$secondCode."-".$request->budget_over;

                $bdt = Budget::create([
                    'budget_code' => $getFinalCode,
                    'budget_total' => $request->budget_total,
                    'budget_month_year' => $request->budget_month_year,
                    'budget_over' => $request->budget_over,
                    'budget_status' => $request->budget_status,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'achieve_at' => null
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Budget created',
                    'data' => $bdt
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
