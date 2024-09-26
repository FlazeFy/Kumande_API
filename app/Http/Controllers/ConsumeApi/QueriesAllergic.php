<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Allergic;
use App\Models\Consume;

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
     *         description="Allergic found"
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
                    'message' => "Allergic found", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Allergic not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
