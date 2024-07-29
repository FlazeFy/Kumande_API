<?php

namespace App\Http\Controllers\PaymentApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\Generator;
use App\Helpers\Converter;
use App\Helpers\Validation;

use App\Models\Payment;
use App\Models\Consume;
use App\Models\User;

class Commands extends Controller
{
    public function updatePayment(Request $request, $id){
        try{
            $validator = Validation::getValidatePayment($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {      
                $user_id = $request->user()->id;

                $res = Payment::where('created_by',$user_id)
                    ->where('id',$id)
                    ->update([
                        'payment_method' => $request->payment_method,
                        'payment_price' => $request->payment_price,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            
                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Payment updated", 
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Payment failed to updated',
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deletePayment(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $res = Payment::where('created_by',$user_id)
                ->where('id', $id)
                ->delete();

            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Payment deleted", 
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment failed to deleted',
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
