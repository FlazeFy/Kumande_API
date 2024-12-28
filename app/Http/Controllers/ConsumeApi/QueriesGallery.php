<?php

namespace App\Http\Controllers\ConsumeApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Models
use App\Models\ConsumeGallery;
use App\Models\Consume;

// Helpers
use App\Helpers\Generator;

class QueriesGallery extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/consume/gallery",
     *     summary="Get all consume gallery",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Consume Gallery found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume Gallery found"),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="consume_name", type="string", example="Fried Rice"),
     *                          @OA\Property(property="consume_type", type="string", example="Food"),
     *                          @OA\Property(property="consume_from", type="string", example="Take Away"),
     *                          @OA\Property(property="is_favorite", type="number", example=0),
     *                          @OA\Property(property="created_at", type="string", example="2024-09-25T08:01:33.000000Z"),
     *                          @OA\Property(property="gallery_url", type="string", example="https://firebasestorage.googleapis.com"),
     *                          @OA\Property(property="gallery_desc", type="string", example="this is gallery"),
     *                      )
     *                  ),
     *              )
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
     *             @OA\Property(property="message", type="string", example="Consume Gallery not found")
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
    public function getAllMyGallery(Request $request){
        try{
            $user_id = $request->user()->id;

            $csl = Consume::selectRaw('consume_name, consume_type, consume_from, is_favorite, consume_gallery.created_at, gallery_url, gallery_desc')
                ->join('consume_gallery','consume_gallery.consume_id','=','consume.id')
                ->where('consume.created_by',$user_id)
                ->whereNull('deleted_at')
                ->orderby('consume.created_by','desc')
                ->paginate(14);

            if (count($csl) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'consume gallery'), 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'consume gallery'),
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
     *     path="/api/v1/consume/gallery/{slug}",
     *     summary="Get consume gallery by slug name consume",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume slug name",
     *         example="telur_rebus",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume Gallery found"
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
     *             @OA\Property(property="message", type="string", example="Consume Gallery not found")
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
    public function getGalleryByConsume(Request $request, $slug){
        try{
            $user_id = $request->user()->id;

            $csl = ConsumeGallery::select('consume_gallery.id','gallery_url', 'gallery_desc', 'consume_gallery.created_at')
                ->join('consume','consume_gallery.consume_id','=','consume.id')
                ->where('consume.created_by',$user_id)
                ->where('consume.slug_name',$slug)
                ->whereNull('deleted_at')
                ->orderby('consume.created_at','desc')
                ->paginate(14);

            if (count($csl) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'consume gallery'), 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'consume gallery'),
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
