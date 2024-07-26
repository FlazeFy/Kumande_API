<?php

namespace App\Http\Controllers\TagApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/tag/my",
     *     summary="Get all of my tag",
     *     tags={"Tag"},
     *     @OA\Response(
     *         response=200,
     *         description="Tag found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
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

    /**
     * @OA\GET(
     *     path="/api/v1/tag",
     *     summary="Get all of my tag and public tag",
     *     tags={"Tag"},
     *     @OA\Response(
     *         response=200,
     *         description="Tag found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
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
