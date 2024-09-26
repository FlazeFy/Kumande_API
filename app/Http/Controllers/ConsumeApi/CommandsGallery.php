<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeGallery;

use App\Helpers\Validation;
use App\Helpers\Generator;
use App\Helpers\Converter;

use Kreait\Firebase\Factory;

class CommandsGallery extends Controller
{
    public function createGallery(Request $request){
        try{
            $validator = Validation::getValidateCreateConsumeGallery($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $user_id = $request->user()->id;

                $res = ConsumeGallery::create([
                    'id' => Generator::getUUID(),
                    'consume_id' => $request->consume_id, 
                    'gallery_desc' => $request->gallery_desc,  
                    'gallery_url' => $request->gallery_url,   
                    'created_at' => date("Y-m-d H:i:s"),
                ]);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Gallery successfully added'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Something error please contact admin',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteGallery($id){
        try{
            $data = ConsumeGallery::find($id);
            $res = ConsumeGallery::destroy($id);

            if($res){
                $factory = (new Factory)->withServiceAccount(base_path('/firebase/kumande-64a66-firebase-adminsdk-maclr-55c5b66363.json'));
                $storage = $factory->createStorage();
                $fileName = Generator::generateUUIDStorageURL('consume',$data->gallery_url);

                if($fileName){
                    $bucket = 'kumande-64a66.appspot.com';
                    $fileUrl = "/consume/$asd";
                    $bucket = $storage->getBucket($bucket);
                    $object = $bucket->object($fileUrl);

                    if ($object->exists()){
                        $object->delete();
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Gallery successfully delete'
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'The gallery already been deleted'
                        ], Response::HTTP_OK);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Something error please contact admin',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Something error please contact admin',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateGallery(Request $request,$id){
        try{
            $validator = Validation::getValidateUpdateConsumeGallery($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $res = ConsumeGallery::where('id',$id)
                    ->update([
                        'gallery_desc' => $request->gallery_desc
                    ]);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Gallery successfully updated',
                        'rows_affected' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Gallery failed to update',
                    ], Response::HTTP_NOT_FOUND);
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
