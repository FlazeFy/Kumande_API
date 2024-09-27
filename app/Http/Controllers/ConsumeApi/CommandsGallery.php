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
    /**
     * @OA\POST(
     *     path="/api/v1/consume/gallery",
     *     summary="Create consume gallery",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume gallery create is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Gallery is created"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
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
                        'message' => 'Gallery is created'
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
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/consume/gallery/{gallery_id}",
     *     summary="Delete consume gallery by id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gallery_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume Gallery ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume gallery delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Gallery is deleted | Gallery already been deleted"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Consume Gallery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Gallery not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function deleteGalleryById($gallery_id){
        try{
            $data = ConsumeGallery::find($gallery_id);
            $res = ConsumeGallery::destroy($gallery_id);

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
                            'message' => 'Gallery is deleted'
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Gallery already been deleted'
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
                    'message' => 'Gallery not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/consume/gallery/{gallery_id}",
     *     summary="Update consume gallery by id",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gallery_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume Gallery ID",
     *         example="23260991-9dbb-a35b-0fc9-adfddf0938d1",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume gallery delete is success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Gallery is updated"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Consume Gallery not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Gallery not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="The name field is required"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function updateGalleryById(Request $request,$gallery_id){
        try{
            $validator = Validation::getValidateUpdateConsumeGallery($request);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $res = ConsumeGallery::where('id',$gallery_id)
                    ->update([
                        'gallery_desc' => $request->gallery_desc
                    ]);

                if($res){
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Gallery is updated',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Gallery not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something error please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
