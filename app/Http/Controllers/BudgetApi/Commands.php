<?php

namespace App\Http\Controllers\BudgetApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use App\Models\Budget;
use App\Models\User;

use App\Helpers\Generator;
use App\Helpers\Validation;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class Commands extends Controller
{
    public function createBudget(Request $request){
        try{
            $validator = Validation::getValidateCreateBudget($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {        
                $user_id = $request->user()->id;
                $id = Generator::getUUID();
                $budget_ava = Budget::searchBudgetAvailable($user_id, $request->month, $request->year);

                if($budget_ava == null){
                    $bdt = Budget::create([
                        'id' => $id, 
                        'firebase_id' => $request->firebase_id, 
                        'budget_total' => $request->budget_total, 
                        'budget_month_year' => [
                            'year' => $request->year,
                            'month' => $request->month
                        ], 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id, 
                        'updated_at' => null, 
                        'updated_by' => null, 
                        'over_at' => null
                    ]);

                    $user = User::getProfile($user_id);
                    $fcm_token = $user->firebase_fcm_token;
                    if($fcm_token){
                        $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                        $messaging = $factory->createMessaging();
                        $message = CloudMessage::withTarget('token', $fcm_token)
                            ->withNotification(Notification::create("You have successfully make budget plan for $request->month-$request->year"));
                        $response = $messaging->send($message);
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Budget is created',
                        'data' => $bdt
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Budget is already exist',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteBudgetById(Request $request, $id){
        try{ 
            $user_id = $request->user()->id;

            $data = Budget::find($id);
            $res = Budget::where('created_by',$user_id)
                ->where('id',$id)
                ->delete();

            if($res){
                $user = User::getProfile($user_id);
                $fcm_token = $user->firebase_fcm_token;
                if($fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $fcm_token)
                        ->withNotification(Notification::create("You have successfully delete budget plan $data->month-$data->year"));
                    $response = $messaging->send($message);
                }
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Budget is deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something error please contact admin',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
