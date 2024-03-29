<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeList;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

use App\Helpers\Validation;
use App\Helpers\Generator;
use App\Helpers\Converter;

class CommandsList extends Controller
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

    public function getAllList($page_limit, $order){
        $csl = ConsumeList::select('*')
            ->orderBy('created_at', $order)
            ->paginate($page_limit);
    
        return response()->json([
            "message"=> "Data retrived", 
            "status"=> 200,
            "data"=> $csl
        ]);
    }

    public function deleteListById($id){
        ConsumeList::where('id', $id)->delete();

        return response()->json([
            "message"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateListData(Request $request, $id){
        try{
            $validator = Validator::make($request->all(), [
                'list_name' => 'required|max:75|min:1',
                'list_desc' => 'nullable|max:255|min:1',
                'list_tag' => 'nullable|json'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $csl = ConsumeList::where('id', $id)->update([
                    'list_name' => $request->list_name,
                    'list_desc' => $request->list_desc,
                    'list_tag' => $request->list_tag,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'List updated',
                    'data' => $csl
                ], Response::HTTP_OK);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createList(Request $request){
        try{
            $validator = Validation::getValidateCreateConsumeList($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                if(ConsumeList::getAvailableListName($request->list_name, $user_id)){
                    $slug = Generator::getSlug($request->list_name, "consume_list");

                    if($request->list_tag){
                        $jsonTag = Converter::getEncoded($request->list_tag);
                        $tag = json_decode($jsonTag, true);
                    } else {
                        $tag = null;
                    }
    
                    $csl = ConsumeList::create([
                        'id' => Generator::getUUID(),
                        'firebase_id' => $request->firebase_id,
                        'slug_name' => $slug,
                        'list_name' => $request->list_name,
                        'list_desc' => $request->list_desc,
                        'list_tag' => $tag,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => $user_id,
                        'updated_at' => null,
                        'updated_by' => null,
                    ]);

                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $request->token_fcm)
                        ->withNotification(Notification::create('You have successfully added new list called ', $request->list_name))
                        ->withData([
                            'list_name' => $request->list_name,
                        ]);
                    $response = $messaging->send($message);
    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'List created',
                        'data' => $csl
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'List name is already exist, try other name',
                        'data' => null
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
}
