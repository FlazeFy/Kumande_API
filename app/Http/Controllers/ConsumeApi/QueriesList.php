<?php

namespace App\Http\Controllers\ConsumeApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Models
use App\Models\ConsumeList;
use App\Models\Consume;
use App\Models\Payment;
use App\Models\RelConsumeList;

// Helpers
use App\Helpers\Generator;

class QueriesList extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/list/limit/{page_limit}/order/{order}",
     *     summary="Get all consume list with limit, pagination, and ordering",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page_limit",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Total of consume to show in each page",
     *         example="14",
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Sorting the consume",
     *         example="desc",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume List found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="consume list fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="c85a1fb8-5f72-1378-22a7-3a07adfcf4c4"),
     *                         @OA\Property(property="slug_name", type="string", example="asdasdzzzz"),
     *                         @OA\Property(property="list_name", type="string", example="asdasdzzzz"),
     *                         @OA\Property(property="list_desc", type="string", example="asdaaaaaa"),
     *                         @OA\Property(property="list_tag", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="slug_name", type="string", example="fast-food"),
     *                                 @OA\Property(property="tag_name", type="string", example="Fast Food")
     *                             )
     *                         ),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-05-08T09:49:53.000000Z"),
     *                         @OA\Property(property="consume", type="array", nullable=true,
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="string", example="33b162f6-a87a-138e-15d9-98951faa64ac"),
     *                                 @OA\Property(property="slug_name", type="string", example="nasi-warteg-tahu-kari-sayur-jantung-pisang-terong-sambal"),
     *                                 @OA\Property(property="consume_name", type="string", example="Nasi Warteg (Tahu Kari, Sayur Jantung Pisang, Terong Sambal)"),
     *                                 @OA\Property(property="consume_type", type="string", example="Food"),
     *                                 @OA\Property(property="calorie", type="integer", example=400),
     *                                 @OA\Property(property="provide", type="string", example="Warteg Basement 2 TLT"),
     *                                 @OA\Property(property="consume_from", type="string", example="Dine-In"),
     *                                 @OA\Property(property="average_price", type="integer", example=14000)
     *                             )
     *                         )
     *                     )
     *                 ),
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
     *         description="Consume List not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume List not found")
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
    public function getAllList(Request $request, $page_limit, $order){
        try{
            $user_id = $request->user()->id;

            $csl = ConsumeList::select('id','slug_name','list_name','list_desc','list_tag','created_at')
                ->orderBy('created_at', $order)
                ->where('created_by', $user_id)
                ->paginate($page_limit);

            if ($csl->count() > 0) {
                foreach($csl as $idx => $dt){
                    $csm = RelConsumeList::selectRaw("consume.id, consume.slug_name, consume_name, consume_type, CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned) as calorie, REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide, consume_from")
                        ->join('consume','consume.id','=','rel_consume_list.consume_id')
                        ->where('list_id',$dt->id)
                        ->get();
                    
                    foreach($csm as $jdx => $du){
                        $pyt = Payment::selectRaw('CAST(AVG(payment_price) as unsigned) as average_price')
                            ->where('consume_id', $du->id)
                            ->groupby('consume_id')
                            ->first();

                        if($pyt){
                            $csm[$jdx]->average_price = $pyt->average_price;
                        } else {
                            $csm[$jdx]->average_price = null;
                        }
                    }

                    if(count($csm) > 0){
                        $csl[$idx]->consume = $csm;
                    } else {
                        $csl[$idx]->consume = null;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "consume list"), 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "consume list"),
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
     *     path="/api/v1/list/detail/{list_id}",
     *     summary="Get consume list detail",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="list_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="List ID",
     *         example="272b7494-409a-a172-1bc3-ec1cec8f8400",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume List found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Consume List found"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="7b3b0912-bd6f-b4a5-01ab-365d46d12466"),
     *                 @OA\Property(property="slug_name", type="string", example="makanan-nusantara"),
     *                 @OA\Property(property="list_name", type="string", example="makanan"),
     *                 @OA\Property(property="list_desc", type="string", example="murah meriahasdasd"),
     *                 @OA\Property(property="list_tag", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="slug_name", type="string", example="cheap"),
     *                         @OA\Property(property="tag_name", type="string", example="Cheap")
     *                     )
     *                 ),
     *                 @OA\Property(property="created_at", type="string", example="2023-05-05T04:32:12.000000Z"),
     *                 @OA\Property(property="consume", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="consume_id", type="string", example="33b162f6-a87a-138e-15d9-98951faa64ac"),
     *                         @OA\Property(property="id", type="string", example="2d98f524-de02-11ed-b5ea-0242ac120002"),
     *                         @OA\Property(property="slug_name", type="string", example="nasi-warteg-tahu-kari-sayur-jantung-pisang-terong-sambal"),
     *                         @OA\Property(property="consume_name", type="string", example="Nasi Warteg (Tahu Kari, Sayur Jantung Pisang, Terong Sambal)"),
     *                         @OA\Property(property="consume_type", type="string", example="Food"),
     *                         @OA\Property(property="calorie", type="integer", example=400),
     *                         @OA\Property(property="provide", type="string", example="Warteg Basement 2 TLT"),
     *                         @OA\Property(property="consume_from", type="string", example="Dine-In"),
     *                         @OA\Property(property="average_price", type="integer", example=14000)
     *                     )
     *                 ),
     *                 @OA\Property(property="whole_avg_calorie", type="integer", example=336),
     *                 @OA\Property(property="whole_avg_price", type="integer", example=21080)
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
     *         description="Consume List not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume List not found")
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
    public function getListDetailById(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $csl = ConsumeList::select('id','slug_name','list_name','list_desc','list_tag','created_at')
                ->where('created_by', $user_id)
                ->where('id', $id)
                ->first();

            if ($csl) {
                $csm = RelConsumeList::selectRaw("consume.id as consume_id, rel_consume_list.id, consume.slug_name, consume_name, consume_type, CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned) as calorie, REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide, consume_from")
                    ->join('consume','consume.id','=','rel_consume_list.consume_id')
                    ->where('list_id',$csl->id)
                    ->get();
                
                foreach($csm as $jdx => $du){
                    $pyt = Payment::selectRaw('CAST(AVG(payment_price) as unsigned) as average_price')
                        ->where('consume_id', $du->consume_id)
                        ->groupby('consume_id')
                        ->first();

                    if($pyt){
                        $csm[$jdx]->average_price = $pyt->average_price;
                    } else {
                        $csm[$jdx]->average_price = null;
                    }
                }

                if(count($csm) > 0){
                    $csl->consume = $csm;

                    $whole_csm = Consume::selectRaw("AVG(CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned)) as average_calorie, AVG(payment_price) as average_price")
                        ->leftjoin('payment','payment.consume_id','=','consume.id')
                        ->first();

                    $csl->whole_avg_calorie = (int)$whole_csm->average_calorie;
                    $csl->whole_avg_price = (int)$whole_csm->average_price;
                } else {
                    $csl->whole_avg_calorie = 0;
                    $csl->whole_avg_price = 0;
                    $csl->consume = null;
                }

                return response()->json([
                    'status' => 'success',
                    'message' => "Consume List found", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume List not found',
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
     *     path="/api/v1/list/check/{consume_slug}/{list_id}",
     *     summary="Get consume calorie, provide, from, average price by slug",
     *     tags={"Consume"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="list_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="List ID",
     *         example="272b7494-409a-a172-1bc3-ec1cec8f8400",
     *     ),
     *     @OA\Parameter(
     *         name="consume_slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Consume slug name",
     *         example="telur_rebus",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consume found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="consume fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="consume_name", type="string", example="Nasi padang"),
     *                 @OA\Property(property="consume_from", type="string", example="Dine-In"),
     *                 @OA\Property(property="calorie", type="string", example="120"),
     *                 @OA\Property(property="provide", type="string", example="Warung"),
     *                 @OA\Property(property="average_price", type="string", example="18000")
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
     *         description="Consume not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Consume has been used in this list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Consume not found")
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
    public function getCheckConsumeBySlug(Request $request, $consume_slug, $list_id){
        try{
            $user_id = $request->user()->id;

            $check = RelConsumeList::selectRaw('1')
                ->join('consume','consume.id','=','rel_consume_list.consume_id')
                ->where('slug_name',$consume_slug)
                ->where('list_id',$list_id)
                ->first();

            if($check){
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("conflict", 'consume'),
                ], Response::HTTP_CONFLICT);
            } else {
                $csl = Consume::selectRaw("consume_name,consume_from,CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as UNSIGNED) as calorie, REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide,
                    CAST(COALESCE(CAST(AVG(payment_price) as UNSIGNED), 0) as UNSIGNED) as average_price")
                    ->leftjoin('payment','payment.consume_id','=','consume.id')
                    ->where('consume.created_by', $user_id)
                    ->where('slug_name', $consume_slug)
                    ->first();

                if ($csl) {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("fetch", 'consume'), 
                        'data' => $csl
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'consume'),
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null)
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
