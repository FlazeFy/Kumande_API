<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Schedule;

class ScheduleController extends Controller
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

    public function getAllSchedule($page_limit, $order){
        $sch = Schedule::select('*')
            ->orderBy('created_at', $order)
            ->paginate($page_limit);
    
        return response()->json([
            "msg"=> count($sch)." Data retrived", 
            "status"=> 200,
            "data"=> $sch
        ]);
    }
}
