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
}
