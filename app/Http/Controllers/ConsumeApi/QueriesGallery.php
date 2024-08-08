<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeGallery;
use App\Models\Consume;

class QueriesGallery extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/consume/gallery",
     *     summary="Get all consume gallery",
     *     tags={"Consume"},
     *     @OA\Response(
     *         response=200,
     *         description="Consume Gallery found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Consume Gallery not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
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
                    'message' => "Consume Gallery found", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume Gallery not found',
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
     *     path="/api/v1/consume/gallery/{slug}",
     *     summary="Get consume gallery by slug name consume",
     *     tags={"Consume"},
     *     @OA\Response(
     *         response=200,
     *         description="Consume Gallery found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Consume Gallery not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
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
                    'message' => "Consume Gallery found", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume Gallery not found',
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
