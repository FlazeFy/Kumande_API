<?php

namespace App\Http\Controllers\TagApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;
use App\Models\Consume;

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

    /**
     * @OA\GET(
     *     path="/api/v1/tag/analyze/{slug}",
     *     summary="Get analyze tag used in consume",
     *     tags={"Tag"},
     *     @OA\Response(
     *         response=200,
     *         description="Tag found / Tag found but never been"
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
    public function getAnalyzeMyTag(Request $request, $slug){
        try{
            $user_id = $request->user()->id;
            $calorie_query = "REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '')";

            $res = Consume::selectRaw("COUNT(1) as total_item, CAST(SUM(payment_price) as UNSIGNED) as total_price, 
                    AVG($calorie_query) as average_calorie, CAST(MAX($calorie_query) as UNSIGNED) as max_calorie, CAST(MIN($calorie_query) as UNSIGNED) as min_calorie, 
                    MAX(consume.created_at) as last_used")
                ->leftjoin('payment','payment.consume_id','=','consume.id')
                ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$slug.'"%'."'")
                ->where('consume.created_by', $user_id)
                ->first();
        
            if ($res) {
                if($res->total_item > 0){
                    $lastUsedConsume = Consume::select('consume_name')
                        ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$slug.'"%'."'")
                        ->where('consume.created_by', $user_id)
                        ->where('consume.created_at', $res->last_used)
                        ->first();

                        $res->last_used_consume_name = $lastUsedConsume ? $lastUsedConsume->consume_name : null;
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tag found", 
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tag found but never been", 
                        'data' => null
                    ], Response::HTTP_OK);
                }
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
