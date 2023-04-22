<?php

namespace App\Http\Controllers\ConsumeApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\ConsumeList;

class QueriesList extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getAllList(Request $request, $page_limit, $order){
        try{
            $user_id = $request->user()->id;

            $csl = ConsumeList::select('*')
                ->orderBy('created_at', $order)
                ->paginate($page_limit);

            if ($csl->count() > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => count($csl)." Data retrived", 
                    'data' => $csl
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Consume List not found',
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
