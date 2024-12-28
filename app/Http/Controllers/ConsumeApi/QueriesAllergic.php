<?php

namespace App\Http\Controllers\ConsumeApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Models
use App\Models\Allergic;
use App\Models\Consume;

// Helpers
use App\Helpers\Generator;

class QueriesAllergic extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/analytic/allergic",
     *     summary="Get my allergic",
     *     tags={"Analytic"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Allergic found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Allergic found"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="id", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                          @OA\Property(property="allergic_context", type="string", example="rice"),
     *                          @OA\Property(property="allergic_desc", type="string", example="rice"),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-19 02:37:58"),
     *                          @OA\Property(property="detected_on", type="array", 
     *                              @OA\Items(
     *                                  @OA\Property(property="consume_name", type="string", example="Fried Rice"),
     *                                  @OA\Property(property="consume_type", type="string", example="Food"),
     *                                  @OA\Property(property="slug_name", type="string", example="fried_rice"),
     *                             
     *                          )
     *                     ),
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
     *         description="Allergic not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Allergic not found")
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
    public function getAllAllergic(Request $request){
        try{
            $user_id = $request->user()->id;

            $csl = Allergic::select('id', 'allergic_context', 'created_at', 'allergic_desc')
                ->orderBy('created_at', 'DESC')
                ->where('created_by', $user_id)
                ->get();

            if ($csl->count() > 0) {
                foreach($csl as $idx => $dt){
                    $csm = Consume::select("consume_name","consume_type","slug_name")
                        ->where('created_by', $user_id)
                        ->where('consume_name', 'LIKE',"%$dt->allergic_context%")
                        ->get();

                    if(count($csm) > 0){
                        $csl[$idx]->detected_on = $csm;
                    } else {
                        $csl[$idx]->detected_on = null;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'allergic'), 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'allergic'),
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
