<?php

namespace App\Http\Controllers\UserApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Helpers
use App\Helpers\Generator;
use App\Helpers\Math;

// Models
use App\Models\BodyInfo;
use App\Models\CountCalorie;

class QueriesBodyInfo extends Controller
{
     /**
     * @OA\GET(
     *     path="/api/v1/user/body_info",
     *     summary="Get my body info (Medstory)",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User body info found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="body info fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="blood_pressure", type="string", example="126/90"),
     *                 @OA\Property(property="blood_glucose", type="integer", example=82),
     *                 @OA\Property(property="gout", type="number", format="float", example=5.8),
     *                 @OA\Property(property="cholesterol", type="integer", example=178),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-10 09:56:46"),
     *                 @OA\Property(property="gender", type="string", example="male"),
     *                 @OA\Property(property="weight", type="integer", example=62),
     *                 @OA\Property(property="height", type="integer", example=182),
     *                 @OA\Property(property="result", type="integer", example=1800),
     *                 @OA\Property(property="calorie_updated", type="string", format="date-time", example="2024-07-30 23:56:40"),
     *                 @OA\Property(property="born_at", type="string", format="date", example="2001-08-08"),
     *                 @OA\Property(property="age", type="integer", example=23),
     *                 @OA\Property(property="bmi", type="number", format="float", example=18.72)
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
     *         description="User body info not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User body info not found")
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
    public function getMyLatestBodyInfo(Request $request) {
        try {
            $user_id = $request->user()->id;

            $bodyInfo = BodyInfo::findLatestBodyInfo($user_id);
            $calorie = CountCalorie::findLatestCountCalorie($user_id);
            $user = User::getProfile($user_id);
            if ($calorie) $calorie->bmi = Math::countBMI($user->gender, $calorie->height, $calorie->weight);

            if ($bodyInfo || $calorie) {
                $res = array_merge(
                    $bodyInfo ? $bodyInfo->toArray() : [],
                    $calorie ? $calorie->toArray() : []
                );

                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $res
                ], Response::HTTP_OK);
            } elseif ($usr) {
                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $usr
                ], Response::HTTP_OK);
            } elseif ($cal) {
                return response()->json([
                    "message" => Generator::getMessageTemplate("fetch", 'body info'),
                    "status" => 'success',
                    "data" => $cal
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => Generator::getMessageTemplate("not_found", 'body info'),
                    "status" => 'failed',
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
     *     path="/api/v1/user/my_body_history",
     *     summary="Get all my body info (Medstory) & Calorie data",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User body history found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="body history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="body_info", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="0264b734-3e92-11ef-8fb5-3216422910e9"),
     *                         @OA\Property(property="blood_pressure", type="string", example="126/90"),
     *                         @OA\Property(property="blood_glucose", type="integer", example=82),
     *                         @OA\Property(property="gout", type="number", format="float", example=5.8),
     *                         @OA\Property(property="cholesterol", type="integer", example=178),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-10 09:56:46")
     *                     )
     *                 ),
     *                 @OA\Property(property="calorie", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="83e64f09-e15f-f1a1-0e97-eafa226008db"),
     *                         @OA\Property(property="weight", type="integer", example=62),
     *                         @OA\Property(property="height", type="integer", example=182),
     *                         @OA\Property(property="result", type="integer", example=1800),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-30 23:56:40")
     *                     )
     *                 ),
     *                 @OA\Property(property="dashboard", type="object",
     *                     @OA\Property(property="max_blood_glucose", type="integer", example=82),
     *                     @OA\Property(property="min_blood_glucose", type="integer", example=82),
     *                     @OA\Property(property="max_gout", type="number", format="float", example=5.8),
     *                     @OA\Property(property="min_gout", type="number", format="float", example=5.8),
     *                     @OA\Property(property="max_cholesterol", type="integer", example=178),
     *                     @OA\Property(property="min_cholesterol", type="integer", example=178),
     *                     @OA\Property(property="max_weight", type="integer", example=62),
     *                     @OA\Property(property="min_weight", type="integer", example=62),
     *                     @OA\Property(property="max_height", type="integer", example=182),
     *                     @OA\Property(property="min_height", type="integer", example=182)
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
     *         description="User body history not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="User body history not found")
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
    public static function getMyBodyHistory(Request $request) {
        try {
            $user_id = $request->user()->id;

            $bodyInfos = BodyInfo::findAllBodyInfo($user_id);
            $cals = CountCalorie::findAllCountCalorie($user_id);
            $dashboard = BodyInfo::findAllMaxMinBodyInfo($user_id);
            if ($usr || $cal) {
                return response()->json([
                    "data" => (object)[
                        "body_info" => $bodyInfos,
                        "calorie" => $cals,
                        "max_min_body_info" => $dashboard
                    ],
                    "message" => Generator::getMessageTemplate("fetch", 'body history'),
                    "status" => 'success'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    "message" => Generator::getMessageTemplate("not_found", 'body history'),
                    "status" => 'failed',
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
