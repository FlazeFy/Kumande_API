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
    public function getMyTag(Request $request) {
        try {
            $user_id = $request->user()->id;
            $paginate = $request->query('per_page_key') ?? 14;

            $res = Tag::findAllTag($user_id, $paginate);
            if (count($res) > 0) {
                $tagUsage = Consume::countUsageByTags($user_id);
                foreach ($res as $dt) {
                    $dt->total_used = $tagUsage[$dt->tag_slug] ?? 0;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'tag'), 
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'tag'),
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
    public function getAllTag(Request $request) {
        try {
            $paginate = $request->query('per_page_key') ?? 14;

            $res = Tag::findAllTag(null, $paginate);
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'tag'), 
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'tag'),
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
     *         description="Tag found | Tag found but never been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="tag fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_item", type="integer", example=2),
     *                 @OA\Property(property="total_price", type="integer", example=16000),
     *                 @OA\Property(property="average_calorie", type="number", example=245),
     *                 @OA\Property(property="max_calorie", type="integer", example=90),
     *                 @OA\Property(property="min_calorie", type="integer", example=400),
     *                 @OA\Property(property="last_used", type="string", format="date-time", example="2024-06-19 13:26:14"),
     *                 @OA\Property(property="last_used_consume_name", type="string", example="Es Teh Tawar"),
     *                 @OA\Property(property="last_used_consume_type", type="string", example="Drink"),
     *                 @OA\Property(property="last_used_consume_slug", type="string", example="es-teh-tawar")
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
    public function getAnalyzeMyTagBySlug(Request $request, $slug) {
        try {
            $user_id = $request->user()->id;

            $res = Consume::findAnalyzeConsumeTag($user_id, $slug);
            if ($res) {
                if ($res->total_item > 0) {
                    $lastUsedConsume = Consume::findLastConsumedByTagSlugAndCreatedAt($user_id, $slug, $res->last_used);
                    $res->last_used_consume_name = $lastUsedConsume ? $lastUsedConsume->consume_name : null;
                    $res->last_used_consume_type = $lastUsedConsume ? $lastUsedConsume->consume_type : null;
                    $res->last_used_consume_slug = $lastUsedConsume ? $lastUsedConsume->slug_name : null;
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'tag'), 
                        'data' => $res
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", 'tag fetched, but never used'), 
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'tag'),
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
