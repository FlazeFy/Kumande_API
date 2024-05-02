<?php

namespace App\Http\Controllers\TagApi;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\Tag;

class Commands extends Controller
{
    public function deleteTagById(Request $request, $id){
        try{
            $user_id = $request->user()->id;

            $res = Tag::where('created_by',$user_id)
                ->where('id',$id)
                ->delete();
        
            if ($res) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Tag deleted", 
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Tag failed to deleted',
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
