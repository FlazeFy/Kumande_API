<?php

namespace App\Http\Controllers\TagApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\Tag;
use App\Models\Consume;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/tag/my",
     *     summary="Get all of my tag",
     *     tags={"Tag"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tag found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tag found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="weight", type="integer", example=68),
     *                          @OA\Property(property="height", type="integer", example=183),
     *                          @OA\Property(property="result", type="integer", example=1800),
     *                          @OA\Property(property="created_at", type="integer", format="date-time", example="2024-03-19 02:37:58"),
     *                 )
     *             )
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
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Tag not found")
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
    public function getMyTag(Request $request){
        try{
            $user_id = $request->user()->id;

            $res = Tag::select('id','tag_name','tag_slug')
                ->orderby('tag_name','ASC')
                ->where('created_by',$user_id)
                ->get();
        
            if (count($res) > 0) {
                foreach($res as $idx => $dt){
                    $csm = Consume::selectRaw('COUNT(1) as total')
                        ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$dt->tag_slug.'"%'."'")
                        ->where('created_by',$user_id)
                        ->first();

                    $res[$idx]->total_used = $csm->total;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Tag found", 
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/tag",
     *     summary="Get all of my tag and public tag",
     *     tags={"Tag"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tag found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="id", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                          @OA\Property(property="tag_name", type="string", example="Low Fat"),
     *                          @OA\Property(property="tag_slug", type="string", example="low_fat"),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                 )
     *             )
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
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Tag not found")
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
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/tag/analyze/{slug}",
     *     summary="Get analyze tag used in consume",
     *     tags={"Tag"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Tag slug name",
     *         example="milk",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag found | Tag found but never been used"
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
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Tag not found")
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
                    $lastUsedConsume = Consume::select('consume_name','consume_type','slug_name')
                        ->whereRaw('consume_tag like '."'".'%"slug_name":"'.$slug.'"%'."'")
                        ->where('consume.created_by', $user_id)
                        ->where('consume.created_at', $res->last_used)
                        ->first();

                        $res->last_used_consume_name = $lastUsedConsume ? $lastUsedConsume->consume_name : null;
                        $res->last_used_consume_type = $lastUsedConsume ? $lastUsedConsume->consume_type : null;
                        $res->last_used_consume_slug = $lastUsedConsume ? $lastUsedConsume->slug_name : null;
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tag found", 
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => "Tag found but never been used", 
                        'data' => null
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
