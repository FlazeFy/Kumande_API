<?php

namespace App\Http\Controllers\TagApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;

class Queries extends Controller
{
    public function getMyTag(Request $request){
        try{
            $user_id = $request->user()->id;

            $sch = Tag::select('id','tag_name','tag_slug','created_by')
                ->orderby('tag_name','ASC')
                ->where('created_by',$user_id)
                ->get();
        
            if (count($sch) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Tag found", 
                    'data' => $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag not found',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllTag(Request $request){
        try{
            $user_id = $request->user()->id;

            $sch = Tag::select('id','tag_name','tag_slug','created_by')
                ->orderby('tag_name','ASC')
                ->whereNull('created_by')
                ->orwhere('created_by',$user_id)
                ->get();
        
            if (count($sch) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Tag found", 
                    'data' => $sch
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag not found',
                    'data' => null
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
