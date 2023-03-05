<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Generator;

use App\Models\ConsumeList;

class ConsumeListController extends Controller
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

    public function getAllList($page_limit, $order){
        $csl = ConsumeList::select('*')
            ->orderBy('created_at', $order)
            ->paginate($page_limit);
    
        return response()->json([
            "msg"=> count($csl)." Data retrived", 
            "status"=> 200,
            "data"=> $csl
        ]);
    }

    public function deleteListById($id){
        ConsumeList::where('id', $id)->delete();

        return response()->json([
            "msg"=> "Data deleted", 
            "status"=> 200
        ]);
    }

    public function updateListData(Request $request, $id){
        $csl = ConsumeList::where('id', $id)->update([
            'list_name' => $request->list_name,
            'list_desc' => $request->list_desc,
            'list_tag' => $request->list_tag,
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully updated',
            'result' => $csl
        ]);
    }

    public function createList(Request $request){
        $firstCode = Generator::getFirstCode("list");
        $secondCode = Generator::getDateCode();
        $thirdCode = Generator::getInitialCode($request->list_name);
        
        $getFinalCode = $firstCode."-".$secondCode."-".$thirdCode;

        $csl = ConsumeList::create([
            'list_code' => $getFinalCode,
            'list_name' => $request->list_name,
            'list_desc' => $request->list_desc,
            'list_tag' => $request->list_tag,
            'created_at' => date("Y-m-d h:i:s"),
            'updated_at' => date("Y-m-d h:i:s")
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data successfully created',
            'result' => $csl
        ]);
    }
}
