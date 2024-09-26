<?php

namespace App\Http\Controllers\TagApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;

use App\Helpers\Validation;
use App\Helpers\Generator;

class Commands extends Controller
{
    public function deleteTagById(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $res = Tag::where('created_by',$user_id)
                ->where('id',$id)
                ->delete();
        
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Tag deleted", 
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag failed to deleted',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createMyTag(Request $request){
        try{
            $user_id = $request->user()->id;

            $check = Tag::where('created_by',$user_id)
                ->where('tag_name',$request->tag_name)
                ->first();
        
            if (!$check) {
                $res = Tag::create([
                    'id' => Generator::getUUID(),
                    'tag_slug' =>  Generator::getSlug($request->tag_name, 'tag'), 
                    'tag_name' => $request->tag_name, 
                    'created_at' => date('Y-m-d H:i:s'), 
                    'created_by' => $user_id
                ]);

                if ($res) {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tag created", 
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Tag failed to created',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag failed to create. Already exist',
                ], Response::HTTP_CONFLICT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
