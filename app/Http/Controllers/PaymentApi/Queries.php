<?php

namespace App\Http\Controllers\PaymentApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

// Models
use App\Models\Payment;

// Helpers
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/payment/total/month/{year}",
     *     summary="Get total spend monthly in a year",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume year from date created",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytic data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="analytic data fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="Jan"),
     *                          @OA\Property(property="total", type="integer", example=2)
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
     *         description="Analytic data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Analytic data not found")
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
    public function getTotalSpendMonth(Request $request, $year) {
        try {
            $user_id = $request->user()->id;

            $res = Payment::findAllMonthlyPayment($user_id, $year);
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'analytic data'), 
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/payment/total/month/{month}/year/{year}",
     *     summary="Get total spend monthly",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume month from date created",
     *         example="11",
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume year from date created",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytic data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="analytic data fetched"),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                          @OA\Property(property="context", type="string", example="2"),
     *                          @OA\Property(property="total", type="integer", example=2)
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
     *         description="Analytic data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Analytic data not found")
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
    public function getTotalSpendDay(Request $request, $month, $year) {
        try {
            $user_id = $request->user()->id;

            $res = Payment::findAllDailyPayment($user_id, $year, $month);
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'analytic data'), 
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/analytic/payment/month/{month}/year/{year}",
     *     summary="Get total spend monthly in a year analytic",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume month from date created",
     *         example="11",
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume year from date created",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Analytic data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="analytic data fetched"),
     *                 @OA\Property(property="data", type="object",
     *                      @OA\Property(property="average", type="integer", example=10000),
     *                      @OA\Property(property="min", type="integer", example=5000),
     *                      @OA\Property(property="max", type="integer", example=15000),
     *                      @OA\Property(property="total", type="integer", example=2)
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
     *         description="Analytic data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Analytic data not found")
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
    public function getAnalyticSpendMonth(Request $request, $month, $year) {
        try {
            $user_id = $request->user()->id;

            $res = Payment::getMonthlyPaymentStats($user_id, $year, $month);
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'analytic data'),
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/count/payment",
     *     summary="Get total payment in whole consume",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Analytic data found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="analytic data fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_days", type="integer", example=9),
     *                 @OA\Property(property="total_payment", type="integer", example=1750000),
     *             ),
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
     *         description="Analytic data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Analytic data not found")
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
    public function getLifetimeSpend(Request $request) {
        try {
            $user_id = $request->user()->id;

            $res = Payment::getLifeTimeSpend($user_id);
            return response()->json([
                'status' => 'success',
                'message' => Generator::getMessageTemplate("fetch", 'analytic data'), 
                'data' => $res
            ], Response::HTTP_OK);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/payment/detail/month/{month}/year/{year}",
     *     summary="Get total payment in a month and year",
     *     tags={"Payment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Payment month from date created",
     *         example="11",
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Payment year from date created",
     *         example="2024",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment data found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="payment fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="consume_name", type="string", example="Nasi Warteg (Tahu Kari, Sayur Jantung Pisang, Terong Sambal)"),
     *                         @OA\Property(property="consume_type", type="string", example="Food"),
     *                         @OA\Property(property="consume_id", type="string", example="33b162f6-a87a-138e-15d9-98951faa64ac"),
     *                         @OA\Property(property="payment_method", type="string", example="MBanking"),
     *                         @OA\Property(property="payment_price", type="integer", example=14000),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-19T09:28:02.000000Z")
     *                     )
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
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Payment not found")
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
    public function getMonthlySpend(Request $request, $month, $year) {
        try {
            $user_id = $request->user()->id;
            $paginate = $request->query('per_page_key') ?? null;

            $res = Payment::getAllMonthlySpend($user_id, $year, $month, $paginate);
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'payment'), 
                    'data' => $csm
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'payment'),
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
