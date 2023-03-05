<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        function getFirstCode(){
            $randChar = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            $check = ConsumeList::select('list_code')
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get();

            foreach($check as $ck){
                $before_alph = substr($ck->list_code,0,2);
                $before_num = substr($ck->list_code,2,1);

                if($before_num < 9){
                    $after_num = (int)$before_num + 1;
                    $after_alph = $before_alph;
                } else {
                    $after_num = 0;
                    $after_alph = substr(str_shuffle(str_repeat($randChar, 5)), 0, 2);
                }
            }            

            return $after_alph.$after_num;
        }

        function getSecondCode(){
            $now = date("myd");
            
            return $now;
        }

        function getThirdCode($name){
            $id = strtoupper(substr($name, 0,1));

            return $id;
        }

        $getFinalCode = getFirstCode()."-".getSecondCode()."-".getThirdCode($request->list_name);

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
